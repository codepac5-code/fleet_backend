@extends('panel.layouts.master')

@section('title', textByLanguage('محادثة', 'Conversation'))
@section('page-title', textByLanguage('محادثة', 'Conversation'))

@php $r = fn ($name) => "panel.{$entity}.{$name}"; @endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('راكب', 'Rider') . ' #' . $conversation['user_id']"
        :subtitle="$conversation['booking_id'] ? (textByLanguage('رحلة', 'Trip') . ' #' . $conversation['booking_id']) : ''">
        <x-slot:actions>
            <a href="{{ route($r('chat.index')) }}" class="p-btn p-btn--ghost">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-card">
        <div class="p-thread">
            @forelse($messages as $m)
                @php $mine = $m['sender_type'] === 'office'; @endphp
                <div style="display:flex;flex-direction:column;margin-bottom:.75rem;{{ $mine ? 'align-items:flex-end;' : 'align-items:flex-start;' }}">
                    <div style="max-width:70%;padding:.6rem .85rem;border-radius:.75rem;{{ $mine ? 'background:var(--p-accent-soft,#eef2ff);' : 'background:var(--p-surface-2,#f4f5f7);' }}">
                        <div style="font-size:.7rem;opacity:.6;margin-bottom:.2rem;">
                            {{ $m['sender_type'] === 'office' ? textByLanguage('المكتب', 'Office') : textByLanguage('الراكب', 'Rider') }}
                            @if($m['created_at'])· {{ \Illuminate\Support\Carbon::parse($m['created_at'])->diffForHumans() }}@endif
                        </div>
                        <div>{{ $m['body'] }}</div>
                    </div>
                </div>
            @empty
                <p class="p-empty"><i class="bi bi-chat"></i> {{ textByLanguage('لا رسائل بعد', 'No messages yet') }}</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route($r('chat.send'), $conversation['id']) }}" style="margin-top:1rem;">
            @csrf
            <textarea name="body" rows="3" class="p-input" style="width:100%;" placeholder="{{ textByLanguage('اكتب رسالة…', 'Write a message…') }}" required></textarea>
            <div style="margin-top:.6rem;text-align:end;">
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-send"></i> {{ textByLanguage('إرسال', 'Send') }}</button>
            </div>
        </form>
    </div>

@endsection
