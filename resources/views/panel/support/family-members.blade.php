@extends('panel.layouts.master')

@section('title', textByLanguage('أفراد العائلة', 'Family members'))
@section('page-title', textByLanguage('أفراد العائلة', 'Family members'))

@php
    $r = fn ($n) => "panel.admin.{$n}";
    $types = ['minor', 'elder', 'adult'];
    $typeTone = ['minor' => 'warning', 'elder' => 'primary', 'adult' => 'gray'];
    $typeLabel = ['minor' => textByLanguage('قاصر', 'Minor'), 'elder' => textByLanguage('كبير سن', 'Elder'), 'adult' => textByLanguage('بالغ', 'Adult')];
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('حسابات العائلة والمرافقين', 'Family & dependent accounts')"
        :subtitle="textByLanguage('أفراد يديرهم الوصيّ من حساب الراكب (قاصر/كبير سن)', 'Dependents a guardian manages from the rider account')" />

    <div class="p-faq-stats" style="grid-template-columns:repeat(4,1fr);">
        <x-panel.stat :label="textByLanguage('قاصرون', 'Minors')" :value="$counts['minor']" icon="bi-person-arms-up" />
        <x-panel.stat :label="textByLanguage('كبار السن', 'Elders')" :value="$counts['elder']" icon="bi-person-wheelchair" />
        <x-panel.stat :label="textByLanguage('بالغون', 'Adults')" :value="$counts['adult']" icon="bi-person" />
        <x-panel.stat :label="textByLanguage('الإجمالي', 'Total')" :value="$counts['total']" icon="bi-people" />
    </div>

    <div class="p-card">
        <form method="GET" action="{{ route($r('family-members.index')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="type" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الأنواع', 'All types') }}</option>
                @foreach($types as $t)<option value="{{ $t }}" @selected($typeFilter === $t)>{{ $typeLabel[$t] }}</option>@endforeach
            </select>
            @if($typeFilter)<a href="{{ route($r('family-members.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
        </form>

        @if($members->count())
            <x-panel.table :headers="[
                textByLanguage('الوصيّ (الحساب الرئيسي)', 'Guardian (main account)'),
                textByLanguage('الفرد', 'Member'),
                textByLanguage('النوع', 'Type'),
                textByLanguage('يتطلّب موافقة', 'Approval'),
                textByLanguage('مشاركة تلقائية', 'Auto-share'),
                textByLanguage('التاريخ', 'When'),
            ]">
                @foreach($members as $m)
                    @php $g = $guardians[$m->user_id] ?? null; @endphp
                    <tr>
                        <td>
                            <div class="p-cell-main"><div>
                                <strong>{{ $g ? trim(($g->firstName ?? '') . ' ' . ($g->lastName ?? '')) : ('#' . $m->user_id) }}</strong>
                                <span class="p-cell-sub" dir="ltr">{{ $g->phoneNumber ?? '' }}</span>
                            </div></div>
                        </td>
                        <td>
                            <strong>{{ $m->name }}</strong>
                            <span class="p-cell-sub" dir="ltr">{{ $m->phone }}</span>
                        </td>
                        <td><x-panel.badge :tone="$typeTone[$m->type] ?? 'gray'">{{ $typeLabel[$m->type] ?? ucfirst($m->type) }}</x-panel.badge></td>
                        <td>{!! $m->approval_required ? '<i class="bi bi-check-circle-fill" style="color:var(--p-success)"></i>' : '<i class="bi bi-dash"></i>' !!}</td>
                        <td>{!! $m->auto_share ? '<i class="bi bi-check-circle-fill" style="color:var(--p-success)"></i>' : '<i class="bi bi-dash"></i>' !!}</td>
                        <td>{{ $m->created_at ? \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() : '—' }}</td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-people"></i> {{ textByLanguage('لا توجد حسابات عائلية', 'No family accounts') }}</p>
        @endif
    </div>

@endsection
