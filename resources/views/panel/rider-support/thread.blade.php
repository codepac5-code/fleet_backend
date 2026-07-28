@extends('panel.layouts.master')

@section('title', textByLanguage('تذكرة الدعم', 'Support ticket'))
@section('page-title', textByLanguage('تذكرة الدعم', 'Support ticket'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $statuses = ['open', 'pending', 'resolved', 'closed'];
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <x-panel.page-toolbar :title="$ticket['subject']"
        :subtitle="'#' . $ticket['ticket_id'] . ' · ' . ucfirst($ticket['category'])">
        <x-slot:actions>
            <a href="{{ route($r('rider-support.index')) }}" class="p-btn p-btn--ghost">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-card">
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;">
            <x-panel.badge :status="$ticket['status']">{{ ucfirst($ticket['status']) }}</x-panel.badge>
            @if($ticket['category'] === 'sos')<x-panel.badge tone="danger">SOS</x-panel.badge>@endif
        </div>

        <div class="p-thread">
            @foreach($ticket['messages'] as $m)
                @php $mine = $m['from'] !== 'user'; @endphp
                <div class="p-thread__msg" style="display:flex;flex-direction:column;margin-bottom:.75rem;{{ $mine ? 'align-items:flex-end;' : 'align-items:flex-start;' }}">
                    <div style="max-width:70%;padding:.6rem .85rem;border-radius:.75rem;{{ $mine ? 'background:var(--p-accent-soft,#eef2ff);' : 'background:var(--p-surface-2,#f4f5f7);' }}">
                        <div style="font-size:.7rem;opacity:.6;margin-bottom:.2rem;">
                            {{ $m['from'] === 'user' ? textByLanguage('الراكب', 'Rider') : ($m['from'] === 'office' ? textByLanguage('المكتب', 'Office') : textByLanguage('الأسطول', 'Fleet')) }}
                            @if($m['at'])· {{ \Illuminate\Support\Carbon::parse($m['at'])->diffForHumans() }}@endif
                        </div>
                        <div>{{ $m['body'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route($r('rider-support.reply'), $ticket['ticket_id']) }}" style="margin-top:1rem;">
            @csrf
            <textarea name="body" rows="3" class="p-input" style="width:100%;" placeholder="{{ textByLanguage('اكتب ردّك…', 'Write your reply…') }}" required></textarea>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.6rem;gap:.75rem;flex-wrap:wrap;">
                @if($isAdmin)
                    <span style="display:flex;gap:.4rem;align-items:center;">
                        <label style="font-size:.8rem;opacity:.7;">{{ textByLanguage('الحالة', 'Status') }}</label>
                @endif
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-send"></i> {{ textByLanguage('إرسال', 'Send') }}</button>
                @if($isAdmin)</span>@endif
            </div>
        </form>

        @if($isAdmin)
            <form method="POST" action="{{ route($r('rider-support.status'), $ticket['ticket_id']) }}" style="margin-top:.75rem;display:flex;gap:.5rem;align-items:center;">
                @csrf
                <select name="status" class="p-search__select">
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" @selected($ticket['status'] === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="p-btn p-btn--ghost">{{ textByLanguage('تحديث الحالة', 'Update status') }}</button>
            </form>
        @endif
    </div>

@endsection
