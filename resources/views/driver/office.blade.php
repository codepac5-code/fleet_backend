@if(isset($office))
{{-- <a href="{{ route('provider.show', ['provider' => optional($office)->id]) }}"> --}}
  <div class="d-flex gap-3 align-items-center">
    {{-- <img src="{{ getSingleMedia(optional($office->image),'profile_image', null) }}" alt="avatar" class="avatar avatar-40 rounded-pill"> --}}
    <div class="text-start">
      <h6 class="m-0">{{ optional($office->officeName) }} </h6>
      <span>{{ optional($office)->email ?? '--' }}</span>
    </div>
  </div>
{{-- </a> --}}
  @else

  <div class="align-items-center">
    <h6 class="text-center">{{ '-' }} </h6>
</div>
@endif


