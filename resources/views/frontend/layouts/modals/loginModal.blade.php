<style>
    .modal-dialog {
        margin: 1.75em auto;
        min-height: calc(100vh - 60px);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    #myModal .close {
        position: absolute;
        right: 0.3rem;
    }

    .g-recaptcha div {
        margin: auto;
    }

    .modal-body .contact_form input[type='radio'] {
        width: auto;
        height: auto;
    }
    .modal-body .contact_form textarea{
        background-color: #eeeeee;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 10px;
        width: 100%;
        border: none
    }

    @media (max-width: 768px) {
        .modal-dialog {
            min-height: calc(100vh - 20px);
        }

        #myModal .modal-body {
            padding: 15px;
        }
    }
    .bootstrap-select {
        width: 100% !important;
        border: 2px solid #eee;
        border-radius: 4px;
    }
    .status{display: none !important;}
    span.select2 {
        width:100% !important;
    }
    .w-50x{width: 50px !important;}
    input[type=checkbox]{
      margin: 0.2em;
      cursor: pointer;
      padding: 0.2em
      width:0px !important;
      height:0px !important;
       -ms-transform: scale(1.4); /* IE */
      -moz-transform: scale(1.4); /* FF */
      -webkit-transform: scale(1.4); /* Safari and Chrome */
      -o-transform: scale(1.4); /* Opera */
    }
    input[type=checkbox]:before {
    content: "";
    display: block;
    margin: 0 auto;
    vertical-align: text-top;
    width: 15px;
    height: 15px;
    background: white;
    border: 0.5px solid;
    border-radius: 3px;
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.12);
    }
    input[type=checkbox]:hover:before {
        border: 0.5px solid #00A63F;
    }
    input[type=checkbox]:checked:before {
    background: #00A63F;
    border: 0.5px solid #00A63F;
    }
    input[type=checkbox]:checked:after {
    content: '';
    position: absolute;
    left: 2px;
    top: 7px;
    background: white;
    width: 2px;
    height: 2px;
    box-shadow: 2px 0 0 white, 4px 0 0 white, 4px -2px 0 white, 4px -4px 0 white, 4px -6px 0 white, 4px -8px 0 white;
    transform: rotate(45deg);
    }
</style>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.min.css"/>
<link rel="stylesheet" href="{{ url('public/css/bootstrap-script.min.css') }}">
<link rel="stylesheet" href="{{ url('public/css/select2.min.css') }}">
@if(!auth()->check())

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header backgroud-style">

                    <div class="gradient-bg"></div>
                    <div class="popup-logo">
                        <img src="{{asset("storage/logos/".config('logo_popup'))}}" alt="">
                    </div>
                    <div class="popup-text text-center">
                        <h2>@lang('labels.frontend.modal.my_account') </h2>
                        <p>@lang('labels.frontend.modal.login_register')</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <div class="tab-content">
                        <div class="tab-pane container active" id="login">

                            <span class="error-response text-danger"></span>
                            <span class="success-response text-success"></span>
                            <form class="contact_form" id="loginForm" action="{{route('frontend.auth.login.post')}}"
                                  method="POST" enctype="multipart/form-data">
                                <a href="#" class="go-register float-left text-info pl-0">
                                    @lang('labels.frontend.modal.new_user_note')
                                </a>
                                <div class="contact-info mb-2">
                                    {{ html()->email('email')
                                        ->class('form-control mb-0')
                                        ->placeholder(__('validation.attributes.frontend.email'))
                                        ->attribute('maxlength', 191)
                                        }}
                                    <span id="login-email-error" class="text-danger"></span>

                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->password('password')
                                                     ->class('form-control mb-0')
                                                     ->placeholder(__('validation.attributes.frontend.password'))
                                                    }}
                                    <span id="login-password-error" class="text-danger"></span>

                                    <a class="text-info p-0 d-block text-right my-2"
                                       href="{{ route('frontend.auth.password.reset') }}">@lang('labels.frontend.passwords.forgot_password')</a>

                                </div>

                                @if(config('access.captcha.registration'))
                                    <div class="contact-info mb-2 text-center">
                                        {!! Captcha::display() !!}
                                        {{ html()->hidden('captcha_status', 'true') }}
                                        <span id="login-captcha-error" class="text-danger"></span>

                                    </div><!--col-->
                                @endif

                                <div class="nws-button text-center white text-capitalize">
                                    <button type="submit"
                                            value="Submit">@lang('labels.frontend.modal.login_now')</button>
                                </div>
                            </form>

                            <div id="socialLinks" class="text-center">
                            </div>

                        </div>
                        <div class="tab-pane container fade" id="register">

                            <form id="registerForm" class="contact_form"
                                  action="#"
                                  method="post">
                                {!! csrf_field() !!}
                                <a href="#"
                                   class="go-login float-right text-info pr-0">@lang('labels.frontend.modal.already_user_note')</a>
                                <div class="contact-info mb-2">


                                    {{ html()->text('first_name')
                                        ->class('form-control mb-0')
                                        ->placeholder(__('validation.attributes.frontend.first_name'))
                                        ->attribute('maxlength', 191) }}
                                    <span id="first-name-error" class="text-danger"></span>
                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->text('last_name')
                                      ->class('form-control mb-0')
                                      ->placeholder(__('validation.attributes.frontend.last_name'))
                                      ->attribute('maxlength', 191) }}
                                    <span id="last-name-error" class="text-danger"></span>

                                </div>

                                <div class="contact-info mb-2">
                                    {{ html()->email('email')
                                       ->class('form-control mb-0')
                                       ->placeholder(__('validation.attributes.frontend.email'))
                                       ->attribute('maxlength', 191)
                                       }}
                                    <span id="email-error" class="text-danger"></span>

                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->password('password')
                                        ->class('form-control mb-0')
                                        ->placeholder(__('validation.attributes.frontend.password'))
                                         }}
                                    <span id="password-error" class="text-danger"></span>
                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->password('password_confirmation')
                                        ->class('form-control mb-0')
                                        ->placeholder(__('validation.attributes.frontend.password_confirmation'))
                                         }}
                                </div>
                                @if(config('registration_fields') != NULL)
                                    @php
                                        $fields = json_decode(config('registration_fields'));
                                        $inputs = ['text','number','date'];
                                    @endphp
                                    @foreach($fields as $item)
                                        @if(in_array($item->type,$inputs) && $item->name!="vendor")
                                            <div class="contact-info mb-2">
                                                <input type="{{$item->type}}" class="form-control mb-0" value="{{old($item->name)}}" name="{{$item->name}}"
                                                       placeholder="{{__('labels.backend.general_settings.user_registration_settings.fields.'.$item->name)}}">
                                            </div>
                                        @elseif($item->type == 'gender')
                                            <div class="contact-info mb-2">
                                                <label class="radio-inline mr-3 mb-0">
                                                    <input type="radio" name="{{$item->name}}" value="male"> {{__('validation.attributes.frontend.male')}}
                                                </label>
                                                <label class="radio-inline mr-3 mb-0">
                                                    <input type="radio" name="{{$item->name}}" value="female"> {{__('validation.attributes.frontend.female')}}
                                                </label>
                                                <label class="radio-inline mr-3 mb-0">
                                                    <input type="radio" name="{{$item->name}}" value="other"> {{__('validation.attributes.frontend.other')}}
                                                </label>
                                            </div>
                                        @elseif($item->type == 'textarea')
                                            <div class="contact-info mb-2">

                                            <textarea name="{{$item->name}}" placeholder="{{__('labels.backend.general_settings.user_registration_settings.fields.'.$item->name)}}" class="form-control mb-0">{{old($item->name)}}</textarea>
                                            </div>
                                        @endif
                                        @if($item->name=='vendor')
                                        @php
                                        @endphp
                                        <div class="contact-info mb-2" id="vendor_div">
                                            @php
                                            $vendors=array();
                                            if(config('registration_fields') != NULL) {
                                                $fields = json_decode(config('registration_fields'));
                                                $inputs = ['text','number','date'];
                                                foreach($fields as $item) {
                                                    if($item->name=='vendor') {
                                                        $vendors = \App\Models\Vendor::orderBy('company_name','asc')->get()->pluck('company_name', 'id')->prepend('Select Vendor','');
                                                    }
                                                }
                                            }
                                            //$vendors['Individual_Contractor']='Individual Contractor';
                                            //$vendors['Vendor_Company_not_listed']='Vendor Company not listed';
                                            @endphp
                                            {{ Form::select('vendor_id', $vendors, null,['class'=>'form-control select2','required','id' => 'vendor_id']) }}
                                        </div>
                                        @endif
                                    @endforeach
                                @endif

                                @if(config('access.captcha.registration'))
                                    <div class="contact-info mt-3 text-center">
                                        {!! Captcha::display() !!}
                                        {{ html()->hidden('captcha_status', 'true')->id('captcha_status') }}
                                        <span id="captcha-error" class="text-danger"></span>

                                    </div><!--col-->
                                @endif
                                <div class="form-group">
                                    <p class="text-center mt-4">Vendor company not listed?</p>
                                    <p class="text-center">Please <a href="#" class="go-vendor text-info">create</a> an account for your company.</p>
                                </div>
                                <div class="contact-info mb-2 mx-auto w-50 py-1">
                                    <div class="nws-button text-center white text-capitalize">
                                        <button id="registerButton" type="submit"
                                                value="Submit">@lang('labels.frontend.modal.register_now')</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane container fade" id="vendor">
                            <form id="vendorForm" class="contact_form"
                                  action="#"
                                  method="post">
                                {!! csrf_field() !!}
                                <a href="#"
                                   class="go-login float-right text-info pr-0">@lang('labels.frontend.modal.already_user_note')</a>
                                <div class="contact-info mb-2">
                                    {{ html()->text('name')
                                        ->class('form-control mb-0')
                                        ->placeholder("Your name")
                                        ->attribute('maxlength', 191) }}
                                    <span id="vendor-name-error" class="text-danger"></span>
                                </div>

                                <div class="contact-info mb-2">
                                    {{ html()->email('email')
                                       ->class('form-control mb-0')
                                       ->placeholder("Your email")
                                       ->attribute('maxlength', 191)
                                       }}
                                    <span id="vendor-email-error" class="text-danger"></span>
                                </div>
                                <div class="contact-info mb-2">
                                    <label>Use my contact info for billing: <input class="form-check-input w-50x" type="checkbox" name="contactinfo" id="contactinfo"></label>
                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->text('contact_name')
                                        ->class('form-control mb-0')
                                        ->placeholder("Billing contact name")
                                        ->attribute('maxlength', 191) }}
                                    <span id="vendor-contact_name-error" class="text-danger"></span>
                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->email('contact_email')
                                       ->class('form-control mb-0')
                                       ->placeholder("Billing contact email")
                                       ->attribute('maxlength', 191)
                                       }}
                                    <span id="vendor-contact_email-error" class="text-danger"></span>
                                </div>
                                <div class="contact-info mb-2">
                                    <label>Will this person need an account to take courses? <input class="form-check-input w-50x" type="checkbox" name="need_course" id="need_course"></label>
                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->password('password')
                                        ->class('form-control mb-0')
                                        ->placeholder(__('validation.attributes.frontend.password'))
                                         }}
                                    <span id="vendor-password-error" class="text-danger"></span>
                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->password('password_confirmation')
                                        ->class('form-control mb-0')
                                        ->placeholder(__('validation.attributes.frontend.password_confirmation'))
                                         }}
                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->text('phone_number')
                                        ->class('form-control mb-0')
                                        ->placeholder("Phone number")
                                        ->attribute('maxlength', 191) }}
                                    <span id="vendor-phone_number-error" class="text-danger"></span>
                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->text('company_name')
                                        ->class('form-control mb-0')
                                        ->placeholder("Company name")
                                        ->attribute('maxlength', 191) }}
                                    <span id="vendor-company_name-error" class="text-danger"></span>
                                </div>
                                <div class="contact-info mb-2">
                                    <input type="hidden" name="country_id" value="United States" id="country_id">
                                    <span id="vendor-country_id-error" class="text-danger"></span>
                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->text('address1')
                                        ->class('form-control mb-0')
                                        ->placeholder("Address 1")
                                        ->attribute('maxlength', 191) }}
                                    <span id="vendor-address1-error" class="text-danger"></span>
                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->text('address2')
                                        ->class('form-control mb-0')
                                        ->placeholder("Address 2")
                                        ->attribute('maxlength', 191) }}
                                    <span id="vendor-address2-error" class="text-danger"></span>
                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->text('city')
                                        ->class('form-control mb-0')
                                        ->placeholder("City")
                                        ->attribute('maxlength', 191) }}
                                    <span id="vendor-city-error" class="text-danger"></span>
                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->text('state')
                                        ->class('form-control mb-0')
                                        ->placeholder("State")
                                        ->attribute('maxlength', 191) }}
                                    <span id="vendor-state-error" class="text-danger"></span>
                                </div>
                                <div class="contact-info mb-2">
                                    {{ html()->text('zip')
                                        ->class('form-control mb-0')
                                        ->placeholder("Zip")
                                        ->attribute('maxlength', 191) }}
                                    <span id="vendor-zip-error" class="text-danger"></span>
                                </div>
                                
                                <div class="contact-info mb-2">
                                    <label>Invoicing: <input class="form-check-input w-50x" type="checkbox" name="invoicing" id="invoicing"></label>
                                </div>
                                <div class="contact-info mb-2">

                                    <select name="clients[]" multiple required class="form-control select2" id="clients">
                                        @foreach(\App\Models\Client::all() as $key => $client)
                                            <option value="{{$client->id}}">{{$client->name}}</option>
                                        @endforeach
                                    </select>
                                    <span id="vendor-client-error" class="text-danger"></span>
                                </div>
                                @if(config('access.captcha.registration'))
                                    <div class="contact-info mt-3 text-center">
                                        {!! Captcha::display() !!}
                                        {{ html()->hidden('captcha_status', 'true')->id('captcha_status') }}
                                        <span id="vendor-captcha-error" class="text-danger"></span>

                                    </div><!--col-->
                                @endif
                                <div class="contact-info mb-2 mx-auto w-50 py-1">
                                    <div class="nws-button text-center white text-capitalize">
                                        <button id="registerButton" type="submit"
                                                value="Submit">@lang('labels.frontend.modal.register_now')</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@push('after-scripts')
    @if(config('access.captcha.registration'))
        {!! Captcha::script() !!}
    @endif
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
<script src="{{ url('public/js/bootstrap-script.min.js') }}"></script>
<script src="{{ url('public/js/vmain.js') }}"></script>
<script src="{{ url('public/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script>
        $(document).ready(function(){
            $("#vendor_id").select2({
                placeholder: ' Select Vendor',
                dropdownParent: $("#vendor_id").parent()
            });
            $("#clients").select2({
                placeholder: ' Select Clients',
                dropdownParent: $("#clients").parent()
            });
        });
        $(function () {
            $('#vendor_id').on('change',function(){
                if($(this).val()=="Individual_Contractor") {
                    $('#registerForm').append('<div class="contact-info mb-2" id="client_div"><?php $clients=array(); $clients = \App\Models\Client::orderBy('name','asc')->get()->pluck('name', 'id')->prepend('Select Client', '');?>{{ Form::select("client_id", $clients, null,["class"=>"select2 form-control","required","id" => "client_id"]) }}</div>');
                        $("#client_id").select2({
                            placeholder: ' Select Client',
                            dropdownParent: $(this).parent()
                        });
                    $("#registerForm #client_div").insertAfter("#registerForm #vendor_div");
                    $('#registerForm #compnay_div').remove();
                } if($(this).val()=="Vendor_Company_not_listed") {
                    $('#registerForm #client_div').remove();
                    $('#registerForm').append('<div class="contact-info mb-2" id="compnay_div"><input class="form-control" name="company_name" placeholder="Company Name" id="company_name" required /></div>');
                    $("#registerForm #compnay_div").insertAfter("#registerForm #vendor_div");
                }if($(this).val()!="Individual_Contractor" && $(this).val()!="Vendor_Company_not_listed") {
                    $('#registerForm #compnay_div').remove();
                    $('#registerForm #client_div').remove();
                }
            });
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).ready(function () {
                $(document).on('click', '.go-login', function () {
                    $('#register').removeClass('active').addClass('fade')
                    $('#vendor').removeClass('active').addClass('fade')
                    $('#login').addClass('active').removeClass('fade')

                });
                $(document).on('click', '.go-register', function () {
                    $('#login').removeClass('active').addClass('fade')
                    $('#vendor').removeClass('active').addClass('fade')
                    $('#register').addClass('active').removeClass('fade')
                });
                $(document).on('click', '.go-vendor', function () {
                    $('#login').removeClass('active').addClass('fade')
                    $('#register').removeClass('active').addClass('fade')
                    $('#vendor').addClass('active').removeClass('fade')
                });

                $(document).on('click', '#openLoginModal', function (e) {
                    $.ajax({
                        type: "GET",
                        url: "{{route('frontend.auth.login')}}",
                        success: function (response) {
                            $('#socialLinks').html(response.socialLinks)
                            $('#myModal').modal('show');
                        },
                    });
                });
                $('#vendorForm #contactinfo').on('click',function(){
                    if($(this).is(":checked")) {
                        $('#vendorForm #contact_name').val($('#vendorForm #name').val());
                        $('#vendorForm #contact_email').val($('#vendorForm #email').val());
                    }
                });

                $('#loginForm').on('submit', function (e) {
                    e.preventDefault();

                    var $this = $(this);

                    $.ajax({
                        type: $this.attr('method'),
                        url: $this.attr('action'),
                        data: $this.serializeArray(),
                        dataType: $this.data('type'),
                        success: function (response) {
                            $('#login-email-error').empty();
                            $('#login-password-error').empty();
                            $('#login-captcha-error').empty();
                            if (response.errors) {
                                if (response.errors.email) {
                                    $('#login-email-error').html(response.errors.email[0]);
                                }
                                if (response.errors.password) {
                                    $('#login-password-error').html(response.errors.password[0]);
                                }

                                var captcha = "g-recaptcha-response";
                                if (response.errors[captcha]) {
                                    $('#login-captcha-error').html(response.errors[captcha][0]);
                                }
                            }
                            if (response.success) {
                                $('#loginForm')[0].reset();
                                if (response.redirect == 'back') {
                                    location.replace('{{url("courses")}}');
                                } else {
                                    window.location.href = "{{route('admin.dashboard')}}"
                                }
                            }
                        },
                        error: function (jqXHR) {
                            var response = $.parseJSON(jqXHR.responseText);
                            console.log(jqXHR)
                            if (response.message) {
                                $('#login').find('span.error-response').html(response.message)
                            }
                        }
                    });
                });

                $(document).on('submit','#registerForm', function (e) {
                    e.preventDefault();
                    var $this = $(this);

                    $.ajax({
                        type: $this.attr('method'),
                        url: "{{  route('frontend.auth.register.post')}}",
                        data: $this.serializeArray(),
                        dataType: $this.data('type'),
                        success: function (data) {
                            console.log(data);
                            $('#first-name-error').empty()
                            $('#last-name-error').empty()
                            $('#email-error').empty()
                            $('#password-error').empty()
                            $('#captcha-error').empty()
                            if (data.errors) {
                                if (data.errors.first_name) {
                                    $('#first-name-error').html(data.errors.first_name[0]);
                                }
                                if (data.errors.last_name) {
                                    $('#last-name-error').html(data.errors.last_name[0]);
                                }
                                if (data.errors.email) {
                                    $('#email-error').html(data.errors.email[0]);
                                }
                                if (data.errors.password) {
                                    $('#password-error').html(data.errors.password[0]);
                                }

                                var captcha = "g-recaptcha-response";
                                if (data.errors[captcha]) {
                                    $('#captcha-error').html(data.errors[captcha][0]);
                                }
                            }
                            if (data.success) {
                                $('#registerForm')[0].reset();
                                $('#register').removeClass('active').addClass('fade')
                                $('.error-response').empty();
                                $('#login').addClass('active').removeClass('fade');
                                if($('#registerForm #email').val()!="{{$_SERVER['SERVER_NAME']}}") {
                                    $('.success-response').html("Please Verify your email, we send you a verification link.");
                                } else {
                                    $('.success-response').html("@lang('labels.frontend.modal.registration_message')");
                                }
                            }
                        },
                        error: function (data) {
                            console.log(data);
                        }
                    });
                });

                $(document).on('submit','#vendorForm', function (e) {
                    e.preventDefault();
                    var $this = $(this);

                    $.ajax({
                        type: $this.attr('method'),
                        url: "{{  route('frontend.auth.register.vendor.post')}}",
                        data: $this.serializeArray(),
                        dataType: $this.data('type'),
                        success: function (data) {
                            console.log(data);
                            $('#vendor-name-error').empty()
                            $('#vendor-email-error').empty()
                            $('#vendor-contact_name-error').empty()
                            $('#vendor-contact_email-error').empty()
                            $('#vendor-password-error').empty()
                            $('#vendor-captcha-error').empty()
                            if (data.errors) {
                                if (data.errors.name) {
                                    $('#vendor-name-error').html(data.errors.name[0]);
                                }
                                if (data.errors.email) {
                                    $('#vendor-email-error').html(data.errors.email[0]);
                                }
                                if (data.errors.contact_name) {
                                    $('#vendor-contact_name-error').html(data.errors.contact_name[0]);
                                }
                                if (data.errors.contact_email) {
                                    $('#vendor-contact_email-error').html(data.errors.contact_email[0]);
                                }
                                if (data.errors.password) {
                                    $('#vendor-password-error').html(data.errors.password[0]);
                                }

                                var captcha = "g-recaptcha-response";
                                if (data.errors[captcha]) {
                                    $('#vendor-captcha-error').html(data.errors[captcha][0]);
                                }
                            }
                            if (data.success) {
                                $('#vendorForm')[0].reset();
                                $('#vendor').removeClass('active').addClass('fade')
                                $('.error-response').empty();
                                $('#login').addClass('active').removeClass('fade');
                                if($('#vendorForm #email').val()!="{{$_SERVER['SERVER_NAME']}}" && !data.confirmed) {
                                    $('.success-response').html("Please Verify your email, we send you a verification link.");
                                } else {
                                    $('.success-response').html("@lang('labels.frontend.modal.registration_message')");
                                }
                            }
                        },
                        error: function (data) {
                            console.log(data);
                        }
                    });
                });
            });
        });
    </script>
@endpush
