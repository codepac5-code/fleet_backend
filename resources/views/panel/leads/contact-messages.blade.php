@extends('panel.layouts.master')

@section('title', textByLanguage('رسائل التواصل', 'Contact messages'))
@section('page-title', textByLanguage('رسائل التواصل', 'Contact messages'))

@php $r = fn ($name) => "panel.admin.{$name}"; @endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('رسائل نموذج التواصل', 'Contact form messages')"
        :subtitle="textByLanguage('عملاء محتملون ورسائل من زوّار الموقع', 'Leads and messages from website visitors')">
        <x-slot:actions>
            <a href="{{ route($r('leads.hub')) }}" class="p-btn p-btn--ghost"><i class="bi bi-grid"></i> {{ textByLanguage('كل الطلبات', 'All leads') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-faq-stats" style="grid-template-columns:repeat(3,1fr);">
        <x-panel.stat :label="textByLanguage('جديدة', 'New')" :value="$counts['new']" icon="bi-envelope" :variant="$counts['new'] ? 'primary' : null" />
        <x-panel.stat :label="textByLanguage('مقروءة', 'Read')" :value="$counts['read']" icon="bi-envelope-open" />
        <x-panel.stat :label="textByLanguage('الإجمالي', 'Total')" :value="$counts['total']" icon="bi-chat-dots" />
    </div>

    <div class="p-card">
        <form method="GET" action="{{ route($r('leads.contacts')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الحالات', 'All statuses') }}</option>
                <option value="new" @selected($statusFilter === 'new')>{{ textByLanguage('جديدة', 'New') }}</option>
                <option value="read" @selected($statusFilter === 'read')>{{ textByLanguage('مقروءة', 'Read') }}</option>
            </select>
            @if($statusFilter)<a href="{{ route($r('leads.contacts')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
        </form>

        @if($messages->count())
            <div class="p-faq-list">
                @foreach($messages as $m)
                    <div class="p-faq-item @if($m->status === 'read') is-off @endif" data-lead>
                        <div class="p-faq-item__head" data-lead-toggle>
                            <span class="p-lead-avatar">{{ mb_strtoupper(mb_substr($m->name ?: '?', 0, 1)) }}</span>
                            <div class="p-faq-item__q">
                                <strong>{{ $m->name }}@if($m->company) · {{ $m->company }}@endif</strong>
                                <span>{{ $m->intent ?: textByLanguage('استفسار', 'Enquiry') }} · {{ $m->created_at ? \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() : '' }}</span>
                            </div>
                            @if($m->status === 'new')<x-panel.badge tone="primary">{{ textByLanguage('جديد', 'New') }}</x-panel.badge>@else<x-panel.badge tone="gray">{{ textByLanguage('مقروء', 'Read') }}</x-panel.badge>@endif
                            <i class="bi bi-chevron-down p-faq-item__chev"></i>
                        </div>
                        <div class="p-faq-item__body">
                            <div class="p-lead-detail">
                                <div class="p-lead-field"><span>{{ textByLanguage('البريد', 'Email') }}</span><b dir="ltr">{{ $m->email ?: '—' }}</b></div>
                                <div class="p-lead-field"><span>{{ textByLanguage('الهاتف', 'Phone') }}</span><b dir="ltr">{{ $m->phone ?: '—' }}</b></div>
                                <div class="p-lead-field p-lead-field--full"><span>{{ textByLanguage('الرسالة', 'Message') }}</span><b style="white-space:pre-line;font-weight:600;">{{ $m->message ?: '—' }}</b></div>
                            </div>
                            <div class="p-faq-item__acts">
                                @if($m->email)
                                    <a href="mailto:{{ $m->email }}" class="p-btn p-btn--ghost"><i class="bi bi-reply"></i> {{ textByLanguage('رد', 'Reply') }}</a>
                                @endif
                                <form method="POST" action="{{ route($r('leads.contacts.review'), $m->id) }}">
                                    @csrf
                                    <button type="submit" class="p-btn p-btn--primary">
                                        <i class="bi bi-{{ $m->status === 'new' ? 'check2-circle' : 'arrow-counterclockwise' }}"></i>
                                        {{ $m->status === 'new' ? textByLanguage('وضع كمقروء', 'Mark read') : textByLanguage('إعادة كجديد', 'Mark new') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="p-empty"><i class="bi bi-chat-square-dots"></i> {{ textByLanguage('لا توجد رسائل', 'No messages') }}</p>
        @endif
    </div>

    <script>
        document.querySelectorAll('[data-lead-toggle]').forEach(function (h) {
            h.addEventListener('click', function () { h.closest('[data-lead]').classList.toggle('is-open'); });
        });
    </script>

@endsection
