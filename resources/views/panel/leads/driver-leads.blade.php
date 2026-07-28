@extends('panel.layouts.master')

@section('title', textByLanguage('طلبات السائقين', 'Driver applications'))
@section('page-title', textByLanguage('طلبات السائقين', 'Driver applications'))

@php
    $r = fn ($name) => "panel.admin.{$name}";
    $imageFields = [
        'profileImage' => textByLanguage('شخصية', 'Profile'), 'idFrontImage' => textByLanguage('هوية أمام', 'ID front'),
        'idBackImage' => textByLanguage('هوية خلف', 'ID back'), 'licenseFrontImage' => textByLanguage('رخصة أمام', 'License front'),
        'licenseBackImage' => textByLanguage('رخصة خلف', 'License back'), 'mechanicalImage' => textByLanguage('ميكانيكي', 'Mechanical'),
        'frontCarImage' => textByLanguage('أمام', 'Front'), 'backCarImage' => textByLanguage('خلف', 'Back'),
        'rightCarImage' => textByLanguage('يمين', 'Right'), 'leftCarImage' => textByLanguage('يسار', 'Left'),
        'insideCarImage' => textByLanguage('داخل', 'Interior'), 'frontSeatsImage' => textByLanguage('مقاعد أمامية', 'Front seats'),
        'backSeatsImage' => textByLanguage('مقاعد خلفية', 'Back seats'),
    ];
    $tones = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('طلبات انضمام السائقين', 'Driver join applications')"
        :subtitle="textByLanguage('طلبات من نموذج القيادة في الموقع مع الوثائق والصور', 'Website drive-with-us applications with documents and photos')">
        <x-slot:actions>
            <a href="{{ route($r('leads.hub')) }}" class="p-btn p-btn--ghost"><i class="bi bi-grid"></i> {{ textByLanguage('كل الطلبات', 'All leads') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-faq-stats" style="grid-template-columns:repeat(4,1fr);">
        <x-panel.stat :label="textByLanguage('قيد الانتظار', 'Pending')" :value="$counts['pending']" icon="bi-hourglass-split" :variant="$counts['pending'] ? 'primary' : null" />
        <x-panel.stat :label="textByLanguage('مقبولة', 'Approved')" :value="$counts['approved']" icon="bi-check2-circle" />
        <x-panel.stat :label="textByLanguage('مرفوضة', 'Rejected')" :value="$counts['rejected']" icon="bi-x-circle" />
        <x-panel.stat :label="textByLanguage('الإجمالي', 'Total')" :value="$counts['total']" icon="bi-people" />
    </div>

    <div class="p-card">
        <form method="GET" action="{{ route($r('leads.drivers')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الحالات', 'All statuses') }}</option>
                @foreach(['pending','approved','rejected'] as $s)
                    <option value="{{ $s }}" @selected($statusFilter === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            @if($statusFilter)<a href="{{ route($r('leads.drivers')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
        </form>

        @if($applications->count())
            <div class="p-faq-list">
                @foreach($applications as $app)
                    <div class="p-faq-item @if($app->status !== 'pending') is-off @endif" data-lead>
                        <div class="p-faq-item__head" data-lead-toggle>
                            @if($app->profileImage)
                                <img class="p-lead-avatar p-lead-avatar--img" src="{{ asset('storage/' . $app->profileImage) }}" alt="" onerror="this.style.display='none'">
                            @else
                                <span class="p-lead-avatar">{{ mb_strtoupper(mb_substr($app->name ?: '?', 0, 1)) }}</span>
                            @endif
                            <div class="p-faq-item__q">
                                <strong>{{ $app->name }}</strong>
                                <span dir="ltr">{{ $app->phoneNumber }} · {{ trim(($app->brand ?? '') . ' ' . ($app->model ?? '') . ' ' . ($app->year ?? '')) ?: '—' }}</span>
                            </div>
                            <x-panel.badge :tone="$tones[$app->status] ?? 'gray'">{{ ucfirst($app->status) }}</x-panel.badge>
                            <i class="bi bi-chevron-down p-faq-item__chev"></i>
                        </div>
                        <div class="p-faq-item__body">
                            <div class="p-lead-detail">
                                <div class="p-lead-field"><span>{{ textByLanguage('المركبة', 'Vehicle') }}</span><b>{{ trim(($app->brand ?? '') . ' ' . ($app->model ?? '')) ?: '—' }}</b></div>
                                <div class="p-lead-field"><span>{{ textByLanguage('السنة', 'Year') }}</span><b>{{ $app->year ?: '—' }}</b></div>
                                <div class="p-lead-field"><span>{{ textByLanguage('اللون', 'Color') }}</span><b>{{ $app->color ?: '—' }}</b></div>
                                <div class="p-lead-field"><span>{{ textByLanguage('اللوحة', 'Plate') }}</span><b dir="ltr">{{ $app->plateNumber ?: '—' }}</b></div>
                            </div>

                            @php $imgs = collect($imageFields)->filter(fn ($l, $f) => ! empty($app->$f)); @endphp
                            @if($imgs->count())
                                <div class="p-lead-gallery">
                                    @foreach($imgs as $field => $label)
                                        <figure class="p-lead-thumb" onclick="panelLightbox('{{ asset('storage/' . $app->$field) }}')">
                                            <img src="{{ asset('storage/' . $app->$field) }}" loading="lazy" alt="{{ $label }}" onerror="this.closest('.p-lead-thumb').style.display='none'">
                                            <figcaption>{{ $label }}</figcaption>
                                        </figure>
                                    @endforeach
                                </div>
                            @endif

                            <div class="p-faq-item__acts">
                                @foreach(['approved' => ['check2-circle', textByLanguage('قبول', 'Approve')], 'rejected' => ['x-circle', textByLanguage('رفض', 'Reject')], 'pending' => ['hourglass-split', textByLanguage('تعليق', 'Pending')]] as $st => $meta)
                                    @if($app->status !== $st)
                                        <form method="POST" action="{{ route($r('leads.drivers.status'), $app->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $st }}">
                                            <button type="submit" class="p-btn {{ $st === 'approved' ? 'p-btn--primary' : 'p-btn--ghost' }}"><i class="bi bi-{{ $meta[0] }}"></i> {{ $meta[1] }}</button>
                                        </form>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="p-empty"><i class="bi bi-person-badge"></i> {{ textByLanguage('لا توجد طلبات', 'No applications') }}</p>
        @endif
    </div>

    <div id="panel-lb" class="p-lightbox" onclick="this.classList.remove('is-on')"><img id="panel-lb-img" src="" alt=""></div>

    <script>
        function panelLightbox(src) {
            var lb = document.getElementById('panel-lb');
            document.getElementById('panel-lb-img').src = src;
            lb.classList.add('is-on');
        }
        document.querySelectorAll('[data-lead-toggle]').forEach(function (h) {
            h.addEventListener('click', function () { h.closest('[data-lead]').classList.toggle('is-open'); });
        });
    </script>

@endsection
