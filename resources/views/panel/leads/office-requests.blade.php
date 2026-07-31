@extends('panel.layouts.master')

@section('title', textByLanguage('طلبات المكاتب', 'Office requests'))
@section('page-title', textByLanguage('طلبات المكاتب', 'Office requests'))

@php
    $r = fn ($name) => "panel.admin.{$name}";
    $statusLabel = [
        'new' => textByLanguage('جديد', 'New'),
        'reviewed' => textByLanguage('تمّت المراجعة', 'Reviewed'),
        'approved' => textByLanguage('مقبول — تم إنشاء الحساب', 'Approved — account created'),
        'rejected' => textByLanguage('مرفوض', 'Rejected'),
    ];
    $statusTone = ['new' => 'primary', 'reviewed' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    @if($credentials = session('office_credentials'))
        <div class="p-card" style="margin-bottom:16px;border:2px solid var(--p-accent);">
            <h3 style="font-size:.95rem;font-weight:800;margin:0 0 6px;"><i class="bi bi-key"></i> {{ textByLanguage('بيانات دخول المكتب الجديد', 'New office sign-in details') }}</h3>
            <p style="font-size:.85rem;color:var(--p-text-muted);margin:0 0 12px;">
                {{ textByLanguage('كلمة المرور تُعرض مرة واحدة فقط — أرسلها للمكتب الآن، ولا يمكن استرجاعها لاحقاً (يمكن تغييرها من صفحة المكتب).', 'The password is shown once — send it to the office now; it cannot be retrieved later (it can be changed from the office page).') }}
            </p>
            <div class="p-lead-detail">
                <div class="p-lead-field"><span>{{ textByLanguage('المكتب', 'Office') }}</span><b>{{ $credentials['name'] }}</b></div>
                <div class="p-lead-field"><span>{{ textByLanguage('الدولة', 'Country') }}</span><b>{{ $credentials['country'] }}</b></div>
                <div class="p-lead-field"><span>{{ textByLanguage('البريد', 'Email') }}</span><b dir="ltr">{{ $credentials['email'] }}</b></div>
                <div class="p-lead-field"><span>{{ textByLanguage('كلمة المرور', 'Password') }}</span><b dir="ltr"><code>{{ $credentials['password'] }}</code></b></div>
            </div>
        </div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('طلبات إطلاق المكاتب', 'Office launch requests')"
        :subtitle="textByLanguage('أنشطة تطلب الانضمام كمكتب أجرة', 'Businesses requesting to join as a taxi office')">
        <x-slot:actions>
            <a href="{{ route($r('leads.hub')) }}" class="p-btn p-btn--ghost"><i class="bi bi-grid"></i> {{ textByLanguage('كل الطلبات', 'All leads') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-faq-stats" style="grid-template-columns:repeat(4,1fr);">
        <x-panel.stat :label="textByLanguage('جديدة', 'New')" :value="$counts['new']" icon="bi-inbox" :variant="$counts['new'] ? 'primary' : null" />
        <x-panel.stat :label="textByLanguage('تمّت مراجعتها', 'Reviewed')" :value="$counts['reviewed']" icon="bi-check2-circle" />
        <x-panel.stat :label="textByLanguage('حسابات أُنشئت', 'Accounts created')" :value="$counts['approved']" icon="bi-building-check" />
        <x-panel.stat :label="textByLanguage('الإجمالي', 'Total')" :value="$counts['total']" icon="bi-building" />
    </div>

    <div class="p-card">
        <form method="GET" action="{{ route($r('leads.offices')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الحالات', 'All statuses') }}</option>
                @foreach($statusLabel as $value => $label)
                    <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                @endforeach
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
                            <x-panel.badge :tone="$statusTone[$req->status] ?? 'gray'">{{ $statusLabel[$req->status] ?? $req->status }}</x-panel.badge>
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
                                @if($req->status !== 'approved')
                                    <form method="POST" action="{{ route($r('leads.offices.review'), $req->id) }}">
                                        @csrf
                                        <button type="submit" class="p-btn p-btn--ghost">
                                            <i class="bi bi-{{ $req->status === 'new' ? 'check2-circle' : 'arrow-counterclockwise' }}"></i>
                                            {{ $req->status === 'new' ? textByLanguage('وضع كمراجَع', 'Mark reviewed') : textByLanguage('إعادة كجديد', 'Mark new') }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route($r('leads.offices.decide'), $req->id) }}"
                                          onsubmit="return confirm('{{ textByLanguage('إنشاء حساب مكتب في الدولة النشطة الآن؟', 'Create an office account in the active country now?') }}');">
                                        @csrf
                                        <input type="hidden" name="decision" value="approve">
                                        <button type="submit" class="p-btn p-btn--primary">
                                            <i class="bi bi-building-check"></i> {{ textByLanguage('قبول وإنشاء الحساب', 'Approve & create account') }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route($r('leads.offices.decide'), $req->id) }}"
                                          onsubmit="return confirm('{{ textByLanguage('رفض هذا الطلب؟', 'Reject this request?') }}');">
                                        @csrf
                                        <input type="hidden" name="decision" value="reject">
                                        <button type="submit" class="p-btn p-btn--ghost" style="color:var(--p-danger);">
                                            <i class="bi bi-x-circle"></i> {{ textByLanguage('رفض', 'Reject') }}
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route($r('office.index')) }}" class="p-btn p-btn--ghost"><i class="bi bi-building"></i> {{ textByLanguage('عرض المكاتب', 'View offices') }}</a>
                                @endif
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
