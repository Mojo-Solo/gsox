<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Helpers\Frontend\Auth\Socialite;
use App\Events\Frontend\Auth\UserRegistered;
use App\Events\Frontend\Auth\VendorRegistered;
use App\Models\Auth\User;
use App\Models\Vendor;
use Arcanedev\NoCaptcha\Rules\CaptchaRule;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Repositories\Frontend\Auth\UserRepository;
use Illuminate\Http\Request;
use App\Mail\VerifyAccount;
use App\Mail\VerifyVendor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ClosureValidationRule;
use Illuminate\Database\Eloquent\Collection;
use Jenssegers\Agent\Agent;
use Messenger;
/**
 * Class RegisterController.
 */
class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * RegisterController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Where to redirect users after login.
     *
     * @return string
     */
    public function redirectPath()
    {
        return route(home_route());
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        abort_unless(config('access.registration'), 404);

        return view('frontend.auth.register')
            ->withSocialiteLinks((new Socialite)->getSocialLinks());
    }

    /**
     * @param RegisterRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Throwable
     */
    public function register(Request $request)
    {
        $vendor=false;
        if(config('registration_fields') != NULL) {
            $fields = json_decode(config('registration_fields'));
            $inputs = ['text','number','date'];
            foreach($fields as $item) {
                if($item->name=='vendor') {
                    $vendor=true;
                }
            }
        }
        $validator = Validator::make(Input::all(), [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'vendor_id' => ($vendor)?'required':'',
            'g-recaptcha-response' => (config('access.captcha.registration') ? ['required',new CaptchaRule] : ''),
        ],[
            'g-recaptcha-response.required' => __('validation.attributes.frontend.captcha'),
        ]);

        if ($validator->passes()) {
            // Store your user in database
            event(new Registered($user = $this->create($request->all())));
            if($user->confirmed) {
                return response(['success' => true,'confirmed'=>true]);
            }
            return response(['success' => true,'confirmed'=>false]);
        }

        return response(['errors' => $validator->errors()]);
    }

    public function registerVendor(Request $request)
    {
        $vendor=false;
        $validator = Validator::make(Input::all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:vendors',
            'contact_name' => 'required|max:255',
            'contact_email' => 'required|email|max:255|unique:vendors',
            'password' => 'required|min:6|confirmed',
            'clients' => 'required',
            'g-recaptcha-response' => (config('access.captcha.registration') ? ['required',new CaptchaRule] : ''),
        ],[
            'g-recaptcha-response.required' => __('validation.attributes.frontend.captcha'),
        ]);

        if ($validator->passes()) {
            // Store your vendor in database
            $clients=implode(",", $request->clients);
            $data=[
                'name' => $request->name,
                'email' => $request->email,
                'is_student' => ($request->need_course)?1:0,
                'company_name' => $request->company_name,
                'contact_name' => $request->contact_name,
                'contact_email' => $request->contact_email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'country_id' => $request->country_id,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                'address1' => $request->address1,
                'address2' => $request->address2,
                'invoicing' => ($request->invoicing)?1:0,
                'clients' => $clients,
                'confirmation_code'=>sha1(time()),
            ];
            $vendor = $this->createvendor($data);
            if($vendor->confirmed) {
                return response(['success' => true,'confirmed'=>true]);
            }
            return response(['success' => true,'confirmed'=>false]);

        }

        return response(['errors' => $validator->errors()]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
                $user->dob = isset($data['dob']) ? $data['dob'] : NULL ;
                $user->phone = isset($data['phone']) ? $data['phone'] : NULL ;
                $user->gender = isset($data['gender']) ? $data['gender'] : NULL;
                $user->address = isset($data['address']) ? $data['address'] : NULL;
                $user->city =  isset($data['city']) ? $data['city'] : NULL;
                $user->pincode = isset($data['pincode']) ? $data['pincode'] : NULL;
                $user->state = isset($data['state']) ? $data['state'] : NULL;
                $user->country = isset($data['country']) ? $data['country'] : NULL;
                if(isset($data['vendor_id']) && $data['vendor_id']!="Individual_Contractor" && $data['vendor_id']!="Vendor_Company_not_listed") {
                    $user->vendor_id = isset($data['vendor_id']) ? $data['vendor_id'] : NULL;
                }elseif(isset($data['vendor_id']) && $data['vendor_id']=="Vendor_Company_not_listed") {
                    $company_name=isset($data['company_name']) ? $data['company_name'] : '';
                    $message = Messenger::from($user)->to(1)->message("Vendor company not listed: "."'".$company_name."'")->send();
                }
                $user->client_id = isset($data['client_id']) ? $data['client_id'] : NULL;
                $user->confirmation_code=sha1(time());
                $user->save();
        $userForRole = User::find($user->id);
        $user_email=explode("@", $user->email);
        if($user_email[1]!=$_SERVER['SERVER_NAME']) {
            $userForRole->confirmed = 0;
            \Mail::to($user->email)->send(new VerifyAccount($user));
        } else {
            $userForRole->confirmed = 1;
        }
        $userForRole->save();
        $userForRole->assignRole('student');
        return $user;
    }

    protected function createvendor(array $data)
    {
        $vendor = Vendor::create($data);
        $user=array();
        if($vendor->is_student) {
            $name=explode(" ", $vendor->name);

            $user = User::create([
            'first_name' => (isset($name[0]))?$name[0]:'',
            'last_name' => (isset($name[1]))?$name[1]:'',
            'email' => $vendor->email,
            'password' => $vendor->password,
            'vendor_id' => $vendor->id,
            'confirmation_code' => $vendor->confirmation_code,
            ]);
            $userForRole = User::find($user->id);
            $user_email=explode("@", $user->email);
            if($user_email[1]!=$_SERVER['SERVER_NAME']) {
                $userForRole->confirmed = 0;
            } else {
                $userForRole->confirmed = 1;
            }
            $userForRole->save();
            $userForRole->assignRole('student');
        }
        $vendor_email=explode("@", $vendor->email);
        if($vendor_email[1]!=$_SERVER['SERVER_NAME']) {
            $vendor->confirmed = 0;
            \Mail::to($vendor->email)->send(new VerifyVendor($vendor));
        } else {
            $vendor->confirmed = 1;
        }
        $vendor->save();
        return $vendor;
    }



}
