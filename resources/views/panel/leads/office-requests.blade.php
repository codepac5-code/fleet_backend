@extends('panel.layouts.master')

@section('title', textByLanguage('طلبات المكاتب', 'Office requests'))
@section('page-title', textByLanguage('طلبات المكاتب', 'Office requests'))

@php $r = fn ($name) => "panel.admin.{$name}"; @endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('طلبات إطلاق المكاتب', 'Office launch requests')"
        :subtitle="textByLanguage('أنشطة تطلب الانضمام كمكتب أجرة', 'Businesses requesting to join as a taxi office')">
        <x-slot:actions>
            <a href="{{ route($r('leads.hub')) }}" class="p-btn p-btn--ghost"><i class="bi bi-grid"></i> {{ textByLanguage('كل الطلبات', 'All leads') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-faq-stats" style="grid-template-columns:repeat(3,1fr);">
        <x-panel.stat :label="textByLanguage('جديدة', 'New')" :value="$counts['new']" icon="bi-inbox" :variant="$counts['new'] ? 'primary' : null" />
        <x-panel.stat :label="textByLanguage('تمّت مراجعتها', 'Reviewed')" :value="$counts['reviewed']" icon="bi-check2-circle" />
        <x-panel.stat :label="textByLanguage('الإجمالي', 'Total')" :value="$counts['total']" icon="bi-building" />
    </div>

    <div class="p-card">
        <form method="GET" action="{{ route($r('leads.offices')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الحالات', 'All statuses') }}</option>
                <option value="new" @selected($statusFilter === 'new')>{{ textByLanguage('جديدة', 'New') }}</option>
                <option value="reviewed" @selected($statusFilter === 'reviewed')>{{ textByLanguage('تمّت مراجعتها', 'Reviewed') }}</option>
            </select>
            @if($statusFilter)<a href="{{ route($r('leads.offices')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
        </form>

        @if($requests->count())
            <div class="p-faq-list">
                @foreach($requests as $req)
                    <div class="p-faq-item @if($req->status !== 'new') is-off @endif" data-lead>
                        <div class="p-faq-item__head" data-lead-toggle>
                            <span class="p-lead-avatar">{{ mb_strtoupper(mb_substr($req->office_name ?: '?', 0, 1)) }}</span>
                            <div class="p-faq-item__q">
                                <strong>{{ $req->office_name }}</strong>
                                <span>{{ $req->contact_name }} · {{ $req->city }}@if($req->country), {{ $req->country }}@endif</span>
                            </div>
                            <x-panel.badge :tone="$req->status === 'new' ? 'primary' : 'success'">{{ $req->status === 'new' ? textByLanguage('جديد', 'New') : textByLanguage('تمّت المراجعة', 'Reviewed') }}</x-panel.badge>
                            <i class="bi bi-chevron-down p-faq-item__chev"></i>
                        </div>
                        <div class="p-faq-item__body">
                            <div class="p-lead-detail">
                                <div class="p-lead-field"><span>{{ textByLanguage('البريد', 'Email') }}</span><b dir="ltr">{{ $req->email ?: '—' }}</b></div>
                                <div class="p-lead-field"><span>{{ textByLanguage('الهاتف', 'Phone') }}</span><b dir="ltr">{{ $req->phone ?: '—' }}</b></div>
                                <div class="p-lead-field"><span>{{ textByLanguage('الموقع الإلكتروني', 'Website') }}</span><b dir="ltr">{{ $req->website ?: '—' }}</b></div>
                                <div class="p-lead-field"><span>{{ textByLanguage('فئة النشاط', 'Category') }}</span><b>{{ $req->business_category ?: '—' }}</b></div>
                                <div class="p-lead-field"><span>{{ textByLanguage('حجم الأسطول', 'Fleet size') }}</span><b>{{ $req->fleet_size ?: '—' }}</b></div>
                                <div class="p-lead-field"><span>{{ textByLanguage('نوع الخدمة', 'Service type') }}</span><b>{{ $req->service_type ?: '—' }}</b></div>
                                <div class="p-lead-field"><span>{{ textByLanguage('التغطية', 'Coverage') }}</span><b>{{ $req->coverage ?: '—' }}</b></div>
                                <div class="p-lead-field"><span>{{ textByLanguage('حالة الترخيص', 'License') }}</span><b>{{ $req->license_status ?: '—' }}</b></div>
                                <div class="p-lead-field"><span>{{ textByLanguage('الجدول الزمني', 'Timeline') }}</span><b>{{ $req->timeline ?: '—' }}</b></div>
                                @if($req->notes)<div class="p-lead-field p-lead-field--full"><span>{{ textByLanguage('ملاحظات', 'Notes') }}</span><b>{{ $req->notes }}</b></div>@endif
                            </div>
                            <div class="p-faq-item__acts">
                                @if($req->email)
                                    <a href="mailto:{{ $req->email }}" class="p-btn p-btn--ghost"><i class="bi bi-envelope"></i> {{ textByLanguage('مراسلة', 'Email') }}</a>
                                @endif
                                <form method="POST" action="{{ route($r('leads.offices.review'), $req->id) }}">
                                    @csrf
                                    <button type="submit" class="p-btn p-btn--primary">
                                        <i class="bi bi-{{ $req->status === 'new' ? 'check2-circle' : 'arrow-counterclockwise' }}"></i>
                                        {{ $req->status === 'new' ? textByLanguage('وضع كمراجَع', 'Mark reviewed') : textByLanguage('إعادة كجديد', 'Mark new') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="p-empty"><i class="bi bi-building"></i> {{ textByLanguage('لا توجد طلبات', 'No requests') }}</p>
        @endif
    </div>

    <script>
        document.querySelectorAll('[data-lead-toggle]').forEach(function (h) {
            h.addEventListener('click', function () { h.closest('[data-lead]').classList.toggle('is-open'); });
        });
    </script>

@endsection
