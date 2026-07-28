@php
    use App\Models\SiteSetting;
    $ar = app()->getLocale() === 'ar';
    $t = fn ($en, $arText) => $ar ? $arText : $en;
    $cur = $booking->currency_code;
    $money = fn ($m) => number_format(((int) $m) / 100, 2) . ' ' . $cur;
    $brand = SiteSetting::val($ar ? 'app_name_ar' : 'app_name_en', 'FleetOS');
    $when = $booking->completed_at ?? $booking->created_at;
@endphp
<!doctype html>
<html lang="{{ $ar ? 'ar' : 'en' }}" dir="{{ $ar ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $t('Receipt', 'إيصال') }} #{{ $booking->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Cairo', system-ui, sans-serif; background: #f3f4f6; color: #111; margin: 0; padding: 24px; }
        .rc { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 14px; padding: 28px; box-shadow: 0 6px 24px rgba(0,0,0,.08); }
        .rc__head { text-align: center; border-bottom: 2px dashed #e5e7eb; padding-bottom: 16px; margin-bottom: 16px; }
        .rc__brand { font-size: 1.4rem; font-weight: 800; }
        .rc__sub { color: #6b7280; font-size: .82rem; margin-top: 4px; }
        .rc__row { display: flex; justify-content: space-between; gap: 10px; padding: 5px 0; font-size: .9rem; }
        .rc__row .k { color: #6b7280; }
        .rc__row .v { font-weight: 600; text-align: end; }
        .rc__sec { border-top: 1px solid #f0f0f0; margin-top: 14px; padding-top: 12px; }
        .rc__total { display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 800; border-top: 2px solid #111; margin-top: 12px; padding-top: 12px; }
        .rc__foot { text-align: center; color: #9ca3af; font-size: .78rem; margin-top: 18px; }
        .rc__print { display: block; width: 100%; margin: 18px auto 0; padding: 11px; border: none; border-radius: 10px; background: #312873; color: #fff; font-weight: 700; font-size: .95rem; cursor: pointer; }
        @media print { body { background: #fff; padding: 0; } .rc { box-shadow: none; } .rc__print { display: none; } }
    </style>
</head>
<body>
    <div class="rc">
        <div class="rc__head">
            <div class="rc__brand">{{ $brand }}</div>
            <div class="rc__sub">{{ $t('Trip receipt', 'إيصال رحلة') }} · #{{ $booking->id }}</div>
            <div class="rc__sub">{{ $when ? \Illuminate\Support\Carbon::parse($when)->format('Y-m-d H:i') : '' }}</div>
        </div>

        <div class="rc__row"><span class="k">{{ $t('Rider', 'الراكب') }}</span><span class="v">{{ $customerName ?: '—' }}</span></div>
        @if($driverName)<div class="rc__row"><span class="k">{{ $t('Driver', 'السائق') }}</span><span class="v">{{ $driverName }}</span></div>@endif
        @if($officeName)<div class="rc__row"><span class="k">{{ $t('Office', 'المكتب') }}</span><span class="v">{{ $officeName }}</span></div>@endif
        <div class="rc__row"><span class="k">{{ $t('Service', 'الخدمة') }}</span><span class="v">{{ $booking->service }}{{ $booking->service_class ? ' · ' . $booking->service_class : '' }}</span></div>
        <div class="rc__row"><span class="k">{{ $t('Payment', 'الدفع') }}</span><span class="v">{{ $booking->payment_method }}</span></div>

        <div class="rc__sec">
            <div class="rc__row"><span class="k">{{ $t('From', 'من') }}</span><span class="v">{{ $booking->pickup_title ?: '—' }}</span></div>
            <div class="rc__row"><span class="k">{{ $t('To', 'إلى') }}</span><span class="v">{{ $booking->dropoff_title ?: '—' }}</span></div>
            @if((int) $booking->distance_m > 0)<div class="rc__row"><span class="k">{{ $t('Distance', 'المسافة') }}</span><span class="v">{{ number_format($booking->distance_m / 1000, 1) }} km</span></div>@endif
        </div>

        <div class="rc__sec">
            <div class="rc__row"><span class="k">{{ $t('Fare', 'الأجرة') }}</span><span class="v">{{ $money($booking->fare_minor) }}</span></div>
            @if((int) $booking->waiting_minor > 0)<div class="rc__row"><span class="k">{{ $t('Waiting', 'انتظار') }}</span><span class="v">{{ $money($booking->waiting_minor) }}</span></div>@endif
            @if((int) $booking->tip_minor > 0)<div class="rc__row"><span class="k">{{ $t('Tip', 'إكرامية') }}</span><span class="v">{{ $money($booking->tip_minor) }}</span></div>@endif
            @if((int) $booking->discount_minor > 0)<div class="rc__row"><span class="k">{{ $t('Discount', 'الخصم') }}</span><span class="v">- {{ $money($booking->discount_minor) }}</span></div>@endif
            <div class="rc__total"><span>{{ $t('Total', 'الإجمالي') }}</span><span>{{ $money($booking->total_minor) }}</span></div>
        </div>

        <div class="rc__foot">{{ $t('Thank you for riding with us', 'شكراً لاختياركم خدمتنا') }}</div>
        <button class="rc__print" onclick="window.print()">{{ $t('Print', 'طباعة') }}</button>
    </div>
</body>
</html>
