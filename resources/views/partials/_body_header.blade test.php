<?php
$selected_lang_flag = file_exists(public_path('/images/flags/' . app()->getLocale() . '.png')) 
    ? asset('/images/flags/' . app()->getLocale() . '.png') 
    : asset('/images/language.png');
?>

<div class="iq-top-navbar shadow-sm">
    <div class="iq-navbar-custom">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-warning btn-lg rounded-pill mr-3">
                    <i class="fa fa-arrow-left"></i> Back to Admin
                </button>
                <div class="change-mode ml-3">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="dark-mode">
                        <label class="custom-control-label" for="dark-mode">Dark Mode</label>
                    </div>
                </div>
            </div>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item nav-icon dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-bell text-warning" style="font-size: 1.5rem;"></i>
                            <span class="badge badge-danger badge-pill">3</span>
                        </a>
                        <div class="dropdown-menu">
                            <div class="dropdown-header">Notifications</div>
                            <a href="#" class="dropdown-item">New message received</a>
                        </div>
                    </li>
                    <li class="nav-item nav-icon dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ $selected_lang_flag }}" class="rounded-circle" alt="lang" width="35">
                        </a>
                        <div class="dropdown-menu">
                            <a href="#" class="dropdown-item">English</a>
                            <a href="#" class="dropdown-item">العربية</a>
                        </div>
                    </li>
                    <li class="nav-item nav-icon dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ asset('profile.jpg') }}" class="rounded-circle" alt="user" width="35">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="#" class="dropdown-item">My Profile</a>
                            <a href="#" class="dropdown-item">Settings</a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item text-danger">Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>

<style>
    .iq-top-navbar {
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
        padding: 12px 25px;
    }
    .navbar-light .navbar-nav .nav-link {
        color: #333;
        font-weight: 600;
        padding: 10px 15px;
    }
    .navbar-nav .nav-item a:hover {
        color: #ffb800;
    }
    .btn-outline-warning {
        border-color: #ffb800;
        color: #ffb800;
        padding: 10px 20px;
        font-size: 1.1rem;
        font-weight: 500;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .btn-outline-warning:hover {
        background-color: #ffb800;
        color: #fff;
    }
    .nav-icon svg, .nav-item img {
        transition: transform 0.3s ease-in-out;
    }
    .nav-item:hover img {
        transform: scale(1.15);
    }
    .dropdown-menu {
        border-radius: 8px;
        background: #f9f9f9;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        padding: 12px;
    }
    .dropdown-item:hover {
        background-color: #f1f1f1;
    }
    .custom-switch-label {
        font-size: 0.875rem;
        color: #333;
    }
    body.dark-mode {
        background: #121212;
        color: #ffffff;
    }
    .navbar.dark-mode {
        background: #1e1e1e;
        color: #ffffff;
    }
    .navbar-nav.ml-auto {
        margin-left: auto;
    }
    .nav-icon i {
        font-size: 1.5rem;  /* أكبر حجم للأيقونات */
        transition: transform 0.3s ease-in-out;
    }
    .nav-item:hover .nav-icon i {
        transform: scale(1.2); /* تكبير الأيقونة عند المرور فوقها */
    }
</style>

<script>
    document.getElementById('dark-mode').addEventListener('change', function() {
        document.body.classList.toggle('dark-mode', this.checked);
        document.querySelector('.navbar').classList.toggle('dark-mode', this.checked);
    });
</script>
