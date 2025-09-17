<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ (app()->getLocale() == 'ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ __('messages.Invoice') }} #{{ $booking->id }}</title>
    <style>

        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap');

body {
    font-family: 'Cairo', sans-serif;
    direction: {{ (app()->getLocale() == 'ar') ? 'rtl' : 'ltr' }};
    background-color: #f9f9f9;
    margin: 10px;
    color: #312873;
    font-size: 14px;
    line-height: 1.4;
}
.invoice-box {
    max-width: 700px;
    margin: auto;
    background: #fff;
    padding: 20px 30px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(49, 40, 115, 0.15);
    border: 2px solid #F8A609;
    page-break-inside: avoid;
}
h1, h2, h3 {
    color: #312873;
    margin-bottom: 8px;
    font-weight: 700;
}
h1 {
    font-size: 26px;
    border-bottom: 3px solid #F8A609;
    padding-bottom: 8px;
    margin-bottom: 15px;
}
.section {
    margin-bottom: 20px;
    border-bottom: 1.5px solid #F8A609;
    padding-bottom: 15px;
}
.section:last-child {
    border-bottom: none;
}
.label {
    font-weight: 700;
    color: #312873;
    width: 140px;
    display: inline-block;
    font-size: 14px;
}
.value {
    color: #555;
    font-size: 14px;
}
p {
    margin: 6px 0;
}
.footer {
    text-align: center;
    font-size: 13px;
    color: #888;
    margin-top: 30px;
    font-style: italic;
}
.value.amount, .value.total {
    color: #F8A609;
    font-weight: 700;
    font-size: 16px;
}
@media print {
    body {
        margin: 0;
        background: #fff;
        font-size: 12px;
        line-height: 1.3;
    }
    .invoice-box {
        box-shadow: none;
        border: none;
        max-width: 100%;
        padding: 10px 15px;
        page-break-after: avoid;
        page-break-before: avoid;
        page-break-inside: avoid;
    }
    h1 {
        font-size: 22px;
        margin-bottom: 12px;
        border-bottom-width: 2px;
    }
    .section {
        margin-bottom: 15px;
        padding-bottom: 10px;
    }
    .label {
        width: 120px;
        font-size: 12px;
    }
    .value {
        font-size: 12px;
    }
    .footer {
        font-size: 11px;
        margin-top: 20px;
    }
}

        .value.amount, .value.total {
            color: #F8A609;
            font-weight: 700;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header-logo" style="text-align: center; margin-bottom: 25px;">
            <span class="site-logo" style="font-size: 43.5px; font-weight: 700; color: #FFC107; font-family: 'Poppins', sans-serif; line-height: 1;">
                fleet.<span style="font-size: 60px; vertical-align: top; color: #FFC107;"></span>
            </span>
        </div>

        <h1>{{ __('messages.Invoice') }} #{{ $booking->id }}</h1>

        <div class="section">
            <h2>{{ __('messages.BookingInformation') }}</h2>
            <p><span class="label">{{ __('messages.StartDate') }}:</span> <span class="value">{{ $booking->startAt ?? __('messages.NotSpecified') }}</span></p>
            <p><span class="label">{{ __('messages.EndDate') }}:</span> <span class="value">{{ $booking->endAt ?? __('messages.NotSpecified') }}</span></p>
            <p><span class="label">{{ __('messages.Amount') }}:</span> <span class="value amount">{{ getPriceFormat($booking->amount) }}</span></p>
            <p><span class="label">{{ __('messages.Discount') }}:</span> <span class="value">
                @if($booking->discount)
                    {{ $booking->discount }} {{ $booking->isPercentage ? '%' : getPriceFormat(0) }}
                @else
                    {{ __('messages.None') }}
                @endif
            </span></p>
            <p><span class="label">{{ __('messages.TotalAmount') }}:</span> <span class="value total">{{ getPriceFormat($booking->totalAmount) }}</span></p>
            <p><span class="label">{{ __('messages.PaymentType') }}:</span> <span class="value">{{ __( 'messages.' . ucfirst($booking->paymentType) ) }}</span></p>
            <p><span class="label">{{ __('messages.PaymentStatus') }}:</span> <span class="value">{{ __( 'messages.' . ucfirst($booking->paymentStatus) ) }}</span></p>
        </div>

        <div class="section">
            <h2>{{ __('messages.DriverInformation') }}</h2>
            @if($booking->driver)
                <p><span class="label">{{ __('messages.DriverName') }}:</span> <span class="value">{{ $booking->driver->firstName }} {{ $booking->driver->lastName }}</span></p>
                <p><span class="label">{{ __('messages.PhoneNumber') }}:</span> <span class="value">{{ $booking->driver->phoneNumber }}</span></p>
                <p><span class="label">{{ __('messages.Office') }}:</span> <span class="value">{{ $booking->driver->office ? $booking->driver->office->officeName : __('messages.NotSpecified') }}</span></p>
            @else
                <p>{{ __('messages.NoDriverData') }}</p>
            @endif
        </div>

        <div class="section">
            <h2>{{ __('messages.OfficeInformation') }}</h2>
            @if($booking->office)
                <p><span class="label">{{ __('messages.OfficeName') }}:</span> <span class="value">{{ $booking->office->officeName }}</span></p>
                <p><span class="label">{{ __('messages.Address') }}:</span> <span class="value">{{ $booking->office->address ?? __('messages.NotSpecified') }}</span></p>
            @else
                <p>{{ __('messages.NoOfficeData') }}</p>
            @endif
        </div>

        <div class="section">
            <h2>{{ __('messages.ServiceInformation') }}</h2>
            @if($booking->subService)
                <p><span class="label">{{ __('messages.SubService') }}:</span> <span class="value">{{ $booking->subService->title ?? __('messages.NotSpecified') }}</span></p>
                <p><span class="label">{{ __('messages.MainService') }}:</span> <span class="value">{{ $booking->subService->service->title ?? __('messages.NotSpecified') }}</span></p>
            @else
                <p>{{ __('messages.NoServiceData') }}</p>
            @endif
        </div>

        <div class="section">
            <h2>{{ __('messages.UserInformation') }}</h2>
            @if($booking->user)
                <p><span class="label">{{ __('messages.UserName') }}:</span> <span class="value">{{ $booking->user->name ?? __('messages.NotSpecified') }}</span></p>
                <p><span class="label">{{ __('messages.UserPhone') }}:</span> <span class="value">{{ $booking->user->phone ?? __('messages.NotSpecified') }}</span></p>
            @else
                <p>{{ __('messages.NoUserData') }}</p>
            @endif
        </div>

        @if($booking->coupon)
        <div class="section">
            <h2>{{ __('messages.CouponUsed') }}</h2>
            <p><span class="label">{{ __('messages.Code') }}:</span> <span class="value">{{ $booking->coupon->code }}</span></p>
            <p><span class="label">{{ __('messages.DiscountValue') }}:</span> <span class="value">{{ getPriceFormat($booking->coupon->value) }}</span></p>
        </div>
        @endif

        <div class="footer">
            <p>{{ __('messages.ThankYou') }}</p>
        </div>
    </div>
</body>
</html>
