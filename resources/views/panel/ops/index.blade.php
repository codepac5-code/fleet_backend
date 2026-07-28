@extends('panel.layouts.master')

@section('title', textByLanguage('صحة التشغيل', 'Ops health'))
@section('page-title', textByLanguage('صحة التشغيل', 'Ops health'))

@php $r = fn ($name) => "panel.admin.{$name}"; @endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('صحة التشغيل', 'Operations health')"
        :subtitle="textByLanguage('الطوابير والوظائف الخلفية', 'Queues and background jobs')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if(session('error'))<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>@endif

    <div class="p-faq-stats" style="grid-template-columns:repeat(3,1fr);">
        <x-panel.stat :label="textByLanguage('بانتظار التنفيذ', 'Queued')" :value="$pending" icon="bi-hourglass-split" />
        <x-panel.stat :label="textByLanguage('فاشلة', 'Failed')" :value="$failedCount" icon="bi-x-octagon" :variant="$failedCount ? 'danger' : null" />
        <x-panel.stat :label="textByLanguage('أحداث معلّقة (outbox)', 'Pending events')" :value="$outboxPending" icon="bi-broadcast" />
    </div>

    @if(!empty($daemons))
        <div class="p-card" style="margin-bottom:16px;">
            <strong style="display:block; margin-bottom:12px;">{{ textByLanguage('العمليات الخلفية (نبضات)', 'Background daemons (heartbeats)') }}</strong>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px;">
                @foreach($daemons as $d)
                    @php
                        $tone = $d['up'] ? '#16a34a' : ($d['seen'] ? '#dc2626' : '#9ca3af');
                        $label = !$d['seen'] ? textByLanguage('لم يُرصد قط', 'never seen')
                            : ($d['up'] ? textByLanguage('يعمل', 'up') : textByLanguage('متوقّف', 'down'));
                    @endphp
                    <div style="border:1px solid var(--p-border); border-inline-start:3px solid {{ $tone }}; border-radius:8px; padding:10px 12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <code style="font-size:.82rem;">{{ $d['name'] }}</code>
                            <span style="color:{{ $tone }}; font-weight:700; font-size:.8rem;">● {{ $label }}</span>
                        </div>
                        <div style="font-size:.78rem; color:var(--p-text-muted); margin-top:4px;">
                            @if($d['seen'])
                                {{ textByLanguage('آخر نبضة', 'last beat') }}: {{ $d['last']->diffForHumans() }}
                            @else
                                {{ textByLanguage('لا نبضة مسجّلة', 'no heartbeat recorded') }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(!empty($byQueue))
        <div class="p-card" style="margin-bottom:16px;">
            <strong>{{ textByLanguage('حسب الطابور', 'By queue') }}:</strong>
            @foreach($byQueue as $q => $c)<span class="p-badge p-badge--gray" style="margin:0 4px;">{{ $q }}: {{ $c }}</span>@endforeach
        </div>
    @endif

    <div class="p-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <strong>{{ textByLanguage('الوظائف الفاشلة', 'Failed jobs') }}</strong>
            @if($failedCount)
                <form method="POST" action="{{ route($r('ops.retry')) }}">@csrf
                    <input type="hidden" name="id" value="all">
                    <button type="submit" class="p-btn p-btn--soft"><i class="bi bi-arrow-clockwise"></i> {{ textByLanguage('إعادة تشغيل الكل', 'Retry all') }}</button>
                </form>
            @endif
        </div>

        @if(!empty($failed))
            <x-panel.table :headers="['#', textByLanguage('الطابور', 'Queue'), textByLanguage('الخطأ', 'Error'), textByLanguage('الوقت', 'When'), '']">
                @foreach($failed as $f)
                    <tr>
                        <td>{{ $f['id'] }}</td>
                        <td><span class="p-badge p-badge--gray">{{ $f['queue'] }}</span></td>
                        <td style="max-width:420px; font-size:.82rem; color:var(--p-danger);">{{ $f['error'] }}</td>
                        <td>{{ $f['failed_at'] ? \Illuminate\Support\Carbon::parse($f['failed_at'])->diffForHumans() : '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route($r('ops.retry')) }}">@csrf
                                <input type="hidden" name="id" value="{{ $f['id'] }}">
                                <button type="submit" class="p-btn p-btn--soft"><i class="bi bi-arrow-clockwise"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-check-circle"></i> {{ textByLanguage('لا توجد وظائف فاشلة', 'No failed jobs') }}</p>
        @endif
    </div>

@endsection
