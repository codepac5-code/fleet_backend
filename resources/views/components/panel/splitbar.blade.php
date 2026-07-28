@props(['parts' => []])

@php
    $total = array_sum(array_map(fn ($p) => (float) ($p['value'] ?? 0), $parts)) ?: 1;
@endphp

<div {{ $attributes->merge(['class' => 'p-split']) }}>
    <div class="p-split__bar">
        @foreach($parts as $part)
            <span style="width: {{ round((float) ($part['value'] ?? 0) / $total * 100, 2) }}%;@if(!empty($part['color'])) background: {{ $part['color'] }};@endif"></span>
        @endforeach
    </div>
    <div class="p-split__legend">
        @foreach($parts as $part)
            <div class="p-split__item">
                <span class="p-split__dot" @if(!empty($part['color'])) style="background: {{ $part['color'] }}"@endif></span>
                <span>{{ $part['label'] ?? '' }}</span>
                <strong>{{ $part['value'] ?? 0 }}{{ !empty($part['suffix']) ? $part['suffix'] : '' }}</strong>
            </div>
        @endforeach
    </div>
</div>
