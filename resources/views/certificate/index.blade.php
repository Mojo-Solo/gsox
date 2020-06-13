<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <title>{{env('APP_NAME')}} : Certificate of Completion</title>

    {{--<link href="https://fonts.googleapis.com/css?family=Dancing+Script:400,700" rel="stylesheet">--}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Lobster+Two:400,700" rel="stylesheet">



    <style>
        @font-face {
            font-family: 'Lobster Two';
            src: url({{public_path('/fonts/lobster/LobsterTwo-Bold.ttf')}}) format('truetype'),
            url({{public_path('/fonts/lobster/LobsterTwo-BoldItalic.ttf')}}) format('truetype'),
            url({{public_path('/fonts/lobster/LobsterTwo-Italic.ttf')}}) format('truetype'),
            url({{public_path('/fonts/lobster/LobsterTwo-Regular.ttf')}}) format('truetype'),

        }

        body, h1, h2, h3, h4, span, div {
            /*font-family: 'Dancing Script', cursive;*/
            font-family: 'Lobster Two', cursive;

        }

        body {
            margin: 0px;
            color: #37231a;

        }

        .main-border {
            border: 20px solid darkred;
        }

        .row {
            position: relative;
        }

        /*.main-border .row{*/
        /*height: 800px;*/
        /*}*/
        .main-border .row h1 {
            font-size: 80px;
        }

        .banner {
            position: absolute;
            left: 0;
            right: 0;
            margin: auto;
        }

        .badge-img {
            right: 0;
            top: 0;
        }

        .logo {
            left: 8%;
            position: absolute;
            bottom:23%;
            right: 0;
            height: 70px;
            display: inline-block;
            margin: auto;
        }

        /*.container-fluid {*/
        /*width: 1200px;*/
        /*height: 855px;*/
        /*}*/

        .wrapper {
            position: absolute;
            left: 0;
            top: 50%;
            right: 0;
            margin: auto;
        }

        .text-block {
            position: absolute;
            right: 0;
            margin: auto;
            top: 40%;
            left: 0;
            text-align: center;
        }

        .text-block p {
            line-height: 1;
            margin-top: 30px;
            font-size: 30px;
            opacity: 0.9;
        }

        .font-weight-bold {
            font-weight: bolder;
        }
    </style>
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>

    <!-- Popper JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container-fluid px-0" style="height: 600px;padding:0px;margin: 0px;">
    <div style="position: relative;text-align: center"
         class="row h-100 justify-content-center text-center position-relative m-0">
        <img class="logo" src="{{public_path('storage/logos/'.config('logo_b_image'))}}">

        <div class="col-12 text-block align-self-center">
            <p class="text-center mb-0" style="margin-top:10px;"><span>This is to certify that</span><br/><br><span class="font-weight-bold" style="margin:10px 0px;font-family: DejaVu Sans;">{{$data['name']}}</span><br/><br><span>successfully completed</span></p>
            <p style="word-wrap: break-word;white-space: nowrap"><span class="font-weight-bold" style="font-family: DejaVu Sans;">{{$data['course_name']}}</span>
            </p>
            <h2><span class="font-weight-bold">{{$data['date']}}</span></h3>
        </div>
        <img width="100%" src="{{public_path('images/certificate.jpg')}}">
        <h3 class="font-weight-bold text-center" style="position: absolute; bottom: 16%;left: 40.01%;">ID: {{$certuuid}}</h3>
        <span class="text-right" style="position: absolute; bottom: 13%; right: 8%;">
            @if(isset($client_object))
                @if(!empty($client_object->image))
                    <img src="{{url('public/images/'.$client_object->image)}}" height="100" style="height: 70px;" />
                @else
                @if(!empty($client_object->icon))
                    <i style="font-size:50px;" class="{{$client_object->icon}}"></i>
                @endif
                @endif
            @endif
        </span>
    </div>
</div>
</body>
</html>