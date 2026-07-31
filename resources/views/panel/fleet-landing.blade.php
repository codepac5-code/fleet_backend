@php
    $ar = app()->getLocale() === 'ar';
    $dir = $ar ? 'rtl' : 'ltr';
    $t = fn($en, $arText) => $ar ? $arText : $en;
    $img = fn($n) => asset('panel/img/brand/' . $n);
    $pageTitle = $t('FleetOS — Launch your taxi office in the cloud', 'FleetOS — أطلق مكتب سيارات الأجرة في السحابة');
    $pageDesc = $t('A multi-tenant mobility marketplace. Launch a branded taxi office, manage drivers, and grow — riders choose you by rating and price.', 'سوق تنقّل متعدّد المستأجرين. أطلق مكتباً باسمك، أدِر السائقين، وانمُ — الركّاب يختارونك بالتقييم والسعر.');
    $pageTitles = [
        'home' => 'FleetOS',
        'platform' => $t('Platform · FleetOS', 'المنصّة · FleetOS'),
        'pricing' => $t('Pricing · FleetOS', 'الأسعار · FleetOS'),
        'drivers' => $t('For drivers · FleetOS', 'للسائقين · FleetOS'),
        'offices' => $t('For offices · FleetOS', 'للمكاتب · FleetOS'),
        'contact' => $t('Contact · FleetOS', 'تواصل · FleetOS'),
    ];
    $socialLinks = collect([
        'facebook_url' => 'fa-facebook-f', 'instagram_url' => 'fa-instagram', 'twitter_url' => 'fa-x-twitter',
        'linkedin_url' => 'fa-linkedin-in', 'youtube_url' => 'fa-youtube', 'tiktok_url' => 'fa-tiktok', 'whatsapp_url' => 'fa-whatsapp',
    ])->map(fn($icon, $key) => ['url' => \App\Models\SiteSetting::val($key), 'icon' => $icon])
      ->filter(fn($x) => !empty($x['url']))->values();
    $footerAbout = \App\Models\SiteSetting::val($ar ? 'footer_about_ar' : 'footer_about_en', $t('The cloud marketplace for mobility. Launch a branded taxi office, manage drivers, and grow.', 'سوق التنقّل السحابيّ. أطلق مكتب أجرة باسمك، أدِر السائقين، وانمُ.'));
    $brandLogo = \App\Models\SiteSetting::val('brand_logo');
    $brandPrimary = \App\Models\SiteSetting::val('brand_primary');
    $brandSecondary = \App\Models\SiteSetting::val('brand_secondary');
    $jsStrings = [
        'ok_office' => $t('Application received! Our team will contact you soon.', 'تمّ استلام طلبك! سيتواصل معك فريقنا قريباً.'),
        'ok_driver' => $t('Driver application submitted! We will review it shortly.', 'تمّ إرسال طلب السائق! سنراجعه قريباً.'),
        'ok_contact' => $t('Message sent! We will get back to you within 24 hours.', 'تمّ إرسال رسالتك! سنعاود التواصل خلال ٢٤ ساعة.'),
        'err' => $t('Please check the highlighted fields.', 'يرجى مراجعة الحقول المميّزة.'),
        'net' => $t('Something went wrong. Please try again.', 'حدث خطأ ما. حاول مرّة أخرى.'),
        'files' => $t('Please attach all required images.', 'يرجى إرفاق كلّ الصور المطلوبة.'),
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}" />
    <meta name="theme-color" content="#1b1440" />
    <link rel="icon" href="{{ asset('favicon.ico') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ $pageTitle }}" />
    <meta property="og:description" content="{{ $pageDesc }}" />
    <meta property="og:site_name" content="FleetOS" />

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <style>
        :root {
            --primary: #F29C0B;
            --primary-2: #FFB43B;
            --primary-dark: #d98708;
            --ink: #1b1440;
            --ink-2: #2a1f66;
            --ink-3: #120d2e;
            --text: #1A1A1A;
            --muted: #6b7391;
            --bg: #FBFBFE;
            --card: #ffffff;
            --line: #ecedf6;
            --line-2: #f2f3fa;
            --radius: 20px;
            /* Layered, low-opacity shadows — the calm depth clean SaaS sites use,
               instead of one heavy drop. */
            --shadow-sm: 0 1px 2px rgba(27,20,64,.04), 0 4px 12px rgba(27,20,64,.05);
            --shadow: 0 2px 4px rgba(27,20,64,.04), 0 12px 32px rgba(27,20,64,.08), 0 24px 60px rgba(27,20,64,.06);
            --shadow-glow: 0 6px 16px rgba(242,156,11,.18), 0 14px 40px rgba(242,156,11,.14);
            --ring: 0 0 0 4px rgba(242,156,11,.18);
            --font: 'Plus Jakarta Sans', sans-serif;
        }
        [dir="rtl"] { --font: 'Cairo', sans-serif; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: var(--font); }
        html { scroll-behavior: smooth; }
        body { background: var(--bg); color: var(--text); overflow-x: hidden;
               -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; text-rendering: optimizeLegibility; }
        :focus-visible { outline: none; box-shadow: var(--ring); border-radius: 12px; }
        a { text-decoration: none; color: inherit; }
        img { max-width: 100%; }
        ::selection { background: var(--primary); color: #fff; }
        .wrap { max-width: 1200px; margin: 0 auto; padding: 0 1.4rem; }

        /* BUTTONS */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: .55rem; font-weight: 700; border-radius: 12px; border: none; cursor: pointer; transition: transform .2s cubic-bezier(.2,.7,.2,1), box-shadow .2s, background .2s, border-color .2s; font-size: .95rem; white-space: nowrap; letter-spacing: -.01em; }
        .btn:active { transform: translateY(0) scale(.985); }
        .btn-primary { background: linear-gradient(180deg, var(--primary-2), var(--primary)); color: #fff; padding: .9rem 1.5rem; box-shadow: var(--shadow-glow), inset 0 1px 0 rgba(255,255,255,.28); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(242,156,11,.26), 0 18px 48px rgba(242,156,11,.18), inset 0 1px 0 rgba(255,255,255,.28); }
        .btn-dark { background: var(--ink); color: #fff; padding: .9rem 1.5rem; box-shadow: var(--shadow-sm); }
        .btn-dark:hover { background: var(--ink-2); transform: translateY(-2px); }
        .btn-ghost { background: #fff; color: var(--ink); padding: .85rem 1.45rem; border: 1px solid var(--line); box-shadow: var(--shadow-sm); }
        .btn-ghost:hover { border-color: #d9dbec; transform: translateY(-2px); box-shadow: var(--shadow); }
        .btn-light { background: rgba(255,255,255,.12); color: #fff; padding: .9rem 1.5rem; border: 1px solid rgba(255,255,255,.22); backdrop-filter: blur(8px); }
        .btn-light:hover { background: rgba(255,255,255,.2); transform: translateY(-2px); }
        .btn-block { width: 100%; }

        .eyebrow { display: inline-flex; align-items: center; gap: .5rem; font-size: .72rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--primary-dark); background: rgba(242, 156, 11, .1); padding: .45rem .9rem; border-radius: 999px; border: 1px solid rgba(242,156,11,.14); }
        .eyebrow.on-dark { color: var(--primary-2); background: rgba(242,156,11,.14); border-color: rgba(242,156,11,.2); }
        .h-sec { font-size: clamp(2rem, 4.2vw, 3.15rem); font-weight: 800; color: var(--ink); letter-spacing: -.03em; line-height: 1.08; }
        .sub-sec { color: var(--muted); font-size: 1.08rem; line-height: 1.7; max-width: 640px; }
        .pad { padding: 5.5rem 0; }
        .center { text-align: center; }
        .center .sub-sec { margin-inline: auto; }

        /* HEADER */
        header { position: fixed; inset: 0 0 auto 0; z-index: 100; height: 76px; display: flex; align-items: center; transition: .35s; }
        header.scrolled { background: rgba(255,255,255,.72); backdrop-filter: blur(20px) saturate(1.5); -webkit-backdrop-filter: blur(20px) saturate(1.5); border-bottom: 1px solid rgba(27,20,64,.06); box-shadow: 0 1px 0 rgba(255,255,255,.6), 0 6px 24px rgba(27,20,64,.05); }
        .hd { display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 1rem; }
        .logo { display: flex; align-items: center; gap: .65rem; cursor: pointer; }
        .logo .mark { width: 44px; height: 44px; border-radius: 14px; background: linear-gradient(135deg, var(--ink), var(--ink-3)); display: grid; place-items: center; color: var(--primary); box-shadow: 0 10px 26px rgba(27,20,64,.34); position: relative; overflow: hidden; }
        .logo .mark::after { content:''; position:absolute; inset:0; background: radial-gradient(circle at 30% 20%, rgba(255,255,255,.28), transparent 60%); }
        .logo .mark i { font-size: 1.3rem; animation: spin 7s linear infinite; }
        .logo .txt { font-size: 1.55rem; font-weight: 800; letter-spacing: -.5px; direction: ltr; }
        .logo .txt b { color: var(--primary); } .logo .txt span { color: var(--ink); }
        header.solid .logo .txt span, header.solid .navlink { color: #fff; }

        nav.main { display: flex; align-items: center; gap: .35rem; }
        .navlink { font-weight: 600; font-size: .95rem; color: var(--ink); opacity: .82; padding: .55rem .95rem; border-radius: 999px; transition: .2s; cursor: pointer; background: none; border: none; }
        .navlink:hover { opacity: 1; background: rgba(27,20,64,.06); }
        .navlink.active { opacity: 1; color: var(--primary-dark); background: rgba(242,156,11,.12); }
        .hd-actions { display: flex; align-items: center; gap: .7rem; }
        .lang { display: inline-flex; align-items: center; gap: .35rem; font-weight: 700; font-size: .82rem; color: var(--ink); border: 1.5px solid var(--line); background: #fff; padding: .55rem .85rem; border-radius: 999px; transition: .2s; }
        .lang:hover { border-color: var(--ink); }
        .signin { font-weight: 600; font-size: .92rem; color: var(--ink); padding: .55rem .6rem; }
        .burger { display: none; font-size: 1.45rem; color: var(--ink); background: none; border: none; }

        /* DRAWER */
        .drawer { position: fixed; inset: 0; z-index: 200; visibility: hidden; }
        .drawer .ov { position: absolute; inset: 0; background: rgba(18,13,46,.5); opacity: 0; transition: .3s; }
        .drawer .panel { position: absolute; top: 0; bottom: 0; inset-inline-end: 0; width: min(86vw, 350px); background: #fff; padding: 1.4rem; transform: translateX(var(--slide, 110%)); transition: .38s cubic-bezier(.4,0,.2,1); display: flex; flex-direction: column; gap: .25rem; }
        [dir="rtl"] .drawer .panel { --slide: -110%; }
        .drawer.open { visibility: visible; }
        .drawer.open .ov { opacity: 1; }
        .drawer.open .panel { transform: translateX(0); }
        .drawer .dlink { padding: .95rem .85rem; border-radius: 12px; font-weight: 600; color: var(--ink); cursor: pointer; }
        .drawer .dlink:hover, .drawer .dlink.active { background: var(--bg); color: var(--primary-dark); }
        .drawer .close { align-self: flex-end; font-size: 1.4rem; background: none; border: none; color: var(--muted); margin-bottom: .4rem; }

        /* PAGES */
        main { padding-top: 76px; }
        .page { display: none; }
        .page.active { display: block; animation: pageIn .55s cubic-bezier(.2,.7,.2,1); }
        @keyframes pageIn { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }

        /* HERO */
        .hero { position: relative; padding: 6.5rem 0 4rem; overflow: hidden; }
        .hero-bg { position: absolute; inset: 0; z-index: 0; background:
            radial-gradient(60% 55% at 18% 8%, rgba(242,156,11,.11), transparent 60%),
            radial-gradient(55% 60% at 88% 20%, rgba(49,40,115,.10), transparent 60%),
            radial-gradient(50% 60% at 60% 110%, rgba(255,180,59,.09), transparent 60%); }
        /* Fine grid that fades out — the quiet texture behind a clean SaaS hero. */
        .hero-bg::after { content:''; position:absolute; inset:0;
            background-image: linear-gradient(rgba(27,20,64,.035) 1px, transparent 1px), linear-gradient(90deg, rgba(27,20,64,.035) 1px, transparent 1px);
            background-size: 36px 36px;
            -webkit-mask-image: radial-gradient(72% 58% at 50% 28%, #000, transparent 76%);
                    mask-image: radial-gradient(72% 58% at 50% 28%, #000, transparent 76%); }
        .hero-grid { position: relative; z-index: 1; display: grid; grid-template-columns: 1.05fr .95fr; gap: 3rem; align-items: center; }
        .hero h1 { font-size: clamp(2.4rem, 5.2vw, 3.9rem); font-weight: 800; line-height: 1.05; letter-spacing: -1.2px; color: var(--ink); margin: 1.1rem 0; }
        .hero h1 .hl { position: relative; color: var(--primary); white-space: nowrap; }
        .hero h1 .hl::after { content:''; position:absolute; inset-inline:0; bottom: 6px; height: 10px; background: linear-gradient(90deg, rgba(242,156,11,.3), rgba(255,180,59,.18)); border-radius: 6px; z-index:-1; }
        .hero p.lead { color: var(--muted); font-size: 1.14rem; line-height: 1.75; max-width: 540px; }
        .hero-cta { display: flex; gap: .9rem; flex-wrap: wrap; margin: 1.8rem 0 1.5rem; }
        .trust { display: flex; flex-wrap: wrap; gap: .55rem; }
        .trust span { font-size: .82rem; font-weight: 600; color: var(--ink); background: #fff; border: 1px solid var(--line); padding: .5rem .85rem; border-radius: 999px; box-shadow: var(--shadow-sm); }
        .trust span i { color: #22c55e; margin-inline-end: .4rem; }

        /* APP SHOWCASE — real screenshots in device frames */
        .showcase { position: relative; overflow: hidden; }
        .showcase-bg { position:absolute; inset:0; z-index:0; background:
            radial-gradient(55% 50% at 50% 0%, rgba(242,156,11,.08), transparent 60%),
            radial-gradient(50% 55% at 100% 100%, rgba(49,40,115,.08), transparent 60%); }
        .shots { position: relative; z-index:1; display: flex; gap: 1.4rem; justify-content: center; align-items: flex-end; flex-wrap: nowrap; overflow-x: auto; padding: 2rem .5rem 3rem; scroll-snap-type: x mandatory; -ms-overflow-style: none; scrollbar-width: none; }
        .shots::-webkit-scrollbar { display: none; }
        .device { flex: 0 0 auto; width: 216px; border-radius: 34px; background: linear-gradient(160deg,#241b52,#0e0a24); padding: 7px; box-shadow: 0 2px 4px rgba(27,20,64,.12), 0 22px 44px rgba(27,20,64,.18), 0 40px 90px rgba(27,20,64,.14); position: relative; scroll-snap-align: center; transition: transform .35s cubic-bezier(.2,.7,.2,1), box-shadow .35s; }
        .device::before { content:''; position:absolute; top:11px; inset-inline-start:50%; transform:translateX(-50%); width:80px; height:20px; background:#0e0a24; border-radius:0 0 12px 12px; z-index:2; }
        .device img { display:block; width:100%; border-radius: 28px; }
        .device:hover { transform: translateY(-8px); box-shadow: 0 4px 8px rgba(27,20,64,.14), 0 30px 60px rgba(27,20,64,.24); }
        .device.raise { transform: translateY(-26px); }
        .device.raise:hover { transform: translateY(-34px); }
        .device .cap { position:absolute; inset-inline:0; bottom:-30px; text-align:center; font-size:.78rem; font-weight:700; color:var(--muted); }
        @media (max-width: 720px) { .shots { justify-content: flex-start; } .device { width: 190px; } .device.raise { transform: none; } }

        /* PHONE MOCKUP */
        .phone-wrap { position: relative; display: flex; justify-content: center; }
        .blob { position: absolute; width: 420px; height: 420px; border-radius: 46% 54% 58% 42%/47% 44% 56% 53%; background: linear-gradient(140deg, var(--ink), var(--ink-3)); z-index: 0; box-shadow: var(--shadow); animation: morph 9s ease-in-out infinite; }
        @keyframes morph { 0%,100%{ border-radius: 46% 54% 58% 42%/47% 44% 56% 53%; } 50%{ border-radius: 58% 42% 44% 56%/54% 58% 42% 46%; } }
        .phone { position: relative; z-index: 1; width: 268px; background: #0f0a26; border-radius: 40px; padding: 12px; box-shadow: 0 40px 80px rgba(18,13,46,.5); border: 1px solid rgba(255,255,255,.08); }
        .phone .screen { background: linear-gradient(180deg,#fff,#f3f4fb); border-radius: 30px; overflow: hidden; }
        .ph-top { background: linear-gradient(135deg,var(--ink),var(--ink-2)); color: #fff; padding: 1rem 1rem .9rem; }
        .ph-top small { opacity: .7; font-size: .68rem; }
        .ph-top h5 { font-size: .98rem; font-weight: 800; margin-top: 2px; }
        .ph-body { padding: .9rem; }
        .ph-off { display: flex; align-items: center; justify-content: space-between; background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: .7rem .8rem; margin-bottom: .55rem; box-shadow: var(--shadow-sm); animation: floatUp .6s both; }
        .ph-off:nth-child(2){ animation-delay:.1s } .ph-off:nth-child(3){ animation-delay:.2s }
        @keyframes floatUp { from{ opacity:0; transform: translateY(10px);} to{opacity:1; transform:none;} }
        .ph-off .nm { font-weight: 800; font-size: .78rem; color: var(--ink); }
        .ph-off .st { font-size: .62rem; color: #f0a500; }
        .ph-off .pr { font-weight: 800; font-size: .82rem; color: var(--primary-dark); }
        .ph-cta { margin-top: .4rem; background: linear-gradient(135deg,var(--primary),var(--primary-2)); color:#fff; text-align:center; font-weight:800; font-size:.82rem; padding:.7rem; border-radius: 12px; }
        .float-badge { position: absolute; z-index: 2; background: #fff; border-radius: 14px; padding: .6rem .8rem; box-shadow: var(--shadow); display: flex; align-items: center; gap: .5rem; font-weight: 700; font-size: .76rem; color: var(--ink); animation: bob 3.5s ease-in-out infinite; }
        .float-badge i { color: #22c55e; }
        .fb-1 { top: 14%; inset-inline-start: -6%; } .fb-2 { bottom: 12%; inset-inline-end: -4%; animation-delay: 1.2s; }
        @keyframes bob { 0%,100%{ transform: translateY(0);} 50%{ transform: translateY(-8px);} }

        /* STRIP */
        .strip { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; background: #fff; border: 1px solid var(--line); border-radius: 22px; padding: 1.8rem; box-shadow: var(--shadow-sm); }
        .strip .s b { display: block; font-size: 2.1rem; font-weight: 800; color: var(--ink); letter-spacing: -1px; }
        .strip .s span { color: var(--muted); font-size: .85rem; font-weight: 600; }

        /* CARDS */
        .grid-3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 1.4rem; }
        .grid-4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 1.2rem; }
        .card { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 1.8rem; transition: transform .28s cubic-bezier(.2,.7,.2,1), box-shadow .28s, border-color .28s; will-change: transform; }
        .card:hover { transform: translateY(-4px); box-shadow: var(--shadow); border-color: var(--line-2); }
        .ic { width: 54px; height: 54px; border-radius: 16px; display: grid; place-items: center; font-size: 1.35rem; margin-bottom: 1.1rem; }
        .ic.o { background: rgba(242,156,11,.13); color: var(--primary-dark); }
        .ic.p { background: rgba(49,40,115,.1); color: var(--ink); }
        .ic.g { background: rgba(16,185,129,.13); color: #10b981; }
        .ic.b { background: rgba(59,130,246,.13); color: #3b82f6; }
        .card h3 { font-size: 1.18rem; font-weight: 800; color: var(--ink); margin-bottom: .5rem; }
        .card p { color: var(--muted); font-size: .93rem; line-height: 1.65; }
        .card ul { list-style: none; margin-top: 1rem; display: grid; gap: .55rem; }
        .card li { font-size: .88rem; color: var(--muted); display: flex; gap: .55rem; }
        .card li i { color: #22c55e; margin-top: .2rem; }
        .tag { font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--primary-dark); }

        /* STEPS */
        .steps { display: grid; grid-template-columns: repeat(5,1fr); gap: 1rem; }
        .step { text-align: center; background: #fff; border: 1px solid var(--line); border-radius: 18px; padding: 1.6rem 1rem; position: relative; transition: .3s; }
        .step:hover { transform: translateY(-6px); box-shadow: var(--shadow); }
        .step .n { position: absolute; top: -14px; inset-inline-start: 50%; transform: translateX(-50%); width: 30px; height: 30px; border-radius: 50%; background: var(--primary); color: #fff; font-weight: 800; font-size: .84rem; display: grid; place-items: center; box-shadow: 0 6px 16px rgba(242,156,11,.4); }
        .step img { width: 60px; height: 60px; object-fit: contain; margin: .7rem auto .8rem; }
        .step h4 { font-size: .96rem; font-weight: 800; color: var(--ink); margin-bottom: .3rem; }
        .step p { font-size: .8rem; color: var(--muted); line-height: 1.5; }

        /* TESTIMONIALS */
        .quote { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: 1.8rem; transition: .3s; }
        .quote:hover { box-shadow: var(--shadow); transform: translateY(-4px); }
        .quote .stars { color: #f0a500; font-size: .85rem; margin-bottom: .8rem; }
        .quote p { color: var(--text); font-size: .96rem; line-height: 1.7; font-weight: 500; }
        .quote .who { display: flex; align-items: center; gap: .7rem; margin-top: 1.1rem; }
        .quote .av { width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg,var(--ink),var(--ink-2)); color: #fff; display: grid; place-items: center; font-weight: 800; }
        .quote .who b { display: block; color: var(--ink); font-size: .9rem; } .quote .who span { color: var(--muted); font-size: .78rem; }

        /* CTA BAND */
        .band { background: linear-gradient(135deg, var(--ink), var(--ink-3)); border-radius: 30px; padding: 3.4rem; color: #fff; position: relative; overflow: hidden; text-align: center; }
        .band::before { content:''; position:absolute; width:460px; height:460px; background: radial-gradient(circle, rgba(242,156,11,.28), transparent 70%); top:-160px; inset-inline-start:-80px; }
        .band::after { content:''; position:absolute; width:400px; height:400px; background: radial-gradient(circle, rgba(255,180,59,.2), transparent 70%); bottom:-160px; inset-inline-end:-60px; }
        .band > * { position: relative; z-index: 1; }
        .band h2 { font-size: clamp(1.8rem,3.6vw,2.6rem); font-weight: 800; margin-bottom: .7rem; }
        .band p { opacity: .85; max-width: 560px; margin-inline: auto; }

        /* PRICING */
        .price-grid { display: grid; grid-template-columns: repeat(5,1fr); gap: 1rem; align-items: stretch; }
        .plan { background: #fff; border: 1px solid var(--line); border-radius: 20px; padding: 1.7rem 1.35rem; display: flex; flex-direction: column; transition: transform .28s cubic-bezier(.2,.7,.2,1), box-shadow .28s, border-color .28s; }
        .plan:hover { transform: translateY(-4px); box-shadow: var(--shadow); border-color: var(--line-2); }
        .plan.pop { border: 1.5px solid rgba(242,156,11,.55); box-shadow: var(--shadow-glow); position: relative; }
        .plan.pop::before { content: '{{ $t('POPULAR', 'الأكثر رواجاً') }}'; position: absolute; top: -13px; inset-inline-start: 50%; transform: translateX(-50%); background: var(--primary); color: #fff; font-size: .68rem; font-weight: 800; letter-spacing: .06em; padding: .32rem .85rem; border-radius: 999px; white-space: nowrap; }
        .plan .pn { font-weight: 800; color: var(--ink); font-size: 1.08rem; }
        .plan .pc { font-size: 2.1rem; font-weight: 800; color: var(--ink); margin: .6rem 0 .1rem; letter-spacing: -1px; }
        .plan .pc small { font-size: .8rem; font-weight: 600; color: var(--muted); }
        .plan .rate { font-size: .82rem; font-weight: 700; color: var(--primary-dark); background: rgba(242,156,11,.1); padding: .32rem .75rem; border-radius: 999px; display: inline-block; margin-bottom: 1rem; }
        .plan ul { list-style: none; display: grid; gap: .5rem; margin-bottom: 1.2rem; flex: 1; }
        .plan li { font-size: .82rem; color: var(--muted); display: flex; gap: .5rem; }
        .plan li i { color: #22c55e; }

        /* PAGE HERO (inner) */
        .phero { background: linear-gradient(135deg, var(--ink), var(--ink-3)); color: #fff; padding: 6.5rem 0 4rem; position: relative; overflow: hidden; }
        .phero::before { content:''; position:absolute; width:520px; height:520px; background: radial-gradient(circle, rgba(242,156,11,.2), transparent 70%); top:-180px; inset-inline-end:-100px; }
        .phero .in { position: relative; z-index: 1; max-width: 720px; }
        .phero h1 { font-size: clamp(2rem,4.4vw,3.1rem); font-weight: 800; letter-spacing: -.5px; margin: 1rem 0 .8rem; }
        .phero p { color: rgba(255,255,255,.82); font-size: 1.1rem; line-height: 1.7; }

        /* FORMS */
        .form-shell { background: #fff; border: 1px solid var(--line); border-radius: 24px; box-shadow: var(--shadow); overflow: hidden; }
        .form-head { padding: 1.6rem 1.8rem; border-bottom: 1px solid var(--line); display: flex; align-items: center; gap: 1rem; }
        .form-head .fi { width: 48px; height: 48px; border-radius: 14px; display: grid; place-items: center; font-size: 1.25rem; color: #fff; }
        .form-body { padding: 1.8rem; }
        .fstep-tag { font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--primary-dark); margin: 1.5rem 0 .8rem; display: block; }
        .fstep-tag:first-child { margin-top: 0; }
        .fgrid { display: grid; grid-template-columns: repeat(2,1fr); gap: 1rem; }
        .field { display: flex; flex-direction: column; gap: .35rem; }
        .field.col-2 { grid-column: 1 / -1; }
        .field label { font-size: .78rem; font-weight: 700; color: var(--ink); }
        .field label .req { color: var(--primary); }
        .field input, .field select, .field textarea { width: 100%; border: 1px solid var(--line); border-radius: 11px; padding: .8rem .9rem; font-size: .92rem; background: #fff; transition: border-color .18s, box-shadow .18s; color: var(--text); }
        .field textarea { resize: vertical; min-height: 92px; }
        .field input:focus, .field select:focus, .field textarea:focus { outline: none; border-color: var(--primary); background: #fff; box-shadow: var(--ring); }
        .field.err input, .field.err select, .field.err textarea { border-color: #ef4444; background: #fef2f2; }
        .field .msg { font-size: .74rem; color: #ef4444; font-weight: 600; display: none; }
        .field.err .msg { display: block; }
        .up-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: .8rem; }
        .up { position: relative; border: 1.5px dashed var(--line); border-radius: 14px; aspect-ratio: 4/3; display: grid; place-items: center; text-align: center; cursor: pointer; overflow: hidden; background: #fbfcff; transition: .2s; }
        .up:hover { border-color: var(--primary); background: #fff; }
        .up input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .up .ph2 { display: flex; flex-direction: column; gap: .3rem; align-items: center; color: var(--muted); padding: .5rem; }
        .up .ph2 i { font-size: 1.3rem; color: var(--primary); }
        .up .ph2 span { font-size: .72rem; font-weight: 600; line-height: 1.3; }
        .up img.prev { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: none; }
        .up.has img.prev { display: block; } .up.has .ph2 { display: none; }
        .up .ok { position: absolute; top: 6px; inset-inline-end: 6px; background: #22c55e; color: #fff; width: 20px; height: 20px; border-radius: 50%; display: none; place-items: center; font-size: .65rem; z-index: 2; }
        .up.has .ok { display: grid; } .up.err { border-color: #ef4444; background: #fef2f2; }

        /* TOAST */
        #toast { position: fixed; inset-block-end: 26px; inset-inline-end: 26px; z-index: 999; display: flex; flex-direction: column; gap: .6rem; }
        .toast { display: flex; align-items: center; gap: .7rem; background: #fff; border-inline-start: 4px solid var(--primary); box-shadow: 0 18px 44px rgba(0,0,0,.18); border-radius: 14px; padding: 1rem 1.2rem; font-weight: 700; font-size: .9rem; color: var(--ink); transform: translateY(20px); opacity: 0; transition: .35s; min-width: 270px; max-width: 370px; }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.ok { border-color: #22c55e; } .toast.ok i { color: #22c55e; }
        .toast.bad { border-color: #ef4444; } .toast.bad i { color: #ef4444; }
        .toast i { font-size: 1.25rem; }

        /* FAQ */
        .faq { max-width: 840px; margin: 0 auto; }
        .qa { background: #fff; border: 1px solid var(--line); border-radius: 14px; margin-bottom: .7rem; overflow: hidden; }
        .qa button { width: 100%; text-align: start; padding: 1.15rem 1.35rem; font-weight: 700; color: var(--ink); background: none; border: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 1rem; font-size: .98rem; }
        .qa button i { transition: .3s; color: var(--primary); }
        .qa .ans { max-height: 0; overflow: hidden; transition: max-height .3s ease; }
        .qa .ans p { padding: 0 1.35rem 1.15rem; color: var(--muted); font-size: .9rem; line-height: 1.7; }
        .qa.open button i { transform: rotate(180deg); }

        /* FOOTER */
        footer { background: linear-gradient(135deg, var(--ink), var(--ink-3)); color: #fff; padding: 4.5rem 0 2rem; margin-top: 4.5rem; position: relative; overflow: hidden; }
        footer::before { content:''; position:absolute; width:540px; height:540px; background: radial-gradient(circle, rgba(242,156,11,.12), transparent 70%); top:-170px; inset-inline-start:-110px; }
        .foot-grid { display: grid; grid-template-columns: 1.7fr 1fr 1fr 1fr; gap: 2.5rem; position: relative; z-index: 1; }
        .foot-grid h5 { font-size: .82rem; letter-spacing: .09em; text-transform: uppercase; color: var(--primary-2); margin-bottom: 1.1rem; }
        .foot-grid .fl { display: block; color: rgba(255,255,255,.72); font-size: .88rem; margin-bottom: .6rem; transition: .2s; cursor: pointer; background:none;border:none;text-align:start; }
        .foot-grid .fl:hover { color: var(--primary-2); transform: translateX(var(--nudge, 4px)); }
        [dir="rtl"] .foot-grid .fl:hover { --nudge: -4px; }
        .foot-desc { color: rgba(255,255,255,.68); font-size: .9rem; line-height: 1.7; max-width: 310px; margin-top: 1rem; }
        .socials { display: flex; gap: .7rem; margin-top: 1.4rem; }
        .socials a { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,.1); display: grid; place-items: center; transition: .25s; }
        .socials a:hover { background: var(--primary); transform: translateY(-3px); }
        .foot-bottom { border-top: 1px solid rgba(255,255,255,.12); margin-top: 2.6rem; padding-top: 1.6rem; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem; color: rgba(255,255,255,.6); font-size: .82rem; position: relative; z-index: 1; }

        /* REVEAL + TOTOP */
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .55s cubic-bezier(.2,.7,.2,1), transform .55s cubic-bezier(.2,.7,.2,1); }
        .reveal.in { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) {
            .reveal { opacity: 1 !important; transform: none !important; transition: none !important; }
            * { animation: none !important; scroll-behavior: auto !important; }
        }
        #toTop { position: fixed; inset-block-end: 26px; inset-inline-start: 26px; z-index: 90; width: 48px; height: 48px; border-radius: 50%; border: none; background: var(--ink); color: #fff; font-size: 1rem; cursor: pointer; box-shadow: 0 14px 30px rgba(27,20,64,.35); opacity: 0; visibility: hidden; transform: translateY(12px); transition: .3s; }
        #toTop.show { opacity: 1; visibility: visible; transform: none; }
        #toTop:hover { background: var(--primary); transform: translateY(-3px); }

        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 980px) {
            nav.main, .signin { display: none; }
            .burger { display: block; }
            .hero { padding-top: 5rem; }
            .hero-grid { grid-template-columns: 1fr; gap: 2.5rem; }
            .phone-wrap { margin-top: 1rem; }
            .strip { grid-template-columns: repeat(2,1fr); }
            .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .steps { grid-template-columns: repeat(2,1fr); }
            .price-grid { grid-template-columns: 1fr; }
            .fgrid, .up-grid { grid-template-columns: 1fr 1fr; }
            .foot-grid { grid-template-columns: 1fr 1fr; }
            .band { padding: 2.2rem; }
            .contact-grid { grid-template-columns: 1fr !important; }
        }
        @media (max-width: 560px) {
            .strip, .steps, .up-grid { grid-template-columns: 1fr 1fr; }
            .fgrid { grid-template-columns: 1fr; }
            .foot-grid { grid-template-columns: 1fr; }
            .pad { padding: 3.8rem 0; }
            .float-badge { display: none; }
        }
    </style>
    @if($brandPrimary || $brandSecondary)
    <style>
        :root {
            @if($brandPrimary) --primary: {{ $brandPrimary }}; --primary-2: {{ $brandPrimary }}; --primary-dark: {{ $brandPrimary }}; @endif
            @if($brandSecondary) --ink: {{ $brandSecondary }}; --ink-2: {{ $brandSecondary }}; --ink-3: {{ $brandSecondary }}; @endif
        }
    </style>
    @endif
    <style>.logo-img{height:42px;width:auto;border-radius:10px;}</style>
</head>
<body>

<!-- HEADER -->
<header id="header">
    <div class="wrap hd">
        <div class="logo" onclick="showPage('home')">
            @if($brandLogo)<img class="logo-img" src="{{ asset('storage/' . $brandLogo) }}" alt="FleetOS">@else<span class="mark"><i class="fa-solid fa-compass"></i></span>@endif
            <span class="txt"><b>fleet</b><span>OS</span></span>
        </div>
        <nav class="main" id="nav">
            <button class="navlink" data-page="home" onclick="showPage('home')">{{ $t('Home', 'الرئيسية') }}</button>
            <button class="navlink" data-page="platform" onclick="showPage('platform')">{{ $t('Platform', 'المنصّة') }}</button>
            <button class="navlink" data-page="pricing" onclick="showPage('pricing')">{{ $t('Pricing', 'الأسعار') }}</button>
            <button class="navlink" data-page="drivers" onclick="showPage('drivers')">{{ $t('For drivers', 'للسائقين') }}</button>
            <button class="navlink" data-page="offices" onclick="showPage('offices')">{{ $t('For offices', 'للمكاتب') }}</button>
            <button class="navlink" data-page="contact" onclick="showPage('contact')">{{ $t('Contact', 'تواصل') }}</button>
        </nav>
        <div class="hd-actions">
            <a class="lang" href="{{ route('lang.switch', ['lang' => $ar ? 'en' : 'ar']) }}"><i class="fa-solid fa-globe"></i> {{ $ar ? 'EN' : 'AR' }}</a>
            <a class="signin" href="{{ route('login.office') }}">{{ $t('Sign in', 'دخول') }}</a>
            <button class="btn btn-primary" onclick="showPage('offices')">{{ $t('Launch office', 'أطلق مكتبك') }}</button>
            <button class="burger" onclick="toggleDrawer(true)"><i class="fa-solid fa-bars"></i></button>
        </div>
    </div>
</header>

<div class="drawer" id="drawer">
    <div class="ov" onclick="toggleDrawer(false)"></div>
    <div class="panel">
        <button class="close" onclick="toggleDrawer(false)"><i class="fa-solid fa-xmark"></i></button>
        <div class="dlink" data-page="home" onclick="showPage('home')">{{ $t('Home', 'الرئيسية') }}</div>
        <div class="dlink" data-page="platform" onclick="showPage('platform')">{{ $t('Platform', 'المنصّة') }}</div>
        <div class="dlink" data-page="pricing" onclick="showPage('pricing')">{{ $t('Pricing', 'الأسعار') }}</div>
        <div class="dlink" data-page="drivers" onclick="showPage('drivers')">{{ $t('For drivers', 'للسائقين') }}</div>
        <div class="dlink" data-page="offices" onclick="showPage('offices')">{{ $t('For offices', 'للمكاتب') }}</div>
        <div class="dlink" data-page="contact" onclick="showPage('contact')">{{ $t('Contact', 'تواصل') }}</div>
        <a class="signin" href="{{ route('login.office') }}" style="padding:.95rem .85rem">{{ $t('Sign in', 'تسجيل الدخول') }}</a>
        <button class="btn btn-primary" style="margin-top:.5rem" onclick="showPage('offices')">{{ $t('Launch office', 'أطلق مكتبك') }}</button>
    </div>
</div>

<main>

@include('panel.landing._home', ['t' => $t, 'img' => $img, 'ar' => $ar])
@include('panel.landing._platform', ['t' => $t, 'img' => $img])
@include('panel.landing._pricing', ['t' => $t, 'plans' => $plans ?? collect()])
@include('panel.landing._drivers', ['t' => $t])
@include('panel.landing._offices', ['t' => $t, 'countries' => $countries ?? collect()])
@include('panel.landing._contact', ['t' => $t])

<!-- FOOTER -->
<footer>
    <div class="wrap">
        <div class="foot-grid">
            <div>
                <div class="logo" style="color:#fff" onclick="showPage('home')">
                    @if($brandLogo)<img class="logo-img" src="{{ asset('storage/' . $brandLogo) }}" alt="FleetOS">@else<span class="mark"><i class="fa-solid fa-compass"></i></span>@endif
                    <span class="txt"><b>fleet</b><span style="color:#fff">OS</span></span>
                </div>
                <p class="foot-desc">{{ $footerAbout }}</p>
                <div class="socials">
                    @forelse($socialLinks as $sl)
                        <a href="{{ $sl['url'] }}" target="_blank" rel="noopener"><i class="fa-brands {{ $sl['icon'] }}"></i></a>
                    @empty
                        <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                    @endforelse
                </div>
            </div>
            <div>
                <h5>{{ $t('Platform', 'المنصّة') }}</h5>
                <button class="fl" onclick="showPage('platform')">{{ $t('The apps', 'التطبيقات') }}</button>
                <button class="fl" onclick="showPage('platform')">{{ $t('How it works', 'كيف يعمل') }}</button>
                <button class="fl" onclick="showPage('pricing')">{{ $t('Pricing', 'الأسعار') }}</button>
            </div>
            <div>
                <h5>{{ $t('Join', 'انضمّ') }}</h5>
                <button class="fl" onclick="showPage('offices')">{{ $t('Launch an office', 'أطلق مكتباً') }}</button>
                <button class="fl" onclick="showPage('drivers')">{{ $t('Become a driver', 'كن سائقاً') }}</button>
                <a class="fl" href="{{ route('login.office') }}">{{ $t('Sign in', 'تسجيل الدخول') }}</a>
            </div>
            <div>
                <h5>{{ $t('Company', 'الشركة') }}</h5>
                <button class="fl" onclick="showPage('contact')">{{ $t('Contact', 'تواصل') }}</button>
                <button class="fl" onclick="showPage('contact')">{{ $t('FAQ', 'الأسئلة') }}</button>
                <a class="fl" href="#">{{ $t('Privacy', 'الخصوصيّة') }}</a>
            </div>
        </div>
        <div class="foot-bottom">
            <span>© {{ date('Y') }} FleetOS. {{ $t('All rights reserved.', 'جميع الحقوق محفوظة.') }}</span>
            <span>{{ $t('Made for mobility offices & their drivers.', 'صُنع لمكاتب التنقّل وسائقيها.') }}</span>
        </div>
    </div>
</footer>

</main>

<button id="toTop" onclick="scrollTo({top:0,behavior:'smooth'})"><i class="fa-solid fa-arrow-up"></i></button>
<div id="toast"></div>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const OFFICE_URL = @json(route('office.request.store'));
    const DRIVER_URL = @json(url('/driver/send-driver-job-application'));
    const CONTACT_URL = @json(route('contact.store'));
    const PAGE_TITLES = @json($pageTitles);
    const T = @json($jsStrings);

    const header = document.getElementById('header');
    const toTop = document.getElementById('toTop');
    addEventListener('scroll', () => {
        header.classList.toggle('scrolled', scrollY > 20);
        toTop.classList.toggle('show', scrollY > 700);
    });
    function toggleDrawer(open) { document.getElementById('drawer').classList.toggle('open', open); document.body.style.overflow = open ? 'hidden' : ''; }
    function toggleQa(btn) { btn.parentElement.classList.toggle('open'); const a = btn.nextElementSibling; a.style.maxHeight = a.style.maxHeight ? '' : a.scrollHeight + 'px'; }

    function showPage(id) {
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
        const page = document.getElementById('page-' + id) || document.getElementById('page-home');
        page.classList.add('active');
        document.querySelectorAll('[data-page]').forEach(l => l.classList.toggle('active', l.dataset.page === id));
        document.title = PAGE_TITLES[id] || 'FleetOS';
        history.replaceState(null, '', '#' + id);
        toggleDrawer(false);
        scrollTo({ top: 0, behavior: 'auto' });
        page.querySelectorAll('.reveal').forEach((el, i) => setTimeout(() => el.classList.add('in'), 60 + i * 40));
        runCounters(page);
    }

    function runCounters(scope) {
        scope.querySelectorAll('[data-count]').forEach(el => {
            if (el.dataset.done) return; el.dataset.done = '1';
            const target = parseFloat(el.dataset.count), suffix = el.dataset.suffix || '';
            let cur = 0; const step = target / 40;
            const tick = () => { cur += step; if (cur >= target) { el.textContent = target + suffix; } else { el.textContent = Math.floor(cur) + suffix; requestAnimationFrame(tick); } };
            tick();
        });
    }

    function toast(msg, kind = 'ok') {
        const box = document.getElementById('toast');
        const el = document.createElement('div');
        el.className = 'toast ' + kind;
        el.innerHTML = '<i class="fa-solid ' + (kind === 'ok' ? 'fa-circle-check' : 'fa-circle-exclamation') + '"></i><span>' + msg + '</span>';
        box.appendChild(el);
        requestAnimationFrame(() => el.classList.add('show'));
        setTimeout(() => { el.classList.remove('show'); setTimeout(() => el.remove(), 400); }, 4200);
    }

    function clearErrors(form) {
        form.querySelectorAll('.field.err').forEach(f => { f.classList.remove('err'); const m = f.querySelector('.msg'); if (m) m.textContent = ''; });
        form.querySelectorAll('.up.err').forEach(u => u.classList.remove('err'));
    }
    function applyErrors(form, errors) {
        Object.keys(errors).forEach(name => {
            const input = form.querySelector('[name="' + name + '"]'); if (!input) return;
            const field = input.closest('.field'); if (field) { field.classList.add('err'); const m = field.querySelector('.msg'); if (m) m.textContent = errors[name][0]; }
            const up = input.closest('.up'); if (up) up.classList.add('err');
        });
    }
    function setLoading(form, on) {
        const btn = form.querySelector('button[type="submit"]'); btn.disabled = on;
        btn.querySelector('.lbl').style.opacity = on ? '.5' : '1';
        btn.querySelector('.spin').style.display = on ? 'inline-block' : 'none';
    }

    document.querySelectorAll('.up input[type="file"]').forEach(inp => {
        inp.addEventListener('change', () => {
            const up = inp.closest('.up'); const file = inp.files[0];
            if (!file) { up.classList.remove('has'); return; }
            up.classList.remove('err');
            up.querySelector('img.prev').src = URL.createObjectURL(file); up.classList.add('has');
        });
    });

    async function submitJson(form, url) {
        clearErrors(form); setLoading(form, true);
        try {
            const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: new FormData(form) });
            if (res.ok) return { ok: true };
            if (res.status === 422) { const d = await res.json(); applyErrors(form, d.errors || {}); return { ok: false, val: true }; }
            return { ok: false };
        } catch (_) { return { ok: false }; }
        finally { setLoading(form, false); }
    }

    const of = document.getElementById('officeForm');
    if (of) of.addEventListener('submit', async e => { e.preventDefault(); const r = await submitJson(of, OFFICE_URL); if (r.ok) { toast(T.ok_office); of.reset(); } else toast(r.val ? T.err : T.net, 'bad'); });

    const cf = document.getElementById('contactForm');
    if (cf) cf.addEventListener('submit', async e => { e.preventDefault(); const r = await submitJson(cf, CONTACT_URL); if (r.ok) { toast(T.ok_contact); cf.reset(); } else toast(r.val ? T.err : T.net, 'bad'); });

    const df = document.getElementById('driverForm');
    if (df) df.addEventListener('submit', async e => {
        e.preventDefault(); clearErrors(df);
        let missing = false;
        df.querySelectorAll('.up input[type="file"]').forEach(inp => { if (!inp.files.length) { inp.closest('.up').classList.add('err'); missing = true; } });
        if (missing) { toast(T.files, 'bad'); return; }
        setLoading(df, true);
        try {
            const res = await fetch(DRIVER_URL, { method: 'POST', headers: { 'Accept': 'application/json' }, body: new FormData(df) });
            if (res.ok) { toast(T.ok_driver); df.reset(); df.querySelectorAll('.up.has').forEach(u => u.classList.remove('has')); }
            else if (res.status === 422) { const d = await res.json(); applyErrors(df, d.errors || {}); toast(T.err, 'bad'); }
            else toast(T.net, 'bad');
        } catch (_) { toast(T.net, 'bad'); }
        setLoading(df, false);
    });

    const start = (location.hash || '').replace('#', '') || 'home';
    showPage(document.getElementById('page-' + start) ? start : 'home');
</script>
</body>
</html>
