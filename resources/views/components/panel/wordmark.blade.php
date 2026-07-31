@props([
    'product' => 'os',
    'tone' => 'dark',
    'tagline' => false,
    'size' => '2.2rem',
])

@php
    // One brand, four lockups: fleetOS is the platform, fleet.Office is what an
    // office signs into, fleet.DriverX is the driver app, and the bare fleet.
    // mark is used where the product is already obvious.
    $lockups = [
        'os'     => ['mark' => 'fleet', 'dot' => false, 'suffix' => 'OS'],
        'office' => ['mark' => 'fleet', 'dot' => true,  'suffix' => 'Office'],
        'driver' => ['mark' => 'fleet', 'dot' => true,  'suffix' => 'DriverX'],
        'plain'  => ['mark' => 'fleet', 'dot' => true,  'suffix' => null],
    ];

    $lockup = $lockups[$product] ?? $lockups['plain'];
    $file = public_path('panel/img/brand/' . $product . '.png');
@endphp

<span {{ $attributes->merge(['class' => 'p-wordmark p-wordmark--' . $tone]) }} style="--wm-size: {{ $size }};">
    @if(is_file($file))
        {{-- Real artwork when it has been dropped in; the type lockup below is
             the fallback so the panel is never unbranded. --}}
        <img src="{{ asset('panel/img/brand/' . $product . '.png') }}" alt="fleet {{ $lockup['suffix'] }}" class="p-wordmark__img">
    @else
        <span class="p-wordmark__type">
            <b>{{ $lockup['mark'] }}</b>@if($lockup['dot'])<i>.</i>@endif
            @if($lockup['suffix'])<em>{{ $lockup['suffix'] }}</em>@endif
        </span>
    @endif

    @if($tagline)
        <small class="p-wordmark__tagline">{{ textByLanguage('لتمكين مكاتب الأجرة السحابية', 'Empowering Cloud Taxi Offices') }}</small>
    @endif
</span>
