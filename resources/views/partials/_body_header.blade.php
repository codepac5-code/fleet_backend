
<div class="iq-top-navbar">
    <div class="iq-navbar-custom">
        <nav class="navbar navbar-expand-lg navbar-light p-0">
            <div class="side-menu-bt-sidebar small-device-toggle">
                <svg xmlns="http://www.w3.org/2000/svg" class="text-secondary wrapper-menu" width="30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </div>
            
            <div class="d-flex align-items-center">
             
                <!--@if(auth()->user()->hasAnyRole(['admin']))-->
                <!--<a href="#" class="btn btn-primary text-white" style="float: right !important;margin-right: 10px;">Back to admin</a>-->
                <!--@endif-->
                <!--jabu-->
                @if(session()->get('backid'))
                <a href="{{ route('login.as',session()->get('backid')) }}" class="btn btn-primary text-white" style="float: right !important;margin-right: 10px;">Back to admin</a>
                @endif
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

                <div class="change-mode">
                    <div class="mode-toggle">
                        <input type="checkbox" class="mode-checkbox" id="dark-mode" data-active="true">
                        <label class="mode-label" for="dark-mode">
                            <div class="mode-circle">
                                <i class="fas fa-sun sun-icon"></i>
                                <i class="fas fa-moon moon-icon"></i>
                            </div>
                        </label>
                    </div>
                </div>
                
                <style>
                    .mode-toggle {
                        position: relative;
                        width: 70px;
                        height: 40px;
                        display: flex;
                        align-items: center;
                        margin-top: 13.5px;
                    }
                
                    .mode-checkbox {
                        display: none;
                    }
                
                    .mode-label {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        width: 100%;
                        height: 100%;
                        background-color: #f1ebe0;
                        border-radius: 50px;
                        padding: 4px;
                        transition: background-color 0.3s ease;
                        cursor: pointer;
                        position: relative;
                    }
                
                    .mode-checkbox:checked + .mode-label {
                        background-color: #2c2f42;
                    }
                
                    .mode-circle {
                        position: absolute;
                        width: 30px;
                        height: 30px;
                        background-color: #ffcc00;
                        border-radius: 50%;
                        transition: transform 0.1s ease;
                        top: 5px;
                        left: 5px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 2;
                    }
                
                    .mode-checkbox:checked + .mode-label .mode-circle {
                        transform: translateX(30px);
                    }
                
                    .sun-icon, .moon-icon {
                        font-size: 18px;
                        color: #ffffff;
                        transition: color 0.3s ease;
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        opacity: 0;
                    }
                
                    .sun-icon {
                        opacity: 1;
                    }
                
                    .moon-icon {
                        opacity: 0;
                    }
                
                    .mode-checkbox:checked + .mode-label .sun-icon {
                        opacity: 0;
                    }
                
                    .mode-checkbox:checked + .mode-label .moon-icon {
                        opacity: 1;
                    }
                
                    .sun-icon {
                        animation: sunAnimation 4s ease-in-out infinite;
                    }
                
                    .moon-icon {
                        animation: moonAnimation 4s ease-in-out infinite;
                    }
                
                    @keyframes sunAnimation {
                        0% {
                            transform: translate(-50%, -50%) rotate(0deg);
                        }
                        50% {
                            transform: translate(-50%, -50%) rotate(180deg);
                        }
                        100% {
                            transform: translate(-50%, -50%) rotate(360deg);
                        }
                    }
                
                    @keyframes moonAnimation {
                        0% {
                            transform: translate(-50%, -50%) rotate(0deg);
                        }
                        50% {
                            transform: translate(-50%, -50%) rotate(-180deg);
                        }
                        100% {
                            transform: translate(-50%, -50%) rotate(-360deg);
                        }
                    }
                
                    body[data-theme="dark"] {
                        background-color: #2c2f42;
                        color: #fff;
                    }
                
                    body[data-theme="light"] {
                        background-color: #fff;
                        color: #1e1e2f;
                    }
                </style>
                
                <script>
                    const checkbox = document.getElementById('dark-mode');
                    checkbox.addEventListener('change', function () {
                        if (checkbox.checked) {
                            document.body.setAttribute('data-theme', 'dark');
                        } else {
                            document.body.setAttribute('data-theme', 'light');
                        }
                    });
                </script>
                

                {{-- <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-label="Toggle navigation">
                    <svg xmlns="http://www.w3.org/2000/svg" class="text-secondary" width="30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button> --}}

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto navbar-list align-items-center">



                        <li class="custom-notify-container nav-item nav-icon dropdown">
                            <a href="#" class="custom-notify-toggle search-toggle dropdown-toggle" id="custom-notify-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" id="custom-notify-icon" height="28" class="custom-notify-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="custom-notify-badge badge badge-pill badge-danger">3</span>
                            </a>
                            <div class="custom-notify-dropdown iq-sub-dropdown dropdown-menu" aria-labelledby="custom-notify-dropdown">
                                <div class="custom-notify-card card shadow-lg border-0" style="width: 450px;">
                                    <div class="custom-notify-body card-body">
                                        <h5 class="custom-notify-title d-flex justify-content-between align-items-center">
                                            <span>الإشعارات</span>
                                            <a href="#" class="custom-notify-mark-all">تحديد الكل كمقروء</a>
                                        </h5>
                                        <div id="loader" class="text-center d-none">
                                            <img src="https://i.gifer.com/ZZ5H.gif" alt="Loading..." width="40">
                                        </div>
                                        <ul class="custom-notify-list list-group list-group-flush" style="max-height: 350px; overflow-y: auto;">
                                            <li class="custom-notify-empty list-group-item text-center d-none">لا يوجد إشعارات جديدة</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </li>
                        
                        
                        
                        


                        
                        
                        <li class="nav-item nav-icon dropdown">
                            <a href="#" class="search-toggle dropdown-toggle language-toggle" id="languageDropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php
                                $selected_lang_flag = file_exists(public_path('/images/flags/' . app()->getLocale() . '.png')) ? asset('/images/flags/' . app()->getLocale() . '.png') : asset('/images/language.png');
                                ?>
                                <img src="{{ $selected_lang_flag }}" class="img-fluid" alt="lang" style="height: 30px; min-width: 30px; width: 30px;">
                                <span class="bg-primary"></span>
                            </a>
                            <div class="iq-sub-dropdown dropdown-menu language-dropdown-menu" aria-labelledby="languageDropdownMenu">
                                <div class="card shadow-none m-0 border-0">
                                    <div class=" p-0 ">
                                        <ul class="dropdown-menu-1 list-group list-group-flush">
                                            <?php
                                             //$language_option = sitesetupSession('get')->language_option ?? ["nl","fr","it","pt"];
                                            $language_option = ["ar","en"];
                                            if (!empty($language_option)) {
                                                $language_array = languagesArray($language_option);
                                            }
                                            ?>
                                            @if(count($language_array) > 0 )
                                            @foreach( $language_array as $lang )
                                            <li class="dropdown-item-1 list-group-item px-2 {{ app()->getLocale() == $lang['id'] ? 'active' : '' }}">
                                                
                                                <a class="p-0" data-lang="{{ $lang['id'] }}" href="{{ route('switch-language',['locale'=> $lang['id'] ]) }}">
                                                    <?php
                    
                                                    $flag_path = file_exists(public_path('/images/flags/' . $lang['id'] . '.png')) ? asset('/images/flags/' . $lang['id'] . '.png') : asset('/images/language.png');
                                                    ?>
                                                    <img src="{{ $flag_path }}" alt="img-flag-{{ $lang['id'] }}" class="img-fluid mr-2" style="width: 20px;height: auto;min-width: 15px;" />
                                                    {{ $lang['title'] }}
                                                </a>
                                            </li>
                                            @endforeach
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </li>


                        <li class="nav-item nav-icon dropdown">
                            <a href="#" class="nav-item nav-icon dropdown-toggle pr-0 search-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <svg class="svg-icon mr-2 text-secondary" width="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                                </svg>
                                {{-- <img src="{{ getSingleMedia(auth()->user(),'profile_image') }}" class="img-fluid avatar-rounded bg-light" alt="user"> --}}
                                {{-- <span class="mb-0  user-name">{{ auth()->user()->officeName}}</span> --}}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right user-dropdown" aria-labelledby="dropdownMenuButton">
                                <li class="dropdown-item d-flex svg-icon">
                                    <svg class="svg-icon mr-0 text-secondary" id="h-01-p" width="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <a href="{{ route('home') }}"> <h6>{{ __('messages.home') }} </h6></a>
                                </li>
                                <li class="dropdown-item d-flex svg-icon">
                                    <svg class="svg-icon mr-0 text-secondary" id="h-01-p" width="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <a href="{{ route('setting.index',['page' => 'profile_form']) }}"> <h6>{{ __('messages.my_profile') }} </h6></a>
                                </li>
                                @role('provider')
                                <li class="dropdown-item d-flex svg-icon">
                                    <svg class="svg-icon mr-0 text-secondary" id="h-01-p" width="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <a href="{{ route('provider.show', ['provider' => auth()->id()]) }}">
                                        <h6> {{ __('messages.my_info') }} </h6></a>
                                </li>
                                @endrole
                                <li class="dropdown-item d-flex svg-icon border-top">
                                    <svg class="svg-icon mr-0 text-secondary" id="h-03-p" width="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <a href="{{  route('setting.index') }}"> <h6>{{ __('messages.Settings') }} </h6></a>
                                </li>
                                <li class="dropdown-item  d-flex svg-icon border-top">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <svg class="svg-icon mr-0 text-secondary" id="h-05-p" width="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <a class="logout-link" href="javascript:void(0)" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                                           <h6> {{ __('Log out') }}</h6>
                                        </a>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</div>



<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<audio id="notification-sound" src="\storage\system\wav\dashboard.notification.wav" preload="auto"></audio>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const htmlLang = document.documentElement.lang || "en";
    const isRTL = htmlLang.startsWith("ar");
    const toastContainer = document.getElementById("toast-container");

    if (isRTL) {
        toastContainer.setAttribute("dir", "rtl");
    } else {
        toastContainer.setAttribute("dir", "ltr");
    }


    


// ------------------- <<<<  for delete    >>>> --------------- 
async function fetchAndShowNotifications() {
    try {
        const response = await fetch('/api/user-alerts');
        const data = await response.json();

        if (!data.notifications || data.notifications.length === 0) return;

        for (let i = 0; i < data.notifications.length; i++) {
            const notification = data.notifications[i];
            showNotificationToast(notification);

            await new Promise(resolve => setTimeout(resolve, 3000));
        }
    } catch (error) {
        console.error('فشل في جلب الإشعارات', error);
    }
}

function showNotificationToast(data) {
    const { title, body, image } = data;
    const isRTL = document.documentElement.dir === 'rtl';

    const toastId = `toast-${Date.now()}`;
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center border-0 custom-toast" role="alert" style="direction: ${isRTL ? 'rtl' : 'ltr'};">
            <div class="toast-header">
                <div class="toast-icon">
                    <i class="fas fa-bell"></i>
                </div>
            </div>
            <div class="d-flex ${isRTL ? 'flex-row' : 'flex-row-reverse'} align-items-center toast-content">
                ${image ? `<img src="${image}" alt="notification-image" class="toast-image">` : ""}
                <div class="toast-body text-${isRTL ? 'end' : 'start'}">
                    <strong>${title}</strong><br>
                    ${body}
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', toastHtml);
    document.getElementById('notification-sound').play().catch(error => console.log("الصوت لم يعمل بسبب سياسات المتصفح"));

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();

    setTimeout(() => {
        toast.hide();
        toastElement.remove();
    }, 5000);
}

fetchAndShowNotifications();
// -------------------   <<<<  for delete    >>>> --------------- 







    socket_notification.on("public-notification-super-admin:new_notification", (data) => {
        document.getElementById('notification-sound').play().catch(error => console.log("الصوت لم يعمل بسبب سياسات المتصفح"));

        const { title, body, image } = data;

        const toastId = `toast-${Date.now()}`;
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center border-0 custom-toast" role="alert" style="direction: ${isRTL ? 'rtl' : 'ltr'};">
                <div class="toast-header">
                    <div class="toast-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
                <div class="d-flex ${isRTL ? 'flex-row' : 'flex-row-reverse'} align-items-center toast-content">
                    ${image ? `<img src="${image}" alt="notification-image" class="toast-image">` : ""}
                    <div class="toast-body text-${isRTL ? 'end' : 'start'}">
                        <strong>${title}</strong><br>
                        ${body}
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;


        toastContainer.insertAdjacentHTML("beforeend", toastHtml);

        const toasts = toastContainer.querySelectorAll(".toast");
        if (toasts.length > 4) {
            toasts[0].remove();
        }

        const newToast = new bootstrap.Toast(document.getElementById(toastId));
        newToast.show();

        setTimeout(() => {
            document.getElementById(toastId)?.remove();
        }, 120000);
    });
});
</script>

<style>
.toast-container {
    z-index: 1050;
    position: fixed;
    bottom: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
    transition: all 0.3s ease-in-out;
}

.toast-container[dir="rtl"] {
    left: 20px;
    right: auto;
}

.toast-container[dir="ltr"] {
    right: 20px;
    left: auto;
}

.toast {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #23242a, #32343d);
    color: #fff;
    border-radius: 20px; 
    box-shadow: 0 4px 10px rgba(161, 156, 156, 0.3), 0 0 15px rgba(255, 204, 0, 0.6); 
    overflow: hidden;
    max-width: 500px;
    min-height: 80px;
    animation: slideIn 0.5s ease, fadeOut 0.5s ease 5s forwards;
    padding: 15px 20px;
    opacity: 1;
    position: relative;
    border: 2px solid transparent; 
    background-clip: padding-box; 
}

@keyframes slideIn {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateY(-20px);
    }
}

.toast i {
    font-size: 26px; 
    color: #ffcc00;
    margin-right: 12px;
    animation: bellShake 1s infinite alternate;
}

@keyframes bellShake {
    0% { transform: rotate(0deg); }
    25% { transform: rotate(10deg); }
    50% { transform: rotate(-10deg); }
    75% { transform: rotate(5deg); }
    100% { transform: rotate(0deg); }
}

.toast-body {
    flex-grow: 1;
    font-size: 15px;
    line-height: 1.6;
    color: #fff;
}

.btn-close {
    background-color: rgba(219, 8, 8, 0.774);
    border-radius: 50%;
    width: 14px;
    height: 14px;
    opacity: 0.8;
    position: absolute;
    top: 12px;
    right: 12px;
}

.btn-close:hover {
    opacity: 1;
    background-color: rgba(255, 255, 255, 0.8);
}

.toast img {
    max-width: 50px;
    max-height: 50px;
    border-radius: 12px;
    margin-left: 12px;
    object-fit: cover;
    border: 3px solid #ffcc00;
}

body.light-mode .toast {
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    color: #000;
    border: 2px solid #ffcc00; 
}

body.dark-mode .toast {
    background: linear-gradient(135deg, #23242a, #32343d);
    color: #fff;
    border: 2px solid #ffcc00; 
}
</style>











@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cairo:wght@300;400;600;700&display=swap');
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        let page = 1; 
        let lastPage = false; 
        let isLoading = false; 
    
        $("#custom-notify-icon").on("click", function () {
            resetNotifications();
            fetchNotifications(); 
        });
    
        function resetNotifications() {
            page = 1;
            lastPage = false;
            isLoading = false;
            $(".custom-notify-list").empty(); 
        }
    
        function fetchNotifications() {
            if (isLoading || lastPage) return; 
    
            isLoading = true;
            $("#loader").removeClass("d-none"); 
    
            $.ajax({
                url: "{{ route('get-notifications') }}?page=" + page, 
                type: "GET",
                dataType: "json",
                success: function (response) {
                    $("#loader").addClass("d-none"); 
                    isLoading = false;
    
                    if (response.statusCode !== 200 || !response.data || response.data.data.length === 0) {
                        lastPage = true; 
                        return;
                    }
    
                    let notifications = response.data.data;
                    let notificationList = $(".custom-notify-list");
                    let currentLang = "{{ app()->getLocale() }}"; 
    
                    notifications.forEach(notification => {
                        let title = currentLang === "ar" ? notification.data.title_ar : notification.data.title_en;
                        let body = currentLang === "ar" ? notification.data.body_ar : notification.data.body_en;
                        let image = notification.data.image || "https://via.placeholder.com/40";
                        let formattedTime = formatTime(notification.created_at);
    
                        let notifyItem = `
                            <li class="custom-notify-item list-group-item d-flex align-items-center ${notification.read_at ? 'active' : ''}">
                                <img src="${image}" alt="صورة" class="custom-notify-img rounded-circle" width="40" height="40">
                                <div class="custom-notify-content ml-2">
                                    <h6 class="custom-notify-title-text mb-1">${title}</h6>
                                    <p class="custom-notify-body-text text-muted small mb-1">${body}</p>
                                    <small class="custom-notify-time text-secondary">${formattedTime}</small>
                                </div>
                            </li>
                        `;
                        notificationList.append(notifyItem);
                    });
    
                    page++; 
                },
                error: function (xhr) {
                    console.error("خطأ في جلب الإشعارات:", xhr.responseText);
                    $("#loader").addClass("d-none");
                    isLoading = false;
                }
            });
        }
    
        $(".custom-notify-list").on("scroll", function () {
            if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight - 10) {
                fetchNotifications(); 
            }
        });
    
        function formatTime(timestamp) {
            let date = new Date(timestamp);
            let now = new Date();
            let diffInMinutes = Math.floor((now - date) / 60000);
    
            if (diffInMinutes < 1) return "{{ __('messages.now') }}";
            if (diffInMinutes < 60) return `قبل ${diffInMinutes} دقيقة`;
            if (diffInMinutes < 1440) return `قبل ${Math.floor(diffInMinutes / 60)} ساعة`;
            return `منذ ${Math.floor(diffInMinutes / 1440)} يوم`;
        }
    });
    </script>
    

    <style>
        .custom-notify-container {
            font-family: 'Roboto', 'Lato', sans-serif;
        }
        
        .custom-notify-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .custom-notify-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            transition: background 0.3s ease;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            font-size: 15px;
        }
        
        .custom-notify-item:hover {
            background-color: #f7f7f7;
            transform: translateX(5px);
        }
        
        .custom-notify-item.active {
            background-color: #ffcc0021;
        }
        
        .custom-notify-item.unread {
            background-color: #ffcc00;
            opacity: 0.8; 
        }
        
        .custom-notify-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .custom-notify-content {
            flex-grow: 1;
        }
        
        .custom-notify-title-text {
            font-size: 16px;
            font-weight: 600;
            color: #222;
            margin: 0;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .custom-notify-body-text {
            font-size: 14px;
            color: #666;
            margin: 8px 0;
            line-height: 1.5;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .custom-notify-time {
            font-size: 13px;
            color: #999;
            font-style: italic;
            margin-top: 5px;
        }
        
        .custom-notify-badge {
            font-size: 13px;
            padding: 6px 9px;
            top: -7px;
            right: -7px;
        }
        
        #loader {
            text-align: center;
            margin: 15px 0;
        }
        
        .custom-notify-empty {
            font-size: 16px;
            color: #777;
        }
        
        
        .custom-notify-toggle {
            display: flex;
            align-items: center;
            padding: 0 10px;
        }
        
        .custom-notify-toggle svg {
            transition: all 0.3s ease;
        }
        
        .custom-notify-toggle:hover svg {
            transform: rotate(180deg);
        }
        
        .custom-notify-list {
            padding-left: 0;
            margin: 0;
            list-style: none;
        }
        </style>
