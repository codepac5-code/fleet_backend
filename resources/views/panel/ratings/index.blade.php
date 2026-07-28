@extends('panel.layouts.master')

@section('title', textByLanguage('التقييمات', 'Ratings'))
@section('page-title', textByLanguage('التقييمات', 'Ratings'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $rateeTypes = ['driver', 'office', 'user'];
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('تقييمات الرحلات', 'Ride ratings')"
        :subtitle="$isAdmin ? textByLanguage('كل التقييمات وفرز المنخفض منها', 'All ratings and low-score triage') : textByLanguage('تقييمات مكتبك وسائقيه', 'Ratings for your office and drivers')" />

    <div class="p-card">
        <form method="GET" action="{{ route($r('ride-ratings.index')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="max_stars" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل النجوم', 'All stars') }}</option>
                @foreach([1,2,3] as $ms)
                    <option value="{{ $ms }}" @selected($maxStars === $ms)>{{ textByLanguage('≤ ' . $ms . ' نجوم', '≤ ' . $ms . ' stars') }}</option>
                @endforeach
            </select>
            @if($isAdmin)
                <select name="ratee_type" onchange="this.form.submit()" class="p-search__select">
                    <option value="">{{ textByLanguage('الكل', 'All') }}</option>
                    @foreach($rateeTypes as $rt)
                        <option value="{{ $rt }}" @selected($rateeTypeFilter === $rt)>{{ ucfirst($rt) }}</option>
                    @endforeach
                </select>
            @endif
            @if($maxStars || $rateeTypeFilter)
                <a href="{{ route($r('ride-ratings.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>
            @endif
        </form>

        @if(count($ratings))
            <x-panel.table :headers="array_filter([
                shardIsAll() ? textByLanguage('الدولة', 'Country') : null,
                textByLanguage('الرحلة', 'Trip'),
                textByLanguage('المُقيَّم', 'Rated'),
                textByLanguage('النجوم', 'Stars'),
                textByLanguage('الملاحظات والوسوم', 'Feedback & tags'),
                textByLanguage('التاريخ', 'When'),
                textByLanguage('المتابعة', 'Follow-up'),
            ], fn($h) => $h !== null)">
                @foreach($ratings as $rt)
                    <tr @if($rt['stars'] <= 2) style="background:rgba(220,38,38,.06);" @endif>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($rt) ?: '—' }}</x-panel.badge></td>@endif
                        <td>#{{ $rt['booking_id'] }}</td>
                        <td>
                            <div class="p-cell-main">
                                <div>
                                    <strong>{{ ucfirst($rt['ratee_type']) }} #{{ $rt['ratee_id'] }}</strong>
                                    <span class="p-cell-sub">{{ textByLanguage('من', 'by') }} {{ $rt['rater_type'] }}</span>
                                </div>
                            </div>
                        </td>
                        <td dir="ltr" style="text-align:start;white-space:nowrap;">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $rt['stars'] ? '-fill' : '' }}" style="color:{{ $rt['stars'] <= 2 ? '#dc2626' : 'var(--p-accent)' }};font-size:.75rem;"></i>
                            @endfor
                        </td>
                        <td style="max-width:320px;">
                            <div>{{ $rt['comment'] ?: '—' }}</div>
                            @if(!empty($rt['tags']))
                                <div class="p-rate-tags">
                                    @foreach($rt['tags'] as $tag)@if(is_string($tag) && $tag !== '')<span class="p-rate-tag">{{ $tag }}</span>@endif @endforeach
                                </div>
                            @endif
                            @if($rt['favorite'] || $rt['book_again'] !== null)
                                <div class="p-rate-flags">
                                    @if($rt['favorite'])<span class="p-rate-flag p-rate-flag--fav"><i class="bi bi-heart-fill"></i> {{ textByLanguage('مفضّل', 'Favorite') }}</span>@endif
                                    @if($rt['book_again'] === true)<span class="p-rate-flag p-rate-flag--yes"><i class="bi bi-arrow-repeat"></i> {{ textByLanguage('سيعيد الحجز', 'Book again') }}</span>@endif
                                    @if($rt['book_again'] === false)<span class="p-rate-flag p-rate-flag--no"><i class="bi bi-x-circle"></i> {{ textByLanguage('لن يعيد', "Won't rebook") }}</span>@endif
                                </div>
                            @endif
                        </td>
                        <td>{{ $rt['at'] ? \Illuminate\Support\Carbon::parse($rt['at'])->diffForHumans() : '—' }}</td>
                        <td>
                            @if(in_array($rt['id'], $flaggedIds, true))
                                <x-panel.badge tone="warning"><i class="bi bi-flag-fill"></i> {{ textByLanguage('مُتابَع', 'Flagged') }}</x-panel.badge>
                            @elseif($rt['stars'] <= 2)
                                <form method="POST" action="{{ route($r('ride-ratings.flag'), $rt['id']) }}">
                                    @csrf
                                    <button type="submit" class="p-btn p-btn--soft"><i class="bi bi-flag"></i> {{ textByLanguage('علّم للمتابعة', 'Flag') }}</button>
                                </form>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty">
                <i class="bi bi-star"></i>
                {{ textByLanguage('لا توجد تقييمات', 'No ratings') }}
            </p>
        @endif
    </div>

@endsection
