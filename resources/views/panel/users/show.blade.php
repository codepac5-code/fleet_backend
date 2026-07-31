@extends('panel.layouts.master')

@php
    $isAr = app()->getLocale() === 'ar';
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $name = trim(($rider->firstName ?? '') . ' ' . ($rider->lastName ?? '')) ?: '—';
    $phone = trim(($rider->dialCode ? '+' . ltrim($rider->dialCode, '+') . ' ' : '') . $rider->phoneNumber);
    $money = fn ($minor) => number_format(((int) $minor) / 100, 2) . ' ' . ($walletCurrency ?? '');
    $tone = [
        'completed' => 'success', 'cancelled' => 'danger', 'rejected' => 'danger',
        'declined' => 'danger', 'no_driver_expired' => 'danger', 'scheduled' => 'primary',
    ];
@endphp

@section('title', $name)
@section('page-title', $name)

@section('content')

    <x-panel.page-toolbar :title="textByLanguage('بطاقة الراكب', 'Rider card')" :subtitle="$name">
        <x-slot:actions>
            @if(\Illuminate\Support\Facades\Route::has($r('user.edit')))
                <a href="{{ route($r('user.edit'), $rider->id) }}" class="p-btn p-btn--ghost"><i class="bi bi-pencil"></i> {{ textByLanguage('تعديل', 'Edit') }}</a>
            @endif
            <a href="{{ route($r('user.index')) }}" class="p-btn p-btn--ghost"><i class="bi bi-arrow-{{ $isAr ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="svc-hero">
        <div class="svc-hero__media">
            @if($rider->photo)<img src="{{ asset('storage/' . $rider->photo) }}" alt="">@else<span style="font-size:2rem;font-weight:800;">{{ mb_substr($name, 0, 1) }}</span>@endif
        </div>
        <div class="svc-hero__body">
            <div class="svc-hero__tags">
                <x-panel.badge :tone="$rider->isActive ? 'success' : 'danger'">{{ $rider->isActive ? textByLanguage('مفعّل', 'Active') : textByLanguage('محظور', 'Blocked') }}</x-panel.badge>
                @if($rider->is_registered)<span class="p-badge p-badge--primary"><i class="bi bi-person-check"></i> {{ textByLanguage('مسجّل', 'Registered') }}</span>@endif
                <span class="svc-hero__id">#{{ $rider->id }}</span>
            </div>
            <h1>{{ $name }}</h1>
            <p>
                <span dir="ltr">{{ $phone }}</span>
                @if($rider->locale) · <i class="bi bi-translate"></i> {{ strtoupper($rider->locale) }}@endif
                @if($rider->created_at) · <i class="bi bi-calendar3"></i> {{ textByLanguage('انضم', 'Joined') }} {{ $rider->created_at->diffForHumans() }}@endif
            </p>
            @if(! $rider->isActive && $rider->block_reason)
                <p style="color:var(--p-danger);font-weight:600;"><i class="bi bi-slash-circle"></i> {{ $rider->block_reason }}</p>
            @endif
        </div>
        <div class="svc-hero__totals">
            <div><span class="svc-hero__num" data-count="{{ $overview['completed'] }}">0</span><span class="svc-hero__lbl">{{ textByLanguage('رحلة مكتملة', 'Completed rides') }}</span></div>
            <div><span class="svc-hero__num svc-hero__num--money">{{ $money($overview['spentMinor']) }}</span><span class="svc-hero__lbl">{{ textByLanguage('إجمالي الإنفاق', 'Total spend') }}</span></div>
        </div>
    </div>

    <p class="p-empty" style="text-align:start;padding:10px 0;margin:0 0 14px;">
        <i class="bi bi-info-circle"></i>
        {{ textByLanguage('الأرقام أدناه تخص الدولة النشطة فقط — الحساب نفسه عالمي.', 'The figures below cover the active country only — the account itself is global.') }}
    </p>

    <div class="p-grid p-grid--4" style="margin-bottom:18px;">
        <x-panel.stat :label="textByLanguage('كل الطلبات', 'All rides')" :value="$overview['total']" icon="bi-list-ul" />
        <x-panel.stat :label="textByLanguage('ملغاة', 'Cancelled')" :value="$overview['cancelled']" icon="bi-x-circle" :variant="$overview['cancelled'] ? 'danger' : null" />
        <x-panel.stat :label="textByLanguage('مجدولة', 'Scheduled')" :value="$overview['scheduled']" icon="bi-calendar-event" />
        <x-panel.stat :label="textByLanguage('إنفاق هذا الشهر', 'Spend this month')" :value="$money($overview['spentThisMonthMinor'])" icon="bi-graph-up" />
    </div>

    <div class="p-grid p-grid--4" style="margin-bottom:18px;">
        <x-panel.stat :label="textByLanguage('رصيد المحفظة', 'Wallet balance')"
                      :value="$walletMinor === null ? '—' : $money($walletMinor)" icon="bi-wallet2" />
        <x-panel.stat :label="textByLanguage('تقييمه للسائقين', 'Ratings given')"
                      :value="$ratings['givenAverage'] !== null ? $ratings['givenAverage'] . ' ★ (' . $ratings['givenCount'] . ')' : '—'" icon="bi-star" />
        <x-panel.stat :label="textByLanguage('تقييم السائقين له', 'Ratings received')"
                      :value="$ratings['receivedAverage'] !== null ? $ratings['receivedAverage'] . ' ★ (' . $ratings['receivedCount'] . ')' : '—'" icon="bi-star-half" />
        <x-panel.stat :label="textByLanguage('شكاوى مفتوحة', 'Open complaints')" :value="$support['openComplaints']"
                      icon="bi-flag" :variant="$support['openComplaints'] ? 'warning' : null" />
    </div>

    <div class="p-card" style="margin-bottom:18px;">
        <h3 class="p-card__title">{{ textByLanguage('آخر الرحلات', 'Recent rides') }}</h3>
        @if($rides->count())
            <x-panel.table :headers="['#', textByLanguage('المكتب', 'Office'), textByLanguage('المسار', 'Route'), textByLanguage('المبلغ', 'Amount'), textByLanguage('الحالة', 'Status'), textByLanguage('التاريخ', 'When')]">
                @foreach($rides as $ride)
                    <tr>
                        <td class="p-row-id">
                            @if(\Illuminate\Support\Facades\Route::has($r('rides.show')))
                                <a href="{{ route($r('rides.show'), $ride->id) }}">#{{ $ride->id }}</a>
                            @else
                                #{{ $ride->id }}
                            @endif
                        </td>
                        <td>{{ $ride->office ?? '—' }}</td>
                        <td style="max-width:280px;">{{ \Illuminate\Support\Str::limit($ride->from ?? '—', 28) }} → {{ \Illuminate\Support\Str::limit($ride->to ?? '—', 28) }}</td>
                        <td>{{ number_format($ride->totalMinor / 100, 2) }} {{ $ride->currency }}</td>
                        <td><x-panel.badge :tone="$tone[$ride->status] ?? 'gray'">{{ str_replace('_', ' ', $ride->status) }}</x-panel.badge></td>
                        <td>{{ $ride->createdAt ? $ride->createdAt->diffForHumans() : '—' }}</td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-signpost-2"></i> {{ textByLanguage('لا توجد رحلات في هذه الدولة', 'No rides in this country') }}</p>
        @endif
    </div>

    <div class="p-grid p-grid--2">
        <div class="p-card">
            <h3 class="p-card__title">{{ textByLanguage('الشكاوى', 'Complaints') }}</h3>
            @if($support['complaints']->count())
                <x-panel.table :headers="[textByLanguage('بخصوص', 'About'), textByLanguage('الوصف', 'Description'), textByLanguage('الحالة', 'Status')]">
                    @foreach($support['complaints'] as $complaint)
                        <tr>
                            <td>{{ ucfirst($complaint->about) }}</td>
                            <td style="max-width:240px;">{{ \Illuminate\Support\Str::limit($complaint->description, 60) }}</td>
                            <td><x-panel.badge :tone="in_array($complaint->status, ['resolved', 'dismissed']) ? 'success' : 'warning'">{{ str_replace('_', ' ', $complaint->status) }}</x-panel.badge></td>
                        </tr>
                    @endforeach
                </x-panel.table>
            @else
                <p class="p-empty"><i class="bi bi-flag"></i> {{ textByLanguage('لا توجد شكاوى', 'No complaints') }}</p>
            @endif
        </div>

        <div class="p-card">
            <h3 class="p-card__title">{{ textByLanguage('المفقودات', 'Lost & found') }}</h3>
            @if($support['lostItems']->count())
                <x-panel.table :headers="[textByLanguage('الصنف', 'Item'), textByLanguage('الرحلة', 'Ride'), textByLanguage('الحالة', 'Status')]">
                    @foreach($support['lostItems'] as $item)
                        <tr>
                            <td>{{ $item->category }}</td>
                            <td>{{ $item->booking_id ? '#' . $item->booking_id : '—' }}</td>
                            <td><x-panel.badge tone="gray">{{ str_replace('_', ' ', $item->status) }}</x-panel.badge></td>
                        </tr>
                    @endforeach
                </x-panel.table>
            @else
                <p class="p-empty"><i class="bi bi-bag"></i> {{ textByLanguage('لا توجد بلاغات', 'No reports') }}</p>
            @endif
        </div>
    </div>

@endsection
