@if(isset($driver->id))
  {{-- <a href="{{ route('driver.detail', ['id' => $query->id]) }}"> --}}
    <div class="d-flex gap-3 align-items-center">
      <img src="{{ $driver->photo }}" alt="avatar" class="avatar avatar-45 rounded-pill">
      <div class="text-start">
        <h6 class="m-0  " style="font-family: 'Arial', sans-serif; font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
          {{ $driver->firstName . ' ' . $driver->lastName }}
      </h6>
              <span>{{'driver' ?? '--' }}</span>
      </div>
    </div>
  {{-- </a> --}}
@else

  <div class="align-items-center">
    <h6 class="text-center">{{ '-' }} </h6>
  </div>
@endif




