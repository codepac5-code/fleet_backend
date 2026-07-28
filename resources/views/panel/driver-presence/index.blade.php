@extends('panel.layouts.master')

@section('title', textByLanguage('حضور السائقين', 'Driver presence'))
@section('page-title', textByLanguage('حضور السائقين', 'Driver presence'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $tone = ['online' => 'success', 'busy' => 'primary', 'offline' => 'danger'];
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('حضور السائقين', 'Driver presence')"
        :subtitle="textByLanguage('الحالة الحيّة للسائقين — متصل/مشغول/غير متصل', 'Live driver availability — online / busy / offline')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if(session('error'))<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>@endif

    <div class="p-faq-stats" style="grid-template-columns:repeat(3,1fr);">
        <x-panel.stat :label="textByLanguage('متصل', 'Online')" :value="$counts['online']" icon="bi-broadcast" variant="success" />
        <x-panel.stat :label="textByLanguage('مشغول', 'Busy')" :value="$counts['busy']" icon="bi-hourglass-split" />
        <x-panel.stat :label="textByLanguage('غير متصل', 'Offline')" :value="$counts['offline']" icon="bi-power" />
    </div>

    <div class="p-card">
        @if($rows->count())
            <x-panel.table
                id="presenceTable"
                :headers="array_filter([
                    textByLanguage('السائق', 'Driver'),
                    textByLanguage('الهاتف', 'Phone'),
                    $isAdmin ? textByLanguage('المكتب', 'Office') : null,
                    textByLanguage('الحالة', 'Status'),
                    textByLanguage('آخر ظهور', 'Last seen'),
                    '',
                ], fn ($h) => $h !== null)">
                @foreach($rows as $row)
                    @php $isOnline = in_array($row['status'], ['online', 'busy'], true); @endphp
                    <tr data-driver="{{ $row['driver_id'] }}">
                        <td>{{ $row['name'] ?: (textByLanguage('سائق', 'Driver') . ' #' . $row['driver_id']) }}</td>
                        <td dir="ltr" style="text-align:start;">{{ $row['phone'] ?: '—' }}</td>
                        @if($isAdmin)<td>{{ $row['office_id'] ? '#' . $row['office_id'] : '—' }}</td>@endif
                        <td data-cell="status">
                            <x-panel.badge :tone="$tone[$row['status']] ?? 'gray'">{{ ucfirst($row['status']) }}</x-panel.badge>
                            @if($row['status'] === 'busy' && $row['busy_reason'])<small style="color:var(--p-text-muted);"> · {{ $row['busy_reason'] }}</small>@endif
                        </td>
                        <td>{{ $row['heartbeat_at'] ? \Illuminate\Support\Carbon::parse($row['heartbeat_at'])->diffForHumans() : '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route($r('driver-presence.offline'), $row['driver_id']) }}">
                                @csrf
                                <button type="submit" class="p-btn p-btn--soft" @disabled(! $isOnline)>
                                    <i class="bi bi-power"></i> {{ textByLanguage('فصل', 'Force offline') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-people"></i> {{ textByLanguage('لا يوجد سائقون', 'No drivers') }}</p>
        @endif
    </div>

@endsection

@push('scripts')
<script>
(function () {
    // Office consoles get presence live over their office room (batch-2 gateway);
    // admins fall back to a periodic reload since presence is not fanned to the
    // fleet room (it would flood on a large fleet).
    var isAdmin = @json($isAdmin);
    var tones = { online: 'success', busy: 'primary', offline: 'danger' };

    window.addEventListener('fleet:rt:presence.changed', function (e) {
        var d = (e.detail && e.detail.data) || {};
        var row = document.querySelector('#presenceTable tr[data-driver="' + d.driver_id + '"]');
        if (!row) { return; }
        var cell = row.querySelector('[data-cell="status"]');
        if (cell) {
            var tone = tones[d.status] || 'gray';
            var label = (d.status || '').replace(/^./, function (c) { return c.toUpperCase(); });
            cell.innerHTML = '<span class="p-badge p-badge--' + tone + '">' + label + '</span>';
        }
    });

    if (isAdmin) {
        setInterval(function () { location.reload(); }, 20000);
    }
})();
</script>
@endpush
