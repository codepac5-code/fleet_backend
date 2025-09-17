@if(isset($user->id))
<a href="{{ route('wallet.history', ['identifier' => $user->id , 'userType'=>'user']) }}">
  <div class="d-flex gap-3 align-items-center">
      <img src="{{ $user->photo }}" alt="avatar" class="avatar avatar-45 rounded-pill">
      <div class="text-start">
        <h6 class="m-0  " style="font-family: 'Arial', sans-serif; font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
          {{ $user->firstName . ' ' . $user->lastName }}
      </h6>
              <span>{{ $user->gender ?? '--' }}</span>
      </div>
    </div>
  </a>
@else

  <div class="align-items-center">
    <h6 class="text-center">{{ '-' }} </h6>
  </div>
@endif




