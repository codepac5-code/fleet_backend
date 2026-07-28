@php $isRtl = app()->getLocale() === 'ar'; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ textByLanguage('تجهيز النظام', 'System Setup') }} — {{ $appName }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --p: #312873; --a: #5b4bd6; --ok: #1a7f37; --danger: #dc3545; --bg: #0f1122; --card: #fff; --border: #e6e6ef; --text: #1c1c28; --muted: #6b6b80; }
        body { font-family: 'Cairo', system-ui, sans-serif; background: linear-gradient(135deg, #1a1633, #2b2358 55%, #0f1122); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; color: var(--text); }
        .wrap { width: min(760px, 100%); background: var(--card); border-radius: 22px; box-shadow: 0 40px 90px -30px rgba(0,0,0,.6); overflow: hidden; }
        .hero { background: linear-gradient(120deg, var(--p), var(--a)); color: #fff; padding: 26px 30px; }
        .hero h1 { font-size: 1.5rem; font-weight: 800; display: flex; align-items: center; gap: 12px; }
        .hero p { opacity: .85; margin-top: 6px; font-size: .9rem; }
        .steps { display: flex; gap: 4px; padding: 18px 30px 0; }
        .st { flex: 1; text-align: center; font-size: .72rem; font-weight: 700; color: var(--muted); padding-bottom: 12px; position: relative; }
        .st::after { content: ''; position: absolute; inset-inline: 0; bottom: 0; height: 3px; border-radius: 3px; background: var(--border); transition: .25s; }
        .st.active { color: var(--p); } .st.active::after { background: var(--p); }
        .st.done { color: var(--ok); } .st.done::after { background: var(--ok); }
        .st b { display: inline-grid; place-items: center; width: 20px; height: 20px; border-radius: 50%; background: var(--border); color: #fff; font-size: .7rem; margin-inline-end: 5px; }
        .st.active b { background: var(--p); } .st.done b { background: var(--ok); }
        .pane { display: none; padding: 24px 30px; }
        .pane.active { display: block; animation: fade .25s; }
        @keyframes fade { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        h2 { font-size: 1.15rem; margin-bottom: 6px; }
        .sub { color: var(--muted); font-size: .86rem; margin-bottom: 18px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .f.full { grid-column: 1 / -1; }
        .f label { display: block; font-size: .8rem; font-weight: 700; margin-bottom: 6px; }
        .f input { width: 100%; padding: 11px 13px; border: 1.5px solid var(--border); border-radius: 10px; font-family: inherit; font-size: .92rem; }
        .f input:focus { outline: none; border-color: var(--a); box-shadow: 0 0 0 3px rgba(91,75,214,.14); }
        .f input[dir=ltr] { direction: ltr; text-align: start; }
        .err { color: var(--danger); font-size: .74rem; margin-top: 4px; min-height: 0; }
        .reqs { list-style: none; display: grid; gap: 10px; }
        .req { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1.5px solid var(--border); border-radius: 12px; font-weight: 600; font-size: .9rem; }
        .req .ico { width: 26px; height: 26px; border-radius: 50%; display: grid; place-items: center; color: #fff; font-weight: 800; }
        .req.ok .ico { background: var(--ok); } .req.bad .ico { background: var(--danger); }
        .req.bad { border-color: var(--danger); }
        .note { margin-top: 16px; padding: 12px 14px; background: #f6f7fb; border-radius: 10px; font-size: .82rem; color: var(--muted); }
        .flash { margin-top: 16px; border-radius: 12px; padding: 12px 15px; font-weight: 700; font-size: .86rem; display: none; align-items: center; gap: 10px; }
        .flash.show { display: flex; }
        .flash.work { background: rgba(49,40,115,.08); color: var(--p); }
        .flash.ok { background: rgba(26,127,55,.12); color: var(--ok); }
        .flash.bad { background: rgba(220,53,69,.1); color: var(--danger); }
        .flash small { font-weight: 500; opacity: .85; direction: ltr; }
        .spin { width: 16px; height: 16px; border: 2px solid currentColor; border-inline-start-color: transparent; border-radius: 50%; animation: sp .6s linear infinite; flex: none; }
        @keyframes sp { to { transform: rotate(360deg); } }
        .foot { display: flex; gap: 10px; align-items: center; padding: 18px 30px; border-top: 1px solid var(--border); }
        .btn { border: none; border-radius: 11px; padding: 12px 22px; font-family: inherit; font-weight: 800; font-size: .92rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .btn.primary { background: var(--p); color: #fff; box-shadow: 0 10px 22px -12px var(--p); }
        .btn.primary:hover { filter: brightness(1.08); }
        .btn.primary:disabled { opacity: .55; cursor: not-allowed; filter: none; }
        .btn.ghost { background: none; border: 1.5px solid var(--border); color: var(--text); }
        .btn.next { margin-inline-start: auto; }
        .done-hero { text-align: center; padding: 20px 0; }
        .done-hero .big { font-size: 3.4rem; }
        @media (max-width: 560px) { .grid { grid-template-columns: 1fr; } .st span { display: none; } }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="hero">
            <h1>⚙️ {{ textByLanguage('تجهيز', 'Setup') }} · {{ $appName }}</h1>
            <p>{{ textByLanguage('لم يتم تثبيت النظام بعد. أكمل الخطوات لتهيئة قاعدة البيانات والمدير وأول دولة.', 'The system is not installed yet. Complete the steps to set up the database, admin, and first country.') }}</p>
        </div>

        <div class="steps">
            <div class="st active" data-step="1"><b>1</b><span>{{ textByLanguage('المتطلبات', 'Requirements') }}</span></div>
            <div class="st" data-step="2"><b>2</b><span>{{ textByLanguage('قاعدة البيانات', 'Database') }}</span></div>
            <div class="st" data-step="3"><b>3</b><span>{{ textByLanguage('المدير', 'Admin') }}</span></div>
            <div class="st" data-step="4"><b>4</b><span>{{ textByLanguage('أول دولة', 'First country') }}</span></div>
            <div class="st" data-step="5"><b>5</b><span>{{ textByLanguage('إنهاء', 'Finish') }}</span></div>
        </div>

        {{-- Step 1: requirements --}}
        <div class="pane active" data-pane="1">
            <h2>{{ textByLanguage('فحص المتطلبات', 'Requirements check') }}</h2>
            <p class="sub">{{ textByLanguage('يجب أن تكون كل البنود مستوفاة للمتابعة.', 'All items must pass before continuing.') }}</p>
            <ul class="reqs">
                @php $labels = ['php'=>'PHP ≥ 8.2','pdo_mysql'=>'PDO MySQL','env_writable'=>textByLanguage('ملف .env قابل للكتابة','.env writable'),'storage_writable'=>textByLanguage('مجلد storage قابل للكتابة','storage writable')]; @endphp
                @foreach($checks as $key => $passed)
                    <li class="req {{ $passed ? 'ok' : 'bad' }}">
                        <span class="ico">{{ $passed ? '✓' : '✕' }}</span>
                        <span>{{ $labels[$key] ?? $key }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Step 2: database --}}
        <div class="pane" data-pane="2">
            <h2>{{ textByLanguage('قاعدة البيانات المركزية', 'Central database') }}</h2>
            <p class="sub">{{ textByLanguage('بيانات الاتصال بالقاعدة الرئيسية. سنحاول إنشاءها إن لم تكن موجودة، ثم نهيّئ الجداول.', 'The central database credentials. We will try to create it if missing, then build the tables.') }}</p>
            <form id="dbForm" onsubmit="return false;">
                <div class="grid">
                    <div class="f"><label>{{ textByLanguage('المضيف','Host') }}</label><input name="db_host" dir="ltr" value="{{ $defaults['db_host'] }}"><div class="err" data-err="db_host"></div></div>
                    <div class="f"><label>{{ textByLanguage('المنفذ','Port') }}</label><input name="db_port" dir="ltr" value="{{ $defaults['db_port'] }}"><div class="err" data-err="db_port"></div></div>
                    <div class="f full"><label>{{ textByLanguage('اسم القاعدة','Database name') }}</label><input name="db_name" dir="ltr" value="{{ $defaults['db_name'] }}"><div class="err" data-err="db_name"></div></div>
                    <div class="f"><label>{{ textByLanguage('المستخدم','Username') }}</label><input name="db_user" dir="ltr" value="{{ $defaults['db_user'] }}"><div class="err" data-err="db_user"></div></div>
                    <div class="f"><label>{{ textByLanguage('كلمة المرور','Password') }}</label><input name="db_pass" type="password" dir="ltr" placeholder="••••••••"><div class="err" data-err="db_pass"></div></div>
                </div>
                <div style="margin-top:14px;"><button type="button" class="btn ghost" onclick="testDb()">🔌 {{ textByLanguage('اختبار الاتصال','Test connection') }}</button></div>
                <div class="flash" id="dbFlash"></div>
            </form>
        </div>

        {{-- Step 3: admin --}}
        <div class="pane" data-pane="3">
            <h2>{{ textByLanguage('حساب المدير','Administrator account') }}</h2>
            <p class="sub">{{ textByLanguage('سيُنشأ المدير الرئيسي مع الأدوار والصلاحيات والعملات والخطط الأساسية.','The super-admin will be created along with roles, permissions, currencies and base plans.') }}</p>
            <form id="adminForm" onsubmit="return false;">
                <div class="grid">
                    <div class="f"><label>{{ textByLanguage('الاسم الأول','First name') }}</label><input name="firstName" value="Super"><div class="err" data-err="firstName"></div></div>
                    <div class="f"><label>{{ textByLanguage('الاسم الأخير','Last name') }}</label><input name="lastName" value="Admin"><div class="err" data-err="lastName"></div></div>
                    <div class="f full"><label>{{ textByLanguage('البريد الإلكتروني','Email') }}</label><input name="email" type="email" dir="ltr" placeholder="admin@fleetos.app"><div class="err" data-err="email"></div></div>
                    <div class="f full"><label>{{ textByLanguage('كلمة المرور','Password') }}</label><input name="password" type="password" dir="ltr" placeholder="{{ textByLanguage('8 أحرف على الأقل','At least 8 characters') }}"><div class="err" data-err="password"></div></div>
                </div>
                <div class="flash" id="adminFlash"></div>
            </form>
        </div>

        {{-- Step 4: country --}}
        <div class="pane" data-pane="4">
            <h2>{{ textByLanguage('أول دولة','First country') }}</h2>
            <p class="sub">{{ textByLanguage('تعمل أول دولة على القاعدة المركزية نفسها. يمكنك لاحقاً إضافة دول بقواعد منفصلة من شاشة الدول.','The first country runs on the central database itself. You can later add countries with separate databases from the Countries screen.') }}</p>
            <form id="countryForm" onsubmit="return false;">
                <div class="grid">
                    <div class="f full"><label>{{ textByLanguage('اسم الدولة','Country name') }}</label><input name="name" placeholder="{{ textByLanguage('سوريا','Syria') }}"><div class="err" data-err="name"></div></div>
                    <div class="f"><label>{{ textByLanguage('رمز الدولة (ISO2)','Country code (ISO2)') }}</label><input name="country_code" dir="ltr" maxlength="2" placeholder="SY" style="text-transform:uppercase;"><div class="err" data-err="country_code"></div></div>
                    <div class="f"><label>{{ textByLanguage('المدينة','City') }}</label><input name="city" placeholder="{{ textByLanguage('دمشق','Damascus') }}"><div class="err" data-err="city"></div></div>
                    <div class="f"><label>{{ textByLanguage('رمز العملة','Currency code') }}</label><input name="currency_code" dir="ltr" maxlength="3" placeholder="USD" style="text-transform:uppercase;"><div class="err" data-err="currency_code"></div></div>
                    <div class="f"><label>{{ textByLanguage('علامة العملة','Currency symbol') }}</label><input name="currency_symbol" placeholder="$"><div class="err" data-err="currency_symbol"></div></div>
                </div>
                <div class="flash" id="countryFlash"></div>
            </form>
        </div>

        {{-- Step 5: finish --}}
        <div class="pane" data-pane="5">
            <div class="done-hero">
                <div class="big">🎉</div>
                <h2>{{ textByLanguage('كل شيء جاهز','Everything is ready') }}</h2>
                <p class="sub">{{ textByLanguage('اضغط إنهاء لقفل وضع التجهيز والانتقال إلى لوحة التحكم.','Click Finish to lock setup mode and go to the dashboard.') }}</p>
            </div>
            <div class="flash" id="finishFlash"></div>
        </div>

        <div class="foot">
            <button type="button" class="btn ghost" id="backBtn" onclick="step(-1)" style="display:none;">‹ {{ textByLanguage('السابق','Back') }}</button>
            <button type="button" class="btn primary next" id="nextBtn" onclick="next()">{{ textByLanguage('التالي','Next') }} ›</button>
        </div>
    </div>

    <script>
    (function () {
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const T = {
            working: @json(textByLanguage('جارٍ التنفيذ…','Working…')),
            migrating: @json(textByLanguage('جارٍ تهيئة الجداول… قد يستغرق دقيقة','Building tables… this may take a minute')),
            reqFail: @json(textByLanguage('عالج المتطلبات غير المستوفاة أولاً','Resolve the failed requirements first')),
            finish: @json(textByLanguage('إنهاء','Finish')),
            next: @json(textByLanguage('التالي','Next')) + ' ›',
        };
        const reqOk = @json(!in_array(false, $checks, true));
        let cur = 1;

        function q(s, r) { return (r||document).querySelector(s); }
        function qa(s, r) { return Array.from((r||document).querySelectorAll(s)); }

        async function api(url, body) {
            const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify(body||{}) });
            let j = {}; try { j = await res.json(); } catch (e) {}
            return { status: res.status, j };
        }
        function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
        function collect(form) { const o = {}; qa('input', form).forEach(i => o[i.name] = i.value); return o; }
        function clearErr(form) { qa('.err', form).forEach(e => e.textContent = ''); }
        function showErr(form, errors) { clearErr(form); Object.keys(errors||{}).forEach(k => { const e = q('[data-err="'+k+'"]', form); if (e) e.textContent = Array.isArray(errors[k]) ? errors[k][0] : errors[k]; }); }
        function flash(id, cls, html) { const el = q('#'+id); el.className = 'flash show ' + cls; el.innerHTML = html; }

        function render() {
            qa('.pane').forEach(p => p.classList.toggle('active', +p.dataset.pane === cur));
            qa('.st').forEach(s => { const n = +s.dataset.step; s.classList.toggle('active', n === cur); s.classList.toggle('done', n < cur); });
            q('#backBtn').style.display = cur > 1 ? 'inline-flex' : 'none';
            q('#nextBtn').textContent = cur === 5 ? T.finish : T.next;
        }
        window.step = d => { cur = Math.min(5, Math.max(1, cur + d)); render(); };

        window.testDb = async function () {
            const c = collect(q('#dbForm'));
            flash('dbFlash', 'work', '<span class="spin"></span> ' + T.working);
            const { j } = await api('{{ route('setup.test') }}', c);
            flash('dbFlash', j.ok ? 'ok' : 'bad', (j.ok ? '✅ ' : '⚠️ ') + esc(j.message||'') + (j.server ? ' <small>· '+esc(j.server)+'</small>' : '') + (j.error ? ' <small>· '+esc(j.error).slice(0,120)+'</small>' : ''));
        };

        async function submitStep() {
            const btn = q('#nextBtn');
            if (cur === 1) { if (!reqOk) { alert(T.reqFail); return false; } return true; }

            if (cur === 2) {
                const form = q('#dbForm'); clearErr(form);
                btn.disabled = true; flash('dbFlash', 'work', '<span class="spin"></span> ' + T.migrating);
                const { status, j } = await api('{{ route('setup.database') }}', collect(form));
                btn.disabled = false;
                if (status === 422) { showErr(form, j.errors); flash('dbFlash','bad','⚠️ '+esc(j.message||'Validation error')); return false; }
                if (!j.ok) { flash('dbFlash','bad','❌ '+esc(j.message||'')+(j.error?' <small>· '+esc(j.error).slice(0,140)+'</small>':'')); return false; }
                flash('dbFlash','ok','✅ '+esc(j.message||'')); return true;
            }

            if (cur === 3) {
                const form = q('#adminForm'); clearErr(form);
                btn.disabled = true; flash('adminFlash','work','<span class="spin"></span> '+T.working);
                const { status, j } = await api('{{ route('setup.admin') }}', collect(form));
                btn.disabled = false;
                if (status === 422) { showErr(form, j.errors); flash('adminFlash','bad','⚠️ '+esc(j.message||'Validation error')); return false; }
                if (!j.ok) { flash('adminFlash','bad','❌ '+esc(j.message||'')+(j.error?' <small>· '+esc(j.error).slice(0,140)+'</small>':'')); return false; }
                flash('adminFlash','ok','✅ '+esc(j.message||'')); return true;
            }

            if (cur === 4) {
                const form = q('#countryForm'); clearErr(form);
                btn.disabled = true; flash('countryFlash','work','<span class="spin"></span> '+T.working);
                const { status, j } = await api('{{ route('setup.country') }}', collect(form));
                btn.disabled = false;
                if (status === 422) { showErr(form, j.errors); flash('countryFlash','bad','⚠️ '+esc(j.message||'Validation error')); return false; }
                if (!j.ok) { flash('countryFlash','bad','❌ '+esc(j.message||'')+(j.error?' <small>· '+esc(j.error).slice(0,140)+'</small>':'')); return false; }
                flash('countryFlash','ok','✅ '+esc(j.message||'')); return true;
            }

            if (cur === 5) {
                btn.disabled = true; flash('finishFlash','work','<span class="spin"></span> '+T.working);
                const { j } = await api('{{ route('setup.finish') }}', {});
                if (j.ok) { flash('finishFlash','ok','✅ '+esc(j.message||'')); location.href = j.redirect || '/'; return false; }
                btn.disabled = false; flash('finishFlash','bad','❌ '+esc(j.message||'')); return false;
            }
            return true;
        }

        window.next = async function () { if (await submitStep()) { if (cur < 5) { cur++; render(); } } };
        render();
    })();
    </script>
</body>
</html>
