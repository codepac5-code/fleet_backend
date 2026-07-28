@extends('panel.layouts.master')

@section('title', textByLanguage('سلامة السائقين', 'Driver safety'))
@section('page-title', textByLanguage('سلامة السائقين', 'Driver safety'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $kinds = ['sos', 'sos_opened', 'report'];
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('أحداث سلامة السائقين', 'Driver safety events')"
        :subtitle="textByLanguage('طوارئ SOS وبلاغات السلامة من السائقين', 'SOS emergencies and safety reports from drivers')" />

    <div id="sosLiveAlerts" style="display:none; flex-direction:column; gap:10px; margin-bottom:16px;"></div>

    <div class="p-faq-stats" style="grid-template-columns:repeat(4,1fr);">
        <x-panel.stat :label="textByLanguage('طوارئ SOS', 'SOS')" :value="$counts['sos']" icon="bi-exclamation-octagon" :variant="$counts['sos'] ? 'danger' : null" />
        <x-panel.stat :label="textByLanguage('بلاغات', 'Reports')" :value="$counts['reports']" icon="bi-flag" />
        <x-panel.stat :label="textByLanguage('اليوم', 'Today')" :value="$counts['today']" icon="bi-calendar-day" />
        <x-panel.stat :label="textByLanguage('الإجمالي', 'Total')" :value="$counts['total']" icon="bi-shield-check" />
    </div>

    <div class="p-card">
        <form method="GET" action="{{ route($r('driver-safety.index')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="kind" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الأنواع', 'All kinds') }}</option>
                @foreach($kinds as $k)
                    <option value="{{ $k }}" @selected($kindFilter === $k)>{{ ucfirst(str_replace('_', ' ', $k)) }}</option>
                @endforeach
            </select>
            @if($kindFilter)<a href="{{ route($r('driver-safety.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
        </form>

        @if($events->count())
            <x-panel.table :headers="array_filter([
                shardIsAll() ? textByLanguage('الدولة', 'Country') : null,
                '#', textByLanguage('السائق', 'Driver'), textByLanguage('النوع', 'Kind'),
                textByLanguage('الرحلة', 'Trip'), $isAdmin ? textByLanguage('المكتب', 'Office') : null,
                textByLanguage('الموقع', 'Location'), textByLanguage('الوقت', 'When'),
            ], fn($h) => $h !== null)">
                @foreach($events as $e)
                    <tr @if($e->kind === 'sos') style="background:rgba(220,38,38,.06);" @endif>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($e) ?: '—' }}</x-panel.badge></td>@endif
                        <td>#{{ $e->id }}</td>
                        <td>{{ textByLanguage('سائق', 'Driver') }} #{{ $e->driver_id }}</td>
                        <td>
                            @if($e->kind === 'sos')
                                <x-panel.badge tone="danger"><i class="bi bi-exclamation-octagon"></i> SOS</x-panel.badge>
                            @else
                                {{ ucfirst(str_replace('_', ' ', $e->kind)) }}@if($e->category) · {{ $e->category }}@endif
                            @endif
                        </td>
                        <td>{{ $e->booking_id ? '#' . $e->booking_id : '—' }}</td>
                        @if($isAdmin)<td>{{ $e->office_id ? '#' . $e->office_id : '—' }}</td>@endif
                        <td dir="ltr" style="text-align:start;">
                            @if($e->lat !== null)
                                <a href="https://www.google.com/maps?q={{ $e->lat }},{{ $e->lng }}" target="_blank" rel="noopener" class="p-maplink">
                                    <i class="bi bi-geo-alt-fill"></i> {{ number_format($e->lat, 4) . ', ' . number_format($e->lng, 4) }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $e->created_at ? \Illuminate\Support\Carbon::parse($e->created_at)->diffForHumans() : '—' }}</td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-shield-check"></i> {{ textByLanguage('لا توجد أحداث', 'No events') }}</p>
        @endif
    </div>

@endsection

@push('scripts')
<script>
(function () {
    // Live SOS/safety feed over the new realtime gateway (see panel-realtime.js).
    // The board is otherwise pull-only; this surfaces an emergency the instant it
    // is raised instead of on the next manual refresh.
    var ar = document.documentElement.lang === 'ar';
    var t = function (en, arText) { return ar ? arText : en; };
    var box = document.getElementById('sosLiveAlerts');
    if (!box) return;

    function bumpStat() {
        var el = document.querySelector('.p-faq-stats .p-stat--danger .p-stat__value, .p-faq-stats .p-stat__value');
        if (el) { var n = parseInt(el.textContent.replace(/\D/g, ''), 10); if (!isNaN(n)) el.textContent = n + 1; }
    }

    window.addEventListener('fleet:rt:support.message_created', function (e) {
        var d = (e.detail && e.detail.data) || {};
        if (d.kind !== 'sos' && d.kind !== 'safety_report') return;

        var isSos = d.kind === 'sos';
        var card = document.createElement('div');
        card.setAttribute('role', 'alert');
        card.style.cssText = 'padding:14px 16px;border-radius:12px;border:1.5px solid ' +
            (isSos ? '#dc2626' : 'var(--p-border)') + ';background:' +
            (isSos ? 'rgba(220,38,38,.08)' : 'var(--p-surface,#fff)') + ';display:flex;gap:12px;align-items:center;justify-content:space-between;';

        var loc = (d.lat != null && d.lng != null)
            ? '<a href="https://www.google.com/maps?q=' + d.lat + ',' + d.lng + '" target="_blank" rel="noopener" style="text-decoration:underline;">' + t('map', 'الخريطة') + '</a>'
            : '—';
        var label = isSos
            ? '<strong style="color:#dc2626;">⚠ ' + t('SOS emergency', 'طوارئ SOS') + '</strong>'
            : '<strong>' + t('Safety report', 'بلاغ سلامة') + '</strong>';

        card.innerHTML =
            '<div>' + label + ' — ' + t('Driver', 'سائق') + ' #' + (d.driver_id || '?') +
            (d.booking_id ? ' · ' + t('Trip', 'رحلة') + ' #' + d.booking_id : '') +
            ' · ' + loc + '</div>' +
            '<button type="button" style="border:none;background:none;cursor:pointer;font-size:1.1rem;line-height:1;color:var(--p-text-muted);">&times;</button>';

        card.querySelector('button').addEventListener('click', function () { card.remove(); if (!box.children.length) box.style.display = 'none'; });
        box.insertBefore(card, box.firstChild);
        box.style.display = 'flex';
        bumpStat();
    });
})();
</script>
@endpush

