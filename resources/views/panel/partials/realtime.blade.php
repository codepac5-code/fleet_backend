@php
    use App\Http\Core\GeoServices\ShardManager;
    use App\Http\Core\Classes\Event\StaffRealtimeToken;

    $rtGateway = (string) config('services.realtime.gateway_url', '');
    $rtNode = $rtGateway !== '' ? ShardManager::current() : null;
    $rtReady = false;

    if ($rtNode !== null) {
        $rtShard = ShardManager::shardKeyFor($rtNode);
        $rtCountry = (string) ($rtNode->country_code ?? '');
        $rtEntity = $entity ?? 'admin';
        $rtUser = auth()->guard($rtEntity)->user();

        if ($rtEntity === 'admin') {
            $rtType = 'admin';
            $rtId = 0;
            $rtChannels = [$rtShard . '.admin'];
            $rtReady = $rtShard !== '';
        } else {
            $rtOfficeId = $rtEntity === 'office'
                ? (int) ($rtUser->id ?? 0)
                : (int) ($rtUser->officeId ?? 0);
            $rtType = 'office';
            $rtId = $rtOfficeId;
            $rtChannels = [$rtShard . '.office.' . $rtOfficeId];
            $rtReady = $rtShard !== '' && $rtOfficeId > 0;
        }
    }
@endphp

@if($rtReady)
<script>
    window.FLEET_RT = {
        url: @json($rtGateway),
        token: @json(StaffRealtimeToken::mint($rtType, $rtId, $rtShard)),
        country: @json($rtCountry),
        channels: @json($rtChannels)
    };
</script>
<script src="{{ asset('js/socket.io.min.js') }}"></script>
<script src="{{ asset('panel/js/panel-realtime.js') }}"></script>
@endif
