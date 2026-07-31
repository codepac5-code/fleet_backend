@extends('panel.layouts.master')

@use('App\Http\Services\Panel\Bookings\Logic\BookingStatus')

@section('title', textByLanguage('أرشيف الطلبات القديم', 'Legacy order archive'))
@section('page-title', textByLanguage('أرشيف الطلبات القديم', 'Legacy order archive'))

@php $r = fn ($name) => "panel.{$entity}.{$name}"; @endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar :title="textByLanguage('أرشيف الطلبات القديم', 'Legacy order archive')"
        :subtitle="textByLanguage('طلبات لوحة التحكّم القديمة — للاطّلاع والمعالجة التاريخية', 'Orders from the old dashboard — kept for history and clean-up')">
        <x-slot:actions>
            @if(\Illuminate\Support\Facades\Route::has($r('rides.index')))
                <a href="{{ route($r('rides.index')) }}" class="p-btn p-btn--primary">
                    <i class="bi bi-card-checklist"></i> {{ textByLanguage('الطلبات الحالية', 'Current orders') }}
                </a>
            @endif
        </x-slot:actions>
    </x-panel.page-toolbar>

    @if(($appRideCount ?? 0) > 0)
        <div class="p-flash p-flash--warn">
            <i class="bi bi-info-circle"></i>
            <div>
                <strong>{{ textByLanguage('هذه ليست طلبات التطبيق', 'These are not the app’s orders') }}</strong>
                <div style="font-size:.83rem;margin-top:3px;">
                    {{ textByLanguage(
                        'الطلبات القادمة من تطبيقَي الراكب والسائق تُحفَظ في جدول آخر ولا تظهر هنا إطلاقاً — عددها الآن ' . number_format($appRideCount) . '. افتح «الطلبات» من القائمة لمتابعتها.',
                        'Orders created by the rider and driver apps are stored in a different table and never appear here — there are ' . number_format($appRideCount) . ' of them right now. Open “Orders” in the menu to work with those.'
                    ) }}
                </div>
            </div>
        </div>
    @endif

    <div class="p-card">
        <form method="GET" action="{{ route($r('booking.index')) }}" class="p-search">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="{{ $search }}"
                placeholder="{{ textByLanguage('ابحث برقم الطلب أو العميل أو السائق', 'Search by order #, customer or driver') }}">
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الحالات', 'All statuses') }}</option>
                @foreach($statusOptions as $val => $label)
                    <option value="{{ $val }}" @selected($statusFilter === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @if($isAdmin && !empty($officeOptions))
                <select name="office" onchange="this.form.submit()" class="p-search__select">
                    <option value="">{{ textByLanguage('كل المكاتب', 'All offices') }}</option>
                    @foreach($officeOptions as $id => $name)
                        <option value="{{ $id }}" @selected($officeFilter == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            @endif
            @if($search || $statusFilter || $officeFilter)
                <a href="{{ route($r('booking.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>
            @endif
            <button type="submit" class="p-btn p-btn--ghost">{{ textByLanguage('بحث', 'Search') }}</button>
        </form>

        @if($bookings->count())
            <x-panel.table :headers="array_filter([
                '#',
                shardIsAll() ? textByLanguage('الدولة', 'Country') : null,
                textByLanguage('العميل', 'Customer'),
                textByLanguage('السائق', 'Driver'),
                $isAdmin ? textByLanguage('المكتب', 'Office') : null,
                textByLanguage('المبلغ', 'Amount'),
                textByLanguage('الدفع', 'Payment'),
                textByLanguage('الحالة', 'Status'),
                textByLanguage('التاريخ', 'Date'),
                '',
            ], fn($h) => $h !== null)">
                @foreach($bookings as $b)
                    <tr>
                        <td><strong>#{{ $b->id }}</strong> @if($b->is_scheduled)<i class="bi bi-clock-history" title="{{ textByLanguage('مجدول', 'Scheduled') }}" style="color:var(--p-accent);"></i>@endif</td>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($b) ?: '—' }}</x-panel.badge></td>@endif
                        <td>{{ trim($b->customer) ?: '—' }}</td>
                        <td>{{ trim($b->driver) ?: '—' }}</td>
                        @if($isAdmin)
                            <td>{{ $b->office_name ?: '—' }}</td>
                        @endif
                        <td>{{ getPriceFormat($b->totalAmount ?? 0) }}</td>
                        <td><x-panel.badge :status="$b->paymentStatus ?: 'pending'">{{ $b->paymentStatus ?: '—' }}</x-panel.badge></td>
                        <td><x-panel.badge :status="$b->status">{{ BookingStatus::label($b->status) }}</x-panel.badge></td>
                        <td style="color:var(--p-text-muted);white-space:nowrap;">{{ $b->created_at ? $b->created_at->format('Y-m-d H:i') : '—' }}</td>
                        <td>
                            <div class="p-row-actions">
                                <a href="{{ shardLink($r('booking.show'), $b->id, $b) }}" class="p-icon-btn" title="{{ textByLanguage('التفاصيل', 'Details') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>

            @if($bookings->hasPages())
                <div class="p-pagination">
                    <a class="p-page {{ $bookings->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $bookings->previousPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></a>
                    <span class="p-page-info">{{ $bookings->currentPage() }} / {{ $bookings->lastPage() }}</span>
                    <a class="p-page {{ ! $bookings->hasMorePages() ? 'is-disabled' : '' }}" href="{{ $bookings->nextPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></a>
                </div>
            @endif
        @else
            <p class="p-empty">
                <i class="bi bi-card-checklist"></i>
                {{ ($search || $statusFilter || $officeFilter) ? textByLanguage('لا توجد نتائج مطابقة', 'No matching results') : textByLanguage('لا توجد طلبات بعد', 'No orders yet') }}
            </p>
        @endif
    </div>

@endsection
