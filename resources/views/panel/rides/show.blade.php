@extends('panel.layouts.master')

@section('title', textByLanguage('تفاصيل الرحلة', 'Ride details'))
@section('page-title', textByLanguage('تفاصيل الرحلة', 'Ride details'))

@php
    use Illuminate\Support\Carbon;
    $r = fn ($n) => "panel.{$entity}.{$n}";
    $ar = app()->getLocale() === 'ar';
    $t = fn ($en, $arText) => $ar ? $arText : $en;
    $cur = $booking->currency_code;
    $money = fn ($m) => number_format(((int) $m) / 100, 2) . ' ' . $cur;
    $moneyN = fn ($m) => number_format(((int) $m) / 100, 2);
    $dt = fn ($v) => $v ? Carbon::parse($v)->translatedFormat('j M Y، H:i') : null;
    $tm = fn ($v) => $v ? Carbon::parse($v)->format('H:i') : null;
    $live = in_array($booking->status, ['matching','assigned','arriving','arrived','on_trip'], true);

    $statusMeta = [
        'scheduled' => ['tone' => '#6366f1', 'icon' => 'bi-calendar-event', 'label' => $t('Scheduled', 'مجدولة')],
        'matching'  => ['tone' => '#f59e0b', 'icon' => 'bi-broadcast', 'label' => $t('Searching for driver', 'جارٍ البحث عن سائق')],
        'assigned'  => ['tone' => '#3b82f6', 'icon' => 'bi-person-check', 'label' => $t('Driver assigned', 'أُسند سائق')],
        'arriving'  => ['tone' => '#3b82f6', 'icon' => 'bi-signpost-2', 'label' => $t('Driver on the way', 'السائق في الطريق')],
        'arrived'   => ['tone' => '#3b82f6', 'icon' => 'bi-geo-alt', 'label' => $t('Awaiting passenger', 'بانتظار الراكب')],
        'on_trip'   => ['tone' => '#8b5cf6', 'icon' => 'bi-car-front-fill', 'label' => $t('Trip in progress', 'الرحلة جارية')],
        'completed' => ['tone' => '#10b981', 'icon' => 'bi-check-circle-fill', 'label' => $t('Completed', 'اكتملت')],
        'cancelled' => ['tone' => '#ef4444', 'icon' => 'bi-x-circle-fill', 'label' => $t('Cancelled', 'ملغاة')],
        'rejected'  => ['tone' => '#ef4444', 'icon' => 'bi-x-octagon-fill', 'label' => $t('Rejected', 'مرفوضة')],
        'no_driver_expired' => ['tone' => '#ef4444', 'icon' => 'bi-hourglass-bottom', 'label' => $t('No driver found', 'لا سائق')],
    ];
    $sm = $statusMeta[$booking->status] ?? ['tone' => '#64748b', 'icon' => 'bi-dot', 'label' => ucfirst(str_replace('_',' ',$booking->status))];

    $steps = [
        ['icon' => 'bi-flag-fill',     'label' => $t('Requested', 'الطلب'),   'at' => $booking->created_at,       'color' => '#6366f1'],
        ['icon' => 'bi-person-check',  'label' => $t('Assigned', 'الإسناد'),  'at' => $booking->assigned_at,      'color' => '#3b82f6'],
        ['icon' => 'bi-geo-alt-fill',  'label' => $t('Arrived', 'الوصول'),    'at' => $booking->arrived_at,       'color' => '#0ea5e9'],
        ['icon' => 'bi-car-front-fill','label' => $t('Started', 'الانطلاق'),  'at' => $booking->trip_started_at,  'color' => '#8b5cf6'],
        ['icon' => 'bi-flag-fill',     'label' => $t('Completed', 'الاكتمال'),'at' => $booking->completed_at,     'color' => '#10b981'],
    ];
    $curStep = 0; foreach ($steps as $i => $s) { if ($s['at']) $curStep = $i; }

    $waitMin  = ($booking->arrived_at && $booking->assigned_at) ? Carbon::parse($booking->assigned_at)->diffInMinutes($booking->arrived_at) : null;
    $tripMin  = ($booking->completed_at && $booking->trip_started_at) ? Carbon::parse($booking->trip_started_at)->diffInMinutes($booking->completed_at) : null;
    $durS     = (int) ($booking->duration_s ?? 0);
    $distKm   = ((float) ($booking->distance_m ?? 0)) / 1000;
    $meterKm  = ((float) ($booking->meter_distance_m ?? 0)) / 1000;
@endphp

@push('styles')
<style>
    @keyframes rdUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:none; } }
    @keyframes rdPulse { 0%,100% { box-shadow:0 0 0 0 rgba(255,255,255,.5); } 50% { box-shadow:0 0 0 10px rgba(255,255,255,0); } }
    @keyframes rdSpin { to { transform:rotate(360deg); } }
    @keyframes rdGrow { from { width:0 !important; } }

    .rd-up { animation:rdUp .5s both; }
    @for ($i = 1; $i <= 10; $i++) .rd-up:nth-child({{ $i }}) { animation-delay:{{ $i * .05 }}s; } @endfor

    .rd-hero { position:relative; overflow:hidden; display:flex; align-items:center; gap:16px; padding:20px 22px; border-radius:16px; color:#fff; margin-bottom:16px;
        background:linear-gradient(120deg, {{ $sm['tone'] }}, {{ $sm['tone'] }}bb 60%, {{ $sm['tone'] }}88); box-shadow:0 10px 30px {{ $sm['tone'] }}44; }
    .rd-hero::after { content:''; position:absolute; inset-inline-end:-40px; top:-40px; width:180px; height:180px; border-radius:50%; background:rgba(255,255,255,.08); }
    .rd-hero__ic { width:56px; height:56px; border-radius:15px; background:rgba(255,255,255,.2); display:flex; align-items:center; justify-content:center; font-size:1.7rem; flex:none; position:relative; z-index:1; {{ $live ? 'animation:rdPulse 2s infinite;' : '' }} }
    .rd-hero__id { font-size:1.4rem; font-weight:800; line-height:1.1; }
    .rd-hero__sub { opacity:.92; font-size:.86rem; margin-top:3px; display:flex; align-items:center; gap:6px; }
    .rd-live { display:inline-block; width:7px; height:7px; border-radius:50%; background:#fff; animation:rdPulse 1.4s infinite; }
    .rd-hero__tot { margin-inline-start:auto; text-align:{{ $ar ? 'left' : 'right' }}; position:relative; z-index:1; }
    .rd-hero__tot b { font-size:1.5rem; font-weight:800; }
    .rd-hero__tot span { display:block; opacity:.9; font-size:.74rem; }

    /* colorful stat tiles */
    .rd-tiles { display:grid; grid-template-columns:repeat(auto-fit,minmax(120px,1fr)); gap:12px; margin-bottom:16px; }
    .rd-tile { display:flex; align-items:center; gap:11px; padding:13px 14px; border-radius:13px; background:var(--p-surface,#fff); border:1px solid var(--p-border); transition:.2s; }
    .rd-tile:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,.08); }
    .rd-tile__ic { width:40px; height:40px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:1.15rem; flex:none; }
    .rd-tile__v { font-size:1.15rem; font-weight:800; line-height:1; }
    .rd-tile__l { font-size:.72rem; color:var(--p-text-muted); margin-top:3px; }

    .rd-cols { display:grid; grid-template-columns:1.5fr 1fr; gap:16px; align-items:start; }
    @media (max-width:900px){ .rd-cols { grid-template-columns:1fr; } }

    .rd-card { transition:.2s; }
    .rd-card:hover { box-shadow:0 8px 24px rgba(0,0,0,.07); }
    .rd-sec { margin-bottom:16px; }
    .rd-sec__h { display:flex; align-items:center; gap:10px; font-size:.95rem; font-weight:800; margin:0 0 14px; }
    .rd-sec__ic { width:32px; height:32px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex:none; }

    .rd-kv { display:grid; grid-template-columns:1fr 1fr; gap:11px 16px; }
    .rd-kv dt { font-size:.73rem; color:var(--p-text-muted); margin-bottom:2px; }
    .rd-kv dd { margin:0; font-weight:600; font-size:.9rem; }

    /* journey timeline */
    .rd-steps { display:flex; gap:0; margin:2px 0 16px; overflow-x:auto; padding-bottom:4px; }
    .rd-step { flex:1; min-width:82px; text-align:center; position:relative; }
    .rd-step__dot { width:38px; height:38px; border-radius:50%; margin:0 auto 7px; display:flex; align-items:center; justify-content:center;
        background:var(--p-bg-soft,#eef1f6); color:var(--p-text-muted); border:2px solid var(--p-border); font-size:.95rem; position:relative; z-index:2; transition:.3s; }
    .rd-step.done .rd-step__lbl { color:var(--p-text); font-weight:700; }
    .rd-step.cur .rd-step__dot { animation:rdPulse 1.8s infinite; transform:scale(1.12); }
    .rd-step:not(:first-child)::before { content:''; position:absolute; top:19px; inset-inline-end:50%; width:100%; height:3px; background:var(--p-border); z-index:1; }
    .rd-step__lbl { font-size:.72rem; color:var(--p-text-muted); }
    .rd-step__at { font-size:.67rem; color:var(--p-text-muted); margin-top:1px; }

    #rdMap { width:100%; height:320px; border-radius:12px; background:linear-gradient(135deg,#eef1f6,#e2e8f0); position:relative; overflow:hidden; }
    #rdMap.loading::after { content:''; position:absolute; top:50%; inset-inline-start:50%; width:26px; height:26px; margin:-13px; border:3px solid var(--p-border); border-top-color:var(--p-primary,#5b5bd6); border-radius:50%; animation:rdSpin .8s linear infinite; }
    .rd-route { list-style:none; margin:12px 0 0; padding:0; }
    .rd-route li { display:flex; gap:11px; padding:8px 0; align-items:flex-start; position:relative; }
    .rd-route li:not(:last-child)::before { content:''; position:absolute; inset-inline-start:10px; top:26px; bottom:-4px; width:2px; background:var(--p-border); }
    .rd-route__pin { width:22px; height:22px; border-radius:50%; flex:none; display:flex; align-items:center; justify-content:center; color:#fff; font-size:.62rem; z-index:1; }
    .rd-route__txt { font-weight:600; font-size:.88rem; }
    .rd-route__sub { font-size:.72rem; color:var(--p-text-muted); }

    .rd-fare { list-style:none; margin:0; padding:0; }
    .rd-fare li { display:flex; align-items:center; gap:10px; padding:9px 0; border-bottom:1px dashed var(--p-border); font-size:.9rem; }
    .rd-fare li:last-child { border-bottom:0; }
    .rd-fare__ic { width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.85rem; flex:none; }
    .rd-fare__amt { margin-inline-start:auto; font-weight:700; }
    .rd-fare .tot { padding-top:13px; } .rd-fare .tot .rd-fare__amt { font-size:1.2rem; font-weight:800; color:var(--p-primary,#5b5bd6); }

    .rd-split { display:flex; height:14px; border-radius:8px; overflow:hidden; margin:12px 0; background:var(--p-bg-soft,#eef1f6); }
    .rd-split span { display:block; transition:width 1s cubic-bezier(.2,.8,.2,1); }
    .rd-legend { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; }
    .rd-legc { text-align:center; padding:8px 4px; border-radius:10px; background:var(--p-bg-soft,#f7f8fb); }
    .rd-legc b { display:block; font-size:.92rem; } .rd-legc span { font-size:.68rem; color:var(--p-text-muted); }

    /* commission equation */
    .rd-eq { margin-top:14px; border:1px solid var(--p-border); border-radius:12px; overflow:hidden; background:var(--p-bg-soft,#f8fafc); }
    .rd-eq__h { display:flex; align-items:center; gap:7px; font-size:.8rem; font-weight:800; padding:9px 13px; background:var(--p-surface,#fff); border-bottom:1px solid var(--p-border); }
    .rd-eq__h i { color:#9333ea; }
    .rd-eq__row { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:9px 13px; font-size:.86rem; border-inline-start:3px solid transparent; }
    .rd-eq__row[style*="--c"] { border-inline-start-color:var(--c); }
    .rd-eq__lbl { display:flex; align-items:center; gap:7px; font-weight:600; color:var(--p-text); }
    .rd-eq__row[style*="--c"] .rd-eq__lbl i { color:var(--c); }
    .rd-eq__base { background:var(--p-surface,#fff); font-weight:700; }
    .rd-eq__val { font-weight:800; }
    .rd-eq__calc { font-variant-numeric:tabular-nums; color:var(--p-text-muted); font-size:.82rem; direction:ltr; }
    .rd-eq__calc b { color:var(--p-text); }
    .rd-eq__eq { margin:0 5px; color:var(--p-text-muted); }
    .rd-eq__out { color:var(--c,#333) !important; font-weight:800; }
    .rd-eq__rule { height:1px; background:repeating-linear-gradient(90deg,var(--p-border) 0 6px,transparent 6px 11px); margin:2px 13px; }
    .rd-eq__driver { background:rgba(16,185,129,.07); }
    .rd-eq__final { font-size:1.05rem; }
    .rd-eq__verify { display:flex; align-items:center; gap:7px; padding:8px 13px; font-size:.78rem; font-variant-numeric:tabular-nums; direction:ltr; border-top:1px solid var(--p-border); }
    .rd-eq__verify.ok { color:#16a34a; background:rgba(16,185,129,.08); }
    .rd-eq__verify.bad { color:#dc2626; background:rgba(239,68,68,.08); }

    .rd-person { display:flex; align-items:center; gap:12px; padding:11px 0; }
    .rd-person__av { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.15rem; color:#fff; flex:none; }
    .rd-person__n { font-weight:700; font-size:.92rem; }
    .rd-person__m { font-size:.77rem; color:var(--p-text-muted); }
    .rd-copy { cursor:pointer; border:0; background:transparent; color:var(--p-text-muted); font-size:.8rem; padding:2px 5px; border-radius:6px; transition:.15s; }
    .rd-copy:hover { color:var(--p-primary,#5b5bd6); background:var(--p-bg-soft,#eef1f6); }

    .rd-stars i { font-size:1.05rem; } .rd-stars .on { color:#f59e0b; } .rd-stars .off { color:var(--p-border); }
    .rd-tags { display:flex; flex-wrap:wrap; gap:6px; margin-top:7px; }
    .rd-tag { font-size:.72rem; padding:3px 10px; border-radius:20px; background:#eef2ff; color:#4f46e5; font-weight:600; }
</style>
@endpush

@section('content')

    <x-panel.page-toolbar :title="textByLanguage('رحلة', 'Ride') . ' #' . $booking->id" :subtitle="$customerName">
        <x-slot:actions>
            @if($booking->status === 'completed')
                <a href="{{ route($r('rides.receipt'), $booking->id) }}" target="_blank" rel="noopener" class="p-btn p-btn--ghost"><i class="bi bi-receipt"></i> {{ $t('Receipt', 'إيصال') }}</a>
            @endif
            <a href="{{ route($r('rides.index')) }}" class="p-btn p-btn--ghost"><i class="bi bi-arrow-{{ $ar ? 'right' : 'left' }}"></i> {{ $t('Back', 'رجوع') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if(session('error'))<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>@endif

    {{-- Status hero --}}
    <div class="rd-hero rd-up">
        <div class="rd-hero__ic"><i class="bi {{ $sm['icon'] }}"></i></div>
        <div>
            <div class="rd-hero__id">{{ $t('Ride', 'رحلة') }} #{{ $booking->id }}</div>
            <div class="rd-hero__sub">@if($live)<span class="rd-live"></span>@endif {{ $sm['label'] }} · {{ ucfirst($booking->service) }}@if($booking->service_class) / {{ ucfirst(str_replace('_',' ',$booking->service_class)) }}@endif</div>
        </div>
        <div class="rd-hero__tot">
            <b><span class="rd-count" data-to="{{ $moneyN($booking->total_minor) }}">{{ $moneyN($booking->total_minor) }}</span> {{ $cur }}</b>
            <span><i class="bi bi-{{ $booking->payment_method === 'cash' ? 'cash-coin' : 'credit-card' }}"></i> {{ ucfirst($booking->payment_method) }} @if($booking->pricing_style)· {{ $t($booking->pricing_style === 'meter' ? 'meter' : 'fixed', $booking->pricing_style === 'meter' ? 'عدّاد' : 'ثابت') }}@endif</span>
        </div>
    </div>

    {{-- Colorful stat tiles --}}
    <div class="rd-tiles">
        <div class="rd-tile rd-up"><div class="rd-tile__ic" style="background:#eef2ff;color:#4f46e5;"><i class="bi bi-rulers"></i></div>
            <div><div class="rd-tile__v"><span class="rd-count" data-to="{{ number_format($distKm,1) }}">{{ number_format($distKm,1) }}</span></div><div class="rd-tile__l">{{ $t('Distance (km)','المسافة (كم)') }}</div></div></div>
        <div class="rd-tile rd-up"><div class="rd-tile__ic" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-stopwatch"></i></div>
            <div><div class="rd-tile__v"><span class="rd-count" data-to="{{ $durS > 0 ? intdiv($durS,60) : ($tripMin ?? 0) }}">{{ $durS > 0 ? intdiv($durS,60) : ($tripMin ?? 0) }}</span></div><div class="rd-tile__l">{{ $t('Duration (min)','المدة (دق)') }}</div></div></div>
        <div class="rd-tile rd-up"><div class="rd-tile__ic" style="background:#fffbeb;color:#d97706;"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="rd-tile__v"><span class="rd-count" data-to="{{ $waitMin ?? 0 }}">{{ $waitMin ?? 0 }}</span></div><div class="rd-tile__l">{{ $t('Wait (min)','الانتظار (دق)') }}</div></div></div>
        <div class="rd-tile rd-up"><div class="rd-tile__ic" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-people-fill"></i></div>
            <div><div class="rd-tile__v">{{ $booking->passengers ?: 1 }}</div><div class="rd-tile__l">{{ $t('Passengers','الركّاب') }}</div></div></div>
        @if($meterKm > 0)
        <div class="rd-tile rd-up"><div class="rd-tile__ic" style="background:#faf5ff;color:#9333ea;"><i class="bi bi-speedometer2"></i></div>
            <div><div class="rd-tile__v">{{ number_format($meterKm,1) }}</div><div class="rd-tile__l">{{ $t('Meter (km)','العدّاد (كم)') }}</div></div></div>
        @endif
    </div>

    {{-- Journey timeline --}}
    <div class="p-card rd-card rd-sec rd-up">
        <div class="rd-steps">
            @foreach($steps as $i => $s)
                <div class="rd-step {{ $s['at'] ? 'done' : '' }} {{ $i === $curStep && $live ? 'cur' : '' }}">
                    <div class="rd-step__dot" @if($s['at']) style="background:{{ $s['color'] }};border-color:{{ $s['color'] }};color:#fff;" @endif><i class="bi {{ $s['icon'] }}"></i></div>
                    @if($i > 0)<div class="rd-step__seg" style="position:absolute;top:19px;inset-inline-end:50%;width:100%;height:3px;z-index:1;{{ $s['at'] ? 'background:'.$s['color'].';' : '' }}"></div>@endif
                    <div class="rd-step__lbl">{{ $s['label'] }}</div>
                    <div class="rd-step__at">{{ $s['at'] ? $tm($s['at']) : '—' }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rd-cols">
        {{-- LEFT --}}
        <div>
            {{-- Map + route --}}
            <div class="p-card rd-card rd-sec rd-up">
                <h3 class="rd-sec__h"><span class="rd-sec__ic" style="background:#eef2ff;color:#4f46e5;"><i class="bi bi-map-fill"></i></span> {{ $t('Route', 'المسار') }}</h3>
                @if(count($path) >= 2 && $mapKey)
                    <div id="rdMap" class="loading" data-path='@json($path)'></div>
                @else
                    <div id="rdMap" style="display:flex;align-items:center;justify-content:center;color:var(--p-text-muted);font-size:.85rem;">
                        <i class="bi bi-geo-alt" style="margin-inline-end:6px;"></i> {{ $t('Route not available', 'المسار غير متاح') }}
                    </div>
                @endif
                <ul class="rd-route">
                    <li>
                        <span class="rd-route__pin" style="background:#16a34a;"><i class="bi bi-check-lg"></i></span>
                        <span><span class="rd-route__txt">{{ $booking->pickup_title ?: $t('Pickup point','نقطة الانطلاق') }}</span>
                        @if($booking->pickup_note)<span class="rd-route__sub">{{ $booking->pickup_note }}</span>@endif</span>
                    </li>
                    @foreach((array) ($booking->stops ?? []) as $i => $stop)
                        <li><span class="rd-route__pin" style="background:#d97706;">{{ $i+1 }}</span>
                        <span class="rd-route__txt">{{ $stop['title'] ?? ($t('Stop','توقّف') . ' ' . ($i+1)) }}</span></li>
                    @endforeach
                    <li>
                        <span class="rd-route__pin" style="background:#dc2626;"><i class="bi bi-flag-fill"></i></span>
                        <span class="rd-route__txt">{{ $booking->dropoff_title ?: $t('Destination','الوجهة') }}</span>
                    </li>
                </ul>
            </div>

            {{-- Fare --}}
            <div class="p-card rd-card rd-sec rd-up">
                <h3 class="rd-sec__h"><span class="rd-sec__ic" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-cash-stack"></i></span> {{ $t('Fare breakdown', 'تفصيل الأجرة') }}</h3>
                <ul class="rd-fare">
                    <li><span class="rd-fare__ic" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-currency-exchange"></i></span> {{ $t('Base fare', 'الأجرة الأساسية') }}<span class="rd-fare__amt">{{ $money($booking->fare_minor) }}</span></li>
                    @if((int)$booking->waiting_minor > 0)<li><span class="rd-fare__ic" style="background:#fffbeb;color:#d97706;"><i class="bi bi-hourglass"></i></span> {{ $t('Waiting', 'الانتظار') }}<span class="rd-fare__amt">{{ $money($booking->waiting_minor) }}</span></li>@endif
                    @if((int)$booking->tip_minor > 0)<li><span class="rd-fare__ic" style="background:#fdf4ff;color:#c026d3;"><i class="bi bi-gift"></i></span> {{ $t('Tip', 'إكرامية') }}<span class="rd-fare__amt">{{ $money($booking->tip_minor) }}</span></li>@endif
                    @if((int)$booking->discount_minor > 0)<li><span class="rd-fare__ic" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-tag-fill"></i></span> {{ $t('Discount', 'الخصم') }} @if($booking->promo_code)<span class="rd-tag">{{ $booking->promo_code }}</span>@endif<span class="rd-fare__amt" style="color:#16a34a;">− {{ $money($booking->discount_minor) }}</span></li>@endif
                    <li class="tot"><span class="rd-fare__ic" style="background:#eef2ff;color:#4f46e5;"><i class="bi bi-wallet2"></i></span> {{ $t('Total', 'الإجمالي') }}<span class="rd-fare__amt">{{ $money($booking->total_minor) }}</span></li>
                </ul>
            </div>

            {{-- Commission split + calculation --}}
            @if($commission)
                @php
                    $fleet=(int)$commission->fleet_minor; $off=(int)$commission->office_minor; $drv=(int)$commission->driver_minor;
                    $sum=max(1,$fleet+$off+$drv);
                    $base=(int)$commission->fare_minor;
                    $fRate=(float)$commission->fleet_rate; $oRate=(float)$commission->office_rate;
                    $rate = fn($x) => rtrim(rtrim(number_format((float)$x, 2), '0'), '.');
                    $ok = ($fleet + $off + $drv) === $base;
                @endphp
                <div class="p-card rd-card rd-sec rd-up">
                    <h3 class="rd-sec__h"><span class="rd-sec__ic" style="background:#faf5ff;color:#9333ea;"><i class="bi bi-pie-chart-fill"></i></span> {{ $t('Commission split', 'توزيع العمولة') }}</h3>
                    <div class="rd-split">
                        <span data-w="{{ $fleet/$sum*100 }}" style="width:0;background:#6366f1;"></span>
                        <span data-w="{{ $off/$sum*100 }}" style="width:0;background:#0ea5e9;"></span>
                        <span data-w="{{ $drv/$sum*100 }}" style="width:0;background:#10b981;"></span>
                    </div>
                    <div class="rd-legend">
                        <div class="rd-legc"><b style="color:#6366f1;">{{ $money($fleet) }}</b><span>{{ $t('Fleet','النظام') }} · {{ $rate($fRate) }}%</span></div>
                        <div class="rd-legc"><b style="color:#0ea5e9;">{{ $money($off) }}</b><span>{{ $t('Office','المكتب') }} · {{ $rate($oRate) }}%</span></div>
                        <div class="rd-legc"><b style="color:#10b981;">{{ $money($drv) }}</b><span>{{ $t('Driver','السائق') }}</span></div>
                    </div>

                    {{-- The equation --}}
                    <div class="rd-eq">
                        <div class="rd-eq__h"><i class="bi bi-calculator"></i> {{ $t('How it is calculated', 'طريقة الحساب') }}</div>
                        <div class="rd-eq__row rd-eq__base">
                            <span class="rd-eq__lbl"><i class="bi bi-cash"></i> {{ $t('Base (charged fare)','الأساس (الأجرة المحصّلة)') }}</span>
                            <span class="rd-eq__val">{{ $money($base) }}</span>
                        </div>
                        <div class="rd-eq__row" style="--c:#6366f1;">
                            <span class="rd-eq__lbl"><i class="bi bi-diagram-3"></i> {{ $t('Fleet','النظام') }}</span>
                            <span class="rd-eq__calc"><b>{{ $moneyN($base) }}</b> × {{ $rate($fRate) }}% <span class="rd-eq__eq">=</span> <b class="rd-eq__out">{{ $money($fleet) }}</b></span>
                        </div>
                        <div class="rd-eq__row" style="--c:#0ea5e9;">
                            <span class="rd-eq__lbl"><i class="bi bi-building"></i> {{ $t('Office','المكتب') }}</span>
                            <span class="rd-eq__calc"><b>{{ $moneyN($base) }}</b> × {{ $rate($oRate) }}% <span class="rd-eq__eq">=</span> <b class="rd-eq__out">{{ $money($off) }}</b></span>
                        </div>
                        <div class="rd-eq__rule"></div>
                        <div class="rd-eq__row rd-eq__driver" style="--c:#10b981;">
                            <span class="rd-eq__lbl"><i class="bi bi-person-badge"></i> {{ $t('Driver net','صافي السائق') }}</span>
                            <span class="rd-eq__calc">{{ $moneyN($base) }} − {{ $moneyN($fleet) }} − {{ $moneyN($off) }} <span class="rd-eq__eq">=</span> <b class="rd-eq__out rd-eq__final">{{ $money($drv) }}</b></span>
                        </div>
                        <div class="rd-eq__verify {{ $ok ? 'ok' : 'bad' }}">
                            <i class="bi bi-{{ $ok ? 'check-circle-fill' : 'exclamation-triangle-fill' }}"></i>
                            {{ $moneyN($fleet) }} + {{ $moneyN($off) }} + {{ $moneyN($drv) }} = {{ $money($base) }} {{ $ok ? '✓' : '⚠' }}
                        </div>
                    </div>

                    <div style="margin-top:10px;display:flex;gap:14px;flex-wrap:wrap;font-size:.76rem;color:var(--p-text-muted);">
                        <span><i class="bi bi-{{ $commission->pricing_style === 'meter' ? 'speedometer2' : 'pin-map' }}"></i> {{ $t($commission->pricing_style === 'meter' ? 'Meter pricing' : 'Fixed pricing', $commission->pricing_style === 'meter' ? 'تسعير بالعدّاد' : 'تسعير ثابت') }}</span>
                        <span><i class="bi bi-wallet2"></i> {{ $booking->payment_method === 'cash' ? $t('Cash — commission debited from driver','كاش — العمولة تُخصم من السائق') : $t('Digital — distributed from fleet','رقمي — يُوزّع من النظام') }}</span>
                        @if($commission->subscription_plan)<span><i class="bi bi-award-fill" style="color:#f59e0b;"></i> {{ $t('Plan','الخطة') }}: {{ ucfirst($commission->subscription_plan) }}</span>@endif
                    </div>
                </div>
            @endif
        </div>

        {{-- RIGHT --}}
        <div>
            {{-- People --}}
            <div class="p-card rd-card rd-sec rd-up">
                <h3 class="rd-sec__h"><span class="rd-sec__ic" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-people-fill"></i></span> {{ $t('People', 'الأطراف') }}</h3>
                <div class="rd-person">
                    <div class="rd-person__av" style="background:linear-gradient(135deg,#3b82f6,#2563eb);"><i class="bi bi-person-fill"></i></div>
                    <div><div class="rd-person__n">{{ $customerName ?: $t('Rider','الراكب') }}</div><div class="rd-person__m">{{ $t('Rider','راكب') }}</div></div>
                    @if($customerPhone)<a class="p-btn p-btn--ghost p-btn--sm" style="margin-inline-start:auto;" dir="ltr" href="tel:{{ $customerPhone }}"><i class="bi bi-telephone-fill"></i> {{ $customerPhone }}</a><button class="rd-copy" data-copy="{{ $customerPhone }}" title="{{ $t('Copy','نسخ') }}"><i class="bi bi-clipboard"></i></button>@endif
                </div>
                <div class="rd-person" style="border-top:1px solid var(--p-border);">
                    <div class="rd-person__av" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);"><i class="bi bi-person-badge-fill"></i></div>
                    <div>
                        <div class="rd-person__n">{{ $driverName ?: ($booking->driver_id ? '#'.$booking->driver_id : $t('Not assigned','لم يُسند')) }}</div>
                        <div class="rd-person__m">
                            @if($vehicle)<i class="bi bi-car-front"></i> {{ trim(($vehicle->vehicleBrand ?? '').' '.($vehicle->model ?? '')) }} @if($vehicle->plate)· <span dir="ltr">{{ $vehicle->plate }}</span>@endif @if($vehicle->color)· {{ $vehicle->color }}@endif
                            @else {{ $t('Driver','سائق') }} @endif
                        </div>
                    </div>
                    @if($driverPhone)<a class="p-btn p-btn--ghost p-btn--sm" style="margin-inline-start:auto;" dir="ltr" href="tel:{{ $driverPhone }}"><i class="bi bi-telephone-fill"></i> {{ $driverPhone }}</a>@endif
                </div>
                @if($officeName)<div style="margin-top:10px;font-size:.82rem;color:var(--p-text-muted);"><i class="bi bi-building-fill" style="color:#0ea5e9;"></i> {{ $officeName }}</div>@endif
            </div>

            {{-- Timeline detail --}}
            <div class="p-card rd-card rd-sec rd-up">
                <h3 class="rd-sec__h"><span class="rd-sec__ic" style="background:#fff7ed;color:#ea580c;"><i class="bi bi-clock-history"></i></span> {{ $t('Timeline', 'الخط الزمني') }}</h3>
                <dl class="rd-kv">
                    <div><dt>{{ $t('Requested','الطلب') }}</dt><dd>{{ $dt($booking->created_at) ?: '—' }}</dd></div>
                    @if($booking->scheduled_at)<div><dt>{{ $t('Scheduled for','مجدولة لـ') }}</dt><dd>{{ $dt($booking->scheduled_at) }}</dd></div>@endif
                    @if($booking->assigned_at)<div><dt>{{ $t('Assigned','الإسناد') }}</dt><dd>{{ $dt($booking->assigned_at) }}</dd></div>@endif
                    @if($booking->arrived_at)<div><dt>{{ $t('Arrived','الوصول') }}</dt><dd>{{ $dt($booking->arrived_at) }}</dd></div>@endif
                    @if($booking->trip_started_at)<div><dt>{{ $t('Started','الانطلاق') }}</dt><dd>{{ $dt($booking->trip_started_at) }}</dd></div>@endif
                    @if($booking->completed_at)<div><dt>{{ $t('Completed','الاكتمال') }}</dt><dd>{{ $dt($booking->completed_at) }}</dd></div>@endif
                    @if($booking->cancelled_at)<div><dt>{{ $t('Cancelled','الإلغاء') }}</dt><dd>{{ $dt($booking->cancelled_at) }}</dd></div>@endif
                </dl>
                @if($booking->cancel_reason)
                    <div style="margin-top:12px;padding:9px 12px;border-radius:10px;background:#fef2f2;color:#b91c1c;font-size:.82rem;">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $t('Reason','السبب') }}: {{ $booking->cancel_reason }}
                    </div>
                @endif
            </div>

            {{-- Ratings --}}
            @if($riderRating || $driverRating)
                <div class="p-card rd-card rd-sec rd-up">
                    <h3 class="rd-sec__h"><span class="rd-sec__ic" style="background:#fffbeb;color:#f59e0b;"><i class="bi bi-star-fill"></i></span> {{ $t('Ratings', 'التقييمات') }}</h3>
                    @foreach([[$riderRating, $t('Rider → Driver','الراكب ← السائق')], [$driverRating, $t('Driver → Rider','السائق ← الراكب')]] as $pair)
                        @php [$rt, $lbl] = $pair; @endphp
                        @if($rt)
                            <div style="{{ !$loop->first ? 'border-top:1px solid var(--p-border);padding-top:11px;margin-top:11px;' : '' }}">
                                <div class="rd-person__m">{{ $lbl }}</div>
                                <div class="rd-stars" style="margin:3px 0;">@for($i=1;$i<=5;$i++)<i class="bi bi-star-fill {{ $i <= (int)$rt->stars ? 'on' : 'off' }}"></i>@endfor <b style="margin-inline-start:6px;">{{ $rt->stars }}/5</b></div>
                                @if($rt->comment)<div style="font-size:.83rem;color:var(--p-text);">“{{ $rt->comment }}”</div>@endif
                                @php $rtags = is_array($rt->tags) ? $rt->tags : (json_decode($rt->tags ?? '[]', true) ?: []); @endphp
                                @if($rtags)<div class="rd-tags">@foreach($rtags as $tg)<span class="rd-tag">{{ $tg }}</span>@endforeach</div>@endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Extras --}}
            @if($booking->luggage || $booking->flight_no || $booking->stripe_payment_intent_id)
                <div class="p-card rd-card rd-sec rd-up">
                    <h3 class="rd-sec__h"><span class="rd-sec__ic" style="background:#f8fafc;color:#64748b;"><i class="bi bi-info-circle-fill"></i></span> {{ $t('Trip details', 'تفاصيل إضافية') }}</h3>
                    <dl class="rd-kv">
                        <div><dt>{{ $t('Source','المصدر') }}</dt><dd>{{ ucfirst($booking->source ?: 'rider') }}</dd></div>
                        @if($booking->luggage)<div><dt>{{ $t('Luggage','الأمتعة') }}</dt><dd><i class="bi bi-luggage"></i> {{ $booking->luggage }}</dd></div>@endif
                        @if($booking->flight_no)<div><dt>{{ $t('Flight','الرحلة الجوية') }}</dt><dd dir="ltr"><i class="bi bi-airplane"></i> {{ $booking->flight_no }}</dd></div>@endif
                        @if($booking->stripe_payment_intent_id)<div><dt>Stripe</dt><dd dir="ltr" style="font-size:.7rem;">{{ $booking->stripe_payment_intent_id }} <button class="rd-copy" data-copy="{{ $booking->stripe_payment_intent_id }}"><i class="bi bi-clipboard"></i></button></dd></div>@endif
                    </dl>
                </div>
            @endif
        </div>
    </div>

    {{-- Refund --}}
    @if($canRefund && $booking->status === 'completed' && $booking->payment_method !== 'cash' && (int) $booking->total_minor > 0)
        <div class="p-card rd-card rd-up">
            <h3 class="rd-sec__h"><span class="rd-sec__ic" style="background:#fef2f2;color:#dc2626;"><i class="bi bi-arrow-counterclockwise"></i></span> {{ $t('Refund', 'استرداد') }}</h3>
            <p style="color:var(--p-text-muted); font-size:.85rem; margin:0 0 12px;">{{ $t('Credits the rider\'s wallet from fleet revenue. One refund per ride.', 'يضيف رصيداً لمحفظة الراكب من إيراد الأسطول. استرداد واحد لكل رحلة.') }}</p>
            <form method="POST" action="{{ route($r('booking.refund'), $booking->id) }}" style="display:flex; gap:.6rem; align-items:flex-end; flex-wrap:wrap;">
                @csrf
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <label style="font-size:.8rem; font-weight:600;">{{ $t('Amount', 'المبلغ') }} ({{ $cur }})</label>
                    <input name="amount" type="number" step="0.01" min="0.01" max="{{ number_format($booking->total_minor / 100, 2, '.', '') }}" value="{{ number_format($booking->total_minor / 100, 2, '.', '') }}" style="padding:10px 12px; border:1.5px solid var(--p-border); border-radius:var(--p-radius-sm);">
                </div>
                <div style="display:flex; flex-direction:column; gap:6px; flex:1; min-width:200px;">
                    <label style="font-size:.8rem; font-weight:600;">{{ $t('Reason (optional)', 'السبب (اختياري)') }}</label>
                    <input name="reason" style="padding:10px 12px; border:1.5px solid var(--p-border); border-radius:var(--p-radius-sm);">
                </div>
                <button type="submit" class="p-btn p-btn--soft" onclick="return confirm('{{ $t('Issue refund?', 'إصدار الاسترداد؟') }}');" style="color:var(--p-danger);">
                    <i class="bi bi-arrow-counterclockwise"></i> {{ $t('Refund', 'استرداد') }}
                </button>
            </form>
        </div>
    @endif

@endsection

@push('scripts')
<script>
(function () {
    // count-up on the metric numbers + total
    function countUp(el) {
        var target = parseFloat(el.dataset.to); if (isNaN(target)) return;
        var dec = (el.dataset.to.indexOf('.') > -1) ? (el.dataset.to.split('.')[1].length) : 0;
        var start = null, dur = 900;
        function step(ts) { if (!start) start = ts; var p = Math.min((ts - start) / dur, 1);
            el.textContent = (target * (0.5 - Math.cos(p * Math.PI) / 2)).toFixed(dec);
            if (p < 1) requestAnimationFrame(step); else el.textContent = target.toFixed(dec); }
        requestAnimationFrame(step);
    }
    document.querySelectorAll('.rd-count').forEach(countUp);

    // grow the commission bars
    setTimeout(function () { document.querySelectorAll('.rd-split span[data-w]').forEach(function (s) { s.style.width = s.dataset.w + '%'; }); }, 150);

    // copy-to-clipboard with feedback
    document.querySelectorAll('.rd-copy').forEach(function (b) {
        b.addEventListener('click', function () {
            navigator.clipboard && navigator.clipboard.writeText(b.dataset.copy);
            var old = b.innerHTML; b.innerHTML = '<i class="bi bi-check-lg" style="color:#16a34a"></i>';
            setTimeout(function () { b.innerHTML = old; }, 1200);
        });
    });
})();
</script>
@if(count($path) >= 2 && $mapKey)
<script>
    function initRideMap() {
        var el = document.getElementById('rdMap');
        if (!el || !window.google) return;
        el.classList.remove('loading');
        var path = JSON.parse(el.dataset.path || '[]');
        if (path.length < 2) return;

        var map = new google.maps.Map(el, { zoom: 13, mapTypeControl: false, streetViewControl: false, fullscreenControl: true,
            styles: [{ featureType: 'poi', stylers: [{ visibility: 'off' }] }] });
        var bounds = new google.maps.LatLngBounds();
        path.forEach(function (p) { bounds.extend({ lat: p.lat, lng: p.lng }); });

        var svg = function (w, h, body) { return { url: 'data:image/svg+xml;utf8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="' + w + '" height="' + h + '">' + body + '</svg>'), scaledSize: new google.maps.Size(w, h), anchor: new google.maps.Point(w / 2, body.indexOf('path') > -1 ? h : h / 2) }; };
        var pinFor = function (kind) {
            if (kind === 'pickup') return svg(28, 28, '<circle cx="14" cy="14" r="10" fill="#16a34a" stroke="#fff" stroke-width="3"/>');
            if (kind === 'dropoff') return svg(28, 36, '<path d="M14 0C7 0 2 5 2 12c0 8 12 24 12 24s12-16 12-24C26 5 21 0 14 0z" fill="#dc2626" stroke="#fff" stroke-width="2"/>');
            return svg(22, 22, '<circle cx="11" cy="11" r="7" fill="#d97706" stroke="#fff" stroke-width="3"/>');
        };

        path.forEach(function (p) {
            var m = new google.maps.Marker({ position: { lat: p.lat, lng: p.lng }, map: map, icon: pinFor(p.kind), title: p.title || '', animation: google.maps.Animation.DROP });
            if (p.title) { var iw = new google.maps.InfoWindow({ content: '<div style="font-size:12px;font-weight:600">' + p.title + '</div>' });
                m.addListener('click', function () { iw.open(map, m); }); }
        });

        var svc = new google.maps.DirectionsService();
        var renderer = new google.maps.DirectionsRenderer({ map: map, suppressMarkers: true, preserveViewport: true, polylineOptions: { strokeColor: '#5b5bd6', strokeWeight: 5, strokeOpacity: .9 } });
        var origin = path[0], dest = path[path.length - 1];
        var waypoints = path.slice(1, -1).map(function (p) { return { location: { lat: p.lat, lng: p.lng }, stopover: true }; });
        svc.route({ origin: { lat: origin.lat, lng: origin.lng }, destination: { lat: dest.lat, lng: dest.lng }, waypoints: waypoints, travelMode: google.maps.TravelMode.DRIVING },
            function (res, status) {
                if (status === 'OK') { renderer.setDirections(res); }
                else { new google.maps.Polyline({ path: path.map(function (p) { return { lat: p.lat, lng: p.lng }; }), map: map, strokeColor: '#5b5bd6', strokeWeight: 4, strokeOpacity: .7, icons: [{ icon: { path: 'M 0,-1 0,1', strokeOpacity: 1, scale: 3 }, offset: '0', repeat: '14px' }] }); }
            });
        map.fitBounds(bounds, 60);
    }
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $mapKey }}&callback=initRideMap"></script>
@endif
@endpush
