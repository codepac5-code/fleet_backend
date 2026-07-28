@props([
    'label',
    'value',
    'icon' => 'bi-graph-up',
    'trend' => null,
    'trendUp' => true,
    'valueId' => null,
    'variant' => null,
    'wave' => false,
])

@php
    $classes = 'p-stat';
    if ($variant) {
        $classes .= ' p-stat--vivid p-stat--' . $variant;
        if ($wave) {
            $classes .= ' p-stat--wave';
        }
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    <div class="p-stat__icon"><i class="bi {{ $icon }}"></i></div>
    <div class="p-stat__body">
        <p class="p-stat__label">{{ $label }}</p>
        <p class="p-stat__value" @if($valueId) id="{{ $valueId }}" @endif>{{ $value }}</p>
        @if($trend !== null)
            <span class="p-stat__trend {{ $trendUp ? 'is-up' : 'is-down' }}">
                <i class="bi {{ $trendUp ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                {{ $trend }}
            </span>
        @endif
    </div>
</div>
