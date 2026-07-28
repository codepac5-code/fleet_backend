@extends('panel.layouts.master')

@php
    $isAr = app()->getLocale() === 'ar';
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $assignedSet = collect($assigned)->flip();
    $total = collect($catalog)->sum(fn ($s) => count($s['subServices']));
    $current = collect($catalog)->sum(fn ($s) => collect($s['subServices'])->filter(fn ($p) => $assignedSet->has($p['id']))->count());
@endphp

@section('title', textByLanguage('خدمات المركبة', 'Vehicle services'))
@section('page-title', textByLanguage('خدمات المركبة', 'Vehicle services'))

@section('content')

    <x-panel.page-toolbar
        :title="trim($vehicle->vehicleBrand.' '.$vehicle->model)"
        :subtitle="textByLanguage('اختر الخدمات الفرعية التي تقدّمها هذه المركبة', 'Choose the sub-services this vehicle offers')">
        <x-slot:actions>
            <a href="{{ route($r('vehicle.index')) }}" class="p-btn p-btn--ghost"><i class="bi bi-arrow-{{ $isAr ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="vsvc-vehicle">
        @if($vehicle->photo)<span class="vsvc-vehicle__img"><img src="{{ asset('storage/'.$vehicle->photo) }}" alt=""></span>@else<span class="vsvc-vehicle__img"><i class="bi bi-car-front"></i></span>@endif
        <div>
            <strong>{{ trim($vehicle->vehicleBrand.' '.$vehicle->model) }}</strong>
            <span dir="ltr">{{ $vehicle->plate }} · {{ $vehicle->modelYear }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route($r('vehicle.services.update'), $vehicle->id) }}" id="vsvcForm">
        @csrf
        @method('PUT')

        <div class="p-card perm-bar vsvc-bar">
            <div class="perm-bar__info">
                <i class="bi bi-diagram-3"></i>
                <span>{{ textByLanguage('الخدمات المختارة', 'Selected services') }}: <strong id="vsvcCount">{{ $current }}</strong> / {{ $total }}</span>
            </div>
            <div class="perm-bar__actions">
                <button type="button" class="p-btn p-btn--ghost" data-bulk="all">{{ textByLanguage('تحديد الكل', 'Select all') }}</button>
                <button type="button" class="p-btn p-btn--ghost" data-bulk="none">{{ textByLanguage('إلغاء الكل', 'Clear all') }}</button>
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ textByLanguage('حفظ', 'Save') }}</button>
            </div>
        </div>

        @forelse($catalog as $service)
            <div class="p-card vsvc-group" data-group style="margin-bottom:18px;">
                <div class="vsvc-group__head">
                    <h3 class="p-card__title" style="margin:0;"><i class="bi bi-grid-1x2"></i> {{ $service['title'] }}</h3>
                    <label class="perm-group__toggle"><input type="checkbox" data-group-toggle> <span>{{ textByLanguage('الكل', 'All') }}</span></label>
                </div>

                @if(!empty($service['subServices']))
                    <div class="vsvc-grid">
                        @foreach($service['subServices'] as $sub)
                            <label class="vsvc-card {{ $assignedSet->has($sub['id']) ? 'is-on' : '' }}">
                                <input type="checkbox" name="sub_services[]" value="{{ $sub['id'] }}" data-pick @checked($assignedSet->has($sub['id']))>
                                <span class="vsvc-card__check"><i class="bi bi-check-lg"></i></span>
                                <span class="vsvc-card__name">{{ $sub['name'] }}</span>
                                <span class="vsvc-card__prices">
                                    <span title="{{ textByLanguage('فتح', 'Open') }}"><i class="bi bi-unlock"></i> {{ getPriceFormat($sub['openPrice']) }}</span>
                                    <span title="{{ textByLanguage('كم', 'km') }}"><i class="bi bi-signpost-2"></i> {{ getPriceFormat($sub['kmPrice']) }}</span>
                                    <span title="{{ textByLanguage('دقيقة', 'min') }}"><i class="bi bi-clock"></i> {{ getPriceFormat($sub['minutePrice']) }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="p-empty" style="padding:12px;"><i class="bi bi-inbox"></i> {{ textByLanguage('لا توجد خدمات فرعية مفعّلة', 'No active sub-services') }}</p>
                @endif
            </div>
        @empty
            <div class="p-card"><p class="p-empty"><i class="bi bi-grid-1x2"></i> {{ textByLanguage('لا توجد خدمات', 'No services') }}</p></div>
        @endforelse
    </form>

@endsection

@push('scripts')
<script>
    (function () {
        var form = document.getElementById('vsvcForm');
        if (!form) return;
        var picks = form.querySelectorAll('[data-pick]');
        var countEl = document.getElementById('vsvcCount');

        function refresh() {
            var n = 0;
            picks.forEach(function (p) { if (p.checked) n++; });
            if (countEl) countEl.textContent = n;
        }
        function syncCard(input) { input.closest('.vsvc-card').classList.toggle('is-on', input.checked); }
        function syncGroup(group) {
            var t = group.querySelector('[data-group-toggle]'); var items = group.querySelectorAll('[data-pick]');
            var all = items.length > 0; items.forEach(function (i) { if (!i.checked) all = false; });
            if (t) t.checked = all;
        }

        form.querySelectorAll('[data-group]').forEach(function (group) {
            var t = group.querySelector('[data-group-toggle]'); var items = group.querySelectorAll('[data-pick]');
            if (t) t.addEventListener('change', function () { items.forEach(function (i) { i.checked = t.checked; syncCard(i); }); refresh(); });
            items.forEach(function (i) { i.addEventListener('change', function () { syncCard(i); syncGroup(group); refresh(); }); });
            syncGroup(group);
        });

        form.querySelectorAll('[data-bulk]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var on = btn.getAttribute('data-bulk') === 'all';
                picks.forEach(function (p) { p.checked = on; syncCard(p); });
                form.querySelectorAll('[data-group-toggle]').forEach(function (t) { t.checked = on; });
                refresh();
            });
        });
    })();
</script>
@endpush
