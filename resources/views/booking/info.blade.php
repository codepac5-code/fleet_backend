@php
    $sitesetup = App\Models\Setting::where('type','site-setup')->where('key', 'site-setup')->first();
    $datetime = optional(json_decode(optional($sitesetup)->value));
    // $servicePriceTotal = ;
@endphp
{{-- @php
    dd($bookingdata->startLatitude, $bookingdata->startLongitude, $bookingdata->endLatitude, $bookingdata->endLongitude);
@endphp --}}
<x-master-layout>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDEutBE5WTbVYGM4uw58MrkdsfX1othIoQ"></script>

<input type="hidden" name="id" value="{{ $bookingdata->id }}">
<div class="card">
    <div class="card-body">
        <div class="card-body p-0">
    <div class="border-bottom pb-3 d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div>
            <h3 class="c1 mb-2">{{__('messages.book_id')}} {{ '#' . $bookingdata->id ?? '-'}}</h3>
            <p class="opacity-75 fz-12">
                {{__('messages.book_placed')}} {{ date("$datetime->date_format / $datetime->time_format", strtotime($bookingdata->created_at)) ?? '-'}}
                {{-- {{__('messages.book_placed')}} {{ date( strtotime($bookingdata->created_at)) ?? '-'}} --}}

            </p>
        </div>

        <div class="d-flex flex-wrap flex-xxl-nowrap gap-3" data-select2-id="select2-data-8-5c7s">

            <div class="w3-third">
                @if($bookingdata->count() == 0)
                    @hasanyrole('admin|demo_admin|provider')
                        <a href="{{ route('booking.assign_form',['id'=> $bookingdata->id ]) }}"
                        class="float-right btn btn-sm btn-primary loadRemoteModel"><i class="lab la-telegram-plane"></i>
                        {{ __('messages.assign') }}</a>
                    @endhasanyrole
                @endif
            </div>
            @if($bookingdata->paymentId != null)
            <a href="{{route('invoice_pdf',$bookingdata->id)}}" class="btn btn-primary" target="_blank">
                <i class="ri-file-text-line"></i>

                {{__('messages.invoice')}}
            </a>
            @endif
        </div>
    </div>

    <div class="pay-box">

        <div class="pay-method-details">
            <h3 class="mb-2">{{__('messages.payment_method')}}</h3>
            <h4 class="c1 mb-2">{{$bookingdata->payment->name ?? '--'}}</h4>
            <p><span>{{__('messages.amount')}} :
                </span><strong>{{!empty($bookingdata->totalAmount) ? getPriceFormat($bookingdata->totalAmount): 0}}</strong>
            </p>

        </div>




        <div class="pay-booking-details">
            <div class="row mb-2">
                <div class="col-sm-6"><span>{{__('messages.order_status')}} :</span></div>
                <div class="col-sm-6 align-text">
                    <span class="c1" id="booking_status__span">{{ $bookingdata->status}}</span>
                </div>
                @if($bookingdata->status === "cancelled")
                    <div class="col-sm-6"><span>{{__('messages.reason')}} :</span></div>
                     <div class="col-sm-6 align-text">
                        <span class="c1" id="booking_status__span">{{ $bookingdata->reason }}</span>
                    </div>
                @endif

            </div>
            <div class="row mb-2">
                <div class="col-sm-6"> <span>{{__('messages.payment_status')}} : </span></div>
                <div class="col-sm-6 align-text">
                    <span id="payment_status__span"
                        class="{{ $bookingdata->paymentStatus == 'paid' ? 'text-success' : 'text-danger' }}">
                        {{ $bookingdata->paymentStatus}}
                    </span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <h5>
                        {{__('messages.order_date')}} :
                    </h5>
                </div>
             <div class="col-sm-6 align-text">
                    <span id="service_schedule__span">{{ date("$datetime->date_format / $datetime->time_format", strtotime($bookingdata->created_at)) ?? '-'}}</span>
                    {{-- <span id="service_schedule__span"> {{ date( strtotime($bookingdata->created_at)) ?? '-'}}</span> --}}
                </div>
            </div>




        </div>
        </div>
    </div>

    <div class="col-12">
        <div class="horizontal-separator"></div>
    </div>

    <div id="map" style="width: 100%; height: 400px; margin-top: 20px;"></div>
    <div class="col-12">
        <div class="horizontal-separator"></div>
    </div>
    <div class="pay-box">



        <div class="pay-method-details">
            <h4 class="mb-2">{{__('messages.pick_up_point')}}</h4>
            {{-- <h5 class="c1 mb-2">{{__('messages.cash_after')}}</h5> --}}
            {{-- <p><span>{{__('messages.amount')}} : --}}
                </span><strong>{{$bookingdata->startAddress ? $bookingdata->startAddress: '--'}}</strong>
            </p>

            <h4 class="mb-2">{{__('messages.drop_off_point')}}</h4>
            {{-- <h5 class="c1 mb-2">{{__('messages.cash_after')}}</h5> --}}
            {{-- <p><span>{{__('messages.amount')}} : --}}
                </span><strong>{{$bookingdata->endAddress ? $bookingdata->endAddress: '--'}}</strong>
            </p>


        </div>

        <div class="pay-method-details">
            {{-- <h4 class="mb-2">{{__('messages.more_details')}}</h4> --}}
            <p><span>{{__('messages.subservice')}} :
            </span><strong>{{ $subservice->name ?  $subservice->name : '--'}}</strong>
             </p>
            {{-- <p><span>{{__('messages.start_at')}} :
                </span><strong>{{ date("$datetime->date_format / $datetime->time_format", strtotime($bookingdata->startAt)) ?? '-'}}</strong>
            </p>
                <p><span>{{__('messages.end_at')}} :
                </span><strong>{{ date("$datetime->date_format / $datetime->time_format", strtotime($bookingdata->endAt)) ?? '-'}}</strong>
            </p> --}}
            <p><span>{{__('messages.distance')}} :
                </span><strong>{{$bookingdata->distance ? $bookingdata->distance.' km' : '--'}}</strong>
            </p>
            <p><span>{{__('messages.time')}} :
            </span><strong>{{$bookingdata->time ? $bookingdata->time.' min' : '--'}}</strong>
          </p>
        </div>

        <div class="pay-method-details">
            {{-- <h4 class="mb-2">{{__('messages.more_details')}}</h4> --}}
            {{-- <h4 class="mb-2">{{__('messages.commissions')}}</h4> --}}

            <p><span>{{__('messages.fleet_commission')}} :
            </span><strong>{{ $bookingdata->fleetCommissionValue ?  $bookingdata->fleetCommissionValue : '--'}}</strong>
        </p>

        @if ($bookingdata->officeCommission != 0)
        <p><span>{{__('messages.office_commission')}} :
        </span><strong>{{ $bookingdata->officeCommissionValue ?  $bookingdata->officeCommissionValue : '--'}}</strong>
        </p>
        @endif

            <p><span>{{__('messages.driver_commission')}} :
                </span><strong>{{ $bookingdata->driverCommissionValue ?  $bookingdata->driverCommissionValue : '--'}}</strong>
            </p>

        </div>



        </div>


    </div>


    <div class="py-3 d-flex gap-3 flex-wrap customer-info-detail mb-2">



        @if($bookingdata->driverId != null)
        <div class="c1-light-bg radius-10 py-3 px-4 flex-grow-1">


            {{-- @foreach($bookingdata->handymanAdded as $booking) --}}
            <h4 class="mb-2">{{__('messages.Driver_information')}}</h4>
            <h5 class="c1 mb-3">{{optional($driver)->firstName.' '.optional($driver)->lastName ?? '-'}}</h5>
            <ul class="list-info">

                <li>
                    <span class="material-icons  customer-info-text">{{__('messages.phone_information')}}</span>
                    <a href="" class=" customer-info-value">
                        <p class="mb-0">{{optional($driver)->phoneNumber ?? '-'}}</p>
                    </a>
                </li>
                <li>
                    <span class="material-icons  customer-info-text">{{__('messages.vehicle_brand')}}</span>
                    <a href="" class=" customer-info-value">
                        <p class="mb-0">{{optional($car)->vehicleBrand ?? '-'}}</p>
                    </a>
                </li>

                <li>
                    <span class="material-icons  customer-info-text">{{__('messages.plate')}}</span>
                    <a href="" class=" customer-info-value">
                        <p class="mb-0">{{optional($car)->plate ?? '-'}}</p>
                    </a>
                </li>

                <li>
                    <span class="material-icons  customer-info-text">{{__('messages.car_owner')}}</span>
                    <a href="" class=" customer-info-value">
                        @if ($driver->car_owner && !$driver->free_driver)
                            <p class="mb-0">{{__('messages.yes')}}</p>
                        @elseif (!$driver->car_owner && !$driver->free_driver)
                        <p class="mb-0">{{__('messages.no')}}</p>

                        @endif

                    </a>
                </li>
            </ul>

            {{-- @endforeach --}}
        </div>
        @endif


        @if (auth()->user()->hasAnyRole(['super-admin']))
        @if ($withOffice)
        <div class="c1-light-bg radius-10 py-3 px-4 flex-grow-1">
            <h4 class="mb-2">{{__('messages.Office_information')}}</h4>
            <h5 class="c1 mb-3">{{optional($office)->officeName ?? '-'}}</h5>
            <ul class="list-info">
                <li>
                    <span class="material-icons customer-info-text">{{__('messages.phone_information')}}</span>
                    <a href="tel:{{optional($user)->phoneNumber}}" class="customer-info-value">
                        <p class="mb-0">{{ optional($office)->contactNumber ?? '-' }}</p>
                    </a>
                </li>
                <li>
                    <span class="material-icons customer-info-text">{{__('messages.rating')}}</span>
                    {{-- <a href="tel:{{optional($user)->rating}}" class="customer-info-value"> --}}
                        <p class="mb-0">{{ optional($office)->rating ?? '-' }}</p>
                    {{-- </a> --}}
                </li>
                <li>
                    <span class="material-icons customer-info-text">{{__('messages.joining_date')}}</span>
                    {{-- <p class="customer-info-text">{{ date("$datetime->date_format / $datetime->time_format", strtotime($office->created_at)) ?? '-'}}</p> --}}
                </li>
            </ul>
        </div>
        @endif
        @endif


        <div class="c1-light-bg radius-10 py-3 px-4 flex-grow-1">
            <h4 class="mb-2">{{__('messages.customer_information')}}</h4>
            <h5 class="c1 mb-3">{{optional($user)->firstName.' ' .optional($user)->firstName?? '-'}}</h5>
            <ul class="list-info">
                <li>
                    <span class="material-icons customer-info-text">{{__('messages.phone_information')}}</span>
                    <a href="tel:{{optional($user)->phoneNumber}}" class="customer-info-value">
                        <p class="mb-0">{{ optional($user)->phoneNumber ?? '-' }}</p>
                    </a>
                </li>
                <li>
                    <span class="material-icons customer-info-text">{{__('messages.rating')}}</span>
                    {{-- <a href="tel:{{optional($user)->rating}}" class="customer-info-value"> --}}
                        <p class="mb-0">{{ optional($user)->rating ?? '-' }}</p>
                    {{-- </a> --}}
                </li>
                <li>
                    <span class="material-icons customer-info-text">{{__('messages.joining_date')}}</span>
                    <p class="customer-info-text">{{ date("$datetime->date_format / $datetime->time_format", strtotime($user->created_at)) ?? '-'}}</p>
                </li>
            </ul>
        </div>

    </div>


    <script>
        function initMap() {
            var startPoint = { lat: {{ $bookingdata->startLatitude }}, lng: {{ $bookingdata->startLongitude }} };
            var endPoint = { lat: {{ $bookingdata->endLatitude }}, lng: {{ $bookingdata->endLongitude }} };

            var map = new google.maps.Map(document.getElementById("map"), {
                zoom: 12,
                center: startPoint
            });

            var directionsService = new google.maps.DirectionsService();
            var directionsRenderer = new google.maps.DirectionsRenderer({
                map: map
            });

            var request = {
                origin: startPoint,
                destination: endPoint,
                travelMode: google.maps.TravelMode.DRIVING
            };

            directionsService.route(request, function (result, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    directionsRenderer.setDirections(result);
                }
                else {
                    console.error("google maps error", status);
                }
            });
        }

        window.onload = initMap;
    </script>




    {{-- @if($bookingdata->bookingExtraCharge->count() > 0 )
        <h3 class="mb-3 mt-3">{{__('messages.extra_charge')}}</h3>
        <div class="table-responsive border-bottom">
            <table class="table text-nowrap align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-lg-3">{{__('messages.title')}}</th>
                        <th>{{__('messages.price')}}</th>
                        <th>{{__('messages.quantity')}}</th>
                        <th class="text-end">{{__('messages.total_amount')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookingdata->bookingExtraCharge as $chrage)
                    <tr>
                        <td class="text-wrap ps-lg-3">
                            <div class="d-flex flex-column">
                                <a href="" class="booking-service-link fw-bold">{{$chrage->title}}</a>
                            </div>
                        </td>
                        <td>{{getPriceFormat($chrage->price)}}</td>
                        <td>{{$chrage->qty}}</td>
                        <td class="text-end">{{getPriceFormat($chrage->price * $chrage->qty)}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif --}}

    {{-- @php
        $addonTotalPrice = 0;
    @endphp --}}

    {{-- @if($bookingdata->bookingAddonService->count() > 0 )
        <h3 class="mb-3 mt-3">{{__('messages.service_addon')}}</h3>
        <div class="table-responsive border-bottom">
            <table class="table text-nowrap align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-lg-3">{{__('messages.title')}}</th>
                        <th>{{__('messages.price')}}</th>
                        <th class="text-end">{{__('messages.total_amount')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookingdata->bookingAddonService as $addonservice)
                        @php
                            $addonTotalPrice += $addonservice->price;
                        @endphp
                    <tr>
                        <td class="text-wrap ps-lg-3">
                            <div class="d-flex flex-column">
                                <a href="" class="booking-service-link fw-bold">{{$addonservice->name}}</a>
                            </div>
                        </td>
                        <td>{{getPriceFormat($addonservice->price)}}</td>
                        <td class="text-end">{{getPriceFormat($addonservice->price)}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif --}}

    <style>
        /* الوضع العادي */
table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
    color: #333;
    background-color: #fff;
}

th {
    font-weight: bold;
    background-color: #f2f2f2;
}

/* الوضع الداكن */
body.dark table {
    border-color: rgba(255, 255, 255, 0.1);
}

body.dark th,
body.dark td {
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    color: #f0e68c; /* أصفر باهت ليتماشى مع #F8A609 */
    background-color: #1e1e2f;
}

body.dark th {
    background-color: #2b2b3d;
    color: #f8a609; /* اللون الأصفر الأساسي */
}

    </style>

    <h3 class="mb-3 mt-3">{{__('messages.booking_summery')}}</h3>
    <div class="table-responsive border-bottom">
        <table class="table text-nowrap align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-lg-3">{{__('messages.subservice')}}</th>
                    <th>{{__('messages.open_price')}}</th>
                    <th>{{__('messages.km_price')}}</th>
                    <th>{{__('messages.minute_price')}}</th>
                    {{-- <th>{{__('messages.quantity')}}</th> --}}
                    <th class="text-end">{{__('messages.sub_total')}}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-wrap ps-lg-3">
                        <div class="d-flex flex-column">
                            <a href=""
                                class="booking-service-link fw-bold">{{optional($bookingdata->subService)->name ?? '-'}}</a>
                        </div>
                    </td>
                    {{-- <td>{{!empty($bookingdata->quantity) ? $bookingdata->quantity : 0}}</td> --}}
                    <td class="text-end">{{getPriceFormat(($bookingdata->subService->openPrice))}}</td>
                    <td class="text-end">{{getPriceFormat(($bookingdata->subService->kmPrice))}}</td>
                    <td class="text-end">{{getPriceFormat(($bookingdata->subService->minutePrice))}}</td>
                    <td>{{ isset($bookingdata->amount) ? getPriceFormat($bookingdata->amount) : 0 }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="row justify-content-end mt-3">
        <div class="col-sm-10 col-md-6 col-xl-5">
            <div class="table-responsive bk-summary-table">
                <table class="table-sm title-color align-right w-100">
                    <tbody>
                        <tr>
                            <td>{{__('messages.price')}}</td>

                            <td class="bk-value">{{$subservice->openPrice }} +  {{'( '.$subservice->kmPrice }}  * {{$bookingdata->distance .' )' }} + {{ '( '.$subservice->minutePrice }} * {{$bookingdata->time .' )' }}  =
                                {{$total_price}}</td>
                        </tr>
                        {{-- @if($bookingdata->discount != null)
                        <tr>
                            <td>{{__('messages.discount')}} ({{$bookingdata->discount}}% off)</td>
                            <td class="bk-value text-success">-{{getPriceFormat($bookingdata->discount * $total_price )}}</td>
                        </tr>
                        @endif --}}
                        @if($bookingdata->couponId != null)
                        <tr>
                            <td>{{__('messages.coupon')}} ({{$bookingdata->discount}}% off)</td>
                            <td class="bk-value text-success">-{{getPriceFormat($bookingdata->discount * $total_price )}}</td>
                        </tr>
                        @endif

                        <tr class="grand-total">
                            <td><strong>{{__('messages.grand_total')}}</strong></td>
                            <td class="bk-value">
                                <h3>{{isset($bookingdata->totalAmount) ? getPriceFormat($bookingdata->totalAmount) : getPriceFormat($bookingdata->totalAmount)}}</h3>
                            </td>
                        </tr>
                        {{-- @if($bookingdata->service->is_enable_advance_payment == 1 )
                        <tr>
                            <td>{{__('messages.advance_payment_amount')}} ({{$bookingdata->service->advance_payment_amount}}%)</td>
                            <td class="text-right">{{getPriceFormat($bookingdata->advance_paid_amount)}}</td>
                        </tr>
                        <tr>
                            <td>{{__('messages.remaining_amount')}}</td>
                            <td class="text-right">{{getPriceFormat($bookingdata->totalAmount - $bookingdata->advance_paid_amount )}}</td>
                        </tr>
                        @endif  --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

{{-- <script>
$(document).on('change', '.bookingstatus', function() {

    var status = $(this).val();

    var id = $(this).attr('data-id');
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "{{ route('bookingStatus.update') }}",
        data: {
            'status': status,
            'bookingId': id
        },
        success: function(data) {}
    });
})

$(document).on('change', '.paymentStatus', function() {

    var status = $(this).val();

    var id = $(this).attr('data-id');
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "{{ route('bookingStatus.update') }}",
        data: {
            'status': status,
            'bookingId': id
        },
        success: function(data) {}
    });
})
</script> --}}


</x-master-layout>
