@props(['title' => null])

<div {{ $attributes->merge(['class' => 'p-card']) }}>
    @if($title || isset($actions))
        <div class="p-card__head">
            @if($title)<h3 class="p-card__title">{{ $title }}</h3>@endif
            @isset($actions)<div class="p-card__actions">{{ $actions }}</div>@endisset
        </div>
    @endif
    {{ $slot }}
</div>
