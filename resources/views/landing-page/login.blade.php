@extends('landing-page.layouts.headerremove')


@section('content')
<div>
    <div class="container-fluid px-lg-0 py-lg-0 pb-5">
        <div class="row min-vh-100 g-lg-0">
            <div class="col-xl-8 col-lg-7 mh-100">
                <div class="py-5 d-flex flex-column justify-content-center h-100">
                    <div class="row justify-content-center">
                        <div class="col-xl-8 col-lg-10">
                            <div class="text-center">

                                <div class="mb-5 text-right">
                                    <h3 class="text-capitalize mb-3">
                                        {{ $sectionData['title'] }}
                                    </h3>
                                    <p class="m-0">
                                        {{$sectionData['description']}}
                                    </p>
                                </div>
                                
                           
                                <div class="auth-logo">
                                    {{-- <a href="{{route('frontend.index')}}" class="header-logo" style="text-decoration: none; display: inline-block; direction: ltr;"> --}}
                                        <span class="custom-site-logo" style="font-size: 220px; font-weight: 610; color: #FFC107; font-family: 'Poppins', sans-serif; line-height: 1;">
                                            fleet.<span class="custom-animated-text" style="font-size: 40px; vertical-align: top; color: #FFC107;"> </span>
                                        </span>
                                        
                                        
                                        
                                   {{-- </a>                      --}}
                               </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-5 mh-100">
                <div class="py-5 px-3 bg-light d-flex flex-column justify-content-center h-100">
                    <div class="row justify-content-center">
                        <div class="col-xl-8 col-lg-10">
                            <div class="authontication-forms">
                                <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3 mb-5">
                                    <h4 class="text-capitalize">{{__('auth.sign_in')}}</h4>
                                    <a href="{{ route('login-admin') }}" class="float-right mr-1 btn btn-sm btn-primary">{{__('landingpage.admin_login')}}</a>
                                </div>

                                
                                <div class="iq-login-form ">
                                    <div class="alert alert-danger d-none" role="alert"  id="error">

                                    </div>
                                    <form action="{{ route('login-office') }}" id="loginForm" data-toggle="validator" method="post">
                                        {{csrf_field()}}
                                        <div class="form-group icon-right mb-5 custom-form-field">
                                            <label>{{__('landingpage.email')}} <span class="text-danger">*</span></label>
                                            <input type="email" id="email" name="email" class="form-control" placeholder="{{__('placeholder.email')}}"
                                                aria-label="Username" aria-describedby="basic-addon1" required>
                                                @error('email')
                                                <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            <small class="help-block with-errors text-danger"></small>
                                        </div>


                                        <div class="form-group icon-right mb-5 custom-form-field">
                                        <label>{{__('landingpage.your')}} {{__('auth.login_password')}} <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" id="password" name="password" class="form-control" placeholder="{{__('placeholder.login_password')}}"
                                                       aria-label="Password" aria-describedby="togglePassword" required>
                                                       @error('password')
                                                       <small class="text-danger">{{ $message }}</small>
                                                       @enderror
                                                <span class="input-group-text" id="togglePassword">
                                                    <i class="fa fa-eye-slash" aria-hidden="true" onclick="togglePassword()"></i>
                                                </span>
                                            </div>
                                            <small class="help-block with-errors text-danger"></small>
                                        </div>


                                        <input type="hidden" name="login" value="user_login">

                                         <div class="d-flex flex-sm-row justify-content-between align-items-center mb-4">
                                            <p class="login-remember m-0"><label class="m-0"><input name="rememberme"
                                                        type="checkbox" id="rememberme" value="forever"> {{__('landingpage.remember_me')}}</label>
                                            </p>
                                            {{-- <a href="{{route('user.forgot_password')}}" class="btn-link p-0 text-capitalize"><i>{{__('auth.forgot_password')}}</i></a> --}}
                                         </div>

                                        <div class="login-submit">
                                            <button type="submit"  class="btn btn-primary w-100 text-capitalize">{{__('auth.login')}}</button>
                                        </div>
                                    </form>


                                  <div class="text-center my-4 text-signup">
                                        <label class="m-0 text-capitalize"> {{__('auth.dont_have_account')}}</label>
                                        {{-- <a href="{{route('user.register')}}" class="ms-1 btn-link align-baseline text-capitalize">{{__('auth.signup')}}</a> --}}
                                    </div>

                                    {{-- <div class="text-center">
                                        <a href="{{route('auth.register')}}" class="btn-link p-0 text-capitalize">{{__('landingpage.handyman_office_register')}}</a>
                                    </div> --}}
                                    <div class="mt-5">
                                        {{-- <h6 class="mb-3 text-capitalize text-center">{{__('landingpage.demo_accounts')}}</h6> --}}
                                        {{--  <div class="px-5 py-3 bg-primary">
                                            <ul class="iq-social-list-text d-flex align-items-center justify-content-center flex-wrap m-0 list-inline">
                                            <li class="me-3 pe-3">
                                                    <a href="{{ route('auth.login') }}" class="text-capitalize text-white">{{__('landingpage.admin_login')}}</a>
                                                </li>
                                                <li class="me-3 pe-3">
                                                    <a href="{{ route('auth.login', ['email' => 'demo@provider.com', 'password' => '12345678']) }}" class="text-capitalize text-white">{{__('messages.provider')}}</a>
                                                </li>
                                                <li class="me-3 pe-3">
                                                    <a href="{{ route('auth.login', ['email' => 'demo@handyman.com', 'password' => '12345678']) }}" class="text-capitalize text-white">{{__('messages.handyman')}}</a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('auth.login', ['email' => 'demo@user.com', 'password' => '12345678']) }}" class="text-capitalize text-white">{{__('landingpage.user')}}</a>
                                                </li> 
                                            </ul>
                                        </div>--}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- <style>
.custom-site-logo {
    display: inline-block;
    animation: zoomInOut 2s infinite ease-in-out;
}

@keyframes zoomInOut {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}

.custom-animated-text {
    animation: textMove 2s infinite ease-in-out;
}

@keyframes textMove {
    0% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-100px);
    }
    100% {
        transform: translateY(5);
    }
}



</style> --}}
@endsection

{{-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>


$(document).ready(function() {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        const baseUrl = document.querySelector('meta[name="baseUrl"]').getAttribute('content');
        $('#loginForm').submit(function(e) {

            e.preventDefault();

            var formData = $(this).serialize();
            const urlParams = new URLSearchParams(window.location.search);
            const serviceId = urlParams.get('service_id');
            const Favservice = urlParams.get('favorite_service');

            $.ajax({
                method: 'post',
                url: baseUrl+'/auth/login-office',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if(response.data){
                       if(serviceId !=null){
                        window.location.href = baseUrl + '/book-service?id=' + serviceId;
                       }else if(Favservice != null){
                        window.location.href = baseUrl + '/service-detail/' + Favservice;
                       }else{
                        window.location.href = baseUrl+'/';
                       }

                    }
                },
                error: function(xhr, status, error) {

                     $('#error').removeClass('d-none')

                     $('#error').text(xhr.responseJSON.message)

                }
            });
        });
    });


    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const icon = document.querySelector('#togglePassword i');

        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        // Change the eye icon based on the password visibility
        icon.className = type === 'password' ? 'fa fa-eye-slash' : 'fa fa-eye';
    }
</script> --}}
