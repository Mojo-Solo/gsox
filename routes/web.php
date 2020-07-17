<?php

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\LanguageController;
use App\Models\Course;
use App\Models\Order;
use App\Models\Vendor;
Route::get('clear', function () {  
    Artisan::call('up');  
    Artisan::call('config:cache');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    \Cache::flush();
    dd("Done");
});
Route::get('site/shutdown', function(){
    return Artisan::call('down');
});

Route::get('site/live', function(){
    return Artisan::call('up');
}); 

Route::get('/testing',function(){
    $orders = Order::all();
    foreach ($orders as $value) {
        if ($value->payment_type == 3) {
            
            $value->items()->update(['invoice_status' => 0]);
        }
        else
        {
            $value->items()->update(['invoice_status' => 1]);
        }
    }
});
/*
 * Global Routes
 * Routes that are used between both frontend and backend.
 */

// Switch between the included languages
Route::get('lang/{lang}', [LanguageController::class, 'swap']);


Route::get('/sitemap-' .str_slug(config('app.name')) . '/{file?}', 'SitemapController@index');
Route::post('search/vendors','Frontend\HomeController@getvendors');



/*
 * Frontend Routes
 * Namespaces indicate folder structure
 */

Route::group(['namespace' => 'Frontend', 'as' => 'frontend.'], function () {
    include_route_files(__DIR__ . '/frontend/');
});

/*
 * Backend Routes
 * Namespaces indicate folder structure
 */
Route::group(['namespace' => 'Backend', 'prefix' => 'user', 'as' => 'admin.', 'middleware' => 'admin'], function () {
    /*
     * These routes need view-backend permission
     * (good if you want to allow more than one group in the backend,
     * then limit the backend features by different roles or permissions)
     *
     * Note: Administrator has all permissions so you do not have to specify the administrator role everywhere.
     * These routes can not be hit if the password is expired
     */
    include_route_files(__DIR__ . '/backend/');
});

Route::group(['namespace' => 'Backend', 'prefix' => 'user', 'as' => 'admin.', 'middleware' => 'auth'], function () {

//==== Messages Routes =====//
    Route::get('messages', ['uses' => 'MessagesController@index', 'as' => 'messages']);
    Route::post('messages/unread', ['uses' => 'MessagesController@getUnreadMessages', 'as' => 'messages.unread']);
    Route::post('messages/send', ['uses' => 'MessagesController@send', 'as' => 'messages.send']);
    Route::post('messages/reply', ['uses' => 'MessagesController@reply', 'as' => 'messages.reply']);
});




Route::get('certificates', 'CertificateController@getCertificates')->name('certificates.index');
Route::post('certificates/generate', 'CertificateController@generateCertificate')->name('certificates.generate');

Route::get('Client/{Client}/blogs', 'BlogController@getByClient')->name('blogs.Client');
Route::get('tag/{tag}/blogs', 'BlogController@getByTag')->name('blogs.tag');
Route::get('blog/{slug?}', 'BlogController@getIndex')->name('blogs.index');
Route::post('blog/{id}/comment', 'BlogController@storeComment')->name('blogs.comment');
Route::get('blog/comment/delete/{id}', 'BlogController@deleteComment')->name('blogs.comment.delete');

Route::get('Supervisors', 'Frontend\HomeController@getSupervisors')->name('Supervisors.index');
Route::get('Supervisors/{id}/show', 'Frontend\HomeController@showSupervisor')->name('Supervisors.show');


Route::post('newsletter/subscribe', 'Frontend\HomeController@subscribe')->name('subscribe');


//============Bundle Routes=================//
// Route::get('bundles', ['uses' => 'BundlesController@all', 'as' => 'bundles.all']);
// Route::get('bundle/{slug}', ['uses' => 'BundlesController@show', 'as' => 'bundles.show']);
// //Route::post('course/payment', ['uses' => 'CoursesController@payment', 'as' => 'courses.payment']);
// Route::post('bundle/{bundle_id}/rating', ['uses' => 'BundlesController@rating', 'as' => 'bundles.rating']);
// Route::get('Client/{Client}/bundles', ['uses' => 'BundlesController@getByClient', 'as' => 'bundles.Client']);
// Route::post('bundles/{id}/review', ['uses' => 'BundlesController@addReview', 'as' => 'bundles.review']);
// Route::get('bundles/review/{id}/edit', ['uses' => 'BundlesController@editReview', 'as' => 'bundles.review.edit']);
// Route::post('bundles/review/{id}/edit', ['uses' => 'BundlesController@updateReview', 'as' => 'bundles.review.update']);
// Route::get('bundles/review/{id}/delete', ['uses' => 'BundlesController@deleteReview', 'as' => 'bundles.review.delete']);


Route::group(['middleware' => 'auth'], function () {
    Route::post('audiostatus','LessonsController@audiostatus')->name('audiostatus');
    Route::post('review-course','LessonsController@resetprogress');
    Route::get('courses/{slug}','CoursesController@all');
    //============Course Routes=================//
	Route::get('courses', ['uses' => 'CoursesController@all', 'as' => 'courses.all']);
	Route::get('course/{slug}', ['uses' => 'CoursesController@show', 'as' => 'courses.show']);
    Route::get('certificate-verification','Backend\CertificateController@getVerificationForm')->name('frontend.certificates.getVerificationForm');
    Route::post('certificate-verification','Backend\CertificateController@verifyCertificate')->name('frontend.certificates.verify');
    Route::get('certificates/download', ['uses' => 'Backend\CertificateController@download', 'as' => 'certificates.download']);
	//Route::post('course/payment', ['uses' => 'CoursesController@payment', 'as' => 'courses.payment']);
	Route::post('course/{course_id}/rating', ['uses' => 'CoursesController@rating', 'as' => 'courses.rating']);
	Route::get('Client/{Client}/courses', ['uses' => 'CoursesController@getByClient', 'as' => 'courses.Client']);
	Route::post('courses/{id}/review', ['uses' => 'CoursesController@addReview', 'as' => 'courses.review']);
	Route::get('courses/review/{id}/edit', ['uses' => 'CoursesController@editReview', 'as' => 'courses.review.edit']);
	Route::post('courses/review/{id}/edit', ['uses' => 'CoursesController@updateReview', 'as' => 'courses.review.update']);
	Route::get('courses/review/{id}/delete', ['uses' => 'CoursesController@deleteReview', 'as' => 'courses.review.delete']);
    Route::get('lesson/{course_id}/{slug}/', ['uses' => 'LessonsController@show', 'as' => 'lessons.show']);
    Route::get('lesson/{course_id}/{slug}/{type}/{token}', ['uses' => 'LessonsController@show', 'as' => 'lessons.show1']);
    Route::post('lesson/{slug}/test', ['uses' => 'LessonsController@test', 'as' => 'lessons.test']);
    Route::post('lesson/{course_id}/{id}/{token}/question', ['uses' => 'LessonsController@question', 'as' => 'lessons.question']);
    Route::post('lesson/{course_id}/{id}/{token}/requestion', ['uses' => 'LessonsController@requestion', 'as' => 'lessons.requestion']);
    Route::post('lesson/{slug}/retest', ['uses' => 'LessonsController@retest', 'as' => 'lessons.retest']);
    Route::post('video/progress', 'LessonsController@videoProgress')->name('update.videos.progress');
    Route::post('lesson/progress', 'LessonsController@courseProgress')->name('update.course.progress');
});

Route::get('/search', [HomeController::class, 'searchCourse'])->name('search');
Route::get('/search-course', [HomeController::class, 'searchCourse'])->name('search-course');
Route::get('/search-bundle', [HomeController::class, 'searchBundle'])->name('search-bundle');
Route::get('/search-blog', [HomeController::class, 'searchBlog'])->name('blogs.search');


Route::get('/faqs', 'Frontend\HomeController@getFaqs')->name('faqs');


/*=============== Theme blades routes ends ===================*/


Route::get('contact', 'Frontend\ContactController@index')->name('contact');
Route::post('contact/send', 'Frontend\ContactController@send')->name('contact.send');


Route::get('download', ['uses' => 'Frontend\HomeController@getDownload', 'as' => 'download']);

Route::group(['middleware' => 'auth'], function () {
    Route::post('cart/checkout', ['uses' => 'CartController@checkout', 'as' => 'cart.checkout']);
    Route::post('cart/add', ['uses' => 'CartController@addToCart', 'as' => 'cart.addToCart']);
    Route::get('cart', ['uses' => 'CartController@index', 'as' => 'cart.index']);
    Route::get('cart/clear', ['uses' => 'CartController@clear', 'as' => 'cart.clear']);
    Route::get('cart/remove', ['uses' => 'CartController@remove', 'as' => 'cart.remove']);
    Route::post('cart/apply-coupon',['uses' => 'CartController@applyCoupon','as'=>'cart.applyCoupon']);
    Route::post('cart/remove-coupon',['uses' => 'CartController@removeCoupon','as'=>'cart.removeCoupon']);
    Route::post('cart/stripe-payment', ['uses' => 'CartController@stripePayment', 'as' => 'cart.stripe.payment']);
    Route::post('cart/authorize-payment', ['uses' => 'CartController@authorizePayment', 'as' => 'cart.authorize.payment']);
    Route::post('cart/paypal-payment', ['uses' => 'CartController@paypalPayment', 'as' => 'cart.paypal.payment']);
    Route::get('cart/paypal-payment/status', ['uses' => 'CartController@getPaymentStatus'])->name('cart.paypal.status');

    Route::get('status', function () {
        return view('frontend.cart.status');
    })->name('status');
    Route::post('cart/offline-payment', ['uses' => 'CartController@offlinePayment', 'as' => 'cart.offline.payment']);
    Route::post('cart/getnow',['uses'=>'CartController@getNow','as' =>'cart.getnow']);
});

//============= Menu  Manager Routes ===============//
Route::group(['namespace' => 'Backend', 'prefix' => 'admin', 'middleware' => config('menu.middleware')], function () {
    //Route::get('wmenuindex', array('uses'=>'\Harimayco\Menu\Controllers\MenuController@wmenuindex'));
    Route::post('add-custom-menu', 'MenuController@addcustommenu')->name('haddcustommenu');
    Route::post('delete-item-menu', 'MenuController@deleteitemmenu')->name('hdeleteitemmenu');
    Route::post('delete-menug', 'MenuController@deletemenug')->name('hdeletemenug');
    Route::post('create-new-menu', 'MenuController@createnewmenu')->name('hcreatenewmenu');
    Route::post('generate-menu-control', 'MenuController@generatemenucontrol')->name('hgeneratemenucontrol');
    Route::post('update-item', 'MenuController@updateitem')->name('hupdateitem');
    Route::post('save-custom-menu', 'MenuController@saveCustomMenu')->name('hcustomitem');
    Route::post('change-location', 'MenuController@updateLocation')->name('update-location');
});


if(config('show_offers') == 1){
    Route::get('offers',['uses' => 'CartController@getOffers', 'as' => 'frontend.offers']);
}

Route::group(['namespace' => 'Frontend', 'as' => 'frontend.'], function () {
    Route::get('/{page?}', [HomeController::class, 'index'])->name('index');
});

Route::post('regenerate',function(){
    return html_entity_decode(captcha_img());
});
Route::get('cron/backup', function () {    
    Artisan::call('schedule:run');
    dd("Done Backup Has Been Started");
});

Route::post('/get/client/courses',function()
{
    $errorBag['error'] = '';
    $html = '';
    $vendor = request()->vendor;

    $vendor = Vendor::where('id',$vendor)->first();

    if ($vendor) {
        if ($vendor->invoicing == 0) 
        {
            $errorBag['error'] = 'Enable invoicing for vendor to register student for courses.';
        }
        else
        {
                $clients = explode(",", $vendor->clients);

                $courses = Course::whereIn('client_id',$clients)->where('published',1)->get();

                if ($courses->count() > 0) {
                        // $html .= '<option disabled selected value>Select Course</option>';
                        foreach ($courses as $value) {
                            $html .=  '<option value="'.$value->id.'">'.
                                           $value->title 
                                      .'</option>';
                        }

                        $errorBag['html'] = $html;
                }
               
        }
    }

  
    return response()->json(['data' => $errorBag]);
});

