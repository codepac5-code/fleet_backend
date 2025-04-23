@php
$sitesetup = App\Models\Setting::where('type','site-setup')->where('key', 'site-setup')->first();
$datetime = $sitesetup ? json_decode($sitesetup->value) : null;
@endphp
<x-master-layout>

@if( $auth_user->hasAnyRole(['super-admin']) )     
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <a  href="{{ route('office.index') }}">
                                <div class="taxi-card">
                                    <div class="taxi-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="taxi-info">
                                        <h3>{{__('messages.offices')}}</h3>
                                        <div class="d-flex gap-2">
                                        <p id="offices">{{$data['dashboard']['count_total_office'] }}</p>
                                        <p>{{' '.__('messages.office')}}</p>
                                    </div>
                                    </div>
                                </div>
                        </a>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <a href="{{ route('user.index') }}">
                                <div class="taxi-card">
                                    <div class="taxi-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="taxi-info">
                                        <h3>{{__('messages.users')}}</h3>
                                        <div class="d-flex gap-2">
                                        <p id="users">{{$data['dashboard']['count_total_user'] }}</p>
                                        <p>{{' '.__('messages.user')}}</p>
                                    </div>
                                    </div>
                                </div>
                        </a>
                    </div>



                    
                    <div class="col-lg-3 col-md-6">
                        <a  href="{{ route('service.index') }}">
                                <div class="taxi-card">
                                    <div class="taxi-icon">
                                        <i class="fas fa-handshake"></i> 
                                    </div>
                                    <div class="taxi-info">
                                        <h3>{{__('messages.services')}}</h3>
                                        <div class="d-flex gap-2">
                                        <p id="services">{{$data['dashboard']['count_total_service']}}</p>
                                        <p>{{' '.__('messages.service')}}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <a  href="{{ route('driver.index') }}"> 
                            <div class="taxi-card">
                                <div class="taxi-icon">
                                    <i class="fas fa-taxi"></i>
                                   </div>
                                <div class="taxi-info">
                                    <h3>{{__('messages.drivers')}}</h3>
                                    <div class="d-flex gap-2">
                                    <p id="drivers">{{$data['dashboard']['count_total_driver']}}</p> 
                                    <p>{{' '.__('messages.driver')}}</p>
                                   </div>
                                </div>
                            </div>
                        </a>
                    </div>


                    
                </div>    
            </div>
        </div>
    </div>




    <div class="col-md-12 mt-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="">{{__('messages.monthly_revenue')}}</h4>
                </div>
                <div id="monthly-revenue" class="custom-chart"></div>
            </div>
        </div>
    </div>




            <div class="col-md-12 mt-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-start align-items-center"> 
                            <div class="event-icon-wrapper">
                                <svg width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11" stroke="white" stroke-width="2" fill="none"/>
                                    <circle cx="12" cy="12" r="7" stroke="white" stroke-width="2" fill="none" stroke-dasharray="56,56" stroke-dashoffset="0">
                                        <animate attributeName="stroke-dashoffset" from="0" to="112" dur="1.5s" repeatCount="indefinite"/>
                                    </circle>
                                </svg>                                
                                
                            </div>
                            <div class=" col-md-6 "> 
                                <h4 > {{__('messages.live_events')}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
    

            

            <div class="container d-flex justify-content-center">
                <div class="trip-status-container">
                    <div class="trip-card finished">
                        <div class="trip-icon">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <div class="trip-info">
                            <h3>{{ __('messages.completed_rides') }}</h3>
                            <p id="completed-ride">{{ $data['system_completed_rides'] }} {{ __('messages.ride') }}</p>
                        </div>
                    </div>
            
                    <div class="trip-card ongoing">
                        <div class="trip-icon">
                            <i class="fas fa-car-side"></i>
                        </div>
                        <div class="trip-info">
                            <h3>{{ __('messages.ongoing_rides') }}</h3>
                            <p id="ongoing-ride">{{ $data['system_ongoing_rides'] }} {{ __('messages.ride') }}</p>
                        </div>
                    </div>
            
                    <div class="trip-card pending">
                        <div class="trip-icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="trip-info">
                            <h3>{{ __('messages.pending_orders') }}</h3>
                            <p id="pending-ride">{{ $data['system_pending_rides'] }} {{ __('messages.order') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            


            <div class="col-md-12 mt-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-start align-items-center"> 
                            <div class="event-icon-wrapper">
                                <i class="fas fa-chart-line" style="font-size: 24px; color: white; animation: bounceUp 2s infinite;"></i>
                            </div>
                            <div class="col-md-6"> 
                                <h4> {{ __('messages.wallet_status_and_dues') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            







<div class="container">
    <div class="row align-items-start">

        <div class="col-lg-7">
            <h2 class="stats-title"><span>{{ __('messages.financial_revenue') }}</span></h2>
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="finance-card withdrawn">
                        <div class="finance-icon"><i class="fas fa-wallet"></i></div>
                        <div class="finance-info">
                            <h3>{{ __('messages.withdrawn_amount') }}</h3>
                            <p id="withdrawn-amount">{{ $data['withdrawn-amount'] }}</p>
                        </div>
                    </div>
                </div>

                {{-- {{ getPriceFormat(15) }} --}}
                <div class="col-md-6">
                    <div class="finance-card available">
                        <div class="finance-icon"><i class="fas fa-hand-holding-usd"></i></div>
                        <div class="finance-info">
                            <h3>{{ __('messages.available_amount') }}</h3>
                            <p id="available-amount">{{ $data['available-amount'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="horizontal-separator"></div>
                </div>

                <div class="col-md-6">
                    <div class="finance-card pending">
                        <div class="finance-icon"><i class="fas fa-clock"></i></div>
                        <div class="finance-info">
                            <h3>{{ __('messages.pending_withdrawals') }}</h3>
                            <p id="pending-amount">{{ $data['pending-amount'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="finance-card total">
                        <div class="finance-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="finance-info">
                            <h3>{{ __('messages.total_earnings') }}</h3>
                            <p id="total-amount">{{ $data['total-amount'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="vertical-separator"></div>

        <div class="col-lg-4">
            <h2 class="stats-title"><span>{{ __('messages.due_amounts') }}</span></h2>
            <div class="row g-2">
                <div class="col-12">
                    <div class="finance-card company-due">
                        <div class="finance-icon"><i class="fas fa-building"></i></div>
                        <div class="finance-info">
                            <h3>{{ __('messages.company_due') }}</h3>
                            <p id="office-due-amount">{{ $data['offices-due-amount'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="horizontal-separator"></div>
                </div>
                <div class="col-12">
                    <div class="finance-card driver-due">
                        <div class="finance-icon"><i class="fas fa-taxi"></i></div>
                        <div class="finance-info">
                            <h3>{{ __('messages.driver_due') }}</h3>
                            <p id="driver-due-amount">{{ $data['drivers-due-amount'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>



<div class="col-12">
    <div class="horizontal-separator"></div>
</div>












            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="card top-providers">
                        <div class="card-header d-flex justify-content-between gap-10">
                            <h4 class="font-weight-bold">{{ __('messages.recent_Office') }}</h4>
                            <a href="{{ route('office.index') }}" class="btn-link btn-link-hover"><u>{{__('messages.view_all')}}</u></a>
                        </div>
                        <div class="card-body p-0">
                            <ul class="common-list list-unstyled">
                                @foreach($offices as $office)
                                <li style="pointer-events:none;">
                                    <div class="media gap-3">
                                        <div class="h-avatar is-medium h-5">
                                            <img class="avatar-50 rounded-circle bg-light" alt="user-icon" src="{{ $office->logo }}">
                                        </div>
                                        <div class="media-body ">
                                            <h5><span class="font-weight-bold">{{!empty($office->officeName) ? $office->officeName : '-'}}</span> </h5>
                                            <span class="common-list_rating d-flex gap-1">
                                                <i class="ri-star-s-fill"></i>
                                                {{round(3.2, 1)}}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            
                <div class="col-md-4 col-sm-6">
                    <div class="card top-providers">
                        <div class="card-header d-flex justify-content-between gap-10">
                            <h4 class="font-weight-bold">{{ __('messages.recent_customer') }}</h4>
                            <a href="{{ route('user.index') }}" class="btn-link btn-link-hover"><u>{{__('messages.view_all')}}</u></a>
                        </div>
                        <div class="card-body p-0">
                            <ul class="common-list list-unstyled">
                                @foreach($users as $customer) 
                                <li style="pointer-events:none;">
                                    <div class="media gap-3">
                                        <div class="h-avatar is-medium h-5">
                                            <img class="avatar-50 rounded-circle bg-light" alt="user-icon" src="{{ $customer->photo }}">
                                        </div>
                                        <div class="media-body ">
                                            <h5><span class="font-weight-bold">{{!empty($customer->firstName) ? $customer->firstName .' '.$customer->lastName : '-'}}</span>  </h5>
                                            <span>{{
                                                optional($datetime)->date_format && optional($datetime)->time_format
                                                ? date(optional($datetime)->date_format, strtotime($customer->created_at)) . ' / ' . date(optional($datetime)->time_format, strtotime($customer->created_at))
                                                : ''
                                            }}</span>
                                        </div>
                                    </div>
                                </li>
                                @endforeach 
                            </ul>
                        </div>
                    </div>
                </div>
            
                <div class="col-md-4 col-sm-6">
                    <div class="card recent-activities">
                        <div class="card-header d-flex justify-content-between gap-10">
                            <h4>{{__('messages.recent_booking')}}</h4>
                            <a href="{{ route('booking.index') }}" class="btn-link btn-link-hover"><u>{{__('messages.view_all')}}</u></a>
                        </div>
                        <div class="card-body">
                            <ul class="common-list p-0">
                                @foreach($orders as $booking)
                                <li class="d-flex flex-wrap gap-2 align-items-start align-items-lg-center justify-content-between flex-column flex-lg-row"  style="pointer-events:none;">
                                    <div class="media align-items-center gap-3">
                                        <div class="h-avatar is-medium h-5">
                                            <img class="avatar-50 rounded-circle bg-light" alt="user-icon" src="{{ $booking->user->photo }}">
                                        </div>
                                        <div class="media-body ">
                                            <h5>#{{$booking->id}}</h5>
                                            <span>{{
                                                optional($datetime)->date_format && optional($datetime)->time_format
                                                ? date(optional($datetime)->date_format, strtotime($booking->date)) . ' / ' . date(optional($datetime)->time_format, strtotime($booking->start))
                                                : ''
                                            }}</span>    
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill py-2 px-3 badge-pending text-capitalize">{{$booking->status}}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            
@if(auth()->user()->hasAnyRole(['office']))     


{{-- <div class="col-md-12 mt-3">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-start align-items-center"> 
                <div class="event-icon-wrapper">
                    <i class="fas fa-chart-line" style="font-size: 24px; color: white; animation: bounceUp 2s infinite;"></i>
                </div>
                <div class="col-md-6"> 
                    <h4> {{ __('messages.wallet_status_and_dues') }}</h4>
                </div>
            </div>
        </div>
    </div>
</div> --}}




<div class="container">
    <div class="row align-items-start justify-content-center">
        <div class="row justify-content-center">
            <div class="d-flex flex-wrap gap-4 justify-content-center"> 
                <div class="finance-card withdrawn text-center">
                    <div class="finance-icon"><i class="fas fa-wallet"></i></div>
                    <div class="finance-info">
                        <h3>{{ __('messages.withdrawn_amount') }}</h3>
                        <p id="withdrawn-amount">{{ $data['withdrawn-amount'] }}</p>
                    </div>
                </div>

                <div class="finance-card available text-center">
                    <div class="finance-icon"><i class="fas fa-hand-holding-usd"></i></div>
                    <div class="finance-info">
                        <h3>{{ __('messages.available_amount') }}</h3>
                        <p id="available-amount">{{ $data['available-amount'] }}</p>
                    </div>
                </div>

                <div class="finance-card pending text-center">
                    <div class="finance-icon"><i class="fas fa-clock"></i></div>
                    <div class="finance-info">
                        <h3>{{ __('messages.pending_withdrawals') }}</h3>
                        <p id="pending-amount">{{ $data['pending-amount'] }}</p>
                    </div>
                </div>

                <div class="finance-card total text-center">
                    <div class="finance-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="finance-info">
                        <h3>{{ __('messages.total_earnings') }}</h3>
                        <p id="total-amount">{{ $data['total-amount'] }}</p>
                    </div>
                </div>
                



                <div class="finance-card driver-due">
                    <div class="finance-icon"><i class="fas fa-taxi"></i></div>
                    <div class="finance-info">
                        <h3>{{ __('messages.driver_due') }}</h3>
                        <p id="driver-due-amount">{{ $data['drivers-due-amount'] }}</p>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>





<div class="col-12">
    <div class="horizontal-separator"></div>
        </div>




<div class="col-md-12 mt-3">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="">{{__('messages.monthly_revenue')}}</h4>
            </div>
            <div id="monthly-revenue" class="custom-chart"></div>
        </div>
    </div>
</div>




{{-- 
<div class="col-lg-4">
<h2 class="stats-title"><span>{{ __('messages.due_amounts') }}</span></h2>
   

    <div class="col-12">
        <div class="finance-card driver-due">
            <div class="finance-icon"><i class="fas fa-taxi"></i></div>
            <div class="finance-info">
                <h3>{{ __('messages.driver_due') }}</h3>
                <p id="driver-due-amount">{{ $data['drivers-due-amount'] }}</p>
            </div>
        </div>
    </div> --}}
{{-- </div> --}}









        <div class="col-md-12 mt-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-start align-items-center"> 
                        <div class="event-icon-wrapper">
                            <svg width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="11" stroke="white" stroke-width="2" fill="none"/>
                                <circle cx="12" cy="12" r="7" stroke="white" stroke-width="2" fill="none" stroke-dasharray="56,56" stroke-dashoffset="0">
                                    <animate attributeName="stroke-dashoffset" from="0" to="112" dur="1.5s" repeatCount="indefinite"/>
                                </circle>
                            </svg>                                
                            
                        </div>
                        <div class=" col-md-6 "> 
                            <h4 > {{__('messages.live_events')}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        


        

        <div class="container d-flex justify-content-center">
            <div class="trip-status-container">
                <div class="trip-card finished">
                    <div class="trip-icon">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                    <div class="trip-info">
                        <h3>{{ __('messages.completed_rides') }}</h3>
                        <p id="completed-ride">{{ $data['system_completed_rides'] }} {{ __('messages.ride') }}</p>
                    </div>
                </div>
        
                <div class="trip-card ongoing">
                    <div class="trip-icon">
                        <i class="fas fa-car-side"></i>
                    </div>
                    <div class="trip-info">
                        <h3>{{ __('messages.ongoing_rides') }}</h3>
                        <p id="ongoing-ride">{{ $data['system_ongoing_rides'] }} {{ __('messages.ride') }}</p>
                    </div>
                </div>
        
                <div class="trip-card pending">
                    <div class="trip-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="trip-info">
                        <h3>{{ __('messages.pending_orders') }}</h3>
                        <p id="pending-ride">{{ $data['system_pending_rides'] }} {{ __('messages.order') }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        





        <div class="col-12">
        <div class="horizontal-separator"></div>
            </div>












        <div class="row">
            <div class="col-md-4 col-sm-6">
                <div class="card top-providers">
                    <div class="card-header d-flex justify-content-between gap-10">
                        <h4 class="font-weight-bold">{{ __('messages.recent_Office') }}</h4>
                        <a href="{{ route('office.index') }}" class="btn-link btn-link-hover"><u>{{__('messages.view_all')}}</u></a>
                    </div>
                    <div class="card-body p-0">
                        <ul class="common-list list-unstyled">
                            @foreach($offices as $office)
                            <li style="pointer-events:none;">
                                <div class="media gap-3">
                                    <div class="h-avatar is-medium h-5">
                                        <img class="avatar-50 rounded-circle bg-light" alt="user-icon" src="{{ $office->logo }}">
                                    </div>
                                    <div class="media-body ">
                                        <h5><span class="font-weight-bold">{{!empty($office->officeName) ? $office->officeName : '-'}}</span> </h5>
                                        <span class="common-list_rating d-flex gap-1">
                                            <i class="ri-star-s-fill"></i>
                                            {{round(3.2, 1)}}
                                        </span>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        
            <div class="col-md-4 col-sm-6">
                <div class="card top-providers">
                    <div class="card-header d-flex justify-content-between gap-10">
                        <h4 class="font-weight-bold">{{ __('messages.recent_customer') }}</h4>
                        <a href="{{ route('user.index') }}" class="btn-link btn-link-hover"><u>{{__('messages.view_all')}}</u></a>
                    </div>
                    <div class="card-body p-0">
                        <ul class="common-list list-unstyled">
                            @foreach($users as $customer) 
                            <li style="pointer-events:none;">
                                <div class="media gap-3">
                                    <div class="h-avatar is-medium h-5">
                                        <img class="avatar-50 rounded-circle bg-light" alt="user-icon" src="{{ $customer->photo }}">
                                    </div>
                                    <div class="media-body ">
                                        <h5><span class="font-weight-bold">{{!empty($customer->firstName) ? $customer->firstName .' '.$customer->lastName : '-'}}</span>  </h5>
                                        <span>{{
                                            optional($datetime)->date_format && optional($datetime)->time_format
                                            ? date(optional($datetime)->date_format, strtotime($customer->created_at)) . ' / ' . date(optional($datetime)->time_format, strtotime($customer->created_at))
                                            : ''
                                        }}</span>
                                    </div>
                                </div>
                            </li>
                            @endforeach 
                        </ul>
                    </div>
                </div>
            </div>
        
            <div class="col-md-4 col-sm-6">
                <div class="card recent-activities">
                    <div class="card-header d-flex justify-content-between gap-10">
                        <h4>{{__('messages.recent_booking')}}</h4>
                        <a href="{{ route('booking.index') }}" class="btn-link btn-link-hover"><u>{{__('messages.view_all')}}</u></a>
                    </div>
                    <div class="card-body">
                        <ul class="common-list p-0">
                            @foreach($orders as $booking)
                            <li class="d-flex flex-wrap gap-2 align-items-start align-items-lg-center justify-content-between flex-column flex-lg-row"  style="pointer-events:none;">
                                <div class="media align-items-center gap-3">
                                    <div class="h-avatar is-medium h-5">
                                        <img class="avatar-50 rounded-circle bg-light" alt="user-icon" src="{{ $booking->user->photo }}">
                                    </div>
                                    <div class="media-body ">
                                        <h5>#{{$booking->id}}</h5>
                                        <span>{{
                                            optional($datetime)->date_format && optional($datetime)->time_format
                                            ? date(optional($datetime)->date_format, strtotime($booking->date)) . ' / ' . date(optional($datetime)->time_format, strtotime($booking->start))
                                            : ''
                                        }}</span>    
                                    </div>
                                </div>
                                <span class="badge rounded-pill py-2 px-3 badge-pending text-capitalize">{{$booking->status}}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif




</x-master-layout>



<style>
    @keyframes bounceUp {
        0% {
            transform: translateY(0);
        }
        25% {
            transform: translateY(-10px);
        }
        50% {
            transform: translateY(0);
        }
        75% {
            transform: translateY(-5px);
        }
        100% {
            transform: translateY(0);
        }
    }
    </style>

<script>


    socket.on('admins:admin-satistic', (data) => {
    console.log('Received message:', data);

    const currentAmountElement = document.getElementById(data.name);
    let currentAmount = currentAmountElement.textContent.replace('$', '').replace(',', '');
   currentAmount = parseFloat(currentAmount);

    
    const newAmount = parseFloat(data.value);

    const updatedAmount = newAmount;

    currentAmountElement.textContent = `${updatedAmount.toLocaleString()}`;
});


</script>




<script>
    document.addEventListener("DOMContentLoaded", function() {
        var revenueData = <?php echo json_encode($data['revenueData']); ?>;
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        if (document.querySelector('#monthly-revenue')) {
            var options = {
                series: [{
                    name: 'الإيرادات',
                    data: revenueData
                }],
                chart: {
                    height: 300,
                    type: 'line',
                    toolbar: { show: true },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: { enabled: true, delay: 150 },
                        dynamicAnimation: { enabled: true, speed: 350 }
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                    colors: ['#f5b041'] 
                },
                markers: {
                    size: 5,
                    colors: ['#f39c12'],
                    strokeWidth: 2,
                    hover: { size: 7 }
                },
                xaxis: {
                    categories: months,
                    labels: { style: { fontSize: '13px', fontWeight: 'bold', colors: '#aaa' } }
                },
                yaxis: {
                    labels: { style: { fontSize: '12px', fontWeight: 'bold', colors: '#aaa' } },
                    title: { text: '', style: { fontSize: '14px', fontWeight: 'bold', color: '#666' } }
                },
                tooltip: {
                    theme: 'dark',
                    y: { formatter: function(val) { return 'ل.س' + val.toLocaleString(); } }
                },
                grid: { borderColor: '#ddd', strokeDashArray: 5 }
            };

            var chart = new ApexCharts(document.querySelector("#monthly-revenue"), options);
            chart.render();
        }
    });
</script>
