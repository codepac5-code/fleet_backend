@extends('panel.layouts.master')

@section('title', textByLanguage('سجل التدقيق', 'Audit log'))
@section('page-title', textByLanguage('سجل التدقيق', 'Audit log'))

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('سجل التدقيق', 'Audit log')"
        :subtitle="textByLanguage('من فعل ماذا ومتى — لهذه الدولة', 'Who did what and when — for this country')" />

    <div class="p-card" style="margin-bottom:16px;">
        <form method="GET" class="p-search" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <input name="action" value="{{ $action }}" placeholder="{{ textByLanguage('بحث بالإجراء', 'Search action') }}" class="p-search__input">
            <select name="actor_type" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الفاعلين', 'All actors') }}</option>
                @foreach($actorTypes as $at)
                    <option value="{{ $at }}" @selected($actorType === $at)>{{ $at }}</option>
                @endforeach
            </select>
            <button type="submit" class="p-btn p-btn--soft"><i class="bi bi-funnel"></i> {{ textByLanguage('تصفية', 'Filter') }}</button>
            @if($action || $actorType)<a href="{{ url()->current() }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
        </form>
    </div>

    <div class="p-card">
        @if($logs->count())
            <x-panel.table :headers="[
                textByLanguage('الوقت', 'When'),
                textByLanguage('الفاعل', 'Actor'),
                textByLanguage('الإجراء', 'Action'),
                textByLanguage('الهدف', 'Subject'),
                textByLanguage('IP', 'IP'),
            ]">
                @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->created_at ? \Illuminate\Support\Carbon::parse($log->created_at)->diffForHumans() : '—' }}</td>
                        <td>{{ $log->actor_type ? $log->actor_type . ' #' . $log->actor_id : '—' }}</td>
                        <td><x-panel.badge tone="primary">{{ $log->action }}</x-panel.badge></td>
                        <td>{{ $log->subject_type ? $log->subject_type . ' #' . $log->subject_id : '—' }}</td>
                        <td dir="ltr" style="text-align:start;">{{ $log->ip ?: '—' }}</td>
                    </tr>
                @endforeach
            </x-panel.table>

            <div style="margin-top:14px;">{{ $logs->links() }}</div>
        @else
            <p class="p-empty"><i class="bi bi-clipboard-check"></i> {{ textByLanguage('لا توجد سجلّات', 'No audit entries') }}</p>
        @endif
    </div>

@endsection
