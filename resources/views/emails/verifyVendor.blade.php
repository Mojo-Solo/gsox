<!DOCTYPE html>
<html>
  <head>
    <title>Welcome To {{ config('app.name') }}</title>
  </head>
  <body>
    <div class="header" style="background: #F5F8FA;padding: 10px;">
      <h3 class="text-center" style="text-align: center;">{{ config('app.name') }}</h3>
    </div>
    <div style="display: block;margin: 0px 25%;">
      <h2><strong>Hello!</strong></h2>
      <p>
        <strong>
          You are receiving this email because {{$vendor['name']}} listed you as a Billing Contact for {{$vendor['company_name']}} on <a href="{{url('/')}}">{{config('app.url')}}</a>.
        </strong>
      </p>
      <h4 style="margin-bottom: 0px;">Please verify your email account:</h4>
      <strong><a href="{{url('vendor/verify', $vendor['confirmation_code'])}}">Verify Account</a></strong>
      <br>
      <p style="margin-bottom: 0px;">If the link is not clickable, please copy/paste this URL into your browser:</p>
      <a href="{{url('vendor/verify', $vendor['confirmation_code'])}}">{{url('vendor/verify', $vendor['confirmation_code'])}}</a>
      <br>
      <p><strong>If you are not the correct Billing Contact, please contact {{$vendor['email']}}</strong></p>
      <p>Regards,</p>
      <p>{{ config('app.name') }}</p>
    </div>
    <div class="header" style="background: #F5F8FA;padding: 10px;">
      <h4 class="text-center" style="text-align: center;">&copy; {{date('Y')}} {{ config('app.name') }}. All rights reserved.</h4>
    </div>
</body>
</html>
