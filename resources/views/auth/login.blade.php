<x-guest-layout>
   <section class="login-content">
       <div class="container h-100 d-flex align-items-center justify-content-center">
          <div class="col-md-5">
             <div class="card shadow-lg rounded-4 p-4 border-0">
                <div class="card-body">
                   <div class="auth-logo mb-4 text-center">
                        <a href="/" class="header-logo" style="text-decoration: none; display: inline-block; direction: ltr;">
                           <span class="site-logo" style="font-size: 100px; font-weight: 700; color: #FFC107; font-family: 'Poppins', sans-serif; line-height: 1;">
                               fleet.<span style="font-size: 45px; vertical-align: top; color: #FFC107;"></span>
                           </span>
                       </a>                     
                   </div>
                   <h3 class="mb-2 font-weight-bold text-center" style="font-size: 28px; font-weight: 600; color: #4B4B7B; font-family: 'Poppins', sans-serif; line-height: 1.2;">{{__('auth.sign_in')}}</h3>
                   <p class="text-center text-muted mb-4" style="font-size: 14px; letter-spacing: 0.5px;">{{__('auth.login_continue')}}</p>
                    
                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />
   
                    <!-- Validation Errors -->
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />
   
                    <form method="POST" action="{{ route('admin.login') }}" data-toggle="validator" novalidate>
                        @csrf
                      <div class="row">
                         <div class="col-lg-12 mb-3">
                            <div class="form-group">
                               <label class="text-secondary fw-semibold" style="font-size: 14px;">{{__('auth.email')}} <span class="text-danger">*</span></label>
                               <input id="email" name="email" value="{{request('email')}}" class="form-control rounded-pill border-1 shadow-sm" type="email" placeholder="{{ __('auth.enter_name',['name' => __('auth.email')]) }}" required autofocus style="padding: 12px 20px; font-size: 15px;">
                               <small class="help-block with-errors text-danger"></small>
                            </div>
                         </div>
                         <div class="col-lg-12 mb-3">
                            <div class="form-group">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="text-secondary fw-semibold" style="font-size: 14px;">{{__('auth.login_password')}} <span class="text-danger">*</span></label>
                                    <a href="{{route('auth.recover-password')}}" class="text-decoration-none" style="font-size: 13px; color: #FFC107;">{{__('auth.forgot_password')}}</a>
                                </div>                                    
                               <input class="form-control rounded-pill border-1 shadow-sm" type="password" value="{{request('password')}}" placeholder="{{ __('auth.enter_name',['name' => __('auth.login_password') ]) }}" name="password"  required autocomplete="current-password" style="padding: 12px 20px; font-size: 15px;">
                               <small class="help-block with-errors text-danger"></small>
                            </div>
                         </div>                              
                      </div>
                      <button type="submit" class="btn btn-warning btn-block rounded-pill fw-semibold py-2 mt-3" style="font-size: 16px; box-shadow: 0 6px 12px rgba(255, 193, 7, 0.5); transition: all 0.3s ease;">
                        {{ __('auth.login') }}
                      </button>
                      <div class="col-lg-12 mt-4 text-center">
                           <p class="mb-0" style="font-size: 14px; color: #6c757d;">{{__('auth.dont_have_account')}} <a href="{{route('auth.register')}}" class="text-warning fw-semibold text-decoration-none">{{__('auth.signup')}}</a></p>
                      </div>
                   </form>
   
                </div>
             </div>
          </div>
       </div>
    </section>
   
   <style>
     body {
       background: linear-gradient(135deg, #9199d6 0%, #30325e 100%);
       font-family: 'Poppins', sans-serif;
     }

     @keyframes gradientMove {
  0% {
    background-position: 0% 50%;
  }
  100% {
    background-position: 200% 50%;
  }
}

.site-logo {
  background: linear-gradient(270deg, #FFC107, #FFD54F, #FFA000, #FFC107);
  background-size: 400% 400%;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  animation: gradientMove 7s ease infinite;
  display: inline-block;
}

   
     .login-content .card {
       background: #f9f9fc;
     }
   
     .btn-warning:hover {
       background-color: #e6ac00 !important;
       box-shadow: 0 8px 15px rgba(230, 172, 0, 0.7);
     }
   
     input.form-control:focus {
       border-color: #FFC107 !important;
       box-shadow: 0 0 8px rgba(255, 193, 7, 0.6);
     }
         input.form-control {
      border-radius: 5px !important; 
      }

      .btn-warning {
      border-radius: 30px !important; 
      }

      .login-content .card {
      border-radius: 35px !important; 
      }

     
   </style>
   </x-guest-layout>
   