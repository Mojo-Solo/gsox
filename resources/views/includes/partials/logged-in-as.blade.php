@if(auth()->user() && session()->has("admin_user_id") && session()->has("temp_user_id"))
    <div class="alert alert-warning logged-in-as mb-0">
        You are currently logged in as {{ auth()->user()->name }}. <a href="{{ route("frontend.auth.logout-as") }}">Re-Login as {{ session()->get("admin_user_name") }}</a>.
    </div><!--alert alert-warning logged-in-as-->
@endif
@if(auth()->guard('vendor')->check() && auth()->guard('vendor')->user()->is_student)
    <div class="alert alert-warning logged-in-as mb-0">
        You are currently logged in as Vendor. <a href="{{ route('admin.user.switch-login') }}">Re-Login as Student</a>.
    </div><!--alert alert-warning logged-in-as-->
@elseif(auth()->user() && session()->has("student_user_id") && !empty(session()->get("student_user_id")))
    <div class="alert alert-warning logged-in-as mb-0">
        You are currently logged in as Student. <a href="{{ route('admin.user.switch-login') }}">Re-Login as Vendor</a>.
    </div><!--alert alert-warning logged-in-as-->
@endif
