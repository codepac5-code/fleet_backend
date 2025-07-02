<x-master-layout>
    <div class="container-fluid py-1">
        <div class="row justify-content-center">

            <div class="col-lg-6">
                <div class="card shadow-lg border-0">
                    <div class="card-body">
                        <h4 class="mb-4 text-center text-primary">{{ __('messages.add_balance') }}</h4>
                        <form method="GET" action="{{ route('wallet.history') }}" enctype="multipart/form-data" id="userForm">
                            @csrf

                            <div class="form-group text-center">
                                <label class="font-weight-bold mb-3">{{ __('messages.select_type') }}</label>
                                <div class="user-type-icons d-flex justify-content-center gap-4">
                                    <div class="icon" id="user" onclick="selectUserType('user')">
                                        <i class="fas fa-user-circle fa-3x"></i>
                                        <p class="mt-2">{{ __('messages.user') }}</p>
                                    </div>
                                    <div class="icon" id="driver" onclick="selectUserType('driver')">
                                        <i class="fas fa-taxi fa-3x"></i>
                                        <p class="mt-2">{{ __('messages.driver') }}</p>
                                    </div>
                                    <div class="icon" id="office" onclick="selectUserType('office')">
                                        <i class="fas fa-building fa-3x"></i>
                                        <p class="mt-2">{{ __('messages.office') }}</p>
                                    </div>
                                </div>
                                <p id="searchingText" style="text-align:center; font-size:18px; margin-top:10px;">
                        {{ __('messages.select_user_type_to_search') }}
                        </p>

                            </div>

                            <div class="form-group position-relative mt-4">
                                <label id="inputLabel" class="font-weight-bold">{{ __('messages.phoneNumber') }}</label>
                                <i class="fas fa-phone dynamic-icon" id="dynamicIcon"></i>
                                <input type="text" id="inputField" name="identifier" class="form-control pl-5 large-input @error('identifier') is-invalid @enderror" placeholder="{{ __('messages.enter_phone') }}" required>
                            
                                @error('identifier')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>                            

                            <input type="hidden" name="userType" id="userType" value="">

                            <button type="submit" class="btn btn-primary btn-block mt-4 wallet-search-btn">
                                <i class="fas fa-search"></i> {{ __('messages.search') }}
                            </button>
                            
                        </form>
                    </div>
                </div>
            </div>

            <!-- معلومات المستخدم -->
            <div class="col-lg-8 mt-5" id="userInfoSection" style="display: none;">
                <div class="card shadow border-0">
                    <div class="card-body">
                        <h4 class="mb-3 text-primary">{{ __('messages.user_information') }}</h4>
                        <ul class="list-unstyled">
                            <li><strong>{{ __('messages.name') }}:</strong> <span id="userName"></span></li>
                            <li><strong>{{ __('messages.phone_number') }}:</strong> <span id="userPhone"></span></li>
                            <li><strong>{{ __('messages.address') }}:</strong> <span id="userAddress"></span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="horizontal-separator"></div>
    </div>
                <div class="col-12 text-center mb-4">
                    <div class="wallet-icon-container">
                        <i class="fas fa-wallet wallet-icon"></i>
                    </div>
                </div>
    

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f9f9f9;
        }    
        .wallet-icon-container {
            background: radial-gradient(circle at 30% 30%, #ffcc00, #ffc107, #fff3cd);
            border-radius: 20px;
            width: 100px;
            height: 70px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: bounce 2.5s infinite;
            box-shadow: 0 10px 30px rgba(255, 204, 0, 0.4);
        }

        .wallet-icon {
            font-size: 56px;
            color: #1e1e2f;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

    .user-type-icons {
    display: flex;
    justify-content: center;
    gap: 25px;
    flex-wrap: wrap;
}

.icon {
    background-color: #f8c7055b; 
    width: 120px;
    height: 130px;
    border-radius: 35px;
    padding: 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}


.icon:hover {
    transform: translateY(-5px);
}
.icon.selected {
    border-color: #ffcc00;
    background: #fff3cd;
    box-shadow: 0 0 15px rgba(255, 204, 0, 0.6);
}




/* تحديد حجم النص لحقل الإدخال */
.large-input {
    font-size: 18px;  /* زيادة حجم النص */
    padding: 12px;    /* توفير مسافة إضافية حول النص */
}


/* مظهر الأيقونات في الوضع الداكن */
body.dark .icon {
    background-color: #2b2b2b; /* خلفية داكنة */
    color: #fff; /* نص أبيض */
    border-color: #444; /* حدود داكنة */
    box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1); /* ظل خفيف */
}

/* تأثير عند المرور فوق الأيقونة في الوضع الداكن */
body.dark .icon:hover {
    background-color: #444444; /* تغيير الخلفية عند المرور */
    box-shadow: 0 4px 20px rgba(255, 255, 255, 0.3); /* تأثير ظل أكبر */
    transform: translateY(-5px);
}

/* التحديد في الوضع الداكن */
body.dark .icon.selected {
    border-color: #ffcc00;
    background: #444444; /* خلفية داكنة مع تأثير مختار */
    box-shadow: 0 0 15px rgba(255, 204, 0, 0.6);
}

        /* حقل الإدخال */
        .form-group {
            position: relative;
        }

        .form-group i.dynamic-icon {
            position: absolute;
            left: 15px;
            top: 70%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 18px;
        }

        #inputField {
    padding-left: 45px;
    border-radius: 12px;
    border: 1px solid #ccc;
    height: 50px;
    transition: border 0.3s;
}


        #inputField:focus {
            border-color: #ffcc00;
            box-shadow: 0 0 0 3px rgba(255, 204, 0, 0.2);
        }

        .wallet-search-btn {
    background: linear-gradient(135deg, #ffcc00, #ffdd57);
    border: none;
    padding: 14px 24px;
    font-size: 17px;
    font-weight: 600;
    color: #1e1e2f;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 6px 18px rgba(255, 204, 0, 0.3);
    transition: all 0.3s ease-in-out;
    text-shadow: 0 1px 1px rgba(255, 255, 255, 0.4);
}

.wallet-search-btn:hover {
    background: linear-gradient(135deg, #ffe066, #ffca2c);
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(255, 204, 0, 0.5);
}


        /* معلومات المستخدم */
        #userInfoSection .card {
            border-radius: 20px;
            background: linear-gradient(135deg, #fffdf5, #ffffff);
            border: 1px dashed #ffcc00;
            box-shadow: 0 5px 20px rgba(255, 204, 0, 0.1);
        }

        #userInfoSection ul li {
            font-size: 17px;
            padding: 10px 0;
            border-bottom: 1px dashed #eee;
        }
    </style>

    <script>
function selectUserType(type) {
    document.getElementById("userType").value = type;

    const icons = document.querySelectorAll(".icon");
    icons.forEach(icon => {
        icon.classList.remove("selected");
    });

    const selectedIcon = document.getElementById(type);
    selectedIcon.classList.add("selected");

    const label = document.getElementById("inputLabel");
    const input = document.getElementById("inputField");
    const icon = document.getElementById("dynamicIcon");
    const searchingText = document.getElementById("searchingText");

    if (type === 'office') {
        label.textContent = "{{ __('messages.email') }}";
        input.placeholder = "{{ __('messages.enter_email') }}";
        input.type = "email";
        icon.className = "fas fa-envelope dynamic-icon";
        searchingText.textContent = "{{ __('messages.searching_office') }}";
    } else if (type === 'driver') {
        label.textContent = "{{ __('messages.phone_number') }}";
        input.placeholder = "{{ __('messages.enter_phone') }}";
        input.type = "tel";
        icon.className = "fas fa-phone dynamic-icon";
        searchingText.textContent = "{{ __('messages.searching_driver') }}";
    } else if (type === 'user') {
        label.textContent = "{{ __('messages.phone_number') }}";
        input.placeholder = "{{ __('messages.enter_phone') }}";
        input.type = "tel";
        icon.className = "fas fa-phone dynamic-icon";
        searchingText.textContent = "{{ __('messages.searching_user') }}";
    } else {
        searchingText.textContent = "{{ __('messages.select_user_type') }}";
    }
}

    </script>
</x-master-layout>
