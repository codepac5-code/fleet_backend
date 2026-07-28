@extends('panel.layouts.master')

@use('App\Http\Services\Panel\Bookings\Logic\BookingStatus')

@section('title', textByLanguage('تفاصيل الطلب', 'Order details'))
@section('page-title', textByLanguage('تفاصيل الطلب', 'Order details'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $customer = $related['customer'] ?? null;
    $driver = $related['driver'] ?? null;
    $office = $related['office'] ?? null;
    $subService = $related['subService'] ?? null;
    $money = fn ($v) => getPriceFormat($v ?? 0);
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar :title="textByLanguage('طلب', 'Order') . ' #' . $booking->id"
        :subtitle="$booking->created_at ? $booking->created_at->format('Y-m-d H:i') : ''">
        <x-slot:actions>
            <x-panel.badge :status="$booking->status">{{ BookingStatus::label($booking->status) }}</x-panel.badge>
            <a href="{{ route($r('booking.index')) }}" class="p-btn p-btn--ghost">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    @can('edit order status')
        <div class="p-card" style="margin-bottom:18px;">
            <form method="POST" action="{{ route($r('booking.status.update'), $booking->id) }}" class="booking-status-form">
                @csrf
                @method('PUT')
                <div>
                    <label class="p-status-label">{{ textByLanguage('تعديل حالة الطلب', 'Update order status') }}</label>
                    <select name="status" class="p-search__select" style="min-width:200px;">
                        @foreach($statusOptions as $val => $label)
                            <option value="{{ $val }}" @selected($booking->status === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ textByLanguage('حفظ الحالة', 'Save status') }}</button>
            </form>
            @error('status')<div class="p-field__error" style="margin-top:8px;">{{ $message }}</div>@enderror
        </div>
    @endcan

    <div class="p-grid p-grid--2" style="align-items:stretch;">
        <div class="p-card">
            <h3 class="p-card__title">{{ textByLanguage('الرحلة', 'Trip') }}</h3>
            <dl class="p-dl">
                <div><dt><i class="bi bi-geo-alt" style="color:var(--p-success);"></i> {{ textByLanguage('من', 'From') }}</dt><dd>{{ $booking->startAddress ?: '—' }}</dd></div>
                <div><dt><i class="bi bi-geo-alt-fill" style="color:var(--p-accent);"></i> {{ textByLanguage('إلى', 'To') }}</dt><dd>{{ $booking->endAddress ?: '—' }}</dd></div>
                <div><dt>{{ textByLanguage('المسافة', 'Distance') }}</dt><dd>{{ $booking->distance ? number_format((float) $booking->distance, 2).' '.textByLanguage('كم','km') : '—' }}</dd></div>
                <div><dt>{{ textByLanguage('الخدمة', 'Service') }}</dt><dd>{{ $subService?->name ?? $subService?->title ?? ($booking->subServiceId ? '#'.$booking->subServiceId : '—') }}</dd></div>
                <div><dt>{{ textByLanguage('مجدول', 'Scheduled') }}</dt><dd>{{ $booking->is_scheduled ? ($booking->scheduled_time ? $booking->scheduled_time->format('Y-m-d H:i') : textByLanguage('نعم','Yes')) : textByLanguage('لا','No') }}</dd></div>
            </dl>
        </div>

        <div class="p-card">
            <h3 class="p-card__title">{{ textByLanguage('الأطراف', 'Parties') }}</h3>
            <dl class="p-dl">
                <div><dt>{{ textByLanguage('العميل', 'Customer') }}</dt><dd>{{ $customer ? trim($customer->firstName.' '.$customer->lastName) : '—' }}<br><span class="p-cell-sub" dir="ltr">{{ $customer?->phoneNumber }}</span></dd></div>
                <div><dt>{{ textByLanguage('السائق', 'Driver') }}</dt><dd>{{ $driver ? trim($driver->firstName.' '.$driver->lastName) : '—' }}<br><span class="p-cell-sub" dir="ltr">{{ $driver?->phoneNumber }}</span></dd></div>
                @if($isAdmin)
                    <div><dt>{{ textByLanguage('المكتب', 'Office') }}</dt><dd>{{ $office?->officeName ?? '—' }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="p-card">
            <h3 class="p-card__title">{{ textByLanguage('المالية', 'Financials') }}</h3>
            <dl class="p-dl">
                <div><dt>{{ textByLanguage('المبلغ', 'Amount') }}</dt><dd>{{ $money($booking->amount) }}</dd></div>
                <div><dt>{{ textByLanguage('الخصم', 'Discount') }}</dt><dd>{{ $money($booking->discount) }}</dd></div>
                <div><dt><strong>{{ textByLanguage('الإجمالي', 'Total') }}</strong></dt><dd><strong>{{ $money($booking->totalAmount) }}</strong></dd></div>
                <div><dt>{{ textByLanguage('عمولة المكتب', 'Office commission') }}</dt><dd>{{ $money($booking->officeCommissionValue) }}</dd></div>
                <div><dt>{{ textByLanguage('عمولة السائق', 'Driver commission') }}</dt><dd>{{ $money($booking->driverCommissionValue) }}</dd></div>
                <div><dt>{{ textByLanguage('عمولة المنصّة', 'Fleet commission') }}</dt><dd>{{ $money($booking->fleetCommissionValue) }}</dd></div>
            </dl>
        </div>

        <div class="p-card">
            <h3 class="p-card__title">{{ textByLanguage('الدفع', 'Payment') }}</h3>
            <dl class="p-dl">
                <div><dt>{{ textByLanguage('طريقة الدفع', 'Payment method') }}</dt><dd>{{ $booking->paymentType ?: '—' }}</dd></div>
                <div><dt>{{ textByLanguage('حالة الدفع', 'Payment status') }}</dt><dd><x-panel.badge :status="$booking->paymentStatus ?: 'pending'">{{ $booking->paymentStatus ?: '—' }}</x-panel.badge></dd></div>
                @if($booking->reason)
                    <div><dt>{{ textByLanguage('السبب', 'Reason') }}</dt><dd>{{ $booking->reason }}</dd></div>
                @endif
            </dl>
        </div>
    </div>

@endsection
