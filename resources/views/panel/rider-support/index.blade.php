@extends('panel.layouts.master')

@section('title', textByLanguage('دعم الراكب', 'Rider support'))
@section('page-title', textByLanguage('دعم الراكب', 'Rider support'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $statuses = ['open', 'pending', 'resolved', 'closed'];
    $categories = ['refund', 'payment', 'safety', 'policy', 'sos'];
    $unfiltered = ! $statusFilter && ! $categoryFilter;
    $rows = collect($tickets);
    $openCount = $rows->where('status', 'open')->count();
    $pendingCount = $rows->where('status', 'pending')->count();
    $sosCount = $rows->where('category', 'sos')->count();
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="$isAdmin ? textByLanguage('دعم الأسطول', 'Fleet support') : textByLanguage('دعم الراكب', 'Rider support')"
        :subtitle="$isAdmin ? textByLanguage('شكاوى ومطالبات وطوارئ الركّاب', 'Rider complaints, claims and emergencies') : textByLanguage('تذاكر ركّاب مكتبك', 'Your office rider tickets')" />

    <a id="rsLiveNotice" href="{{ url()->current() }}" style="display:none; align-items:center; gap:10px; padding:11px 15px; border-radius:12px; margin-bottom:14px; background:color-mix(in srgb, var(--p-primary) 10%, transparent); color:var(--p-primary); text-decoration:none; font-weight:600;">
        <i class="bi bi-arrow-repeat"></i>
        <span data-rs-notice>{{ textByLanguage('نشاط جديد على التذاكر — حدّث للعرض', 'New ticket activity — refresh to view') }}</span>
        <span data-rs-count style="background:var(--p-primary); color:#fff; border-radius:999px; padding:1px 9px; font-size:.8rem;">1</span>
    </a>

    @if($unfiltered && count($tickets))
        <div class="p-faq-stats">
            <x-panel.stat :label="textByLanguage('مفتوحة', 'Open')" :value="$openCount" icon="bi-envelope-open" />
            <x-panel.stat :label="textByLanguage('قيد المتابعة', 'Pending')" :value="$pendingCount" icon="bi-hourglass-split" />
            <x-panel.stat :label="textByLanguage('طوارئ SOS', 'SOS')" :value="$sosCount" icon="bi-exclamation-octagon" :variant="$sosCount ? 'danger' : null" />
        </div>
    @endif

    <div class="p-card">
        <form method="GET" action="{{ route($r('rider-support.index')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الحالات', 'All statuses') }}</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected($statusFilter === $s)>{{ __('messages.' . $s) !== 'messages.' . $s ? __('messages.' . $s) : ucfirst($s) }}</option>
                @endforeach
            </select>
            @if($isAdmin)
                <select name="category" onchange="this.form.submit()" class="p-search__select">
                    <option value="">{{ textByLanguage('كل الفئات', 'All categories') }}</option>
                    @foreach($categories as $c)
                        <option value="{{ $c }}" @selected($categoryFilter === $c)>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
            @endif
            @if($statusFilter || $categoryFilter)
                <a href="{{ route($r('rider-support.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>
            @endif
        </form>

        @if(count($tickets))
            <x-panel.table :headers="array_filter([
                shardIsAll() ? textByLanguage('الدولة', 'Country') : null,
                '#',
                textByLanguage('الموضوع', 'Subject'),
                textByLanguage('الفئة', 'Category'),
                textByLanguage('الحالة', 'Status'),
                textByLanguage('آخر رسالة', 'Last message'),
                '',
            ], fn($h) => $h !== null)">
                @foreach($tickets as $t)
                    <tr @if($t['category'] === 'sos') style="background:rgba(220,38,38,.06);" @endif>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($t) ?: '—' }}</x-panel.badge></td>@endif
                        <td>#{{ $t['ticket_id'] }}</td>
                        <td>
                            <div class="p-cell-main">
                                <div>
                                    <strong>{{ $t['subject'] }}</strong>
                                    <span class="p-cell-sub">{{ textByLanguage('راكب', 'Rider') }} @if($t['booking_id'])· {{ textByLanguage('رحلة', 'Trip') }} #{{ $t['booking_id'] }}@endif</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($t['category'] === 'sos')
                                <x-panel.badge tone="danger"><i class="bi bi-exclamation-octagon"></i> SOS</x-panel.badge>
                            @else
                                {{ ucfirst($t['category']) }}
                            @endif
                        </td>
                        <td><x-panel.badge :status="$t['status']">{{ ucfirst($t['status']) }}</x-panel.badge></td>
                        <td>{{ $t['last_reply_at'] ? \Illuminate\Support\Carbon::parse($t['last_reply_at'])->diffForHumans() : '—' }}</td>
                        <td>
                            <a href="{{ shardLink($r('rider-support.show'), $t['ticket_id'], $t) }}" class="p-icon-btn" title="{{ textByLanguage('فتح', 'Open') }}">
                                <i class="bi bi-chat-left-text"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty">
                <i class="bi bi-inbox"></i>
                {{ textByLanguage('لا توجد تذاكر', 'No tickets') }}
            </p>
        @endif
    </div>

@endsection

@push('scripts')
<script>
(function () {
    // Live ticket activity over the new realtime gateway. The list is otherwise
    // pull-only, so new fleet tickets/replies used to appear only on refresh.
    var notice = document.getElementById('rsLiveNotice');
    if (!notice) return;
    var count = 0;

    window.addEventListener('fleet:rt:support.message_created', function (e) {
        var d = (e.detail && e.detail.data) || {};
        // The driver-safety board already surfaces SOS pins; here we care about
        // ticket traffic (has a ticket_id).
        if (d.ticket_id == null) return;
        count++;
        var c = notice.querySelector('[data-rs-count]');
        if (c) c.textContent = count;
        notice.style.display = 'inline-flex';
    });
})();
</script>
@endpush

