<?php

use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\Auth\User\AccountController;
use App\Http\Controllers\Backend\Auth\User\ProfileController;
use \App\Http\Controllers\Backend\Auth\User\UpdatePasswordController;

/*
 * All route names are prefixed with 'admin.'.
 */

//===== General Routes =====//
Route::redirect('/', '/user/dashboard', 301);
Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');


//===== Supervisors Routes =====//
Route::resource('Supervisors', 'Admin\SupervisorsController');
Route::get('get-Supervisors-data', ['uses' => 'Admin\SupervisorsController@getData', 'as' => 'Supervisors.get_data']);
Route::post('Supervisors_mass_destroy', ['uses' => 'Admin\SupervisorsController@massDestroy', 'as' => 'Supervisors.mass_destroy']);
Route::post('Supervisors_restore/{id}', ['uses' => 'Admin\SupervisorsController@restore', 'as' => 'Supervisors.restore']);
Route::delete('Supervisors_perma_del/{id}', ['uses' => 'Admin\SupervisorsController@perma_del', 'as' => 'Supervisors.perma_del']);

//===== Vendors Routes =====//
Route::resource('vendors', 'Admin\VendorsController');
Route::get('get-vendors-data', ['uses' => 'Admin\VendorsController@getData', 'as' => 'Vendors.get_data']);
Route::post('vendors_mass_destroy', ['uses' => 'Admin\VendorsController@massDestroy', 'as' => 'Vendors.mass_destroy']);
Route::delete('vendors_perma_del/{id}', ['uses' => 'Admin\VendorsController@perma_del', 'as' => 'Vendors.perma_del']);

//===== Students Routes =====//
Route::resource('students', 'Admin\StudentsController');
Route::get('get-students-data', ['uses' => 'Admin\StudentsController@getData', 'as' => 'Students.get_data']);
Route::post('students_mass_destroy', ['uses' => 'Admin\StudentsController@massDestroy', 'as' => 'Students.mass_destroy']);
Route::delete('students_perma_del/{id}', ['uses' => 'Admin\StudentsController@perma_del', 'as' => 'Students.perma_del']);



//===== Clients Routes =====//
Route::resource('Clients', 'Admin\ClientsController');
Route::get('get-Clients-data', ['uses' => 'Admin\ClientsController@getData', 'as' => 'Clients.get_data']);
Route::post('Clients_mass_destroy', ['uses' => 'Admin\ClientsController@massDestroy', 'as' => 'Clients.mass_destroy']);
Route::post('Clients_restore/{id}', ['uses' => 'Admin\ClientsController@restore', 'as' => 'Clients.restore']);
Route::delete('Clients_perma_del/{id}', ['uses' => 'Admin\ClientsController@perma_del', 'as' => 'Clients.perma_del']);


//===== Courses Routes =====//
Route::resource('courses', 'Admin\CoursesController');
Route::get('get-courses-data', ['uses' => 'Admin\CoursesController@getData', 'as' => 'courses.get_data']);
Route::post('courses_mass_destroy', ['uses' => 'Admin\CoursesController@massDestroy', 'as' => 'courses.mass_destroy']);
Route::post('courses_restore/{id}', ['uses' => 'Admin\CoursesController@restore', 'as' => 'courses.restore']);
Route::delete('courses_perma_del/{id}', ['uses' => 'Admin\CoursesController@perma_del', 'as' => 'courses.perma_del']);
Route::post('course-save-sequence', ['uses' => 'Admin\CoursesController@saveSequence', 'as' => 'courses.saveSequence']);
Route::get('course-publish/{id}',['uses' => 'Admin\CoursesController@publish' , 'as' => 'courses.publish']);
Route::get('course-question/search',['uses' => 'Admin\CoursesController@searchquestion' , 'as' => 'courses.searchquestion']);
Route::post('course-question/save',['uses' => 'Admin\CoursesController@savequestion' , 'as' => 'courses.saveQuestion']);
Route::post('course-content/save',['uses' => 'Admin\CoursesController@savecontent' , 'as' => 'courses.savecontent']);



//===== Bundles Routes =====//
// Route::resource('bundles', 'Admin\BundlesController');
// Route::get('get-bundles-data', ['uses' => 'Admin\BundlesController@getData', 'as' => 'bundles.get_data']);
// Route::post('bundles_mass_destroy', ['uses' => 'Admin\BundlesController@massDestroy', 'as' => 'bundles.mass_destroy']);
// Route::post('bundles_restore/{id}', ['uses' => 'Admin\BundlesController@restore', 'as' => 'bundles.restore']);
// Route::delete('bundles_perma_del/{id}', ['uses' => 'Admin\BundlesController@perma_del', 'as' => 'bundles.perma_del']);
// Route::post('bundle-save-sequence', ['uses' => 'Admin\BundlesController@saveSequence', 'as' => 'bundles.saveSequence']);
// Route::get('bundle-publish/{id}',['uses' => 'Admin\BundlesController@publish' , 'as' => 'bundles.publish']);



//===== Lessons Routes =====//
// Route::resource('chapters', 'Admin\ChaptersController');
// Route::get('get-chapters-data', ['uses' => 'Admin\ChaptersController@getData', 'as' => 'chapters.get_data']);
// Route::get('chapters/lessons/{id}', ['uses' => 'Admin\ChaptersController@getLessons', 'as' => 'chapters.get_lessons']);
// Route::post('chapters_mass_destroy', ['uses' => 'Admin\ChaptersController@massDestroy', 'as' => 'chapters.mass_destroy']);
// Route::post('chapters_restore/{id}', ['uses' => 'Admin\ChaptersController@restore', 'as' => 'chapters.restore']);
// Route::delete('chapters_perma_del/{id}', ['uses' => 'Admin\ChaptersController@perma_del', 'as' => 'chapters.perma_del']);


//===== Topics Routes =====//
Route::resource('topics', 'Admin\TopicsController');
Route::get('get-topics-data', ['uses' => 'Admin\TopicsController@getData', 'as' => 'topics.get_data']);
Route::post('topics_mass_destroy', ['uses' => 'Admin\TopicsController@massDestroy', 'as' => 'topics.mass_destroy']);
Route::post('topics_restore/{id}', ['uses' => 'Admin\TopicsController@restore', 'as' => 'topics.restore']);
Route::delete('topics_perma_del/{id}', ['uses' => 'Admin\TopicsController@perma_del', 'as' => 'topics.perma_del']);
Route::delete('topics/image_del/{id}', ['uses' => 'Admin\TopicsController@image_del', 'as' => 'topics.image_del']);
Route::get('topic/preview/{id}', ['uses' => 'Admin\TopicsController@preview', 'as' => 'topics.preview']);
Route::get('lesson/preview/{id}', ['uses' => 'Admin\LessonsController@preview', 'as' => 'lessons.preview']);
Route::get('question/preview/{id}', ['uses' => 'Admin\QuestionsController@preview', 'as' => 'questions.preview']);
Route::post('question/preview/{id}', ['uses' => 'Admin\QuestionsController@validatequestion', 'as' => 'questions.preview.check']);

//===== Lessons Routes =====//
Route::resource('lessons', 'Admin\LessonsController');
Route::get('get-lessons-data', ['uses' => 'Admin\LessonsController@getData', 'as' => 'lessons.get_data']);
Route::post('lessons_mass_destroy', ['uses' => 'Admin\LessonsController@massDestroy', 'as' => 'lessons.mass_destroy']);
Route::post('lessons_restore/{id}', ['uses' => 'Admin\LessonsController@restore', 'as' => 'lessons.restore']);
Route::delete('lessons_perma_del/{id}', ['uses' => 'Admin\LessonsController@perma_del', 'as' => 'lessons.perma_del']);

// Image upload
Route::post('upload/image', ['uses' => 'Admin\TopicsController@uploadimage', 'as' => 'upload.image']);
//file upload
Route::post('upload/file', ['uses' => 'Admin\TopicsController@uploadfile', 'as' => 'upload.file']);

//===== Questions Routes =====//
Route::resource('questions', 'Admin\QuestionsController');
Route::get('get-questions-data', ['uses' => 'Admin\QuestionsController@getData', 'as' => 'questions.get_data']);
Route::post('questions_mass_destroy', ['uses' => 'Admin\QuestionsController@massDestroy', 'as' => 'questions.mass_destroy']);
Route::post('questions_restore/{id}', ['uses' => 'Admin\QuestionsController@restore', 'as' => 'questions.restore']);
Route::delete('questions_perma_del/{id}', ['uses' => 'Admin\QuestionsController@perma_del', 'as' => 'questions.perma_del']);


//===== Questions Options Routes =====//
Route::resource('questions_options', 'Admin\QuestionsOptionsController');
Route::get('get-qo-data', ['uses' => 'Admin\QuestionsOptionsController@getData', 'as' => 'questions_options.get_data']);
Route::post('questions_options_mass_destroy', ['uses' => 'Admin\QuestionsOptionsController@massDestroy', 'as' => 'questions_options.mass_destroy']);
Route::post('questions_options_restore/{id}', ['uses' => 'Admin\QuestionsOptionsController@restore', 'as' => 'questions_options.restore']);
Route::delete('questions_options_perma_del/{id}', ['uses' => 'Admin\QuestionsOptionsController@perma_del', 'as' => 'questions_options.perma_del']);


//===== Tests Routes =====//
Route::resource('tests', 'Admin\TestsController');
Route::get('get-tests-data', ['uses' => 'Admin\TestsController@getData', 'as' => 'tests.get_data']);
Route::post('tests_mass_destroy', ['uses' => 'Admin\TestsController@massDestroy', 'as' => 'tests.mass_destroy']);
Route::post('tests_restore/{id}', ['uses' => 'Admin\TestsController@restore', 'as' => 'tests.restore']);
Route::delete('tests_perma_del/{id}', ['uses' => 'Admin\TestsController@perma_del', 'as' => 'tests.perma_del']);


//===== Media Routes =====//
Route::post('media/remove', ['uses' => 'Admin\MediaController@destroy', 'as' => 'media.destroy']);


//===== User Account Routes =====//
Route::group(['middleware' => ['auth', 'password_expires']], function () {
    Route::get('account', [AccountController::class, 'index'])->name('account'); 
    Route::patch('account', [UpdatePasswordController::class, 'update'])->name('account.post');
    Route::patch('profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('profile/file/upload', [ProfileController::class, 'uploadFiles'])->name('profile.file.upload');
    Route::get('profile/file/upload/{id}', [ProfileController::class, 'deleteFiles'])->name('profile.file.delete');
    Route::patch('profile/update/vendor', [ProfileController::class, 'updateVendor'])->name('profile.updateVendor');
});


//==== Messages Routes =====//
Route::get('messages', ['uses' => 'MessagesController@index', 'as' => 'messages']);
Route::post('messages/unread', ['uses' => 'MessagesController@getUnreadMessages', 'as' => 'messages.unread']);
Route::post('messages/send', ['uses' => 'MessagesController@send', 'as' => 'messages.send']);
Route::post('messages/reply', ['uses' => 'MessagesController@reply', 'as' => 'messages.reply']);

//==== MailBox Routes =====//
Route::get('mailbox', ['uses' => 'Admin\MailboxController@index', 'as' => 'mailbox']);


//===== Orders Routes =====//
Route::resource('orders', 'Admin\OrderController');
Route::get('get-orders-data', ['uses' => 'Admin\OrderController@getData', 'as' => 'orders.get_data']);
Route::post('orders_mass_destroy', ['uses' => 'Admin\OrderController@massDestroy', 'as' => 'orders.mass_destroy']);
Route::post('orders/complete', ['uses' => 'Admin\OrderController@complete', 'as' => 'orders.complete']);
Route::delete('orders_perma_del/{id}', ['uses' => 'Admin\OrderController@perma_del', 'as' => 'orders.perma_del']);


//=== Invoice Routes =====//
Route::get('invoice/download', ['uses' => 'Admin\InvoiceController@getInvoice', 'as' => 'invoice.download']);
Route::get('invoices', ['uses' => 'Admin\InvoiceController@getIndex', 'as' => 'invoices.index']);



//Route::group(['middleware' => 'role:admin'], function () {

//===== Settings Routes =====//
    Route::get('settings/general', ['uses' => 'Admin\ConfigController@getGeneralSettings', 'as' => 'general-settings']);
    Route::post('settings/general', ['uses' => 'Admin\ConfigController@saveGeneralSettings'])->name('general-settings');
    Route::get('settings/social', ['uses' => 'Admin\ConfigController@getSocialSettings'])->name('social-settings');
    Route::post('settings/social', ['uses' => 'Admin\ConfigController@saveSocialSettings'])->name('social-settings');

    Route::get('contact', ['uses' => 'Admin\ConfigController@getContact'])->name('contact-settings');
    Route::get('footer', ['uses' => 'Admin\ConfigController@getFooter'])->name('footer-settings');
    Route::get('newsletter', ['uses' => 'Admin\ConfigController@getNewsletterConfig'])->name('newsletter-settings');
    Route::post('newsletter/sendgrid-lists', ['uses' => 'Admin\ConfigController@getSendGridLists'])->name('newsletter.getSendGridLists');

//});



//===== Slider Routes =====/
Route::resource('sliders', 'Admin\SliderController');
Route::get('sliders/status/{id}', 'Admin\SliderController@status')->name('sliders.status', 'id');
Route::post('sliders/save-sequence', ['uses' => 'Admin\SliderController@saveSequence', 'as' => 'sliders.saveSequence']);


//===== Sponsors Routes =====//
Route::resource('sponsors', 'Admin\SponsorController');
Route::get('get-sponsors-data', ['uses' => 'Admin\SponsorController@getData', 'as' => 'sponsors.get_data']);
Route::post('sponsors_mass_destroy', ['uses' => 'Admin\SponsorController@massDestroy', 'as' => 'sponsors.mass_destroy']);
Route::get('sponsors/status/{id}', 'Admin\SponsorController@status')->name('sponsors.status', 'id');


//===== Testimonials Routes =====//
Route::resource('testimonials', 'Admin\TestimonialController');
Route::get('get-testimonials-data', ['uses' => 'Admin\TestimonialController@getData', 'as' => 'testimonials.get_data']);
Route::post('testimonials_mass_destroy', ['uses' => 'Admin\TestimonialController@massDestroy', 'as' => 'testimonials.mass_destroy']);
Route::get('testimonials/status/{id}', 'Admin\TestimonialController@status')->name('testimonials.status', 'id');


//======= Blog Routes =====//
Route::group(['prefix' => 'blog'], function () {
    Route::get('/create', 'Admin\BlogController@create');
    Route::post('/create', 'Admin\BlogController@store');
    Route::get('delete/{id}', 'Admin\BlogController@destroy')->name('blogs.delete');
    Route::get('edit/{id}', 'Admin\BlogController@edit')->name('blogs.edit');
    Route::post('edit/{id}', 'Admin\BlogController@update');
    Route::get('view/{id}', 'Admin\BlogController@show');
//        Route::get('{blog}/restore', 'BlogController@restore')->name('blog.restore');
    Route::post('{id}/storecomment', 'Admin\BlogController@storeComment')->name('storeComment');
});
Route::resource('blogs', 'Admin\BlogController');
Route::get('get-blogs-data', ['uses' => 'Admin\BlogController@getData', 'as' => 'blogs.get_data']);
Route::post('blogs_mass_destroy', ['uses' => 'Admin\BlogController@massDestroy', 'as' => 'blogs.mass_destroy']);


//======= Pages Routes =====//
Route::resource('pages', 'Admin\PageController');
Route::get('get-pages-data', ['uses' => 'Admin\PageController@getData', 'as' => 'pages.get_data']);
Route::post('pages_mass_destroy', ['uses' => 'Admin\PageController@massDestroy', 'as' => 'pages.mass_destroy']);
Route::post('pages_restore/{id}', ['uses' => 'Admin\PageController@restore', 'as' => 'pages.restore']);
Route::delete('pages_perma_del/{id}', ['uses' => 'Admin\PageController@perma_del', 'as' => 'pages.perma_del']);


//===== FAQs Routes =====//
Route::resource('faqs', 'Admin\FaqController');
Route::get('get-faqs-data', ['uses' => 'Admin\FaqController@getData', 'as' => 'faqs.get_data']);
Route::post('faqs_mass_destroy', ['uses' => 'Admin\FaqController@massDestroy', 'as' => 'faqs.mass_destroy']);
Route::get('faqs/status/{id}', 'Admin\FaqController@status')->name('faqs.status');



//===== FORUMS Routes =====//
Route::resource('forums-Client', 'Admin\ForumController');
Route::get('forums-Client/status/{id}', 'Admin\ForumController@status')->name('forums-Client.status');


//==== Reasons Routes ====//
Route::resource('reasons', 'Admin\ReasonController');
Route::get('get-reasons-data', ['uses' => 'Admin\ReasonController@getData', 'as' => 'reasons.get_data']);
Route::post('reasons_mass_destroy', ['uses' => 'Admin\ReasonController@massDestroy', 'as' => 'reasons.mass_destroy']);
Route::get('reasons/status/{id}', 'Admin\ReasonController@status')->name('reasons.status');


Route::get('menu-manager', ['uses'=>'MenuController@index','middleware'=>['auth','role:administrator']])->name('menu-manager');


//====== Contacts Routes =====//
Route::resource('contact-requests', 'ContactController');
Route::delete('contact-request/{id}/delete', 'ContactController@destroy');
Route::get('get-contact-requests-data', ['uses' => 'ContactController@getData', 'as' => 'contact_requests.get_data']);

//====== Review Routes =====//
Route::resource('reviews', 'ReviewController');
Route::get('get-reviews-data', ['uses' => 'ReviewController@getData', 'as' => 'reviews.get_data']);


//====== Reports Routes =====//
Route::get('report/sales', ['uses' => 'ReportController@getSalesReport','as' => 'reports.sales']);
Route::get('report/invoices', ['uses' => 'ReportController@getInvoicesReport','as' => 'reports.invoices']);
Route::post('report/invoices/paid/', ['uses' => 'ReportController@markAsPaid','as' => 'reports.invoices.paid']);
Route::post('report/invoices/unpaid/', ['uses' => 'ReportController@markAsUnpaid','as' => 'reports.invoices.unpaid']);
Route::post('report/invoices/group/', ['uses' => 'ReportController@generateInvoiceGroup','as' => 'reports.invoices.group']);
Route::post('report/invoices/update/', ['uses' => 'ReportController@updateInvoiceID','as' => 'invoiceid.update']);
Route::post('report/invoices/delete/', ['uses' => 'ReportController@deleteInvoice','as' => 'reports.invoices.delete']);
Route::get('report/students', ['uses' => 'ReportController@getStudentsReport','as' => 'reports.students']);
Route::get('report/vendors', ['uses' => 'ReportController@getVendorsReport','as' => 'reports.vendors']);

Route::get('get-course-reports-data', ['uses' => 'ReportController@getCourseData', 'as' => 'reports.get_course_data']);
Route::get('get-bundle-reports-data', ['uses' => 'ReportController@getBundleData', 'as' => 'reports.get_bundle_data']);
Route::get('get-students-reports-data', ['uses' => 'ReportController@getStudentsData', 'as' => 'reports.get_students_data']);
Route::get('get-vendors-reports-data', ['uses' => 'ReportController@getVendorsData', 'as' => 'reports.get_vendors_data']);
Route::post('get-vendors-reports-data', ['uses' => 'ReportController@getVendorsData', 'as' => 'reports.get_vendors_data_bydate']);
Route::get('get-invoices-reports-data', ['uses' => 'ReportController@getinvoicesData', 'as' => 'reports.get_invoices_data']);
Route::post('get-invoices-reports-data', ['uses' => 'ReportController@getinvoicesData', 'as' => 'reports.get_vendors_data_bydate']);


//====== Tax Routes =====//
Route::resource('tax', 'TaxController');
Route::get('tax/status/{id}', 'TaxController@status')->name('tax.status', 'id');

//====== Coupon Routes =====//
Route::resource('coupons', 'CouponController');
Route::get('coupons/status/{id}', 'CouponController@status')->name('coupons.status', 'id');



//==== Remove Locale FIle ====//
Route::post('delete-locale', function () {
    \Barryvdh\TranslationManager\Models\Translation::where('locale', request('locale'))->delete();

    \Illuminate\Support\Facades\File::deleteDirectory(public_path('../resources/lang/' . request('locale')));
})->name('delete-locale');


//==== Certificates ====//
Route::get('certificates', 'CertificateController@getCertificates')->name('certificates.index');
Route::post('certificates/generate', 'CertificateController@generateCertificate')->name('certificates.generate');
Route::get('certificates/download', ['uses' => 'CertificateController@download', 'as' => 'certificates.download']);


//==== Update Theme Routes ====//
Route::get('update-theme','UpdateController@index')->name('update-theme');
Route::post('update-theme','UpdateController@updateTheme')->name('update-files');
Route::post('list-files','UpdateController@listFiles')->name('list-files');
Route::get('backup','BackupController@index')->name('backup');
Route::get('generate-backup','BackupController@generateBackup')->name('generate-backup');

Route::post('backup','BackupController@storeBackup')->name('backup.store');

//===Trouble shoot ====//
Route::get('troubleshoot','Admin\ConfigController@troubleshoot')->name('troubleshoot');

//==== Sitemap Routes =====//
Route::get('sitemap','SitemapController@getIndex')->name('sitemap.index');
Route::post('sitemap','SitemapController@saveSitemapConfig')->name('sitemap.config');
Route::get('sitemap/generate','SitemapController@generateSitemap')->name('sitemap.generate');

Route::get('models/{id}/trash','Admin\ConfigController@removetimeline');
Route::post('add/contents','Admin\CoursesController@addContents');