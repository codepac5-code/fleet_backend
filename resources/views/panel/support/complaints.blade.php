@extends('panel.layouts.master')

@section('title', textByLanguage('الشكاوى', 'Complaints'))
@section('page-title', textByLanguage('الشكاوى', 'Complaints'))

@php
    $r = fn ($n) => "panel.{$entity}.{$n}";
    $statuses = ['open', 'in_review', 'resolved', 'dismissed'];
    $abouts = ['driver', 'office', 'safety', 'other'];
    $tone = ['open' => 'warning', 'in_review' => 'primary', 'resolved' => 'success', 'dismissed' => 'gray'];
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('شكاوى الركّاب', 'Rider complaints')"
        :subtitle="textByLanguage('شكاوى السائقين والمكاتب والسلامة الواردة من التطبيق', 'Driver, office and safety complaints from the app')" />

    <div class="p-faq-stats" style="grid-template-columns:repeat(4,1fr);">
        <x-panel.stat :label="textByLanguage('مفتوحة', 'Open')" :value="$counts['open']" icon="bi-envelope-open" />
        <x-panel.stat :label="textByLanguage('عاجلة', 'Urgent')" :value="$counts['urgent']" icon="bi-exclamation-octagon" :variant="$counts['urgent'] ? 'danger' : null" />
        <x-panel.stat :label="textByLanguage('مُعالَجة', 'Resolved')" :value="$counts['resolved']" icon="bi-check2-circle" />
        <x-panel.stat :label="textByLanguage('الإجمالي', 'Total')" :value="$counts['total']" icon="bi-flag" />
    </div>

    <div class="p-card">
        <form method="GET" action="{{ route($r('complaints.index')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الحالات', 'All statuses') }}</option>
                @foreach($statuses as $s)<option value="{{ $s }}" @selected($statusFilter === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>@endforeach
            </select>
            <select name="about" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الأنواع', 'All types') }}</option>
                @foreach($abouts as $a)<option value="{{ $a }}" @selected($aboutFilter === $a)>{{ ucfirst($a) }}</option>@endforeach
            </select>
            @if($statusFilter || $aboutFilter)<a href="{{ route($r('complaints.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
        </form>

        @if($complaints->count())
            <x-panel.table :headers="array_filter([
                '#', shardIsAll() ? textByLanguage('الدولة', 'Country') : null, textByLanguage('العميل', 'Customer'), textByLanguage('الرحلة', 'Trip'),
                textByLanguage('بخصوص', 'About'), textByLanguage('الأولوية', 'Priority'),
                textByLanguage('الوصف', 'Description'), textByLanguage('الحالة', 'Status'), textByLanguage('التاريخ', 'When'),
            ], fn($h) => $h !== null)">
                @foreach($complaints as $c)
                    <tr @if($c->priority === 'urgent' && !in_array($c->status, ['resolved', 'dismissed'])) style="background:rgba(220,38,38,.06);" @endif>
                        <td class="p-row-id">#{{ $c->id }}</td>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($c) ?: '—' }}</x-panel.badge></td>@endif
                        <td>
                            <div class="p-cell-main"><div>
                                <strong>{{ $c->user ? trim(($c->user->firstName ?? '') . ' ' . ($c->user->lastName ?? '')) : '—' }}</strong>
                                <span class="p-cell-sub" dir="ltr">{{ $c->user->phoneNumber ?? '' }}</span>
                            </div></div>
                        </td>
                        <td>{{ $c->booking_id ? '#' . $c->booking_id : '—' }}</td>
                        <td>
                            @if($c->about === 'safety')
                                <x-panel.badge tone="danger"><i class="bi bi-shield-exclamation"></i> {{ textByLanguage('سلامة', 'Safety') }}</x-panel.badge>
                            @else
                                {{ ucfirst($c->about) }}
                            @endif
                        </td>
                        <td><x-panel.badge :tone="$c->priority === 'urgent' ? 'danger' : 'gray'">{{ ucfirst($c->priority) }}</x-panel.badge></td>
                        <td style="max-width:280px;">{{ \Illuminate\Support\Str::limit($c->description, 90) }}</td>
                        <td>
                            <form method="POST" action="{{ route($r('complaints.status'), $c->id) }}">
                                @csrf
                                @if(shardOf($c))<input type="hidden" name="country" value="{{ shardOf($c) }}">@endif
                                <select name="status" onchange="this.form.submit()" class="p-search__select">
                                    @foreach($statuses as $s)<option value="{{ $s }}" @selected($c->status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>@endforeach
                                </select>
                            </form>
                        </td>
                        <td>{{ $c->created_at ? \Illuminate\Support\Carbon::parse($c->created_at)->diffForHumans() : '—' }}</td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-flag"></i> {{ textByLanguage('لا توجد شكاوى', 'No complaints') }}</p>
        @endif
    </div>

@endsection
