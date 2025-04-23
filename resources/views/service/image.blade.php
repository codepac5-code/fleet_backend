@if(isset($query->id))
<a href="{{ route('office.add', ['id' => $query->id]) }}">
  <div class="d-flex gap-3 align-items-center">
    <img src="{{ getSingleMedia($query,'service_image', null) }}" alt="avatar" class="avatar avatar-40 rounded-pill">
    
  </div>
</a>
@else

<div class="align-items-center">
    <h6 class="text-center">{{ '-' }} </h6>
</div>
@endif
