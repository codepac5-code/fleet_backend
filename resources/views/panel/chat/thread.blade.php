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
        <div class="p-thread" id="chatThread" data-conversation="{{ $conversation['id'] }}">
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

@push('scripts')
<script>
(function () {
    // Live inbound rider messages over the realtime gateway (see panel-realtime.js).
    // The thread was pull-only — staff had to reload to see a reply.
    var thread = document.getElementById('chatThread');
    if (!thread) return;
    var convId = String(thread.getAttribute('data-conversation') || '');
    var ar = document.documentElement.lang === 'ar';

    window.addEventListener('fleet:rt:chat.message_created', function (e) {
        var d = (e.detail && e.detail.data) || {};
        if (String(d.conversation_id || '') !== convId) return;
        // Only append messages FROM the rider — the office's own send re-renders on POST.
        if ((d.sender_type || d.sender_role) === 'office') return;

        var empty = thread.querySelector('.p-empty');
        if (empty) empty.remove();

        var wrap = document.createElement('div');
        wrap.style.cssText = 'display:flex;flex-direction:column;margin-bottom:.75rem;align-items:flex-start;';
        var bubble = document.createElement('div');
        bubble.style.cssText = 'max-width:70%;padding:.6rem .85rem;border-radius:.75rem;background:var(--p-surface-2,#f4f5f7);';
        var meta = document.createElement('div');
        meta.style.cssText = 'font-size:.7rem;opacity:.6;margin-bottom:.2rem;';
        meta.textContent = (ar ? 'الراكب' : 'Rider') + ' · ' + (ar ? 'الآن' : 'now');
        var body = document.createElement('div');
        body.textContent = d.body || d.preview || '';
        bubble.appendChild(meta); bubble.appendChild(body); wrap.appendChild(bubble);
        thread.appendChild(wrap);
        thread.scrollTop = thread.scrollHeight;
    });
})();
</script>
@endpush
