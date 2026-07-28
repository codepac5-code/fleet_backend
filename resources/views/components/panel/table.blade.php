@props(['headers' => []])

<div class="p-table-wrap">
    <table {{ $attributes->merge(['class' => 'p-table']) }}>
        @if(count($headers))
            <thead>
                <tr>
                    @foreach($headers as $h)<th>{{ $h }}</th>@endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
