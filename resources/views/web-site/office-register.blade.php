@php
    $ar = app()->getLocale() === 'ar';
    $t = fn ($en, $arText) => $ar ? $arText : $en;
@endphp
<!DOCTYPE html>
<html lang="{{ $ar ? 'ar' : 'en' }}" dir="{{ $ar ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $t('Join FleetOS', 'انضم إلى FleetOS') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --p:#312873; --p2:#3e3499; --a:#F8A609; --bg:#eef0f8; --tx:#1f2333; --mut:#6b7088; --bd:#dcdfee; --ok:#16a34a; --err:#dc3545; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:'Cairo',system-ui,sans-serif; color:var(--tx);
            min-height:100vh; display:grid; place-items:center; padding:28px 16px;
            background:radial-gradient(1000px 520px at 8% -10%, rgba(49,40,115,.14), transparent 58%),
                       radial-gradient(820px 480px at 100% 0%, rgba(248,166,9,.10), transparent 55%), var(--bg); }
        .reg { width:100%; max-width:940px; display:grid; grid-template-columns:1fr 1.15fr; border-radius:22px; overflow:hidden;
            background:#fff; box-shadow:0 30px 70px -30px rgba(31,35,51,.5); }
        .reg__aside { position:relative; overflow:hidden; padding:38px 32px; color:#fff;
            background:linear-gradient(150deg, var(--p), var(--p2) 60%, #5c4bb0); display:flex; flex-direction:column; gap:18px; }
        .reg__aside::after { content:""; position:absolute; inset-block:-40px; inset-inline-end:-60px; width:220px; height:220px; border-radius:50%; background:rgba(248,166,9,.16); }
        .reg__brand { font-family:'Poppins',sans-serif; font-weight:800; font-size:2.1rem; letter-spacing:-1px; direction:ltr; }
        .reg__brand i { color:var(--a); font-style:normal; }
        .reg__aside h1 { position:relative; z-index:1; font-size:1.5rem; margin:6px 0 0; line-height:1.4; }
        .reg__aside p { position:relative; z-index:1; color:rgba(255,255,255,.85); font-size:.95rem; line-height:1.7; margin:0; }
        .reg__feats { position:relative; z-index:1; list-style:none; margin:auto 0 0; padding:0; display:flex; flex-direction:column; gap:11px; }
        .reg__feats li { display:flex; align-items:center; gap:10px; font-size:.9rem; }
        .reg__feats i { color:var(--a); font-size:1.15rem; }
        .reg__form { padding:34px 32px; }
        .reg__form h2 { margin:0 0 4px; font-size:1.35rem; font-weight:800; }
        .reg__form .sub { margin:0 0 20px; color:var(--mut); font-size:.9rem; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .fld { display:flex; flex-direction:column; gap:6px; margin-bottom:2px; }
        .fld.full { grid-column:1 / -1; }
        .fld label { font-size:.82rem; font-weight:700; }
        .fld input, .fld select { width:100%; padding:11px 13px; font-family:inherit; font-size:.95rem; color:var(--tx);
            border:1.5px solid var(--bd); border-radius:11px; background:#fff; }
        .fld input:focus, .fld select:focus { outline:none; border-color:var(--a); box-shadow:0 0 0 3px rgba(248,166,9,.16); }
        .err { color:var(--err); font-size:.78rem; }
        .flash-err { background:rgba(220,53,69,.09); border:1px solid rgba(220,53,69,.3); color:#a01727; padding:11px 14px; border-radius:11px; font-size:.85rem; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
        .btn { width:100%; margin-top:18px; padding:13px 18px; border:0; border-radius:12px; cursor:pointer;
            font-family:inherit; font-weight:800; font-size:.98rem; color:#2a2363;
            background:linear-gradient(120deg, var(--a), #f7b738); box-shadow:0 8px 20px -4px rgba(248,166,9,.5);
            display:inline-flex; align-items:center; justify-content:center; gap:9px; transition:transform .18s, box-shadow .18s; }
        .btn:hover { transform:translateY(-2px); box-shadow:0 13px 28px -4px rgba(248,166,9,.6); }
        .signin { margin-top:16px; text-align:center; font-size:.86rem; color:var(--mut); }
        .signin a { color:var(--p); font-weight:800; text-decoration:none; }
        .empty { text-align:center; padding:20px; color:var(--mut); font-size:.9rem; }
        @media (max-width:760px){ .reg{ grid-template-columns:1fr; } .reg__aside{ display:none; } }
    </style>
</head>
<body>
    <div class="reg">
        <div class="reg__aside">
            <div class="reg__brand">fleet<i>.</i></div>
            <h1>{{ $t('Launch your taxi office in minutes.', 'أطلق مكتب الأجرة خاصّتك خلال دقائق.') }}</h1>
            <p>{{ $t('Sign up, start a free trial, and go live on the marketplace — no setup calls, no waiting.', 'سجّل، ابدأ تجربة مجانية، وانطلق على المنصّة — دون مكالمات إعداد ولا انتظار.') }}</p>
            <ul class="reg__feats">
                <li><i class="bi bi-check-circle-fill"></i> {{ $t('Free trial, cancel anytime', 'تجربة مجانية، إلغاء في أي وقت') }}</li>
                <li><i class="bi bi-check-circle-fill"></i> {{ $t('Live dispatch & driver app', 'توزيع حيّ وتطبيق سائق') }}</li>
                <li><i class="bi bi-check-circle-fill"></i> {{ $t('Secure billing via Stripe', 'فوترة آمنة عبر Stripe') }}</li>
            </ul>
        </div>

        <div class="reg__form">
            <h2>{{ $t('Create your office account', 'أنشئ حساب مكتبك') }}</h2>
            @if(!empty($selectedPlan))
                <p class="sub">{{ $t('You picked the', 'اخترت خطة') }} <b style="color:var(--brand,#F8A609)">{{ $selectedPlan['name'] }}</b> {{ $t('plan — create your account to start its free trial.', '— أنشئ حسابك لبدء تجربتها المجانية.') }}</p>
            @else
                <p class="sub">{{ $t('Start free — pick a plan after signup.', 'ابدأ مجاناً — اختر خطة بعد التسجيل.') }}</p>
            @endif

            @if($errors->any() && !$errors->has('office_name') && !$errors->has('email') && !$errors->has('password') && !$errors->has('country_id'))
                <div class="flash-err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>
            @endif

            @if(count($countries) === 0)
                <div class="empty"><i class="bi bi-geo-alt"></i><br>{{ $t('Self-registration is not open in any region yet. Please check back soon.', 'التسجيل الذاتي غير متاح في أي منطقة بعد. يُرجى المراجعة قريباً.') }}</div>
            @else
                <form method="POST" action="{{ route('office.register.store') }}">
                    @csrf
                    @if(!empty($selectedPlan))
                        <input type="hidden" name="plan" value="{{ $selectedPlan['key'] }}">
                    @endif
                    <div class="grid">
                        <div class="fld full">
                            <label>{{ $t('Office name', 'اسم المكتب') }}</label>
                            <input name="office_name" value="{{ old('office_name') }}" required>
                            @error('office_name')<span class="err">{{ $message }}</span>@enderror
                        </div>
                        <div class="fld">
                            <label>{{ $t('Contact name', 'اسم المسؤول') }}</label>
                            <input name="contact_name" value="{{ old('contact_name') }}" required>
                            @error('contact_name')<span class="err">{{ $message }}</span>@enderror
                        </div>
                        <div class="fld">
                            <label>{{ $t('Country', 'الدولة') }}</label>
                            <select name="country_id" required>
                                <option value="">{{ $t('Select…', 'اختر…') }}</option>
                                @foreach($countries as $c)
                                    <option value="{{ $c['id'] }}" @selected(old('country_id') == $c['id'])>{{ $c['name'] }}</option>
                                @endforeach
                            </select>
                            @error('country_id')<span class="err">{{ $message }}</span>@enderror
                        </div>
                        <div class="fld">
                            <label>{{ $t('Email', 'البريد الإلكتروني') }}</label>
                            <input type="email" name="email" value="{{ old('email') }}" required>
                            @error('email')<span class="err">{{ $message }}</span>@enderror
                        </div>
                        <div class="fld">
                            <label>{{ $t('Phone', 'الهاتف') }}</label>
                            <input name="phone" value="{{ old('phone') }}" required>
                            @error('phone')<span class="err">{{ $message }}</span>@enderror
                        </div>
                        <div class="fld">
                            <label>{{ $t('City', 'المدينة') }}</label>
                            <select name="city" id="reg_city" disabled data-old="{{ old('city') }}">
                                <option value="">{{ $t('Select a country first', 'اختر الدولة أولاً') }}</option>
                            </select>
                        </div>
                        <div class="fld">
                            <label>{{ $t('Password', 'كلمة المرور') }}</label>
                            <input type="password" name="password" required>
                            @error('password')<span class="err">{{ $message }}</span>@enderror
                        </div>
                        <div class="fld">
                            <label>{{ $t('Confirm password', 'تأكيد كلمة المرور') }}</label>
                            <input type="password" name="password_confirmation" required>
                        </div>
                    </div>
                    <button type="submit" class="btn"><i class="bi bi-rocket-takeoff"></i> {{ $t('Create account & continue', 'إنشاء الحساب والمتابعة') }}</button>
                </form>
            @endif

            <p class="signin">{{ $t('Already have an account?', 'لديك حساب بالفعل؟') }} <a href="{{ url('/') }}">{{ $t('Sign in', 'تسجيل الدخول') }}</a></p>
        </div>
    </div>

    <script>
    (function () {
        var country = document.querySelector('select[name="country_id"]');
        var city = document.getElementById('reg_city');
        if (!country || !city) return;

        var URL = "{{ route('public.office-form') }}";
        var oldCity = city.getAttribute('data-old') || '';

        function loadCities(id, keep) {
            city.disabled = true;
            city.innerHTML = '<option value="">{{ $t('Loading…', 'جارٍ التحميل…') }}</option>';
            if (!id) { city.innerHTML = '<option value="">{{ $t('Select a country first', 'اختر الدولة أولاً') }}</option>'; return; }
            fetch(URL + '?country=' + encodeURIComponent(id), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    city.innerHTML = '<option value="">{{ $t('Select a city', 'اختر مدينة') }}</option>';
                    (d.cities || []).forEach(function (c) {
                        var o = new Option(c.name, c.name);
                        if (keep && c.name === keep) o.selected = true;
                        city.appendChild(o);
                    });
                    city.disabled = !(d.cities && d.cities.length);
                })
                .catch(function () { city.innerHTML = '<option value="">{{ $t('Could not load cities', 'تعذّر تحميل المدن') }}</option>'; });
        }

        country.addEventListener('change', function () { loadCities(country.value, ''); });
        // Repopulate on a validation bounce-back so the chosen city survives.
        if (country.value) { loadCities(country.value, oldCity); }
    })();
    </script>
</body>
</html>
