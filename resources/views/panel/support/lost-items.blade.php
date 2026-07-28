@extends('panel.layouts.master')

@section('title', textByLanguage('المفقودات', 'Lost items'))
@section('page-title', textByLanguage('المفقودات', 'Lost items'))

@php
    $r = fn ($n) => "panel.{$entity}.{$n}";
    // The governed lifecycle (see App\Http\Core\Const\LostItemStatus).
    $statuses = ['reported', 'acknowledged', 'matched', 'ready_for_handback', 'returned', 'unresolved', 'cancelled'];
    $tone = [
        'reported' => 'warning', 'acknowledged' => 'primary', 'matched' => 'primary',
        'ready_for_handback' => 'primary', 'returned' => 'success', 'unresolved' => 'danger', 'cancelled' => 'muted',
    ];
    $label = fn ($s) => ucfirst(str_replace('_', ' ', $s));
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('الأغراض المفقودة', 'Lost & found')"
        :subtitle="textByLanguage('بلاغات الركّاب (مفقود) والسائقين (موجود) — طابِق وأعِد', 'Rider (lost) & driver (found) reports — match and return')" />

    <div class="p-faq-stats" style="grid-template-columns:repeat(5,1fr);">
        <x-panel.stat :label="textByLanguage('مُبلَّغة', 'Reported')" :value="$counts['reported']" icon="bi-box-seam" :variant="$counts['reported'] ? 'primary' : null" />
        <x-panel.stat :label="textByLanguage('مُطابَقة', 'Matched')" :value="$counts['matched']" icon="bi-link-45deg" />
        <x-panel.stat :label="textByLanguage('جاهزة للتسليم', 'Ready')" :value="$counts['ready']" icon="bi-hourglass-split" />
        <x-panel.stat :label="textByLanguage('أُعيدت', 'Returned')" :value="$counts['returned']" icon="bi-check2-circle" />
        <x-panel.stat :label="textByLanguage('الإجمالي', 'Total')" :value="$counts['total']" icon="bi-bag" />
    </div>

    <div class="p-card">
        <form method="GET" action="{{ route($r('lost-items.index')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الحالات', 'All statuses') }}</option>
                @foreach($statuses as $s)<option value="{{ $s }}" @selected($statusFilter === $s)>{{ $label($s) }}</option>@endforeach
            </select>
            @if($statusFilter)<a href="{{ route($r('lost-items.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
        </form>

        @if($items->count())
            <x-panel.table :headers="array_filter([
                '#', shardIsAll() ? textByLanguage('الدولة', 'Country') : null, textByLanguage('المُبلِّغ', 'Reporter'), textByLanguage('العميل', 'Customer'), textByLanguage('الرحلة', 'Trip'),
                textByLanguage('الفئة', 'Category'), textByLanguage('الوصف', 'Description'),
                textByLanguage('المطابقة', 'Match'), textByLanguage('الحالة', 'Status'), textByLanguage('التاريخ', 'When'),
            ], fn($h) => $h !== null)">
                @foreach($items as $it)
                    @php
                        $u = $users[$it->user_id] ?? null;
                        $isDriver = $it->reporter_type === 'driver';
                        $suggested = $suggestions[$it->id] ?? collect();
                        $nexts = $transitions[$it->status] ?? [];
                    @endphp
                    <tr>
                        <td class="p-row-id">#{{ $it->id }}</td>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($it) ?: '—' }}</x-panel.badge></td>@endif
                        <td>
                            <x-panel.badge :tone="$isDriver ? 'primary' : 'warning'">
                                <i class="bi {{ $isDriver ? 'bi-car-front' : 'bi-person' }}"></i>
                                {{ $isDriver ? textByLanguage('سائق · موجود', 'Driver · found') : textByLanguage('راكب · مفقود', 'Rider · lost') }}
                            </x-panel.badge>
                        </td>
                        <td>
                            <div class="p-cell-main"><div>
                                <strong>{{ $u ? trim(($u->firstName ?? '') . ' ' . ($u->lastName ?? '')) : '—' }}</strong>
                                <span class="p-cell-sub" dir="ltr">{{ $u->phoneNumber ?? '' }}</span>
                            </div></div>
                        </td>
                        <td>{{ $it->booking_id ? '#' . $it->booking_id : '—' }}</td>
                        <td><x-panel.badge tone="primary">{{ $it->category }}</x-panel.badge></td>
                        <td style="max-width:240px;">{{ \Illuminate\Support\Str::limit($it->description, 80) ?: '—' }}</td>
                        <td>
                            @if($it->matched_item_id)
                                <x-panel.badge tone="success"><i class="bi bi-link-45deg"></i> #{{ $it->matched_item_id }}</x-panel.badge>
                            @elseif($suggested->count())
                                {{-- Auto-suggested opposite-side reports; the office confirms. --}}
                                <form method="POST" action="{{ route($r('lost-items.match'), $it->id) }}" style="display:flex;gap:4px;align-items:center;">
                                    @csrf
                                    @if(shardOf($it))<input type="hidden" name="country" value="{{ shardOf($it) }}">@endif
                                    <select name="matched_item_id" class="p-search__select">
                                        @foreach($suggested as $sug)
                                            <option value="{{ $sug->id }}">#{{ $sug->id }} · {{ $sug->category }} · {{ $sug->reporter_type === 'driver' ? textByLanguage('سائق', 'driver') : textByLanguage('راكب', 'rider') }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="p-btn p-btn--sm p-btn--primary" title="{{ textByLanguage('تأكيد المطابقة', 'Confirm match') }}"><i class="bi bi-link-45deg"></i></button>
                                </form>
                            @else
                                <span class="p-cell-sub">—</span>
                            @endif
                        </td>
                        <td>
                            @if(count($nexts))
                                {{-- Only the LEGAL next transitions for this status are offered. --}}
                                <form method="POST" action="{{ route($r('lost-items.status'), $it->id) }}">
                                    @csrf
                                    @if(shardOf($it))<input type="hidden" name="country" value="{{ shardOf($it) }}">@endif
                                    <select name="status" onchange="this.form.submit()" class="p-search__select">
                                        <option value="" selected disabled>{{ $label($it->status) }} →</option>
                                        @foreach($nexts as $to)<option value="{{ $to }}">{{ $label($to) }}</option>@endforeach
                                    </select>
                                </form>
                            @else
                                <x-panel.badge :tone="$tone[$it->status] ?? 'muted'">{{ $label($it->status) }}</x-panel.badge>
                            @endif
                        </td>
                        <td>{{ $it->created_at ? \Illuminate\Support\Carbon::parse($it->created_at)->diffForHumans() : '—' }}</td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-bag-check"></i> {{ textByLanguage('لا توجد مفقودات', 'No lost items') }}</p>
        @endif
    </div>

@endsection
