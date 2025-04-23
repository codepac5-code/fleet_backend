@php
    $sitesetup = App\Models\Setting::where('type','site-setup')->where('key', 'site-setup')->first();
    $datetime = optional(json_decode(optional($sitesetup)->value));
    // $servicePriceTotal = ;
@endphp
{{-- @php
    dd($order->startLatitude, $order->startLongitude, $order->endLatitude, $order->endLongitude);
@endphp --}}
<x-master-layout>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDEutBE5WTbVYGM4uw58MrkdsfX1othIoQ"></script>

<input type="hidden" name="id" value="{{ $order->id }}">
<div class="card">
    <div class="card-body">
        <div class="card-body p-0">
    <div class="border-bottom pb-3 d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div>
            <h3 class="c1 mb-2">{{__('messages.orderId')}} {{ '#' . $order->id ?? '-'}}</h3>
            <p class="opacity-75 fz-12">
                {{__('messages.book_placed')}} {{ date("$datetime->date_format / $datetime->time_format", strtotime($order->created_at)) ?? '-'}}
                {{-- {{__('messages.book_placed')}} {{ date( strtotime($order->created_at)) ?? '-'}} --}}

            </p>
        </div>
        
        <div class="d-flex flex-wrap flex-xxl-nowrap gap-3" data-select2-id="select2-data-8-5c7s">
            
            <div class="w3-third">
                @if($order->count() == 0)
                    @hasanyrole('admin|demo_admin|provider')
                        <a href="{{ route('booking.assign_form',['id'=> $order->id ]) }}"
                        class="float-right btn btn-sm btn-primary loadRemoteModel"><i class="lab la-telegram-plane"></i>
                        {{ __('messages.assign') }}</a>
                    @endhasanyrole
                @endif
            </div>
        </div> 
    </div>

    <div class="pay-box">
    <div class="pay-method-details">
        <h4 class="mb-2">{{__('messages.pick_up_point')}}</h4>
        {{-- <h5 class="c1 mb-2">{{__('messages.cash_after')}}</h5> --}}
        {{-- <p><span>{{__('messages.amount')}} : --}}
            </span><strong>{{$order->startAddress ? $order->startAddress: '--'}}</strong>
        </p>

        <h4 class="mb-2">{{__('messages.drop_off_point')}}</h4>
        {{-- <h5 class="c1 mb-2">{{__('messages.cash_after')}}</h5> --}}
        {{-- <p><span>{{__('messages.amount')}} : --}}
            </span><strong>{{$order->endAddress ? $order->endAddress: '--'}}</strong>
        </p>


    </div>

    <div class="pay-method-details">
        {{-- <h4 class="mb-2">{{__('messages.more_details')}}</h4> --}}
        <p><span>{{__('messages.driver')}} :
        </span><strong>{{ $driver->firstName ?  $driver->firstName.' '.$driver->lastName : '--'}}</strong>
         </p>
        {{-- <p><span>{{__('messages.start_at')}} :
            </span><strong>{{ date("$datetime->date_format / $datetime->time_format", strtotime($order->startAt)) ?? '-'}}</strong>
        </p>
            <p><span>{{__('messages.end_at')}} :
            </span><strong>{{ date("$datetime->date_format / $datetime->time_format", strtotime($order->endAt)) ?? '-'}}</strong>
        </p> --}}
        <p><span>{{__('messages.vehicle_brand')}} :
        </span><strong>{{$car->vehicleBrand ? $car->vehicleBrand : '--'}}</strong>
      </p>

        <p><span>{{__('messages.plate')}} :
            </span><strong>{{$car->plate ? $car->plate : '--'}}</strong>
        </p>

    </div>

    <div class="pay-method-details">
        {{-- <h4 class="mb-2">{{__('messages.more_details')}}</h4> --}}
        <p><span>{{__('messages.subservice')}} :
        </span><strong>{{ $subservice->name ?  $subservice->name : '--'}}</strong>
         </p>
        {{-- <p><span>{{__('messages.start_at')}} :
            </span><strong>{{ date("$datetime->date_format / $datetime->time_format", strtotime($order->startAt)) ?? '-'}}</strong>
        </p>
            <p><span>{{__('messages.end_at')}} :
            </span><strong>{{ date("$datetime->date_format / $datetime->time_format", strtotime($order->endAt)) ?? '-'}}</strong>
        </p> --}}
        <p><span>{{__('messages.distance')}} :
            </span><strong>{{$order->distance ? $order->distance.' km' : '--'}}</strong>
        </p>
        <p><span>{{__('messages.time')}} :
        </span><strong>{{$order->time ? $order->time.' min' : '--'}}</strong>
      </p>
    </div>



    </div>

    <div class="col-12">
        <div class="horizontal-separator"></div>
    </div>

    <div id="map" style="width: 100%; height: 700px; margin-top: 20px;"></div>
    <div class="col-12">
        <div class="horizontal-separator"></div>
    </div>
    <div class="pay-box">


        </div>
    </div>
</div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const orderId = document.querySelector("input[name='id']").value;
    const initialLat = parseFloat("{{$order->startLatitude}}");
    const initialLng = parseFloat("{{$order->startLongitude}}");
    const destinationLat = parseFloat("{{$order->endLatitude}}");
    const destinationLng = parseFloat("{{$order->endLongitude}}");

    let map, driverMarker, directionsService, directionsRenderer;

    function initMap() {
        const startPoint = { lat: initialLat, lng: initialLng };
        const endPoint = { lat: destinationLat, lng: destinationLng };

        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 12,
            center: startPoint,
            mapTypeId: "roadmap",
        });

        driverMarker = new google.maps.Marker({
            position: startPoint,
            map,
            icon: {
                url:  "/storage/system/images/car-map.png",
                scaledSize: new google.maps.Size(50, 50),
            },
        });

        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            map: map,
        });

        drawRoute(startPoint, endPoint);
    }

    function drawRoute(start, end) {
        const request = {
            origin: start,
            destination: end,
            travelMode: google.maps.TravelMode.DRIVING,
        };

        directionsService.route(request, function (result, status) {
            if (status === google.maps.DirectionsStatus.OK) {
                directionsRenderer.setDirections(result);
            } else {
                console.error("خطأ في رسم المسار:", status);
            }
        });
    }

    function updateDriverPosition(lat, lng) {
        const position = { lat, lng };

        if (!driverMarker) {
            driverMarker = new google.maps.Marker({
                position,
                map,
                icon: {
                    url: "/storage/system/images/car-map.png",
                    scaledSize: new google.maps.Size(50, 50),
                },
            });
        } else {
            driverMarker.setPosition(position);
        }

        map.setCenter(position); 
    }

    socket.emit("subscribe", `order.${orderId}`);

    socket.on(`order.${orderId}:driver_position_changed`, function (data) {
        updateDriverPosition(data.driverLatitude, data.driverLongitude);
    });

    window.addEventListener("beforeunload", function () {
        socket.emit("unsubscribe", `order.${orderId}`);
    });

    window.onload = initMap;
});


</script>
</x-master-layout>