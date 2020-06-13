<!DOCTYPE html>
<html>
  <head>
    <title>Welcome To {{ config('app.name') }}</title>
  </head>
  <body>
    <div style="display: block;margin: 0px 25%;">
      <p>Welcome to the site {{$user['first_name'].' '.$user['last_name']}}</p>
      <h4 style="margin-bottom: 0px;">Please verify your email account:</h4>
      <strong><a href="{{url('user/verify', $user['confirmation_code'])}}">Verify Account</a></strong>
      <br>
      <p style="margin-bottom: 0px;">If the link is not clickable, please copy/paste this URL into your browser:</p>
      <a href="{{url('user/verify', $user['confirmation_code'])}}">{{url('user/verify', $user['confirmation_code'])}}</a>
      <br>
      <p style="margin-bottom: 0px;">Your registered email is:</p>
      <p style="margin-top: 0px;">{{$user['email']}}</p>
      <br>
      <p>Thank you,</p>
      <p>{{ config('app.name') }}</p>
    </div>
</body>
</html>
