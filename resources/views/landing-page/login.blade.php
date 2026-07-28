<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title> login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Poppins:wght@600;900&display=swap');
    * { box-sizing: border-box; }

    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: 'Cairo', sans-serif;
      background: #f0f2f5;
      overflow-x: hidden;
      direction: rtl;
    }

    .slider-section {
      position: fixed;
      top: 0;
      left: 0;
      width: 70%;
      height: 100vh;
      background: linear-gradient(135deg, #ffb300 0%, #ff7043 100%);
      color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 60px 40px;
      overflow: hidden;
      user-select: none;
    }

    .slider-content {
      max-width: 480px;
      text-align: right;
      z-index: 10;
    }

    .slider-content h2 {
      font-size: clamp(2rem, 5vw, 3.5rem);
      font-weight: 900;
      margin-bottom: 20px;
      letter-spacing: 1.5px;
      text-shadow: 0 0 10px rgba(0,0,0,0.3);
    }

    .slider-content p {
      font-size: clamp(1.1rem, 2.5vw, 1.5rem);
      line-height: 1.6;
      text-shadow: 0 0 6px rgba(0,0,0,0.2);
    }

    .bubbles {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 5;
    }

    .bubble {
      position: absolute;
      bottom: -100px;
      background: rgba(255, 255, 255, 0.25);
      border-radius: 50%;
      animation: rise 15s linear infinite;
      opacity: 0.8;
      filter: drop-shadow(0 0 4px rgba(255,255,255,0.6));
    }

    .bubble.small { width: 25px; height: 25px; animation-duration: 12s; }
    .bubble.medium { width: 50px; height: 50px; animation-duration: 18s; }
    .bubble.large { width: 75px; height: 75px; animation-duration: 22s; }

    .bubbles > .bubble:nth-child(1) { left: 10%; animation-delay: 0s; }
    .bubbles > .bubble:nth-child(2) { left: 30%; animation-delay: 3s; }
    .bubbles > .bubble:nth-child(3) { left: 50%; animation-delay: 6s; }
    .bubbles > .bubble:nth-child(4) { left: 70%; animation-delay: 9s; }
    .bubbles > .bubble:nth-child(5) { left: 85%; animation-delay: 12s; }
    .bubbles > .bubble:nth-child(6) { left: 15%; animation-delay: 2s; }
    .bubbles > .bubble:nth-child(7) { left: 25%; animation-delay: 4s; }
    .bubbles > .bubble:nth-child(8) { left: 40%; animation-delay: 7s; }
    .bubbles > .bubble:nth-child(9) { left: 60%; animation-delay: 10s; }
    .bubbles > .bubble:nth-child(10) { left: 80%; animation-delay: 13s; }

    @keyframes rise {
      0% { transform: translateY(0) translateX(0); opacity: 0.8; }
      100% { transform: translateY(-120vh) translateX(30px); opacity: 0; }
    }

    .form-section {
      position: fixed;
      top: 0;
      right: 0;
      width: 30%;
      height: 100vh;
      background: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px 30px;
      box-shadow: -6px 0 25px rgba(0, 0, 0, 0.08);
      overflow-y: auto;
    }

    form {
      width: 100%;
      max-width: 400px;
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    .auth-logo {
      text-align: center;
      margin-bottom: 30px;
    }

    .site-logo {
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      font-size: clamp(50px, 8vw, 80px);
      color: #ff7043;
      letter-spacing: 3px;
    }

    .form-title {
      font-size: clamp(1.8rem, 4vw, 2.8rem);
      font-weight: 600;
      color: #222;
      text-align: center;
      margin-bottom: 5px;
    }

    label {
      font-weight: 600;
      font-size: 1.1rem;
      margin-bottom: 8px;
      color: #444;
    }

    input[type='text'],
    input[type='email'],
    input[type='password'] {
      width: 100%;
      height: 48px;
      padding: 10px 15px;
      font-size: 1.2rem;
      border-radius: 12px;
      border: 2px solid #ddd;
      box-shadow: inset 0 1px 3px rgb(0 0 0 / 0.07);
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
      font-family: 'Cairo', sans-serif;
      outline-offset: 2px;
    }

    input:focus {
      border-color: #ffc343;
      box-shadow: 0 0 10px rgba(255, 211, 67, 0.5);
      outline: none;
    }

    .input-group {
      position: relative;
      display: flex;
      align-items: center;
    }

    #togglePassword {
      position: absolute;
      top: 50%;
      left: 15px;
      transform: translateY(-50%);
      border: none;
      background: transparent;
      cursor: pointer;
      color: #999;
      font-size: 1.3rem;
    }

    #togglePassword:hover,
    #togglePassword:focus {
      color: #ff7043;
      outline: none;
    }

    input[type='password'] {
      padding-left: 50px;
    }

    .switch-buttons {
      display: inline-flex;
      border-radius: 35px;
      border: 2px solid #ddd;
      overflow: hidden;
      box-shadow: 0 0 8px rgba(0,0,0,0.05);
      user-select: none;
      justify-content: center;
      margin: 0 auto 20px auto;
      width: fit-content;
    }

    .toggle-btn {
      padding: 12px 25px;
      font-weight: 700;
      font-size: 1.1rem;
      background-color: #f7f7f7;
      color: #666;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 10px;
      transition:
        background-color 0.3s ease,
        color 0.3s ease,
        box-shadow 0.3s ease;
      user-select: none;
    }

    .toggle-btn i {
      font-size: 1.3rem;
    }

    .toggle-btn:first-child {
      border-top-right-radius: 35px;
      border-bottom-right-radius: 35px;
      border-right: 1px solid #ddd;
    }

    .toggle-btn:last-child {
      border-top-left-radius: 35px;
      border-bottom-left-radius: 35px;
    }

    .toggle-btn.active {
      background: linear-gradient(90deg, #ff7043 0%, #ffb300 100%);
      color: white;
      box-shadow: 0 4px 15px rgba(255, 112, 67, 0.7);
      border: none;
      z-index: 1;
    }

    .toggle-btn:hover:not(.active) {
      background-color: #ffe3b8;
      color: #ff7043;
    }

    button[type='submit'] {
      background: linear-gradient(90deg, #ff7043 0%, #ffb300 100%);
      border: none;
      border-radius: 25px;
      color: white;
      font-weight: 700;
      font-size: 1.2rem;
      padding: 14px 0;
      cursor: pointer;
      box-shadow: 0 6px 18px rgba(255, 211, 67, 0.6);
      transition: background 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
    }

    button[type='submit']:hover {
      background: linear-gradient(90deg, #ffb300d2 0%, #ff6f43c5 100%);
      box-shadow: 0 8px 22px rgba(255, 112, 67, 0.9);
      transform: translateY(-2px);
    }

    .form-footer {
      text-align: center;
      margin-top: 15px;
      font-weight: 600;
      font-size: 1rem;
      color: #666;
    }

    .form-footer a {
      color: #ff7043;
      text-decoration: none;
      font-weight: 600;
    }

    .form-footer a:hover {
      text-decoration: underline;
    }

    @media screen and (max-width: 1024px) {
      .slider-section {
        display: none;
      }
      .form-section {
        position: relative;
        width: 100%;
        height: auto;
        box-shadow: none;
        padding: 40px 20px;
      }
    }


    .email-wrapper {
  position: relative;
}

.email-wrapper input {
  padding-left: 95px;
}

.country-inside {
  position: absolute;
  top: 50%;
  left: 12px;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 8px;
  transition: 0.2s;
}

.country-inside:hover {
  background: #fff3e0;
}

.mini-flag {
  width: 22px;
  height: 15px;
  border-radius: 2px;
}

.country-list {
  position: absolute;
  top: 105%;
  left: 0;
  width: 100%;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
  display: none;
  z-index: 50;
}

.country-item {
  padding: 12px 15px;
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  transition: 0.2s;
}

.country-item:hover {
  background: #fff3e0;
}

  </style>
</head>
<body>


  <section class="slider-section" aria-hidden="true">
    <div class="slider-content">
    <h2>!Welcome Back</h2>
    <p>Log in securely and easily. Select your account type and continue

    </div>
    <div class="bubbles" aria-hidden="true">
      <div class="bubble small">[</div>
      <div class="bubble medium"></div>
      <div class="bubble large"></div>
      <div class="bubble small"></div>
      <div class="bubble medium"></div>
      <div class="bubble large"></div>
      <div class="bubble small"></div>
      <div class="bubble medium"></div>
      <div class="bubble large"></div>
      <div class="bubble small"></div>
    </div>
  </section>

 <section class="form-section" aria-label="نموذج تسجيل الدخول">
    <form action="{{ route('login-office-check') }}" method="POST" autocomplete="off" novalidate>
        @csrf

        <div style="text-align: center;">
            <a href="/" class="header-logo" style="text-decoration: none; display: inline-block; direction: ltr;">
                <img id="siteLogo"
                     src="{{ asset('storage/system/logos/employee_logo.png') }}"
                     alt="Fleet Logo"
                     style="height:120px; width:auto; margin-bottom:0px;">
            </a>
        </div>

        @if ($errors->any() && !($errors->has('email') || $errors->has('password')))
        <div class="alert alert-danger">
            <div style="color: #D8000C; background-color: #FFD2D2; border-radius: 8px; padding: 12px; margin-bottom: 20px; text-align: center; font-weight: 600;">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <div class="switch-buttons" role="radiogroup" aria-label="Select account type">
            <button type="button" class="toggle-btn active" data-role="employee" aria-pressed="true" title="Employee">
                <i class="fa-solid fa-user"></i> Employee
            </button>
            <button type="button" class="toggle-btn" data-role="manager" aria-pressed="false" title="Office Manager">
                <i class="fa-solid fa-user-tie"></i> Office Manager
            </button>
        </div>

        <input type="hidden" name="role" id="roleInput" value="employee" />
        <input type="hidden" name="region" id="regionInput" value="us" />


        <div class="input-group email-wrapper">
            <div class="country-inside" onclick="toggleCountries()">
                <img id="selectedFlag" src="storage/system/flags/us.jpg" class="mini-flag">
                <i class="fa-solid fa-chevron-down"></i>
            </div>

            <input type="email" name="email" placeholder="Email" required>

            <div class="country-list" id="countryList">
                @foreach($countries as $country)
                    <div onclick="selectCountry('{{ $country->id }}', '{{ $country->iso2 }}', this)" class="country-item">
                        <img src="{{ asset($country->flag) }}" class="mini-flag"> {{ $country->name }}
                    </div>
                @endforeach
            </div>
        </div>

        <div class="input-group">
            <input id="password" name="password" type="password" placeholder="********" required aria-required="true" aria-describedby="togglePasswordDesc" />
            <button type="button" id="togglePassword" aria-label="Show or hide password" aria-describedby="togglePasswordDesc">
                <i class="fa-solid fa-eye"></i>
            </button>
        </div>
        @error('password')
        <small style="color: red; font-size: 16px; font-family: 'Cairo', sans-serif;">
          {{ $message }}
        </small>
        @enderror
        <span id="togglePasswordDesc" class="sr-only"></span>

        <button type="submit" id="loginBtn">Login</button>

        <p class="form-footer">Don't have an account? <a href="#">Sign up now</a></p>
    </form>
</section>

<style>
.country-list {
    display: none;
    position: absolute;
    background: #fff;
    border: 1px solid #ddd;
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
    z-index: 10;
    margin-top: 5px;
    border-radius: 8px;
}
.country-list.show { display: block; }
.country-item {
    padding: 8px 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s;
}
.country-item:hover, .country-item.selected {
    background: #FCB902;
    color: #222f5a;
}
.mini-flag { width: 26px; height: 18px; object-fit: cover; border-radius: 3px; }
.country-inside { display: flex; align-items: center; cursor: pointer; gap: 6px; }
</style>

<script>
let regionInput = document.getElementById('regionInput');
let selectedFlag = document.getElementById('selectedFlag');

function toggleCountries() {
    document.getElementById('countryList').classList.toggle('show');
}

function selectCountry(id, iso, element) {
    regionInput.value = id;

    selectedFlag.src = `storage/system/flags/${iso}.jpg`;

    document.querySelectorAll('.country-item').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');

    document.getElementById('countryList').classList.remove('show');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.country-inside') && !e.target.closest('.country-list')) {
        document.getElementById('countryList').classList.remove('show');
    }
});
</script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
        const loginBtn = document.getElementById("loginBtn");
        const form = document.querySelector("form");
        const tokenInput = document.querySelector('input[name="_token"]');

        loginBtn.addEventListener("click", function (e) {
            e.preventDefault();

            fetch("{{ route('refresh-csrf') }}", {
                method: "GET",
                credentials: "same-origin"
            })
            .then(response => response.json())
            .then(data => {
                if (data.token) {
                    tokenInput.value = data.token;
                    form.submit();
                } else {
                    console.error("لم يتم استلام CSRF Token جديد");
                }
            })
            .catch(err => {
                console.error("خطأ في جلب CSRF Token:", err);
            });
        });
    });
    </script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const switchButtons = document.querySelectorAll('.toggle-btn');
    const roleInput = document.getElementById('roleInput');
    const siteLogo = document.getElementById('siteLogo');

    switchButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            switchButtons.forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-pressed', 'false');
            });
            btn.classList.add('active');
            btn.setAttribute('aria-pressed', 'true');

            const role = btn.getAttribute('data-role');
            roleInput.value = role;

            if (role === 'employee') {
                siteLogo.src = "{{ asset('storage/system/logos/employee_logo.png') }}";
            } else if (role === 'manager') {
                siteLogo.src = "{{ asset('storage/system/logos/office_logo.png') }}";
            }
        });
    });

    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePassword');

    togglePasswordBtn.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        togglePasswordBtn.querySelector('i').classList.toggle('fa-eye');
        togglePasswordBtn.querySelector('i').classList.toggle('fa-eye-slash');
    });
});
</script>




</body>
</html>

