@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'p-toolbar']) }}>
    <div class="p-toolbar__title">
        @if($title)<h2>{{ $title }}</h2>@endif
        @if($subtitle)<p>{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)<div class="p-toolbar__actions">{{ $actions }}</div>@endisset
</div>
