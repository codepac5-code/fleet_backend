@php
    $ar = app()->getLocale() === 'ar';
    $dir = $ar ? 'rtl' : 'ltr';
    $t = fn($en, $arText) => $ar ? $arText : $en;
    $img = fn($n) => asset('panel/img/brand/' . $n);
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @php
        $pageTitle = $t('FleetOS — Launch your taxi office in the cloud', 'فليت أو إس — أطلق مكتب سيارات الأجرة في السحابة');
        $pageDesc = $t('FleetOS is a multi-tenant mobility marketplace. Launch a branded taxi office, manage drivers, and grow — riders choose you by rating and price.', 'فليت أو إس سوق تنقّل متعدّد المستأجرين. أطلق مكتباً باسمك، أدِر السائقين، وانمُ — الركّاب يختارونك بالتقييم والسعر.');
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}" />
    <meta name="theme-color" content="#312873" />
    <link rel="icon" href="{{ asset('favicon.ico') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ $pageTitle }}" />
    <meta property="og:description" content="{{ $pageDesc }}" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:site_name" content="FleetOS" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $pageTitle }}" />
    <meta name="twitter:description" content="{{ $pageDesc }}" />

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <style>
        :root {
            --primary: #F29C0B;
            --primary-dark: #d98708;
            --secondary: #312873;
            --secondary-dark: #1f1848;
            --accent: #FFB43B;
            --text: #1A1A1A;
            --muted: #64708a;
            --bg: #F7F8FC;
            --line: #e9ecf5;
            --radius: 18px;
            --shadow: 0 20px 50px rgba(49, 40, 115, .10);
            --font: 'Plus Jakarta Sans', sans-serif;
        }
        [dir="rtl"] { --font: 'Cairo', sans-serif; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: var(--font); }
        html { scroll-behavior: smooth; scroll-padding-top: 90px; }
        body { background: var(--bg); color: var(--text); overflow-x: hidden; }
        a { text-decoration: none; color: inherit; }
        ::selection { background: var(--primary); color: #fff; }

        .wrap { max-width: 1200px; margin: 0 auto; padding: 0 1.25rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: .55rem; font-weight: 700; border-radius: 999px; border: none; cursor: pointer; transition: .25s; font-size: .95rem; }
        .btn-primary { background: linear-gradient(135deg, var(--primary), var(--accent)); color: #fff; padding: .95rem 1.6rem; box-shadow: 0 14px 30px rgba(242, 156, 11, .35); }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 20px 38px rgba(242, 156, 11, .45); }
        .btn-dark { background: var(--secondary); color: #fff; padding: .95rem 1.6rem; }
        .btn-dark:hover { background: var(--secondary-dark); transform: translateY(-3px); }
        .btn-ghost { background: #fff; color: var(--secondary); padding: .9rem 1.5rem; border: 1.5px solid var(--line); }
        .btn-ghost:hover { border-color: var(--secondary); transform: translateY(-3px); }
        .btn-block { width: 100%; }

        .eyebrow { display: inline-flex; align-items: center; gap: .5rem; font-size: .72rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: var(--primary); background: rgba(242, 156, 11, .1); padding: .45rem .9rem; border-radius: 999px; }
        .h-sec { font-size: clamp(1.9rem, 4vw, 2.9rem); font-weight: 800; color: var(--secondary); letter-spacing: -.5px; line-height: 1.1; }
        .sub-sec { color: var(--muted); font-size: 1.05rem; line-height: 1.7; max-width: 620px; }
        section { position: relative; }
        .pad { padding: 5.5rem 0; }

        /* HEADER */
        header { position: fixed; inset: 0 0 auto 0; z-index: 100; height: 74px; display: flex; align-items: center; transition: .3s; }
        header.scrolled { background: rgba(255, 255, 255, .82); backdrop-filter: blur(16px); box-shadow: 0 6px 30px rgba(49, 40, 115, .08); }
        .hd { display: flex; align-items: center; justify-content: space-between; width: 100%; }
        .logo { display: flex; align-items: center; gap: .6rem; font-weight: 800; }
        .logo .mark { width: 42px; height: 42px; border-radius: 13px; background: linear-gradient(135deg, var(--secondary), var(--secondary-dark)); display: grid; place-items: center; color: var(--primary); box-shadow: 0 8px 22px rgba(49, 40, 115, .3); transition: .4s; }
        .logo:hover .mark { transform: rotate(-8deg) scale(1.06); }
        .logo .mark i { font-size: 1.25rem; animation: spin 6s linear infinite; }
        .logo .txt { font-size: 1.5rem; letter-spacing: -.5px; }
        .logo .txt b { color: var(--secondary); } .logo .txt span { color: var(--primary); }
        nav.main { display: flex; align-items: center; gap: 2rem; }
        nav.main a { font-weight: 600; font-size: .95rem; color: var(--secondary); opacity: .85; transition: .2s; position: relative; }
        nav.main a:hover { opacity: 1; color: var(--primary); }
        nav.main a.active { color: var(--primary); opacity: 1; }
        nav.main a.active::after { content: ''; position: absolute; inset-inline: 0; bottom: -6px; height: 2px; background: var(--primary); border-radius: 2px; }

        #toTop { position: fixed; inset-block-end: 26px; inset-inline-start: 26px; z-index: 90; width: 46px; height: 46px; border-radius: 50%; border: none; background: var(--secondary); color: #fff; font-size: 1rem; cursor: pointer; box-shadow: 0 12px 26px rgba(49, 40, 115, .35); opacity: 0; visibility: hidden; transform: translateY(12px); transition: .3s; }
        #toTop.show { opacity: 1; visibility: visible; transform: none; }
        #toTop:hover { background: var(--primary); transform: translateY(-3px); }
        .hd-actions { display: flex; align-items: center; gap: .8rem; }
        .lang { display: inline-flex; align-items: center; gap: .3rem; font-weight: 700; font-size: .82rem; color: var(--secondary); border: 1.5px solid var(--line); background: #fff; padding: .5rem .8rem; border-radius: 999px; transition: .2s; }
        .lang:hover { border-color: var(--secondary); }
        .signin { font-weight: 600; font-size: .92rem; color: var(--secondary); }
        .burger { display: none; font-size: 1.4rem; color: var(--secondary); background: none; border: none; }

        /* DRAWER */
        .drawer { position: fixed; inset: 0; z-index: 200; visibility: hidden; }
        .drawer .ov { position: absolute; inset: 0; background: rgba(31, 24, 72, .45); opacity: 0; transition: .3s; }
        .drawer .panel { position: absolute; top: 0; bottom: 0; inset-inline-end: 0; width: min(84vw, 340px); background: #fff; padding: 1.4rem; transform: translateX(var(--slide, 110%)); transition: .35s cubic-bezier(.4, 0, .2, 1); display: flex; flex-direction: column; gap: .3rem; }
        [dir="rtl"] .drawer .panel { --slide: -110%; }
        .drawer.open { visibility: visible; }
        .drawer.open .ov { opacity: 1; }
        .drawer.open .panel { transform: translateX(0); }
        .drawer a { padding: .9rem .8rem; border-radius: 12px; font-weight: 600; color: var(--secondary); }
        .drawer a:hover { background: var(--bg); }
        .drawer .close { align-self: flex-end; font-size: 1.4rem; background: none; border: none; color: var(--muted); margin-bottom: .5rem; }

        /* HERO */
        .hero { padding: 8.5rem 0 4rem; overflow: hidden; }
        .glow { position: absolute; border-radius: 50%; filter: blur(120px); z-index: 0; }
        .glow.a { width: 460px; height: 460px; background: var(--primary); opacity: .18; top: -160px; inset-inline-start: -120px; }
        .glow.b { width: 420px; height: 420px; background: var(--secondary); opacity: .14; bottom: -180px; inset-inline-end: -100px; }
        .hero-grid { display: grid; grid-template-columns: 1.05fr .95fr; gap: 3.5rem; align-items: center; position: relative; z-index: 1; }
        .hero h1 { font-size: clamp(2.3rem, 5vw, 3.6rem); font-weight: 800; line-height: 1.06; letter-spacing: -1.2px; color: var(--secondary); margin: 1.2rem 0; }
        .hero h1 em { font-style: normal; color: var(--primary); position: relative; }
        .hero p.lead { color: var(--muted); font-size: 1.12rem; line-height: 1.75; max-width: 540px; }
        .hero-cta { display: flex; gap: .9rem; flex-wrap: wrap; margin: 1.8rem 0 1.4rem; }
        .trust { display: flex; flex-wrap: wrap; gap: .55rem; }
        .trust span { font-size: .8rem; font-weight: 600; color: var(--secondary); background: #fff; border: 1px solid var(--line); padding: .45rem .8rem; border-radius: 999px; }
        .trust span i { color: #22c55e; margin-inline-end: .35rem; }

        /* MARKET CARD */
        .market { background: linear-gradient(150deg, var(--secondary), var(--secondary-dark)); border-radius: 26px; padding: 1.6rem; color: #fff; box-shadow: 0 40px 80px rgba(49, 40, 115, .4); position: relative; }
        .market h4 { font-size: .8rem; letter-spacing: .12em; text-transform: uppercase; opacity: .7; margin-bottom: 1rem; }
        .m-row { display: flex; align-items: center; justify-content: space-between; background: rgba(255, 255, 255, .08); border: 1px solid rgba(255, 255, 255, .07); padding: .85rem 1rem; border-radius: 14px; margin-bottom: .6rem; transition: .25s; }
        .m-row:hover { background: rgba(255, 255, 255, .16); transform: translateX(var(--nudge, 5px)); }
        [dir="rtl"] .m-row:hover { --nudge: -5px; }
        .m-row .nm { font-weight: 700; font-size: .92rem; }
        .m-row .st { color: #FFD54A; font-size: .72rem; }
        .m-row .pr { font-weight: 800; color: var(--accent); }
        .m-live { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem; font-size: .78rem; opacity: .85; }
        .m-live i { color: #4ade80; margin-inline-end: .3rem; }
        .pulse { width: 8px; height: 8px; border-radius: 50%; background: #4ade80; display: inline-block; box-shadow: 0 0 0 rgba(74, 222, 128, .6); animation: pulse 1.8s infinite; }

        /* STATS */
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; background: #fff; border: 1px solid var(--line); border-radius: 22px; padding: 1.8rem; box-shadow: var(--shadow); }
        .stat b { display: block; font-size: 2rem; font-weight: 800; color: var(--secondary); letter-spacing: -1px; }
        .stat span { color: var(--muted); font-size: .85rem; font-weight: 600; }

        /* GRIDS / CARDS */
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.4rem; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.2rem; }
        .card { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: 1.7rem; transition: .3s; }
        .card:hover { transform: translateY(-6px); box-shadow: var(--shadow); border-color: transparent; }
        .card .ic { width: 52px; height: 52px; border-radius: 15px; display: grid; place-items: center; font-size: 1.3rem; margin-bottom: 1rem; }
        .ic.o { background: rgba(242, 156, 11, .13); color: var(--primary); }
        .ic.p { background: rgba(49, 40, 115, .1); color: var(--secondary); }
        .ic.g { background: rgba(16, 185, 129, .13); color: #10b981; }
        .ic.b { background: rgba(59, 130, 246, .13); color: #3b82f6; }
        .card h3 { font-size: 1.15rem; font-weight: 800; color: var(--secondary); margin-bottom: .5rem; }
        .card p { color: var(--muted); font-size: .92rem; line-height: 1.6; }

        /* STEPS */
        .steps { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; }
        .step { text-align: center; background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 1.5rem 1rem; position: relative; transition: .3s; }
        .step:hover { transform: translateY(-6px); box-shadow: var(--shadow); }
        .step .n { position: absolute; top: -14px; inset-inline-start: 50%; transform: translateX(-50%); width: 28px; height: 28px; border-radius: 50%; background: var(--primary); color: #fff; font-weight: 800; font-size: .82rem; display: grid; place-items: center; box-shadow: 0 6px 14px rgba(242, 156, 11, .4); }
        .step img { width: 58px; height: 58px; object-fit: contain; margin: .6rem auto .8rem; }
        .step h4 { font-size: .95rem; font-weight: 800; color: var(--secondary); margin-bottom: .3rem; }
        .step p { font-size: .8rem; color: var(--muted); line-height: 1.5; }

        /* PLATFORMS */
        .platf { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.4rem; }
        .platf .card { padding: 2rem; }
        .platf .tag { font-size: .72rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--primary); }
        .platf ul { list-style: none; margin-top: 1rem; display: grid; gap: .55rem; }
        .platf li { font-size: .88rem; color: var(--muted); display: flex; gap: .55rem; align-items: flex-start; }
        .platf li i { color: #22c55e; margin-top: .2rem; }

        /* PRICING */
        .price-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; align-items: stretch; }
        .plan { background: #fff; border: 1px solid var(--line); border-radius: 18px; padding: 1.6rem 1.3rem; display: flex; flex-direction: column; transition: .3s; }
        .plan:hover { transform: translateY(-6px); box-shadow: var(--shadow); }
        .plan.pop { border: 2px solid var(--primary); box-shadow: 0 24px 50px rgba(242, 156, 11, .2); position: relative; }
        .plan.pop::before { content: '{{ $t('POPULAR', 'الأكثر رواجاً') }}'; position: absolute; top: -12px; inset-inline-start: 50%; transform: translateX(-50%); background: var(--primary); color: #fff; font-size: .68rem; font-weight: 800; letter-spacing: .08em; padding: .3rem .8rem; border-radius: 999px; white-space: nowrap; }
        .plan .pn { font-weight: 800; color: var(--secondary); font-size: 1.05rem; }
        .plan .pc { font-size: 2rem; font-weight: 800; color: var(--secondary); margin: .6rem 0 .1rem; letter-spacing: -1px; }
        .plan .pc small { font-size: .8rem; font-weight: 600; color: var(--muted); }
        .plan .rate { font-size: .82rem; font-weight: 700; color: var(--primary); background: rgba(242, 156, 11, .1); padding: .3rem .7rem; border-radius: 999px; display: inline-block; margin-bottom: 1rem; }
        .plan ul { list-style: none; display: grid; gap: .5rem; margin-bottom: 1.2rem; flex: 1; }
        .plan li { font-size: .82rem; color: var(--muted); display: flex; gap: .5rem; }
        .plan li i { color: #22c55e; }

        /* PROMO */
        .promo { background: linear-gradient(135deg, var(--secondary), var(--secondary-dark)); border-radius: 28px; padding: 3rem; color: #fff; position: relative; overflow: hidden; }
        .promo::after { content: ''; position: absolute; width: 380px; height: 380px; background: radial-gradient(circle, rgba(242, 156, 11, .3), transparent 70%); top: -120px; inset-inline-end: -80px; }
        .promo .badge { display: inline-flex; align-items: center; gap: .5rem; background: var(--primary); color: #fff; font-weight: 800; font-size: .78rem; padding: .5rem 1rem; border-radius: 999px; }
        .promo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1.6rem; position: relative; z-index: 1; }
        .promo-grid div { background: rgba(255, 255, 255, .08); border: 1px solid rgba(255, 255, 255, .1); border-radius: 14px; padding: 1.1rem; }
        .promo-grid b { display: block; color: var(--accent); font-size: 1.4rem; font-weight: 800; }
        .promo-grid span { font-size: .85rem; opacity: .85; }

        /* FORMS */
        .form-shell { background: #fff; border: 1px solid var(--line); border-radius: 24px; box-shadow: var(--shadow); overflow: hidden; }
        .form-head { padding: 1.6rem 1.8rem; border-bottom: 1px solid var(--line); display: flex; align-items: center; gap: 1rem; }
        .form-head .fi { width: 46px; height: 46px; border-radius: 13px; display: grid; place-items: center; font-size: 1.2rem; color: #fff; }
        .form-body { padding: 1.8rem; }
        .fstep-tag { font-size: .7rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--primary); margin: 1.4rem 0 .8rem; display: block; }
        .fstep-tag:first-child { margin-top: 0; }
        .fgrid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .field { display: flex; flex-direction: column; gap: .35rem; }
        .field.col-2 { grid-column: 1 / -1; }
        .field label { font-size: .78rem; font-weight: 700; color: var(--secondary); }
        .field label .req { color: var(--primary); }
        .field input, .field select, .field textarea { width: 100%; border: 1.5px solid var(--line); border-radius: 12px; padding: .8rem .9rem; font-size: .92rem; background: #fbfcfe; transition: .2s; color: var(--text); }
        .field textarea { resize: vertical; min-height: 90px; }
        .field input:focus, .field select:focus, .field textarea:focus { outline: none; border-color: var(--primary); background: #fff; box-shadow: 0 0 0 4px rgba(242, 156, 11, .12); }
        .field.err input, .field.err select, .field.err textarea { border-color: #ef4444; background: #fef2f2; }
        .field .msg { font-size: .74rem; color: #ef4444; font-weight: 600; display: none; }
        .field.err .msg { display: block; }

        /* FILE UPLOADS */
        .up-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .8rem; }
        .up { position: relative; border: 1.5px dashed var(--line); border-radius: 14px; aspect-ratio: 4/3; display: grid; place-items: center; text-align: center; cursor: pointer; overflow: hidden; background: #fbfcfe; transition: .2s; }
        .up:hover { border-color: var(--primary); background: #fff; }
        .up input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .up .ph { display: flex; flex-direction: column; gap: .3rem; align-items: center; color: var(--muted); padding: .5rem; }
        .up .ph i { font-size: 1.3rem; color: var(--primary); }
        .up .ph span { font-size: .72rem; font-weight: 600; line-height: 1.3; }
        .up img.prev { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: none; }
        .up.has img.prev { display: block; }
        .up.has .ph { display: none; }
        .up .ok { position: absolute; top: 6px; inset-inline-end: 6px; background: #22c55e; color: #fff; width: 20px; height: 20px; border-radius: 50%; display: none; place-items: center; font-size: .65rem; z-index: 2; }
        .up.has .ok { display: grid; }
        .up.err { border-color: #ef4444; background: #fef2f2; }

        /* TOAST */
        #toast { position: fixed; inset-block-end: 26px; inset-inline-end: 26px; z-index: 999; display: flex; flex-direction: column; gap: .6rem; }
        .toast { display: flex; align-items: center; gap: .7rem; background: #fff; border-inline-start: 4px solid var(--primary); box-shadow: 0 16px 40px rgba(0, 0, 0, .18); border-radius: 14px; padding: .95rem 1.2rem; font-weight: 700; font-size: .9rem; color: var(--secondary); transform: translateY(20px); opacity: 0; transition: .35s; min-width: 260px; max-width: 360px; }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.ok { border-color: #22c55e; } .toast.ok i { color: #22c55e; }
        .toast.bad { border-color: #ef4444; } .toast.bad i { color: #ef4444; }
        .toast i { font-size: 1.2rem; }

        /* FAQ */
        .faq { max-width: 820px; margin: 0 auto; }
        .qa { background: #fff; border: 1px solid var(--line); border-radius: 14px; margin-bottom: .7rem; overflow: hidden; }
        .qa button { width: 100%; text-align: start; padding: 1.1rem 1.3rem; font-weight: 700; color: var(--secondary); background: none; border: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 1rem; font-size: .98rem; }
        .qa button i { transition: .3s; color: var(--primary); }
        .qa .ans { max-height: 0; overflow: hidden; transition: max-height .3s ease; }
        .qa .ans p { padding: 0 1.3rem 1.1rem; color: var(--muted); font-size: .9rem; line-height: 1.7; }
        .qa.open button i { transform: rotate(180deg); }

        /* FOOTER */
        footer { background: linear-gradient(135deg, var(--secondary), var(--secondary-dark)); color: #fff; padding: 4.5rem 0 2rem; margin-top: 4rem; position: relative; overflow: hidden; }
        footer::before { content: ''; position: absolute; width: 500px; height: 500px; background: radial-gradient(circle, rgba(242, 156, 11, .12), transparent 70%); top: -150px; inset-inline-start: -100px; }
        .foot-grid { display: grid; grid-template-columns: 1.6fr 1fr 1fr 1fr; gap: 2.5rem; position: relative; z-index: 1; }
        .foot-grid h5 { font-size: .82rem; letter-spacing: .1em; text-transform: uppercase; color: var(--primary); margin-bottom: 1.1rem; }
        .foot-grid a { display: block; color: rgba(255, 255, 255, .72); font-size: .88rem; margin-bottom: .6rem; transition: .2s; }
        .foot-grid a:hover { color: var(--accent); transform: translateX(var(--nudge, 4px)); }
        [dir="rtl"] .foot-grid a:hover { --nudge: -4px; }
        .foot-desc { color: rgba(255, 255, 255, .68); font-size: .9rem; line-height: 1.7; max-width: 300px; margin-top: 1rem; }
        .socials { display: flex; gap: .7rem; margin-top: 1.4rem; }
        .socials a { width: 40px; height: 40px; border-radius: 50%; background: rgba(255, 255, 255, .1); display: grid; place-items: center; transition: .25s; margin: 0; }
        .socials a:hover { background: var(--primary); transform: translateY(-3px); }
        .foot-bottom { border-top: 1px solid rgba(255, 255, 255, .12); margin-top: 2.6rem; padding-top: 1.6rem; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem; color: rgba(255, 255, 255, .6); font-size: .82rem; position: relative; z-index: 1; }

        /* REVEAL */
        .reveal { opacity: 0; transform: translateY(28px); transition: .7s cubic-bezier(.2, .7, .2, 1); }
        .reveal.in { opacity: 1; transform: none; }

        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(74, 222, 128, .5); } 70% { box-shadow: 0 0 0 8px rgba(74, 222, 128, 0); } 100% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); } }

        @media (max-width: 980px) {
            nav.main, .signin { display: none; }
            .burger { display: block; }
            .hero-grid { grid-template-columns: 1fr; gap: 2.5rem; }
            .stats { grid-template-columns: repeat(2, 1fr); }
            .grid-3, .grid-4, .platf, .promo-grid { grid-template-columns: 1fr; }
            .steps { grid-template-columns: repeat(2, 1fr); }
            .price-grid { grid-template-columns: 1fr; }
            .fgrid, .up-grid { grid-template-columns: 1fr 1fr; }
            .foot-grid { grid-template-columns: 1fr 1fr; }
            .promo { padding: 2rem; }
            .contact-grid { grid-template-columns: 1fr !important; }
            .faas-grid { grid-template-columns: 1fr !important; }
        }
        @media (max-width: 560px) {
            .stats, .steps, .up-grid { grid-template-columns: 1fr 1fr; }
            .fgrid { grid-template-columns: 1fr; }
            .foot-grid { grid-template-columns: 1fr; }
            .pad { padding: 3.8rem 0; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header id="header">
    <div class="wrap hd">
        <a href="#top" class="logo">
            <span class="mark"><i class="fa-solid fa-compass"></i></span>
            <span class="txt"><b>Fleet</b><span>OS</span></span>
        </a>
        <nav class="main">
            <a href="#how">{{ $t('How it works', 'كيف يعمل') }}</a>
            <a href="#platforms">{{ $t('Platform', 'المنصّة') }}</a>
            <a href="#pricing">{{ $t('Pricing', 'الأسعار') }}</a>
            <a href="#apply-driver">{{ $t('Drivers', 'السائقون') }}</a>
            <a href="#contact">{{ $t('Contact', 'تواصل') }}</a>
        </nav>
        <div class="hd-actions">
            <a class="lang" href="{{ route('lang.switch', ['lang' => $ar ? 'en' : 'ar']) }}">
                <i class="fa-solid fa-globe"></i> {{ $ar ? 'EN' : 'AR' }}
            </a>
            <a class="signin" href="{{ route('login.office') }}">{{ $t('Sign in', 'دخول') }}</a>
            <a class="btn btn-primary" href="#apply-office">{{ $t('Launch office', 'أطلق مكتبك') }}</a>
            <button class="burger" onclick="toggleDrawer(true)"><i class="fa-solid fa-bars"></i></button>
        </div>
    </div>
</header>

<div class="drawer" id="drawer">
    <div class="ov" onclick="toggleDrawer(false)"></div>
    <div class="panel">
        <button class="close" onclick="toggleDrawer(false)"><i class="fa-solid fa-xmark"></i></button>
        <a href="#how" onclick="toggleDrawer(false)">{{ $t('How it works', 'كيف يعمل') }}</a>
        <a href="#platforms" onclick="toggleDrawer(false)">{{ $t('Platform', 'المنصّة') }}</a>
        <a href="#pricing" onclick="toggleDrawer(false)">{{ $t('Pricing', 'الأسعار') }}</a>
        <a href="#apply-driver" onclick="toggleDrawer(false)">{{ $t('Drivers', 'السائقون') }}</a>
        <a href="#faq" onclick="toggleDrawer(false)">{{ $t('FAQ', 'الأسئلة') }}</a>
        <a href="{{ route('login.office') }}">{{ $t('Sign in', 'تسجيل الدخول') }}</a>
        <a class="btn btn-primary" style="margin-top:.6rem" href="#apply-office" onclick="toggleDrawer(false)">{{ $t('Launch office', 'أطلق مكتبك') }}</a>
    </div>
</div>

<span id="top"></span>

<!-- HERO -->
<section class="hero">
    <div class="glow a"></div>
    <div class="glow b"></div>
    <div class="wrap hero-grid">
        <div class="reveal in">
            <span class="eyebrow"><i class="fa-solid fa-bolt"></i> {{ $t('The mobility marketplace', 'سوق التنقّل') }}</span>
            <h1>{{ $t('Launch your own', 'أطلق مكتب') }} <em>{{ $t('taxi office', 'أجرة خاصّاً بك') }}</em> {{ $t('in the cloud', 'في السحابة') }}</h1>
            <p class="lead">{{ $t('FleetOS gives you a branded office, driver & rider apps, live dispatch, wallets and analytics — riders choose you in a shared marketplace by rating and price. Go live in minutes, not months.', 'يمنحك فليت أو إس مكتباً باسمك، وتطبيقَي سائق وراكب، وإسناداً حيّاً، ومحافظ وتحليلات — والركّاب يختارونك في سوق مشترك بالتقييم والسعر. انطلق خلال دقائق لا شهور.') }}</p>
            <div class="hero-cta">
                <a class="btn btn-primary" href="#apply-office"><i class="fa-solid fa-rocket"></i> {{ $t('Launch an office', 'أطلق مكتباً') }}</a>
                <a class="btn btn-ghost" href="#apply-driver"><i class="fa-solid fa-id-card"></i> {{ $t('Apply as a driver', 'تقدّم كسائق') }}</a>
            </div>
            <div class="trust">
                <span><i class="fa-solid fa-check"></i>{{ $t('No code, no servers', 'بلا برمجة أو خوادم') }}</span>
                <span><i class="fa-solid fa-check"></i>{{ $t('Shared rider demand', 'طلب ركّاب مشترك') }}</span>
                <span><i class="fa-solid fa-check"></i>{{ $t('Your brand, your pricing', 'علامتك وأسعارك') }}</span>
            </div>
        </div>

        <div class="reveal in">
            <div class="market">
                <h4><span class="pulse"></span> &nbsp;{{ $t('Riders are choosing now', 'الركّاب يختارون الآن') }}</h4>
                <div class="m-row"><div><div class="nm">{{ $t('Local Fleet', 'أسطول محلّي') }}</div><div class="st">★★★★☆ 4.7</div></div><div class="pr">$12.50</div></div>
                <div class="m-row"><div><div class="nm">{{ $t('City Cabs', 'كابات المدينة') }}</div><div class="st">★★★★☆ 4.6</div></div><div class="pr">$14.00</div></div>
                <div class="m-row"><div><div class="nm">{{ $t('Premium Ride', 'رحلة مميّزة') }}</div><div class="st">★★★★★ 5.0</div></div><div class="pr">$18.00</div></div>
                <div class="m-live">
                    <span><i class="fa-solid fa-car-side"></i>{{ $t('12 drivers online', '١٢ سائقاً متّصلاً') }}</span>
                    <span><i class="fa-solid fa-building"></i>{{ $t('4 offices live', '٤ مكاتب فعّالة') }}</span>
                    <span><i class="fa-solid fa-route"></i>{{ $t('3 rides now', '٣ رحلات الآن') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<section>
    <div class="wrap">
        <div class="stats reveal">
            <div class="stat"><b>$25<small style="font-size:1rem">/{{ $t('mo', 'شهر') }}</small></b><span>{{ $t('Start your office', 'ابدأ مكتبك') }}</span></div>
            <div class="stat"><b>11–18%</b><span>{{ $t('Commission by plan', 'عمولة حسب الخطّة') }}</span></div>
            <div class="stat"><b>3</b><span>{{ $t('Apps: rider · office · driver', 'تطبيقات: راكب · مكتب · سائق') }}</span></div>
            <div class="stat"><b>&lt; 10{{ $t(' min', ' د') }}</b><span>{{ $t('To go live', 'حتى الانطلاق') }}</span></div>
        </div>
    </div>
</section>

<!-- HOW -->
<section id="how" class="pad">
    <div class="wrap">
        <div style="text-align:center; margin-bottom:2.6rem" class="reveal">
            <span class="eyebrow">{{ $t('How it works', 'كيف يعمل') }}</span>
            <h2 class="h-sec" style="margin:.8rem 0">{{ $t('From application to live in five steps', 'من الطلب إلى الانطلاق بخمس خطوات') }}</h2>
        </div>
        <div class="steps reveal">
            @php
                $steps = [
                    ['icones-01.png', $t('Apply', 'تقدّم'), $t('Submit your office or driver application.', 'أرسل طلب مكتبك أو طلبك كسائق.')],
                    ['icones-02.png', $t('Get approved', 'اعتماد'), $t('We review your docs and activate you.', 'نراجع وثائقك ونفعّل حسابك.')],
                    ['icones-03.png', $t('Set up', 'تهيئة'), $t('Add drivers, pricing and coverage.', 'أضف السائقين والأسعار والتغطية.')],
                    ['icones-04-1.png', $t('Go live', 'انطلاق'), $t('Appear in the rider marketplace.', 'اظهر في سوق الركّاب.')],
                    ['icones-05.png', $t('Grow', 'نموّ'), $t('Earn, settle and scale with analytics.', 'اربح، سوِّ، ونمِّ مع التحليلات.')],
                ];
            @endphp
            @foreach ($steps as $i => $s)
                <div class="step">
                    <span class="n">{{ $i + 1 }}</span>
                    <img src="{{ $img($s[0]) }}" alt="">
                    <h4>{{ $s[1] }}</h4>
                    <p>{{ $s[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- PLATFORMS -->
<section id="platforms" class="pad" style="background:#fff">
    <div class="wrap">
        <div style="margin-bottom:2.6rem" class="reveal">
            <span class="eyebrow">{{ $t('One platform, three apps', 'منصّة واحدة، ثلاثة تطبيقات') }}</span>
            <h2 class="h-sec" style="margin:.8rem 0">{{ $t('Everything the marketplace needs', 'كلّ ما يحتاجه السوق') }}</h2>
        </div>
        <div class="platf reveal">
            <div class="card">
                <div class="ic o"><i class="fa-solid fa-mobile-screen-button"></i></div>
                <span class="tag">{{ $t('Rider app', 'تطبيق الراكب') }}</span>
                <h3 style="margin-top:.4rem">Fleet Ride</h3>
                <ul>
                    <li><i class="fa-solid fa-check"></i>{{ $t('Choose an office by rating & price', 'اختيار المكتب بالتقييم والسعر') }}</li>
                    <li><i class="fa-solid fa-check"></i>{{ $t('Live tracking & secure wallet', 'تتبّع حيّ ومحفظة آمنة') }}</li>
                    <li><i class="fa-solid fa-check"></i>{{ $t('Rate every ride', 'تقييم كلّ رحلة') }}</li>
                </ul>
            </div>
            <div class="card">
                <div class="ic p"><i class="fa-solid fa-gauge-high"></i></div>
                <span class="tag">{{ $t('Office dashboard', 'لوحة المكتب') }}</span>
                <h3 style="margin-top:.4rem">Fleet Panel</h3>
                <ul>
                    <li><i class="fa-solid fa-check"></i>{{ $t('Live dispatch board & map', 'لوحة إسناد حيّة وخريطة') }}</li>
                    <li><i class="fa-solid fa-check"></i>{{ $t('Drivers, pricing & subscriptions', 'السائقون والأسعار والاشتراكات') }}</li>
                    <li><i class="fa-solid fa-check"></i>{{ $t('Wallets, payouts & reports', 'المحافظ والسحوبات والتقارير') }}</li>
                </ul>
            </div>
            <div class="card">
                <div class="ic g"><i class="fa-solid fa-car"></i></div>
                <span class="tag">{{ $t('Driver app', 'تطبيق السائق') }}</span>
                <h3 style="margin-top:.4rem">DriverX</h3>
                <ul>
                    <li><i class="fa-solid fa-check"></i>{{ $t('Instant ride offers', 'عروض رحلات فوريّة') }}</li>
                    <li><i class="fa-solid fa-check"></i>{{ $t('Earnings & instant payouts', 'أرباح وسحوبات فوريّة') }}</li>
                    <li><i class="fa-solid fa-check"></i>{{ $t('Navigation & presence', 'ملاحة وحضور') }}</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- WHY / FEATURES -->
<section class="pad">
    <div class="wrap">
        <div style="margin-bottom:2.6rem" class="reveal">
            <span class="eyebrow">{{ $t('Why FleetOS', 'لماذا فليت أو إس') }}</span>
            <h2 class="h-sec" style="margin:.8rem 0">{{ $t('Own the brand. We run the tech.', 'امتلك العلامة. نحن ندير التقنية.') }}</h2>
        </div>
        <div class="grid-4 reveal">
            <div class="card"><div class="ic o"><i class="fa-solid fa-layer-group"></i></div><h3>{{ $t('Shared demand', 'طلب مشترك') }}</h3><p>{{ $t('Tap into a marketplace of riders instead of building demand alone.', 'استفد من سوق ركّاب بدل بناء الطلب وحدك.') }}</p></div>
            <div class="card"><div class="ic b"><i class="fa-solid fa-sliders"></i></div><h3>{{ $t('Full control', 'تحكّم كامل') }}</h3><p>{{ $t('Set your pricing, coverage and driver roster from one dashboard.', 'حدّد أسعارك وتغطيتك وسائقيك من لوحة واحدة.') }}</p></div>
            <div class="card"><div class="ic p"><i class="fa-solid fa-shield-halved"></i></div><h3>{{ $t('Trust & safety', 'الثقة والأمان') }}</h3><p>{{ $t('Verified drivers, dual ratings and secure escrow payments.', 'سائقون موثّقون، تقييم ثنائيّ، ومدفوعات ضمان آمنة.') }}</p></div>
            <div class="card"><div class="ic g"><i class="fa-solid fa-chart-line"></i></div><h3>{{ $t('Grow with data', 'انمُ بالبيانات') }}</h3><p>{{ $t('Live reports on revenue, commission and driver earnings.', 'تقارير حيّة عن الإيراد والعمولة وأرباح السائقين.') }}</p></div>
        </div>
    </div>
</section>

<!-- FLEET AS A SERVICE -->
<section id="faas" class="pad" style="background:#fff">
    <div class="wrap">
        <div style="text-align:center; margin-bottom:2.6rem" class="reveal">
            <span class="eyebrow">{{ $t('Fleet-as-a-Service', 'الأسطول كخدمة') }}</span>
            <h2 class="h-sec" style="margin:.8rem 0">{{ $t('The Shopify moment for taxi offices', 'لحظة شوبيفاي لمكاتب الأجرة') }}</h2>
            <p class="sub-sec" style="margin:0 auto">{{ $t('Instead of building an app, a dispatch system and a payment stack alone, you launch a ready office on a shared marketplace — and keep your brand.', 'بدل بناء تطبيق ونظام إسناد ومنظومة دفع وحدك، تُطلق مكتباً جاهزاً على سوق مشترك — وتحتفظ بعلامتك.') }}</p>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.4rem" class="reveal faas-grid">
            <div class="card" style="border-top:4px solid #ef4444">
                <div class="ic" style="background:rgba(239,68,68,.12); color:#ef4444"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <h3>{{ $t('The old way', 'الطريقة القديمة') }}</h3>
                <ul style="list-style:none; display:grid; gap:.6rem; margin-top:.8rem">
                    <li style="font-size:.9rem; color:var(--muted)"><i class="fa-solid fa-xmark" style="color:#ef4444; margin-inline-end:.5rem"></i>{{ $t('Months of development & big upfront cost', 'شهور تطوير وتكلفة أوّليّة كبيرة') }}</li>
                    <li style="font-size:.9rem; color:var(--muted)"><i class="fa-solid fa-xmark" style="color:#ef4444; margin-inline-end:.5rem"></i>{{ $t('You must build rider demand from zero', 'تبني طلب الركّاب من الصفر') }}</li>
                    <li style="font-size:.9rem; color:var(--muted)"><i class="fa-solid fa-xmark" style="color:#ef4444; margin-inline-end:.5rem"></i>{{ $t('Maintenance, servers and updates on you', 'الصيانة والخوادم والتحديثات عليك') }}</li>
                </ul>
            </div>
            <div class="card" style="border-top:4px solid #22c55e">
                <div class="ic g"><i class="fa-solid fa-circle-check"></i></div>
                <h3>{{ $t('The FleetOS way', 'طريقة فليت أو إس') }}</h3>
                <ul style="list-style:none; display:grid; gap:.6rem; margin-top:.8rem">
                    <li style="font-size:.9rem; color:var(--muted)"><i class="fa-solid fa-check" style="color:#22c55e; margin-inline-end:.5rem"></i>{{ $t('Launch in minutes on a monthly plan', 'انطلاق خلال دقائق باشتراك شهريّ') }}</li>
                    <li style="font-size:.9rem; color:var(--muted)"><i class="fa-solid fa-check" style="color:#22c55e; margin-inline-end:.5rem"></i>{{ $t('Share demand in a rider marketplace', 'طلب مشترك في سوق الركّاب') }}</li>
                    <li style="font-size:.9rem; color:var(--muted)"><i class="fa-solid fa-check" style="color:#22c55e; margin-inline-end:.5rem"></i>{{ $t('We run the tech; you run the business', 'نحن ندير التقنية وأنت تدير النشاط') }}</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- TRUST & SAFETY -->
<section id="trust" class="pad">
    <div class="wrap">
        <div style="margin-bottom:2.4rem" class="reveal">
            <span class="eyebrow"><i class="fa-solid fa-shield-halved"></i> {{ $t('Trust & safety', 'الثقة والأمان') }}</span>
            <h2 class="h-sec" style="margin:.8rem 0">{{ $t('Built to be safe on both sides', 'مصمّم ليكون آمناً للطرفين') }}</h2>
        </div>
        <div class="grid-4 reveal">
            <div class="card"><div class="ic p"><i class="fa-solid fa-user-check"></i></div><h3>{{ $t('Verified drivers', 'سائقون موثّقون') }}</h3><p>{{ $t('Every driver is reviewed with ID, license and vehicle documents.', 'كلّ سائق يُراجَع بالهويّة والرخصة ووثائق المركبة.') }}</p></div>
            <div class="card"><div class="ic o"><i class="fa-solid fa-star-half-stroke"></i></div><h3>{{ $t('Dual ratings', 'تقييم ثنائيّ') }}</h3><p>{{ $t('Riders and drivers rate each other after every trip.', 'الركّاب والسائقون يقيّم كلٌّ منهما الآخر بعد كلّ رحلة.') }}</p></div>
            <div class="card"><div class="ic g"><i class="fa-solid fa-lock"></i></div><h3>{{ $t('Escrow payments', 'مدفوعات ضمان') }}</h3><p>{{ $t('Fares are held securely and settled only when the ride completes.', 'تُحجز الأجرة بأمان وتُسوّى فقط عند اكتمال الرحلة.') }}</p></div>
            <div class="card"><div class="ic b"><i class="fa-solid fa-scale-balanced"></i></div><h3>{{ $t('Fair governance', 'حَوكمة عادلة') }}</h3><p>{{ $t('Transparent commissions, audit logs and marketplace rules.', 'عمولات شفّافة وسجلّات تدقيق وقواعد سوق واضحة.') }}</p></div>
        </div>
    </div>
</section>

<!-- PRICING -->
<section id="pricing" class="pad" style="background:#fff">
    <div class="wrap">
        <div style="text-align:center; margin-bottom:3rem" class="reveal">
            <span class="eyebrow">{{ $t('Pricing', 'الأسعار') }}</span>
            <h2 class="h-sec" style="margin:.8rem 0">{{ $t('Plans that scale with your office', 'خطط تنمو مع مكتبك') }}</h2>
            <p class="sub-sec" style="margin:.6rem auto 0">{{ $t('Lower platform commission as you grow. Cancel anytime.', 'عمولة منصّة أقلّ كلّما نموت. ألغِ في أيّ وقت.') }}</p>
        </div>
        @php
            $planList = collect($plans ?? [])->map(fn ($p) => [
                'name' => $p->name,
                'price_minor' => $p->price_minor,
                'currency' => $p->currency_code ?: 'USD',
                'rate' => $p->fleet_commission_rate,
                'limit' => $p->driver_limit,
                'trial' => $p->trial_days,
                'popular' => (bool) $p->is_popular,
            ])->all();

            if (empty($planList)) {
                foreach (\App\Http\Core\Const\Subscription\PlanKey::CATALOG as $k => $c) {
                    $planList[] = [
                        'name' => $c['name'], 'price_minor' => $c['price_minor'], 'currency' => 'USD',
                        'rate' => $c['fleet_rate'], 'limit' => $c['driver_limit'], 'trial' => null,
                        'popular' => $k === 'business',
                    ];
                }
            }
        @endphp
        <div class="price-grid reveal">
            @foreach ($planList as $p)
                @php $custom = $p['price_minor'] === null; @endphp
                <div class="plan {{ $p['popular'] ? 'pop' : '' }}">
                    <div class="pn">{{ $p['name'] }}</div>
                    <div class="pc">
                        @if($custom){{ $t('Custom', 'مخصّص') }}@else{{ $p['currency'] }} {{ number_format($p['price_minor'] / 100, 0) }}<small>/{{ $t('mo', 'شهر') }}</small>@endif
                    </div>
                    <span class="rate">{{ $p['rate'] !== null ? (rtrim(rtrim(number_format($p['rate'], 2), '0'), '.') . '%') : $t('Custom', 'مخصّص') }} {{ $t('commission', 'عمولة') }}</span>
                    <ul>
                        <li><i class="fa-solid fa-check"></i>{{ $p['limit'] ? $p['limit'] . ' ' . $t('drivers', 'سائقاً') : $t('Unlimited drivers', 'سائقون غير محدودين') }}</li>
                        @if($p['trial'])<li><i class="fa-solid fa-check"></i>{{ $p['trial'] }} {{ $t('day free trial', 'يوم تجربة مجانية') }}</li>@endif
                        <li><i class="fa-solid fa-check"></i>{{ $t('Office dashboard', 'لوحة المكتب') }}</li>
                        <li><i class="fa-solid fa-check"></i>{{ $t('Driver & rider apps', 'تطبيقا السائق والراكب') }}</li>
                        <li><i class="fa-solid fa-check"></i>{{ $t('Wallets & payouts', 'محافظ وسحوبات') }}</li>
                    </ul>
                    <a class="btn {{ $p['popular'] ? 'btn-primary' : 'btn-ghost' }} btn-block"
                       href="{{ $custom ? '#contact' : route('office.register') }}">
                        {{ $custom ? $t('Contact sales', 'تواصل معنا') : $t('Start free', 'ابدأ مجاناً') }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- PROMO -->
<section class="pad">
    <div class="wrap reveal">
        <div class="promo">
            <span class="badge"><i class="fa-solid fa-gift"></i> {{ $t('Launch offer', 'عرض الإطلاق') }}</span>
            <h2 style="font-size:clamp(1.6rem,3.5vw,2.4rem); font-weight:800; margin:1rem 0 .4rem; max-width:640px">{{ $t('Join the founding offices and save on your first months', 'انضمّ إلى المكاتب المؤسِّسة ووفّر في أشهرك الأولى') }}</h2>
            <p style="opacity:.85; max-width:560px">{{ $t('Early partners get onboarding support, reduced commission and priority marketplace placement.', 'الشركاء الأوائل يحصلون على دعم تهيئة، وعمولة مخفّضة، وأولويّة في ظهور السوق.') }}</p>
            <div class="promo-grid">
                <div><b>{{ $t('1 month free', 'شهر مجّاني') }}</b><span>{{ $t('On any paid plan', 'على أيّ خطّة مدفوعة') }}</span></div>
                <div><b>-3%</b><span>{{ $t('Commission for 90 days', 'عمولة لمدّة ٩٠ يوماً') }}</span></div>
                <div><b>{{ $t('Priority', 'أولويّة') }}</b><span>{{ $t('Marketplace placement', 'ظهور في السوق') }}</span></div>
            </div>
            <a class="btn btn-primary" style="margin-top:1.6rem" href="#apply-office"><i class="fa-solid fa-rocket"></i> {{ $t('Claim the offer', 'احصل على العرض') }}</a>
        </div>
    </div>
</section>

<!-- APPLY: OFFICE -->
<section id="apply-office" class="pad" style="background:#fff">
    <div class="wrap" style="max-width:760px">
        <div style="text-align:center; margin-bottom:2rem" class="reveal">
            <span class="eyebrow"><i class="fa-solid fa-building"></i> {{ $t('For offices', 'للمكاتب') }}</span>
            <h2 class="h-sec" style="margin:.8rem 0">{{ $t('Launch your office', 'أطلق مكتبك') }}</h2>
            <p class="sub-sec" style="margin:0 auto">{{ $t('Tell us about your business. Our team reviews every application and gets you live fast.', 'حدّثنا عن نشاطك. يراجع فريقنا كلّ طلب ويجعلك فعّالاً بسرعة.') }}</p>
        </div>

        <div class="form-shell reveal">
            <div class="form-head">
                <span class="fi" style="background:linear-gradient(135deg,var(--primary),var(--accent))"><i class="fa-solid fa-building"></i></span>
                <div><div style="font-weight:800; color:var(--secondary)">{{ $t('Office application', 'طلب مكتب') }}</div><div style="font-size:.82rem; color:var(--muted)">{{ $t('Takes about 2 minutes', 'يستغرق نحو دقيقتين') }}</div></div>
            </div>
            <div class="form-body">
                <form id="officeForm" novalidate>
                    <span class="fstep-tag">{{ $t('1 · Office info', '١ · معلومات المكتب') }}</span>
                    <div class="fgrid">
                        <div class="field"><label>{{ $t('Office name', 'اسم المكتب') }} <span class="req">*</span></label><input name="office_name" required><div class="msg"></div></div>
                        <div class="field"><label>{{ $t('Contact person', 'الشخص المسؤول') }} <span class="req">*</span></label><input name="contact_name" required><div class="msg"></div></div>
                        <div class="field"><label>{{ $t('Email', 'البريد') }} <span class="req">*</span></label><input type="email" name="email" required><div class="msg"></div></div>
                        <div class="field"><label>{{ $t('Phone', 'الهاتف') }} <span class="req">*</span></label><input name="phone" required><div class="msg"></div></div>
                        <div class="field"><label>{{ $t('City', 'المدينة') }} <span class="req">*</span></label><input name="city" required><div class="msg"></div></div>
                        <div class="field"><label>{{ $t('Country', 'الدولة') }} <span class="req">*</span></label><input name="country" required><div class="msg"></div></div>
                        <div class="field col-2"><label>{{ $t('Website (optional)', 'الموقع (اختياري)') }}</label><input type="url" name="website" placeholder="https://"><div class="msg"></div></div>
                    </div>

                    <span class="fstep-tag">{{ $t('2 · Business', '٢ · النشاط') }}</span>
                    <div class="fgrid">
                        <div class="field"><label>{{ $t('Business type', 'نوع النشاط') }} <span class="req">*</span></label>
                            <select name="business_category" required><option value="">{{ $t('Select', 'اختر') }}</option><option value="New">{{ $t('New business', 'نشاط جديد') }}</option><option value="Existing">{{ $t('Existing fleet', 'أسطول قائم') }}</option><option value="Corporate">{{ $t('Corporate', 'شركة') }}</option></select><div class="msg"></div></div>
                        <div class="field"><label>{{ $t('Fleet size', 'حجم الأسطول') }} <span class="req">*</span></label><input type="number" name="fleet_size" min="1" required><div class="msg"></div></div>
                        <div class="field"><label>{{ $t('Service type', 'نوع الخدمة') }} <span class="req">*</span></label>
                            <select name="service_type" required><option value="">{{ $t('Select', 'اختر') }}</option><option value="City">{{ $t('City', 'داخل المدينة') }}</option><option value="Airport">{{ $t('Airport', 'مطار') }}</option><option value="Corporate">{{ $t('Corporate', 'شركات') }}</option><option value="Mixed">{{ $t('Mixed', 'مختلط') }}</option></select><div class="msg"></div></div>
                        <div class="field"><label>{{ $t('Current tools', 'الأدوات الحاليّة') }}</label><input name="current_tools"><div class="msg"></div></div>
                        <div class="field col-2"><label>{{ $t('Coverage area', 'منطقة التغطية') }}</label><input name="coverage"><div class="msg"></div></div>
                    </div>

                    <span class="fstep-tag">{{ $t('3 · Details', '٣ · تفاصيل') }}</span>
                    <div class="fgrid">
                        <div class="field"><label>{{ $t('Licensed?', 'مرخّص؟') }} <span class="req">*</span></label>
                            <select name="license_status" required><option value="">{{ $t('Select', 'اختر') }}</option><option value="Yes">{{ $t('Yes', 'نعم') }}</option><option value="No">{{ $t('No', 'لا') }}</option><option value="Not sure">{{ $t('Not sure', 'غير متأكّد') }}</option></select><div class="msg"></div></div>
                        <div class="field"><label>{{ $t('Timeline', 'الإطار الزمنيّ') }} <span class="req">*</span></label>
                            <select name="timeline" required><option value="">{{ $t('Select', 'اختر') }}</option><option value="Immediate">{{ $t('Immediate', 'فوري') }}</option><option value="30 days">{{ $t('30 days', '٣٠ يوماً') }}</option><option value="60-90 days">{{ $t('60–90 days', '٦٠–٩٠ يوماً') }}</option><option value="Exploring">{{ $t('Just exploring', 'مجرّد استكشاف') }}</option></select><div class="msg"></div></div>
                        <div class="field col-2"><label>{{ $t('Notes', 'ملاحظات') }}</label><textarea name="notes"></textarea><div class="msg"></div></div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="margin-top:1.6rem; padding:1.05rem">
                        <span class="lbl">{{ $t('Submit application', 'إرسال الطلب') }}</span>
                        <i class="fa-solid fa-spinner fa-spin spin" style="display:none"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- APPLY: DRIVER -->
<section id="apply-driver" class="pad">
    <div class="wrap" style="max-width:860px">
        <div style="text-align:center; margin-bottom:2rem" class="reveal">
            <span class="eyebrow"><i class="fa-solid fa-car"></i> {{ $t('For drivers', 'للسائقين') }}</span>
            <h2 class="h-sec" style="margin:.8rem 0">{{ $t('Drive with FleetOS', 'قُد مع فليت أو إس') }}</h2>
            <p class="sub-sec" style="margin:0 auto">{{ $t('Register your details and vehicle, upload your documents, and get approved to start earning.', 'سجّل بياناتك ومركبتك، وارفع وثائقك، واحصل على الاعتماد لتبدأ الكسب.') }}</p>
        </div>

        <div class="form-shell reveal">
            <div class="form-head">
                <span class="fi" style="background:linear-gradient(135deg,var(--secondary),var(--secondary-dark))"><i class="fa-solid fa-id-card"></i></span>
                <div><div style="font-weight:800; color:var(--secondary)">{{ $t('Driver application', 'طلب سائق') }}</div><div style="font-size:.82rem; color:var(--muted)">{{ $t('Your data is stored securely', 'بياناتك محفوظة بأمان') }}</div></div>
            </div>
            <div class="form-body">
                <form id="driverForm" novalidate>
                    <span class="fstep-tag">{{ $t('1 · Personal & account', '١ · شخصيّ وحساب') }}</span>
                    <div class="fgrid">
                        <div class="field"><label>{{ $t('Full name', 'الاسم الكامل') }} <span class="req">*</span></label><input name="name" required><div class="msg"></div></div>
                        <div class="field"><label>{{ $t('Phone number', 'رقم الهاتف') }} <span class="req">*</span></label><input name="phone" required><div class="msg"></div></div>
                        <div class="field col-2"><label>{{ $t('Password (for the driver app)', 'كلمة المرور (لتطبيق السائق)') }} <span class="req">*</span></label><input type="password" name="password" required><div class="msg"></div></div>
                    </div>

                    <span class="fstep-tag">{{ $t('2 · Vehicle', '٢ · المركبة') }}</span>
                    <div class="fgrid">
                        <div class="field"><label>{{ $t('Brand', 'الماركة') }} <span class="req">*</span></label><input name="brand" required><div class="msg"></div></div>
                        <div class="field"><label>{{ $t('Model', 'الطراز') }} <span class="req">*</span></label><input name="model" required><div class="msg"></div></div>
                        <div class="field"><label>{{ $t('Year', 'السنة') }} <span class="req">*</span></label><input name="year" required><div class="msg"></div></div>
                        <div class="field"><label>{{ $t('Color', 'اللون') }} <span class="req">*</span></label><input name="color" required><div class="msg"></div></div>
                        <div class="field col-2"><label>{{ $t('Plate number', 'رقم اللوحة') }} <span class="req">*</span></label><input name="plateNumber" required><div class="msg"></div></div>
                    </div>

                    <span class="fstep-tag">{{ $t('3 · Documents', '٣ · الوثائق') }}</span>
                    <div class="up-grid">
                        @php
                            $docs = [
                                ['profileImage', $t('Profile photo', 'صورة شخصيّة'), 'fa-user'],
                                ['idFrontImage', $t('ID — front', 'الهويّة — أمام'), 'fa-id-card'],
                                ['idBackImage', $t('ID — back', 'الهويّة — خلف'), 'fa-id-card'],
                                ['licenseFrontImage', $t('License — front', 'الرخصة — أمام'), 'fa-address-card'],
                                ['licenseBackImage', $t('License — back', 'الرخصة — خلف'), 'fa-address-card'],
                                ['mechanicalImage', $t('Mechanical check', 'الفحص الميكانيكيّ'), 'fa-gears'],
                            ];
                        @endphp
                        @foreach ($docs as $d)
                            <label class="up" data-name="{{ $d[0] }}">
                                <span class="ok"><i class="fa-solid fa-check"></i></span>
                                <input type="file" name="{{ $d[0] }}" accept="image/*" required>
                                <span class="ph"><i class="fa-solid {{ $d[2] }}"></i><span>{{ $d[1] }}</span></span>
                                <img class="prev" alt="">
                            </label>
                        @endforeach
                    </div>

                    <span class="fstep-tag">{{ $t('4 · Vehicle photos', '٤ · صور المركبة') }}</span>
                    <div class="up-grid">
                        @php
                            $photos = [
                                ['frontCarImage', $t('Front', 'أمام')],
                                ['backCarImage', $t('Back', 'خلف')],
                                ['rightCarImage', $t('Right', 'يمين')],
                                ['leftCarImage', $t('Left', 'يسار')],
                                ['insideCarImage', $t('Interior', 'داخل')],
                                ['frontSeatsImage', $t('Front seats', 'المقاعد الأماميّة')],
                                ['backSeatsImage', $t('Back seats', 'المقاعد الخلفيّة')],
                            ];
                        @endphp
                        @foreach ($photos as $p)
                            <label class="up" data-name="{{ $p[0] }}">
                                <span class="ok"><i class="fa-solid fa-check"></i></span>
                                <input type="file" name="{{ $p[0] }}" accept="image/*" required>
                                <span class="ph"><i class="fa-solid fa-camera"></i><span>{{ $p[1] }}</span></span>
                                <img class="prev" alt="">
                            </label>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-dark btn-block" style="margin-top:1.8rem; padding:1.05rem">
                        <span class="lbl">{{ $t('Submit driver application', 'إرسال طلب السائق') }}</span>
                        <i class="fa-solid fa-spinner fa-spin spin" style="display:none"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section id="faq" class="pad" style="background:#fff">
    <div class="wrap">
        <div style="text-align:center; margin-bottom:2.4rem" class="reveal">
            <span class="eyebrow">{{ $t('FAQ', 'الأسئلة الشائعة') }}</span>
            <h2 class="h-sec" style="margin:.8rem 0">{{ $t('Answers before you start', 'إجابات قبل أن تبدأ') }}</h2>
        </div>
        <div class="faq reveal">
            @php
                $faqs = [
                    [$t('What exactly is FleetOS?', 'ما هو فليت أو إس بالضبط؟'), $t('A multi-tenant mobility marketplace. You launch a branded taxi office in the cloud; riders pick their office by rating and price, and you manage everything from one dashboard.', 'سوق تنقّل متعدّد المستأجرين. تُطلق مكتب أجرة باسمك في السحابة؛ يختار الركّاب مكتبهم بالتقييم والسعر، وتدير كلّ شيء من لوحة واحدة.')],
                    [$t('How much does it cost?', 'كم التكلفة؟'), $t('Plans start free (18% commission) and scale to Business at $35/mo with 12% commission. Enterprise pricing is custom. You can change plans anytime.', 'تبدأ الخطط مجّاناً (عمولة ١٨٪) وتتوسّع إلى الأعمال بـ٣٥$ شهريّاً وعمولة ١٢٪. أسعار المؤسّسات مخصّصة. يمكنك تغيير الخطّة في أيّ وقت.')],
                    [$t('How do drivers get paid?', 'كيف يُدفع للسائقين؟'), $t('Earnings settle to a secure in-app wallet after each ride. Drivers request payouts to their bank; offices withdraw revenue the same way.', 'تُسوّى الأرباح إلى محفظة آمنة داخل التطبيق بعد كلّ رحلة. يطلب السائقون السحب إلى بنوكهم، وتسحب المكاتب إيرادها بالطريقة نفسها.')],
                    [$t('What documents do drivers need?', 'ما الوثائق التي يحتاجها السائقون؟'), $t('A profile photo, ID (front & back), driving license (front & back), a mechanical check, and photos of the vehicle. Everything is uploaded in the application form above.', 'صورة شخصيّة، الهويّة (أمام وخلف)، رخصة القيادة (أمام وخلف)، فحص ميكانيكيّ، وصور للمركبة. كلّها تُرفع في نموذج الطلب أعلاه.')],
                    [$t('How long until I go live?', 'كم حتى الانطلاق؟'), $t('Most offices are reviewed within a day or two. Once approved, setup takes minutes and you appear in the rider marketplace immediately.', 'تُراجَع معظم المكاتب خلال يوم أو يومين. بعد الاعتماد، تستغرق التهيئة دقائق وتظهر في سوق الركّاب فوراً.')],
                ];
            @endphp
            @foreach ($faqs as $f)
                <div class="qa">
                    <button onclick="toggleQa(this)">{{ $f[0] }} <i class="fa-solid fa-chevron-down"></i></button>
                    <div class="ans"><p>{{ $f[1] }}</p></div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CONTACT / DEMO -->
<section id="contact" class="pad">
    <div class="wrap" style="max-width:900px">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:2.5rem; align-items:center" class="reveal contact-grid">
            <div>
                <span class="eyebrow"><i class="fa-solid fa-headset"></i> {{ $t('Talk to us', 'تواصل معنا') }}</span>
                <h2 class="h-sec" style="margin:.8rem 0">{{ $t('Book a demo or ask us anything', 'احجز عرضاً توضيحيّاً أو اسأل') }}</h2>
                <p class="sub-sec">{{ $t('Whether you run an existing fleet, are starting fresh, or just exploring — our team will walk you through FleetOS and answer every question.', 'سواء تدير أسطولاً قائماً، أو تبدأ من الصفر، أو تستكشف فقط — سيشرح لك فريقنا فليت أو إس ويجيب عن كلّ سؤال.') }}</p>
                <div style="display:grid; gap:.9rem; margin-top:1.6rem">
                    <div style="display:flex; align-items:center; gap:.8rem"><span class="ic o" style="width:42px;height:42px;margin:0;border-radius:12px"><i class="fa-solid fa-envelope"></i></span><span style="font-weight:600;color:var(--secondary)">hello@fleetos.app</span></div>
                    <div style="display:flex; align-items:center; gap:.8rem"><span class="ic p" style="width:42px;height:42px;margin:0;border-radius:12px"><i class="fa-solid fa-clock"></i></span><span style="font-weight:600;color:var(--secondary)">{{ $t('We reply within 24 hours', 'نردّ خلال ٢٤ ساعة') }}</span></div>
                </div>
            </div>

            <div class="form-shell">
                <div class="form-body">
                    <form id="contactForm" novalidate>
                        <div class="fgrid">
                            <div class="field col-2"><label>{{ $t('I want to…', 'أريد أن…') }} <span class="req">*</span></label>
                                <select name="intent" required>
                                    <option value="demo">{{ $t('Book a demo', 'أحجز عرضاً توضيحيّاً') }}</option>
                                    <option value="sales">{{ $t('Talk to sales', 'أتحدّث للمبيعات') }}</option>
                                    <option value="support">{{ $t('Get support', 'أحصل على دعم') }}</option>
                                    <option value="waitlist">{{ $t('Join the waitlist', 'أنضمّ لقائمة الانتظار') }}</option>
                                </select><div class="msg"></div></div>
                            <div class="field"><label>{{ $t('Name', 'الاسم') }} <span class="req">*</span></label><input name="name" required><div class="msg"></div></div>
                            <div class="field"><label>{{ $t('Email', 'البريد') }} <span class="req">*</span></label><input type="email" name="email" required><div class="msg"></div></div>
                            <div class="field"><label>{{ $t('Phone', 'الهاتف') }}</label><input name="phone"><div class="msg"></div></div>
                            <div class="field"><label>{{ $t('Company', 'الشركة') }}</label><input name="company"><div class="msg"></div></div>
                            <div class="field col-2"><label>{{ $t('Message', 'الرسالة') }}</label><textarea name="message"></textarea><div class="msg"></div></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" style="margin-top:1.4rem; padding:1.05rem">
                            <span class="lbl">{{ $t('Send message', 'إرسال الرسالة') }}</span>
                            <i class="fa-solid fa-spinner fa-spin spin" style="display:none"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FINAL CTA -->
<section class="pad">
    <div class="wrap reveal" style="text-align:center">
        <h2 class="h-sec">{{ $t('Ready to put your brand on the road?', 'جاهز لوضع علامتك على الطريق؟') }}</h2>
        <p class="sub-sec" style="margin:.8rem auto 1.6rem">{{ $t('Join the marketplace built for mobility offices and their drivers.', 'انضمّ إلى السوق المبنيّ لمكاتب التنقّل وسائقيها.') }}</p>
        <div style="display:flex; gap:.9rem; justify-content:center; flex-wrap:wrap">
            <a class="btn btn-primary" href="#apply-office"><i class="fa-solid fa-building"></i> {{ $t('Launch an office', 'أطلق مكتباً') }}</a>
            <a class="btn btn-ghost" href="#apply-driver"><i class="fa-solid fa-car"></i> {{ $t('Become a driver', 'كن سائقاً') }}</a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="wrap">
        <div class="foot-grid">
            <div>
                <a href="#top" class="logo" style="color:#fff"><span class="mark"><i class="fa-solid fa-compass"></i></span><span class="txt" style="color:#fff"><b style="color:#fff">Fleet</b><span>OS</span></span></a>
                <p class="foot-desc">{{ $t('The cloud marketplace for mobility. Launch a branded taxi office, manage drivers, and grow.', 'سوق التنقّل السحابيّ. أطلق مكتب أجرة باسمك، أدِر السائقين، وانمُ.') }}</p>
                <div class="socials">
                    <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
            </div>
            <div>
                <h5>{{ $t('Platform', 'المنصّة') }}</h5>
                <a href="#how">{{ $t('How it works', 'كيف يعمل') }}</a>
                <a href="#platforms">{{ $t('The apps', 'التطبيقات') }}</a>
                <a href="#pricing">{{ $t('Pricing', 'الأسعار') }}</a>
            </div>
            <div>
                <h5>{{ $t('Join', 'انضمّ') }}</h5>
                <a href="#apply-office">{{ $t('Launch an office', 'أطلق مكتباً') }}</a>
                <a href="#apply-driver">{{ $t('Become a driver', 'كن سائقاً') }}</a>
                <a href="{{ route('login.office') }}">{{ $t('Sign in', 'تسجيل الدخول') }}</a>
            </div>
            <div>
                <h5>{{ $t('Company', 'الشركة') }}</h5>
                <a href="#faq">{{ $t('FAQ', 'الأسئلة') }}</a>
                <a href="#">{{ $t('Privacy', 'الخصوصيّة') }}</a>
                <a href="#">{{ $t('Terms', 'الشروط') }}</a>
            </div>
        </div>
        <div class="foot-bottom">
            <span>© {{ date('Y') }} FleetOS. {{ $t('All rights reserved.', 'جميع الحقوق محفوظة.') }}</span>
            <span>{{ $t('Made for mobility offices & their drivers.', 'صُنع لمكاتب التنقّل وسائقيها.') }}</span>
        </div>
    </div>
</footer>

<button id="toTop" aria-label="Back to top" onclick="scrollTo({top:0,behavior:'smooth'})"><i class="fa-solid fa-arrow-up"></i></button>

<div id="toast"></div>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const OFFICE_URL = @json(route('office.request.store'));
    const DRIVER_URL = @json(url('/driver/send-driver-job-application'));
    const CONTACT_URL = @json(route('contact.store'));
    const T = {
        ok_office: @json($t('Application received! Our team will contact you soon.', 'تمّ استلام طلبك! سيتواصل معك فريقنا قريباً.')),
        ok_driver: @json($t('Driver application submitted! We will review it shortly.', 'تمّ إرسال طلب السائق! سنراجعه قريباً.')),
        ok_contact: @json($t('Message sent! We will get back to you within 24 hours.', 'تمّ إرسال رسالتك! سنعاود التواصل خلال ٢٤ ساعة.')),
        err: @json($t('Please check the highlighted fields.', 'يرجى مراجعة الحقول المميّزة.')),
        net: @json($t('Something went wrong. Please try again.', 'حدث خطأ ما. حاول مرّة أخرى.')),
        files: @json($t('Please attach all required images.', 'يرجى إرفاق كلّ الصور المطلوبة.')),
    };

    // header + drawer + back-to-top
    const header = document.getElementById('header');
    const toTop = document.getElementById('toTop');
    addEventListener('scroll', () => {
        header.classList.toggle('scrolled', scrollY > 20);
        toTop.classList.toggle('show', scrollY > 600);
        spy();
    });
    function toggleDrawer(open) { document.getElementById('drawer').classList.toggle('open', open); document.body.style.overflow = open ? 'hidden' : ''; }
    function toggleQa(btn) { btn.parentElement.classList.toggle('open'); const a = btn.nextElementSibling; a.style.maxHeight = a.style.maxHeight ? '' : a.scrollHeight + 'px'; }

    // reveal on scroll
    const io = new IntersectionObserver((es) => es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { threshold: .12 });
    document.querySelectorAll('.reveal').forEach(el => io.observe(el));

    // scroll-spy active nav
    const navLinks = [...document.querySelectorAll('nav.main a')];
    const spySections = navLinks.map(a => document.querySelector(a.getAttribute('href'))).filter(Boolean);
    function spy() {
        const y = scrollY + 120;
        let current = null;
        spySections.forEach(s => { if (s.offsetTop <= y) current = s.id; });
        navLinks.forEach(a => a.classList.toggle('active', a.getAttribute('href') === '#' + current));
    }
    spy();

    // toast
    function toast(msg, kind = 'ok') {
        const box = document.getElementById('toast');
        const el = document.createElement('div');
        el.className = 'toast ' + kind;
        el.innerHTML = '<i class="fa-solid ' + (kind === 'ok' ? 'fa-circle-check' : 'fa-circle-exclamation') + '"></i><span>' + msg + '</span>';
        box.appendChild(el);
        requestAnimationFrame(() => el.classList.add('show'));
        setTimeout(() => { el.classList.remove('show'); setTimeout(() => el.remove(), 400); }, 4200);
    }

    // clear errors
    function clearErrors(form) {
        form.querySelectorAll('.field.err').forEach(f => { f.classList.remove('err'); const m = f.querySelector('.msg'); if (m) m.textContent = ''; });
        form.querySelectorAll('.up.err').forEach(u => u.classList.remove('err'));
    }
    function applyErrors(form, errors) {
        Object.keys(errors).forEach(name => {
            const input = form.querySelector('[name="' + name + '"]');
            if (!input) return;
            const field = input.closest('.field');
            if (field) { field.classList.add('err'); const m = field.querySelector('.msg'); if (m) m.textContent = errors[name][0]; }
            const up = input.closest('.up'); if (up) up.classList.add('err');
        });
    }
    function setLoading(form, on) {
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = on;
        btn.querySelector('.lbl').style.opacity = on ? '.5' : '1';
        btn.querySelector('.spin').style.display = on ? 'inline-block' : 'none';
    }

    // file previews
    document.querySelectorAll('.up input[type="file"]').forEach(inp => {
        inp.addEventListener('change', () => {
            const up = inp.closest('.up');
            const file = inp.files[0];
            if (!file) { up.classList.remove('has'); return; }
            up.classList.remove('err');
            const img = up.querySelector('img.prev');
            img.src = URL.createObjectURL(file);
            up.classList.add('has');
        });
    });

    // OFFICE form
    document.getElementById('officeForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = this; clearErrors(form); setLoading(form, true);
        try {
            const res = await fetch(OFFICE_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: new FormData(form)
            });
            if (res.ok) { toast(T.ok_office, 'ok'); form.reset(); }
            else if (res.status === 422) { const d = await res.json(); applyErrors(form, d.errors || {}); toast(T.err, 'bad'); }
            else { toast(T.net, 'bad'); }
        } catch (_) { toast(T.net, 'bad'); }
        setLoading(form, false);
    });

    // CONTACT form
    document.getElementById('contactForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = this; clearErrors(form); setLoading(form, true);
        try {
            const res = await fetch(CONTACT_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: new FormData(form)
            });
            if (res.ok) { toast(T.ok_contact, 'ok'); form.reset(); }
            else if (res.status === 422) { const d = await res.json(); applyErrors(form, d.errors || {}); toast(T.err, 'bad'); }
            else { toast(T.net, 'bad'); }
        } catch (_) { toast(T.net, 'bad'); }
        setLoading(form, false);
    });

    // DRIVER form
    document.getElementById('driverForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = this; clearErrors(form);
        let missing = false;
        form.querySelectorAll('.up input[type="file"]').forEach(inp => { if (!inp.files.length) { inp.closest('.up').classList.add('err'); missing = true; } });
        if (missing) { toast(T.files, 'bad'); return; }
        setLoading(form, true);
        try {
            const res = await fetch(DRIVER_URL, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: new FormData(form)
            });
            if (res.ok) { toast(T.ok_driver, 'ok'); form.reset(); form.querySelectorAll('.up.has').forEach(u => u.classList.remove('has')); }
            else if (res.status === 422) { const d = await res.json(); applyErrors(form, d.errors || {}); toast(T.err, 'bad'); }
            else { toast(T.net, 'bad'); }
        } catch (_) { toast(T.net, 'bad'); }
        setLoading(form, false);
    });
</script>
</body>
</html>
