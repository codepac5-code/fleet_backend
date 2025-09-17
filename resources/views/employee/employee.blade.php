@if(isset($employee->id))
    {{-- @if($employee->officeId != null)
        <a href="{{ route('office.show', ['officeId' => $employee->officeId]) }}">
            <div class="d-flex gap-3 align-items-center">
                <img src="{{ $employee->photo }}" alt="avatar" class="avatar avatar-40 rounded-pill">
                <div class="text-start">
                    <h6 class="m-0">
                        {{ $employee->firstName . ' ' . $employee->lastName }}
                    </h6>
                    <span>{{ $employee->email ?? '--' }}</span>
                </div>
            </div>
        </a>
    @else --}}
        <div class="d-flex gap-3 align-items-center">
            <img src="{{asset($employee->photo)  }}" alt="avatar" class="avatar avatar-40 rounded-pill">
            <div class="text-start">
                <h6 class="m-0">
                    {{ $employee->firstName . ' ' . $employee->lastName }}
                </h6>
                <span>{{ $employee->email ?? '--' }}</span>
            </div>
        </div>
    {{-- @endif --}}
@else
    <div class="align-items-center">
        <h6 class="text-center">-</h6>
    </div>
@endif
