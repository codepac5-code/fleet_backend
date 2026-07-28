<!doctype html>
@php $isRtl = app()->getLocale() === 'ar'; @endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ textByLanguage('رحلة مشتركة', 'Shared trip') }} · {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Cairo', system-ui, sans-serif; background: #0f172a; color: #e2e8f0; display: grid; place-items: center; min-height: 100vh; padding: 1.25rem; }
        .card { width: 100%; max-width: 460px; background: #1e293b; border: 1px solid #334155; border-radius: 20px; padding: 1.75rem; box-shadow: 0 20px 60px rgba(0,0,0,.35); }
        .brand { display: flex; align-items: center; gap: .5rem; font-weight: 700; font-size: 1.15rem; margin-bottom: 1.25rem; }
        .brand span { color: #38bdf8; }
        .status { display: inline-block; padding: .3rem .75rem; border-radius: 999px; font-size: .8rem; font-weight: 600; background: rgba(56,189,248,.15); color: #7dd3fc; margin-bottom: 1.25rem; text-transform: capitalize; }
        .route { display: grid; gap: .9rem; margin: 0 0 1.25rem; }
        .point { display: flex; align-items: flex-start; gap: .7rem; }
        .dot { width: 12px; height: 12px; border-radius: 50%; margin-top: .35rem; flex: none; }
        .dot.from { background: #22c55e; } .dot.to { background: #ef4444; }
        .point small { display: block; opacity: .55; font-size: .72rem; margin-bottom: .1rem; }
        .point strong { font-weight: 600; font-size: 1rem; }
        .meta { border-top: 1px solid #334155; padding-top: 1rem; display: flex; justify-content: space-between; font-size: .85rem; opacity: .8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">fleet<span>.</span> — {{ textByLanguage('رحلة مشتركة', 'Shared trip') }}</div>
        <div class="status">{{ str_replace('_', ' ', $trip['status']) }}</div>
        <div class="route">
            <div class="point"><span class="dot from"></span><div><small>{{ textByLanguage('من', 'From') }}</small><strong>{{ $trip['from'] ?: '—' }}</strong></div></div>
            <div class="point"><span class="dot to"></span><div><small>{{ textByLanguage('إلى', 'To') }}</small><strong>{{ $trip['to'] ?: '—' }}</strong></div></div>
        </div>
        <div class="meta">
            <span>{{ $trip['office'] }}</span>
            <span>{{ $trip['at'] ? \Illuminate\Support\Carbon::parse($trip['at'])->format('Y-m-d H:i') : '' }}</span>
        </div>
    </div>
</body>
</html>
