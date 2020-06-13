@extends('frontend.layouts.app'.config('theme_layout'))

@section('title', 'Contact | '.app_name())
@section('meta_description', '')
@section('meta_keywords','')

@push('after-styles')
    <style>
        .my-alert{
            position: absolute;
            z-index: 10;
            left: 0;
            right: 0;
            top: 25%;
            width: 50%;
            margin: auto;
            display: inline-block;
        }.flex{
            display:flex;
        }
        .fa-refresh{
            cursor:pointer;
        }
    </style>
@endpush

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    @php
        $footer_data = json_decode(config('footer_data'));
    @endphp
    @if(session()->has('alert'))
        <div class="alert alert-light alert-dismissible fade my-alert show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>{{session('alert')}}</strong>
        </div>
    @endif

    <!-- Start of breadcrumb section
        ============================================= -->
    <section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
        <div class="blakish-overlay"></div>
        <div class="container">
            <div class="page-breadcrumb-content text-center">
                <div class="page-breadcrumb-title">
                    <h2 class="breadcrumb-head black bold">{{env('APP_NAME')}} <span> @lang('labels.frontend.contact.title')</span></h2>
                </div>
            </div>
        </div>
    </section>
    <!-- End of breadcrumb section
        ============================================= -->


    <!-- Start of contact section
        ============================================= -->
    <section id="contact-page" class="contact-page-section">
        <div class="container">
            <div class="section-title mb45 headline text-center">
                <h2>@lang('labels.frontend.contact.keep_in_touch')</h2>
            </div>
            @if(($footer_data->social_links->status == 1) && (count($footer_data->social_links->links) > 0))
                <div class="social-contact text-center d-inline-block w-100">
                    @foreach($footer_data->social_links->links as $item)
                    <div class="Client-icon-title text-center">
                        <a href="{{$item->link}}" target="_blank">
                            <div class="Client-icon">
                                <i class="text-gradiant {{$item->icon}}"></i>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
    <!-- End of contact section
        ============================================= -->
-
    <!-- Start of contact area form
        ============================================= -->
    <section id="contact-form" class="contact-form-area_3 contact-page-version">
        <div class="container">
            <div class="section-title mb45 headline text-center">
                <h2>@lang('labels.frontend.contact.send_us_a_message')</h2>
            </div>

            <div class="contact_third_form">
                <form class="contact_form" action="{{route('contact.send')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="contact-info">
                                <input class="name" name="name" type="text" placeholder="@lang('labels.frontend.contact.your_name')">
                                @if($errors->has('name'))
                                    <span class="help-block text-danger">{{$errors->first('name')}}</span>
                                @endif
                            </div>

                        </div>
                        <div class="col-md-4">
                            <div class="contact-info">
                                <input class="email" name="email" type="email" placeholder="@lang('labels.frontend.contact.your_email')">
                                @if($errors->has('email'))
                                    <span class="help-block text-danger">{{$errors->first('email')}}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="contact-info">
                                <input class="number" name="phone" type="number" placeholder="@lang('labels.frontend.contact.phone_number')">
                                @if($errors->has('phone'))
                                    <span class="help-block text-danger">{{$errors->first('phone')}}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <textarea name="message" placeholder="@lang('labels.frontend.contact.message')"></textarea>
                    @if($errors->has('message'))
                        <span class="help-block text-danger">{{$errors->first('message')}}</span>
                    @endif
                    <div class="form-group flex col-4 offset-4" id="captcha_div">
                    <?php echo html_entity_decode(captcha_img()); ?>
                    <input type="text" name="captcha" id="captcha" placeholder="Enter Captcha" class="form-control" />
                    <i class="fa fa-refresh mt-2 ml-1" id="regenerate" aria-hidden="true"></i>
                    </div>
                    @if($errors->has('captcha'))
                        <span class="help-block text-danger col-4 offset-4">Incorrect Captcha</span>
                    @endif
                    <div class="nws-button text-center  gradient-bg text-uppercase">
                        <button class="text-uppercase" type="submit" value="Submit">@lang('labels.frontend.contact.send_email') <i class="fas fa-caret-right"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <script src="//code.jquery.com/jquery-3.4.1.min.js"></script>
    <script>
    $('#captcha_div > i').on('click',function(e){
        $.ajax({
          type: "POST",
          async:true,
          url: '{{url("regenerate")}}',
          data: {_token: '{{csrf_token()}}'},
          success: function(data){
            $('#captcha_div > img').remove();
            $('#captcha_div').prepend(data);
          }
        });
       
    });
    </script>
    <!-- End of contact area form
        ============================================= -->


    <!-- Start of contact area
        ============================================= -->
    @include('frontend.layouts.partials.contact_area')
    <!-- End of contact area
        ============================================= -->


@endsection    