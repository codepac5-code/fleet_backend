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

        // Where a live item leads, resolved server-side (routes are permission-
        // gated). Built HERE, not inside @json(...): Blade's directive-argument
        // parser mishandles a closure with nested arrays and truncates it.
        $rtRoutes = collect([
            'bookings.live' => 'booking.live',
            'subscriptions.index' => 'subscriptions.index',
            'payouts.index' => 'payouts.index',
            'wallet.transactions' => 'wallet.transactions',
            'ride-ratings.index' => 'ride-ratings.index',
        ])->mapWithKeys(function ($name, $key) use ($rtEntity) {
            $route = 'panel.' . $rtEntity . '.' . $name;

            return [$key => \Illuminate\Support\Facades\Route::has($route) ? route($route) : null];
        })->filter()->all();
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

    {{-- Where a live item leads (built in the @php block above). --}}
    window.FLEET_PANEL_ROUTES = @json($rtRoutes ?? []);
</script>
<script src="{{ asset('js/socket.io.min.js') }}"></script>
<script src="{{ asset('panel/js/panel-realtime.js') }}"></script>
<script src="{{ asset('panel/js/panel-live-feed.js') }}"></script>
@endif
