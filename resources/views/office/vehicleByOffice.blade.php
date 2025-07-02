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
                                              
                                                @foreach($office->vehicles as $vehicle)
                                                <div class="vehicle-card">
                                                    <div class="vehicle-image">
                                                      <img src="{{ $vehicle->photo ? asset($vehicle->photo) : 'https://via.placeholder.com/400x200?text=Vehicle+Photo' }}" alt="{{ __('messages.vehicle_photo_alt', ['year' => $vehicle->modelYear]) }}" />
                                                      <span class="vehicle-year">{{ $vehicle->modelYear }}</span>
                                                    </div>
                                                    <div class="vehicle-content">
                                                      <div class="vehicle-plate">{{ $vehicle->plate }}</div>
                                                      <div class="vehicle-info">
                                                        <p>
                                                          <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
                                                          <strong>{{ __('messages.vehicle_brand') }}:</strong> {{ $vehicle->vehicleBrand }}
                                                        </p>
                                                        {{-- <p>
                                                          <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 17v-6h13v6M3 17v-6h6v6"></path></svg>
                                                          <strong>{{ __('messages.model') }}:</strong> {{ $vehicle->model }}
                                                        </p> --}}
                                                        <p>
                                                          <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /></svg>
                                                          <strong>{{ __('messages.color') }}:</strong> {{ $vehicle->color }}
                                                        </p>
                                                        <p>
                                                          <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-3.33 0-6 2.67-6 6h12c0-3.33-2.67-6-6-6z"/></svg>
                                                          <strong>{{ __('messages.driver') }}:</strong> {{ optional($vehicle->driver)->name ?? __('messages.unknown') }}
                                                        </p>
                                                      </div>
                                                    </div>
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

    <style>



.vehicle-card {
  font-family: 'Tajawal', sans-serif;
  width: 100%;
  max-width: 400px;
  border-radius: 16px;
  overflow: hidden;
  background: #ece7b6b2; 
  box-shadow: 0 6px 20px rgba(255, 204, 0, 0.26);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  margin: auto;
  direction: rtl;
}

.vehicle-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 10px 30px rgba(255, 204, 0, 0.25);
}

.vehicle-image {
  position: relative;
  height: 220px;
  overflow: hidden;
}

.vehicle-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.vehicle-card:hover .vehicle-image img {
  transform: scale(1.03);
}

.vehicle-year {
  position: absolute;
  top: 12px;
  right: 12px;
  background: #ffcc00;
  color: #3a3a58;
  font-size: 14px;  
  font-weight: 700;
  padding: 5px 12px;
  border-radius: 9999px;
  box-shadow: 0 2px 6px rgba(58, 58, 88, 0.25);
}

.vehicle-content {
  padding: 20px 22px;
}

.vehicle-plate {
  font-size: 17px;   
  font-weight: 600;
  color: #38385a;
  margin-bottom: 16px;
  text-align: center;
  background: rgba(235, 232, 61, 0.664);
  padding: 8px 10px;
  border-radius: 14px;
  box-shadow: inset 0 1px 4px rgba(255, 204, 0, 0.616);
}

.vehicle-info {
  display: flex;
  flex-direction: column;
  gap: 9px;
}

.vehicle-info p {
  font-size: 16.5px;   
  color: #3a3a58;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 7px;
  line-height: 1.4;
}

.vehicle-info p svg {
  width: 16px;   
  height: 16px;
  color: #3a3a58;
  flex-shrink: 0;
}

.vehicle-info strong {
  min-width: 65px;    
  color: #3a3a58;
  font-weight: 600;
  font-size: 14.5px;  
}


        </style>
        
        
</x-master-layout>


