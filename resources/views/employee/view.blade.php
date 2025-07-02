<x-master-layout>
    {{-- <form action="{{ route('office.destroy', $office->id) }}" method="POST" data--submit="office{{ $office->id }}"> 
        @csrf
        @method('POST') --}}

         <main class="main-area">
            <div class="main-content">
                <div class="container-fluid">

                    @if(auth()->user()->hasAnyRole(['super-admin']))
                    @include('partials._office')
                    @endif
                    {{-- <div class="taxi-dashboard-card">
                        <div class="card-body"> --}}
                            {{-- <h3 class="taxi-section-title">{{ __('messages.drivers_registered') }}</h3> --}}
                            {{-- <div class="taxi-drivers-list"> --}}
                                <div class="card">
                                    <div class="card-body p-30">
                                        <div class="service-man-list">
                                            @foreach($office->drivers as $driver)
                                            <div class="taxi-driver-card">
                                                <div class="taxi-driver-img-box">
                                                    <img src="{{ $driver->photo }}" alt="Driver Photo" class="taxi-driver-photo">
                                                </div>
                            
                                                <h4 class="taxi-driver-name">{{ $driver->firstName ?? '-' }} {{ $driver->lastName ?? '' }}</h4>
                                                <a class="taxi-driver-phone" href="tel:{{ $driver->phoneNumber }}">{{ __('messages.phone') }}: {{ $driver->phoneNumber ?? '-' }}</a>
                            
                                                <div class="taxi-driver-info">
                                                    <span><i class="fas fa-star"></i> {{ __('messages.rating') }}: {{ $driver->rating ?? 'N/A' }}</span>
                                                    <span><i class="fas fa-car"></i> {{ __('messages.ride_count') }}: {{ $driver->rideCount ?? 0 }}</span>
                                                    <span><i class="fas fa-wallet"></i> {{ __('messages.wallet_balance') }}: {{ $driver->walletBalance ?? 0 }}$</span>
                                                </div>
                            
                                                <span class="taxi-status-badge {{ $driver->isActive ? 'taxi-status-active' : 'taxi-status-inactive' }}">
                                                    {{ $driver->isActive ? __('messages.active') : __('messages.inactive') }}
                                                </span>
                                            </div>
                                        @endforeach
                            </div>
                        </div>
                    </div>
                    


                    
                </div>
            </div>
        </main>
    {{-- </form> --}}

    @section('bottom_script')
    @endsection
</x-master-layout>


