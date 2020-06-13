<?php

namespace App\Http\Controllers;

use App\Mail\OfflineOrderMail;
use App\Models\Bundle;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\Tax;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Cart;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PHPUnit\Framework\Constraint\Count;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Stripe;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class CartController extends Controller
{

    private $path;
    private $currency;

    public function __construct()
    {
        /** PayPal api context **/
        $paypal_conf = \Config::get('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential(
                $paypal_conf['client_id'],
                $paypal_conf['secret'])
        );
        $this->_api_context->setConfig($paypal_conf['settings']);

        $path = 'frontend';
        if (session()->has('display_type')) {
            if (session('display_type') == 'rtl') {
                $path = 'frontend-rtl';
            } else {
                $path = 'frontend';
            }
        } else if (config('app.display_type') == 'rtl') {
            $path = 'frontend-rtl';
        }
        $this->path = $path;
        $this->currency = getCurrency(config('app.currency'));


    }

    public function index(Request $request)
    {
        $ids = Cart::session(auth()->user()->id)->getContent()->keys();
        $course_ids = [];
        $bundle_ids = [];
        foreach (Cart::session(auth()->user()->id)->getContent() as $item) {
            if ($item->attributes->type == 'bundle') {
                $bundle_ids[] = $item->id;
            } else {
                $course_ids[] = $item->id;
            }
        }
        $courses = new Collection(Course::find($course_ids));
        $bundles = Bundle::find($bundle_ids);
        $courses = $bundles->merge($courses);

        $total = $courses->sum('price');
        //Apply Tax
        $taxData = $this->applyTax('total');
        $offline='';
        if(auth()->check() && !auth()->guard('vendor')->check() && isset(auth()->user()->vendor->invoicing)) {
            $offline=auth()->user()->vendor->invoicing;
        }
        if(auth()->guard('vendor')->check()) {
            $offline=auth()->guard('vendor')->user()->invoicing;
        }

        return view($this->path . '.cart.checkout', compact('courses', 'bundles','total','taxData','offline'));
    }

    public function addToCart(Request $request)
    {
        $product = "";
        $Supervisors = "";
        $type = "";
        if ($request->has('course_id')) {
            $product = Course::findOrFail($request->get('course_id'));
            $Supervisors = $product->Supervisors->pluck('id', 'name');
            $type = 'course';

        } elseif ($request->has('bundle_id')) {
            $product = Bundle::findOrFail($request->get('bundle_id'));
            $Supervisors = $product->user->name;
            $type = 'bundle';
        }

        $cart_items = Cart::session(auth()->user()->id)->getContent()->keys()->toArray();
        if (!in_array($product->id, $cart_items)) {
            Cart::session(auth()->user()->id)
                ->add($product->id, $product->title, $product->price, 1,
                    [
                        'user_id' => auth()->user()->id,
                        'description' => $product->description,
                        'image' => $product->course_image,
                        'type' => $type,
                        'Supervisors' => $Supervisors
                    ]);
        }


        Session::flash('success', trans('labels.frontend.cart.product_added'));
        return back();
    }

    public function checkout(Request $request)
    {
        $product = "";
        $Supervisors = "";
        $type = "";
        $bundle_ids = [];
        $course_ids = [];
        if ($request->has('course_id')) {
            $product = Course::findOrFail($request->get('course_id'));
            $Supervisors = $product->Supervisors->pluck('id', 'name');
            $type = 'course';

        } elseif ($request->has('bundle_id')) {
            $product = Bundle::findOrFail($request->get('bundle_id'));
            $Supervisors = $product->user->name;
            $type = 'bundle';
        }

        $cart_items = Cart::session(auth()->user()->id)->getContent()->keys()->toArray();
        if (!in_array($product->id, $cart_items)) {

            Cart::session(auth()->user()->id)
                ->add($product->id, $product->title, $product->price, 1,
                    [
                        'user_id' => auth()->user()->id,
                        'description' => $product->description,
                        'image' => $product->course_image,
                        'type' => $type,
                        'Supervisors' => $Supervisors
                    ]);
        }
        foreach (Cart::session(auth()->user()->id)->getContent() as $item) {
            if ($item->attributes->type == 'bundle') {
                $bundle_ids[] = $item->id;
            } else {
                $course_ids[] = $item->id;
            }
        }
        $courses = new Collection(Course::find($course_ids));
        $bundles = Bundle::find($bundle_ids);
        $courses = $bundles->merge($courses);

        $total = $courses->sum('price');

        //Apply Tax
        $taxData = $this->applyTax('total');
        $offline=auth()->user()->vendor->invoicing;

        return view($this->path . '.cart.checkout', compact('courses','total','taxData','offline'));
    }

    public function clear(Request $request)
    {
        Cart::session(auth()->user()->id)->clear();
        return back();
    }

    public function remove(Request $request)
    {
        Cart::session(auth()->user()->id)->removeConditionsByType('coupon');


        if(Cart::session(auth()->user()->id)->getContent()->count() < 2){
            Cart::session(auth()->user()->id)->clearCartConditions();
            Cart::session(auth()->user()->id)->removeConditionsByType('tax');
            Cart::session(auth()->user()->id)->removeConditionsByType('coupon');
            Cart::session(auth()->user()->id)->clear();
        }
        Cart::session(auth()->user()->id)->remove($request->course);
        return redirect(route('cart.index'));
    }

    public function stripePayment(Request $request)
    {

        //Making Order
        $order = $this->makeOrder();

        //Charging Customer
        $status = $this->createStripeCharge($request);

        if ($status == 'success') {
            $order->status = 1;
            $order->payment_type = 1;
            $order->save();
            foreach ($order->items as $orderItem) {
                //Bundle Entries
                if ($orderItem->item_type == Bundle::class) {
                    foreach ($orderItem->item->courses as $course) {
                        $course->students()->attach($order->user_id);
                    }
                }
                $orderItem->item->students()->attach($order->user_id);
            }

            //Generating Invoice
            generateInvoice($order);

            Cart::session(auth()->user()->id)->clear();
            return redirect()->route('status');

        } else {
            $order->status = 2;
            $order->save();
            return redirect()->route('cart.index');
        }
    }

    public function authorizePayment(Request $request)
    {
        if(empty($request->cname) || empty($request->cnumber) || empty($request->ccode)|| empty($request->card_expiry_year) || empty($request->card_expiry_month)) {
            \Session::put('error', 'Please Fill all the payment fields');
            return redirect()->back();
        }
 
    //Making Order
    $order = $this->makeOrder();
    $order->status = 0;
    $order->payment_type = 4;
    $order->save();
    $cartTotal = Cart::session(auth()->user()->id)->getTotal();
    $currency = $this->currency['short_code'];
    $transaction_id='';
    $payment=false;
    $success_msg='';
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(config('services.authorize.login'));
        $merchantAuthentication->setTransactionKey(config('services.authorize.key'));
        $refId = 'ref'.time();
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($request->cnumber);
        $expiry=$request->card_expiry_year.'-'.$request->card_expiry_month;
        $creditCard->setExpirationDate($expiry);
        $paymentOne=new AnetAPI\PaymentType();
        $paymentOne->setCreditCard($creditCard);

        $billFrom = new AnetAPI\CustomerAddressType();
        $billFrom->setFirstName(auth()->user()->first_name);
        $billFrom->setLastName(auth()->user()->last_name);
        $billFrom->setCompany(env('APP_NAME'));
        $billFrom->setAddress(auth()->user()->address);
        $billFrom->setCity(auth()->user()->city);
        $billFrom->setEmail(auth()->user()->email);
        $billFrom->setState(auth()->user()->state);
        $billFrom->setCountry(auth()->user()->country);
        $billFrom->setZip('');
        $billFrom->setPhoneNumber(auth()->user()->phone);
        $billFrom->setfaxNumber(auth()->user()->phone);

        // // Create a customer shipping address
        $customerShippingAddress = new AnetAPI\CustomerAddressType();
        $customerShippingAddress->setFirstName(auth()->user()->first_name);
        $customerShippingAddress->setLastName(auth()->user()->last_name);
        $customerShippingAddress->setCompany(env('APP_NAME'));
        $customerShippingAddress->setAddress(auth()->user()->address);
        $customerShippingAddress->setCity(auth()->user()->city);
        $customerShippingAddress->setState(auth()->user()->state);
        $customerShippingAddress->setZip('');
        $customerShippingAddress->setCountry(auth()->user()->country);
        $customerShippingAddress->setPhoneNumber(auth()->user()->phone);
        $customerShippingAddress->setFaxNumber(auth()->user()->phone);

        // Create an array of any shipping addresses
        $shippingProfiles[] = $customerShippingAddress;


        // Create a new CustomerPaymentProfile object
        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setCustomerType('individual');
        $paymentProfile->setBillTo($billFrom);
        $paymentProfile->setPayment($paymentOne);
        $paymentProfiles[] = $paymentProfile;

        // Create a new CustomerProfileType and add the payment profile object
        $customerProfile = new AnetAPI\CustomerProfileType();
        $customerProfile->setDescription("Payment for Course");
        $customerProfile->setMerchantCustomerId("M_" . time());
        $customerProfile->setEmail(auth()->user()->email);
        $customerProfile->setpaymentProfiles($paymentProfiles);
        $customerProfile->setShipToList($shippingProfiles);
        // Assemble the complete transaction request
        $request1 = new AnetAPI\CreateCustomerProfileRequest();
        $request1->setMerchantAuthentication($merchantAuthentication);
        $request1->setRefId($refId);
        $request1->setProfile($customerProfile);

        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId(auth()->user()->id);
        $customerData->setEmail(auth()->user()->email);

        $customershipp = new AnetAPI\NameAndAddressType();
        $customershipp->setFirstName(auth()->user()->first_name);
        $customershipp->setLastName(auth()->user()->last_name);
        $customershipp->setCompany(env('APP_NAME'));
        $customershipp->setAddress(auth()->user()->address);
        $customershipp->setCity(auth()->user()->city);
        $customershipp->setState(auth()->user()->state);
        $customershipp->setZip('');
        $customershipp->setCountry(auth()->user()->country);


        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($cartTotal);
        $transactionRequestType->setBillTo($billFrom);
        $transactionRequestType->setCustomer($customerData);
        $transactionRequestType->setPayment($paymentOne);
        $request2 = new AnetAPI\CreateTransactionRequest();
        $request2->setMerchantAuthentication($merchantAuthentication);
        $request2->setRefId( $refId);
        $request2->setTransactionRequest($transactionRequestType);
        $controller = new AnetController\CreateTransactionController($request2);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        if ($response != null)
        {
          $tresponse = $response->getTransactionResponse();
          // dd($tresponse);
          if (($tresponse != null) && ($tresponse->getResponseCode()=="1"))
          {
            $payment=true;
            $transaction_id=$tresponse->getTransId();
            if(isset($tresponse->getmessages()[0]) && !empty($tresponse->getmessages()[0]->getdescription())){
                $success_msg=$tresponse->getmessages()[0]->getdescription();
            } else {
                $success_msg='Payment successfully paid.';
            }
          }
          else
          {
            if(!empty($tresponse) && isset($tresponse->geterrors()[0]) && !empty($tresponse->geterrors()[0]->geterrorText())) {
                $msg=$tresponse->geterrors()[0]->geterrorText();
            } else {
                $msg='Payment Failed! Credit Card ERROR: Invalid response.';
            }
            $order->status = 2;
            $order->payment_type = 4;
            $order->save();
            return redirect()->route('status')->with('error',$msg);
          }
        }
        else
        {
            $order->status = 2;
            $order->payment_type = 4;
            $order->save();
            return redirect()->route('status')->with('error','Payment Failed! Credit Card ERROR: Charge Credit Card Null response returned.');
        }
    if ($payment) {
        $order->status = 1;
        $order->payment_type = 4;
        $order->save();
        $courses=array();
        foreach ($order->items as $orderItem) {
            //Bundle Entries
            if ($orderItem->item_type == Bundle::class) {
                foreach ($orderItem->item->courses as $course) {
                    array_push($courses, $course);
                    $course->students()->attach($order->user_id);
                }
            }

            if ($orderItem->item_type == Course::class) {
                $course=Course::findOrFail($orderItem->item_id);
                array_push($courses, $course);
            }
            $orderItem->item->students()->attach($order->user_id);
        }

        //Generating Invoice
        generateInvoice($order);

        Cart::session(auth()->user()->id)->clear();

        return redirect()->route('status')->with('courses',$courses)->with('success',$success_msg);

    } else {
            $order->status = 2;
            $order->save();
            return redirect()->route('cart.index');
        }
    }

    public function paypalPayment(Request $request)
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $items = [];

        $cartItems = Cart::session(auth()->user()->id)->getContent();
        $cartTotal = Cart::session(auth()->user()->id)->getTotal();
        $currency = $this->currency['short_code'];

        foreach ($cartItems as $cartItem) {

            $item_1 = new Item();
            $item_1->setName($cartItem->name)/** item name **/
            ->setCurrency($currency)
                ->setQuantity(1)
                ->setPrice($cartItem->price);
            /** unit price **/
            $items[] = $item_1;
        }

        $item_list = new ItemList();
        $item_list->setItems($items);

        $amount = new Amount();
        $amount->setCurrency($currency)
            ->setTotal($cartTotal);


        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription(auth()->user()->name);

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(URL::route('cart.paypal.status'))/** Specify return URL **/
        ->setCancelUrl(URL::route('cart.paypal.status'));
        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));
        /** dd($payment->create($this->_api_context));exit; **/
        try {
            $payment->create($this->_api_context);
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            if (\Config::get('app.debug')) {
                \Session::put('failure', trans('labels.frontend.cart.connection_timeout'));
                return Redirect::route('cart.paypal.status');
            } else {
                \Session::put('failure', trans('labels.frontend.cart.unknown_error'));
                return Redirect::route('cart.paypal.status');
            }
        }

        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() == 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }
        /** add payment ID to session **/
        Session::put('paypal_payment_id', $payment->getId());
        if (isset($redirect_url)) {
            /** redirect to paypal **/
            return Redirect::away($redirect_url);
        }
        \Session::put('failure', trans('labels.frontend.cart.unknown_error'));
        return Redirect::route('cart.paypal.status');
    }

    public function offlinePayment(Request $request)
    {
        //Making Order
        $order = $this->makeOrder();
        $order->payment_type = 3;
        $order->status = 0;
        $order->save();
        $content = [];
        $items = [];
        $counter = 0;
        foreach (Cart::session(auth()->user()->id)->getContent() as $key => $cartItem) {
            $counter++;
            array_push($items, ['number' => $counter, 'name' => $cartItem->name, 'price' => $cartItem->price]);
        }

        $content['items'] = $items;
        $content['total'] = Cart::session(auth()->user()->id)->getTotal();
        $content['reference_no'] = $order->reference_no;

        try {
            \Mail::to(auth()->user()->email)->send(new OfflineOrderMail($content));
        } catch (\Exception $e) {
            \Log::info($e->getMessage() . ' for order ' . $order->id);
        }

        Cart::session(auth()->user()->id)->clear();
        \Session::flash('success', trans('labels.frontend.cart.offline_request'));
        return redirect()->route('courses.all');
    }

    public function getPaymentStatus()
    {
        /** Get the payment ID before session clear **/
        $payment_id = Session::get('paypal_payment_id');
        /** clear the session payment ID **/
        Session::forget('paypal_payment_id');
        if (empty(Input::get('PayerID')) || empty(Input::get('token'))) {
            \Session::put('failure', trans('labels.frontend.cart.payment_failed'));
            return Redirect::route('status');
        }
        $payment = Payment::get($payment_id, $this->_api_context);
        $order = $this->makeOrder();
        $order->payment_type = 2;
        $order->save();
        $execution = new PaymentExecution();
        $execution->setPayerId(Input::get('PayerID'));
        /**Execute the payment **/
        $result = $payment->execute($execution, $this->_api_context);
        if ($result->getState() == 'approved') {
            \Session::flash('success', trans('labels.frontend.cart.payment_done'));
            $order->status = 1;
            $order->save();
            foreach ($order->items as $orderItem) {
                //Bundle Entries
                if ($orderItem->item_type == Bundle::class) {
                    foreach ($orderItem->item->courses as $course) {
                        $course->students()->attach($order->user_id);
                    }
                }
                $orderItem->item->students()->attach($order->user_id);
            }

            //Generating Invoice
            generateInvoice($order);
            Cart::session(auth()->user()->id)->clear();
            return Redirect::route('status');
        } else {
            \Session::flash('failure', trans('labels.frontend.cart.payment_failed'));
            $order->status = 2;
            $order->save();
            return Redirect::route('status');
        }

    }


    public function getNow(Request $request)
    {
        $order = new Order();
        $order->user_id = auth()->user()->id;
        $order->reference_no = str_random(8);
        $order->amount = 0;
        $order->status = 1;
        $order->payment_type = 0;
        $order->save();
        //Getting and Adding items
        if ($request->course_id) {
            $type = Course::class;
            $id = $request->course_id;
        } else {
            $type = Bundle::class;
            $id = $request->bundle_id;

        }
        $order->items()->create([
            'item_id' => $id,
            'item_type' => $type,
            'price' => 0
        ]);

        foreach ($order->items as $orderItem) {
            //Bundle Entries
            if ($orderItem->item_type == Bundle::class) {
                foreach ($orderItem->item->courses as $course) {
                    $course->students()->attach($order->user_id);
                }
            }
            $orderItem->item->students()->attach($order->user_id);
        }
        Session::flash('success', trans('labels.frontend.cart.purchase_successful'));
        return back();

    }

    public function getOffers()
    {
        $coupons = Coupon::where('status', '=', 1)->get();
        return view('frontend.cart.offers', compact('coupons'));
    }

    public function applyCoupon(Request $request)
    {
        Cart::session(auth()->user()->id)->removeConditionsByType('coupon');

        $coupon = $request->coupon;
        $coupon = Coupon::where('code', '=', $coupon)
            ->where('status', '=', 1)
            ->first();
        if ($coupon != null) {
            Cart::session(auth()->user()->id)->clearCartConditions();
            Cart::session(auth()->user()->id)->removeConditionsByType('coupon');
            Cart::session(auth()->user()->id)->removeConditionsByType('tax');

            $ids = Cart::session(auth()->user()->id)->getContent()->keys();
            $course_ids = [];
            $bundle_ids = [];
            foreach (Cart::session(auth()->user()->id)->getContent() as $item) {
                if ($item->attributes->type == 'bundle') {
                    $bundle_ids[] = $item->id;
                } else {
                    $course_ids[] = $item->id;
                }
            }
            $courses = new Collection(Course::find($course_ids));
            $bundles = Bundle::find($bundle_ids);
            $courses = $bundles->merge($courses);

            $total = $courses->sum('price');
            $isCouponValid = false;

            if($coupon->per_user_limit > $coupon->useByUser()){
                $isCouponValid = true;
                if(($coupon->min_price != null) && ($coupon->min_price > 0)){
                    if($total >= $coupon->min_price){
                        $isCouponValid = true;
                    }
                }else{
                    $isCouponValid = true;
                }
            }
            if($coupon->expires_at != null){
                if(Carbon::parse($coupon->expires_at) >= Carbon::now()){
                    $isCouponValid = true;
                }else{
                    $isCouponValid = false;
                }
            }


            if($isCouponValid == true){
                $type = null;
                if($coupon->type == 1){
                    $type = '-'.$coupon->amount.'%';
                }else{
                    $type = '-'.$coupon->amount;
                }

                $condition = new \Darryldecode\Cart\CartCondition(array(
                    'name' => $coupon->code,
                    'type' => 'coupon',
                    'target' => 'total', // this condition will be applied to cart's subtotal when getSubTotal() is called.
                    'value' => $type,
                    'order' => 1
                ));

                Cart::session(auth()->user()->id)->condition($condition);
                //Apply Tax
                $taxData = $this->applyTax('subtotal');

                $html = view('frontend.cart.partials.order-stats',compact('total','taxData'))->render();
                return ['status' => 'success', 'html' => $html];
            }


        }
        return ['status' => 'fail', 'message' => trans('labels.frontend.cart.invalid_coupon')];
    }


    public function removeCoupon(Request $request){

        Cart::session(auth()->user()->id)->clearCartConditions();
        Cart::session(auth()->user()->id)->removeConditionsByType('coupon');
        Cart::session(auth()->user()->id)->removeConditionsByType('tax');

        $course_ids = [];
        $bundle_ids = [];
        foreach (Cart::session(auth()->user()->id)->getContent() as $item) {
            if ($item->attributes->type == 'bundle') {
                $bundle_ids[] = $item->id;
            } else {
                $course_ids[] = $item->id;
            }
        }
        $courses = new Collection(Course::find($course_ids));
        $bundles = Bundle::find($bundle_ids);
        $courses = $bundles->merge($courses);

        $total = $courses->sum('price');

        //Apply Tax
        $taxData = $this->applyTax('subtotal');

        $html = view('frontend.cart.partials.order-stats',compact('total','taxData'))->render();
        return ['status' => 'success', 'html' => $html];

    }


    private function makeOrder()
    {
       $coupon = Cart::session(auth()->user()->id)->getConditionsByType('coupon')->first();
       if($coupon != null){
           $coupon = Coupon::where('code','=',$coupon->getName())->first();
       }

        $order = new Order();
        $order->user_id = auth()->user()->id;
        $order->reference_no = str_random(8);
        $order->amount = Cart::session(auth()->user()->id)->getTotal();
        $order->status = 1;
        $order->coupon_id = ($coupon == null) ? 0 : $coupon->id;
        $order->payment_type = 3;
        $order->save();
        //Getting and Adding items
        foreach (Cart::session(auth()->user()->id)->getContent() as $cartItem) {
            if ($cartItem->attributes->type == 'bundle') {
                $type = Bundle::class;
            } else {
                $type = Course::class;
            }
            $order->items()->create([
                'item_id' => $cartItem->id,
                'item_type' => $type,
                'price' => $cartItem->price
            ]);
        }
        Cart::session(auth()->user()->id)->removeConditionsByType('coupon');
        return $order;
    }

    private function createStripeCharge($request)
    {
        $status = "";
        Stripe::setApiKey(config('services.stripe.secret'));
        $amount = Cart::session(auth()->user()->id)->getTotal();
        $currency = $this->currency['short_code'];
        try {
            Charge::create(array(
                "amount" => $amount * 100,
                "currency" => strtolower($currency),
                "source" => $request->reservation['stripe_token'], // obtained with Stripe.js
                "description" => auth()->user()->name
            ));
            $status = "success";
            Session::flash('success', trans('labels.frontend.cart.payment_done'));
        } catch (\Exception $e) {
            \Log::info($e->getMessage() . ' for id = ' . auth()->user()->id);
            Session::flash('failure', trans('labels.frontend.cart.try_again'));
            $status = "failure";
        }
        return $status;
    }

    private function applyTax($target)
    {
        //Apply Conditions on Cart
        $taxes = Tax::where('status', '=', 1)->get();
        Cart::session(auth()->user()->id)->removeConditionsByType('tax');
        if ($taxes != null) {
            $taxData = [];
            foreach ($taxes as $tax){
                $total = Cart::session(auth()->user()->id)->getTotal();
                $taxData[] = ['name'=> '+'.$tax->rate.'% '.$tax->name,'amount'=> $total*$tax->rate/100 ];
            }

            $condition = new \Darryldecode\Cart\CartCondition(array(
                'name' => 'Tax',
                'type' => 'tax',
                'target' => 'total', // this condition will be applied to cart's subtotal when getSubTotal() is called.
                'value' => $taxes->sum('rate') .'%',
                'order' => 2
            ));
            Cart::session(auth()->user()->id)->condition($condition);
            return $taxData;
        }
    }

    //testing function
    public function chargeCreditCard(Request $request)
    {
        // Common setup for API credentials
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(config('services.authorize.login'));
        $merchantAuthentication->setTransactionKey(config('services.authorize.key'));
        $refId = 'ref'.time();

// Create the payment data for a credit card
          $creditCard = new AnetAPI\CreditCardType();
          $creditCard->setCardNumber("4242424242424242");
          $creditCard->setExpirationDate( "2038-12");
          //$expiry = $request->card_expiry_year . '-' . $request->card_expiry_month;
          //$creditCard->setExpirationDate($expiry);
          $paymentOne = new AnetAPI\PaymentType();
          $paymentOne->setCreditCard($creditCard);

// Create a transaction
          $transactionRequestType = new AnetAPI\TransactionRequestType();
          $transactionRequestType->setTransactionType("authCaptureTransaction");
          $transactionRequestType->setAmount($request->camount);
          $transactionRequestType->setPayment($paymentOne);
          $request = new AnetAPI\CreateTransactionRequest();
          $request->setMerchantAuthentication($merchantAuthentication);
          $request->setRefId( $refId);
          $request->setTransactionRequest($transactionRequestType);
          $controller = new AnetController\CreateTransactionController($request);
          $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
          if ($response != null)
            {
              $tresponse = $response->getTransactionResponse();
              if ($tresponse != null)
              { 
                if($tresponse->getResponseCode() == 1 || $tresponse->getResponseCode() == 4 ){
                   if($tresponse->getResponseCode() == 4){
                     $authCode = "pending";
                   }else{
                     $authCode = $tresponse->getAuthCode();
                   }
                   $transId = $tresponse->getTransId();
                   $result = ['authCode' => $authCode, 'transId' => $transId];
                   $res = $this->transData($result);
                   if($res){
                     Session::flash('success', "The order is successfully placed!"); 
                     return redirect('/');
                   }
                }else{
                   $response = $response->getTransactionResponse()->getErrors()[0]->getErrorText();
                   Session::flash('error', $response); 
                   return redirect('/');
                }
              }
              else
              {
                echo  "Charge Credit Card Null response returned";
              }
            }
            else
            {
              echo  "Charge Credit Card Null response returned";
            }

      die();
  }

}
