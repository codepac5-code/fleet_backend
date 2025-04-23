@if(isset($user))
{{-- <a href="{{ route('user.show', ['user' => optional($user)->id]) }}"> --}}
  <div class="d-flex gap-3 align-items-center">
     {{-- <img src="{{ getSingleMedia(optional($user),'profile_image', null) }}" alt="avatar" class="avatar avatar-40 rounded-pill"> --}}
    <div class="text-start">
      <h6 class="m-0">{{ optional($user)->firstName .' '.optional($user)->last }} </h6>
      <span>{{ optional($user)->phoneNumber ?? '--' }}</span>
    </div>
  </div>
{{-- </a> --}}
  @else

  <div class="align-items-center">
    <h6 class="text-center">{{ '-' }} </h6>
  </div>
@endif




