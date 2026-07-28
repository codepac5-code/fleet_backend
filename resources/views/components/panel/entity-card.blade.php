@props(['icon' => 'bi-box', 'title', 'status' => null])

<article {{ $attributes->merge(['class' => 'p-entity']) }}>
    <div class="p-entity__icon"><i class="bi {{ $icon }}"></i></div>
    <div class="p-entity__body">
        <h3>{{ $title }}</h3>
        {{ $slot }}
    </div>
    <div class="p-entity__side">
        @if($status)<x-panel.badge :status="$status" />@endif
        @isset($actions)<div class="p-entity__actions">{{ $actions }}</div>@endisset
    </div>
</article>
