<div class="page-title-wrap mb-3 p-3">
    <h2 class="page-title">{{__('messages.Office_Details')}}</h2>
    {{-- @if(auth()->user()->hasAnyRole(['admin']))
    <a href="{{ route('login.as',$providerdata->id) }}" class="btn btn-primary text-white" style="float: right !important;">Login as User</a>
    
    @endif --}}
</div>


<div class="mb-3 ms-2">
    <ul class="nav nav--tabs nav--tabs__style2 provider-detail-tab">
        <li class="nav-item {{request()->routeIs('office.show') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('office.show',['officeId' => $office->id]) }}"> {{__('messages.overview')}}</a>
        </li>
        <li class="nav-item {{request()->routeIs('vehicle.index') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('vehicle.byOffice', ['officeId' =>$office->id]) }}"> {{__('messages.vehicles')}}</a>
        </li>
        
        <li class="nav-item {{request()->routeIs('driver.byOffice') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('driver.byOffice', ['officeId' =>$office->id]) }}">{{ __('messages.drivers') }}</a>
        </li>
        <li class="nav-item {{request()->routeIs('office.orders') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('office.orders', ['officeId' =>$office->id]) }}">{{ __('messages.orders') }}</a>
        </li>

        

        <li class="nav-item {{request()->routeIs('setting.comission') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('setting.comission',['officeId' =>$office->id]) }}">{{__('messages.commission')}}</a>
        </li>
        <li class="nav-item {{request()->routeIs('office.review') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('office.review',['officeId' =>$office->id]) }}">{{__('messages.Reviews')}}</a>
        </li>



    {{-- 
        <li class="nav-item {{request()->routeIs('service.show') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('service.show',$office->id) }}"> {{__('messages.plan')}}</a>
        </li>
        <li class="nav-item {{request()->routeIs('booking.details') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('booking.details',$office->id) }}"> {{__('messages.Bookings')}}</a>
        </li>

        <li class="nav-item {{request()->routeIs('VehicleProvider.vehicleDetails') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('VehicleProvider.vehicleDetails',$office->id) }}"> {{__('messages.Vehicle')}}</a>
        </li>

        <!-- <li class="nav-item {{request()->routeIs('bank.show') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('bank.show',$office->id) }}">{{__('messages.Bank_info')}}</a>
        </li> -->

        <li class="nav-item {{request()->routeIs('providerdocument.show') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('providerdocument.show',$office->id) }}">{{__('messages.list_form_title',['form' => __('messages.document')])}}</a>
        </li>
        <li class="nav-item {{request()->routeIs('providerpayout.show') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('providerpayout.show',$office->id) }}">{{__('messages.list_form_title',['form' => __('messages.provider_payout')])}}</a>
        </li>
        <li class="nav-item {{request()->routeIs('provideraddress.show') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('provideraddress.show',$office->id) }}">{{__('messages.list_form_title',['form' => __('messages.provider_address')])}}</a>
        </li> 
    --}}



        {{-- <li class="nav-item {{request()->routeIs('bank.list') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('bank.list',$providerdata->id) }}">{{__('messages.list_form_title',['form' => __('messages.bank')])}}</a>
        </li>
        <li class="nav-item {{request()->routeIs('provider.time-slot') ? 'active' : ''}}">
            <a class="nav-link" href="{{ route('provider.time-slot',$providerdata->id) }}">{{__('messages.list_form_title',['form' => __('messages.manage_slot')])}}</a>
        </li> --}}
    </ul>
</div>