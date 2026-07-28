
<form action="{{ route('vehicle.destroy', ['vehicleId' => $vehicle->id]) }}" method="POST" data--submit="vehicle{{ $vehicle->id }}">
    @csrf
    @method('DELETE')
    <div class="d-flex justify-content-end align-items-center">

    @if(!$vehicle->trashed())
    @if(auth()->user()->can('update vehicle'))
    <a class="mr-2" href="{{route('vehicle.create', ['id' => $vehicle->id]) }}" title="{{ __('messages.update_form_title',['form' => __('messages.office') ]) }}"><i class="fas fa-pen text-secondary"></i></a>
    @endif

    @if(auth()->user()->can('delete sub-service'))
        <a class="mr-3 text-danger" href="{{ route('vehicle.destroy', ['vehicleId'=>$vehicle->id]) }}" data--submit="vehicle{{$vehicle->id}}"
            data--confirmation='true'
            data--ajax="true"
            data-datatable="reload"
            data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.vehicle') ]) }}"
            title="{{ __('messages.delete_form_title',['form'=>  __('messages.vehicle') ]) }}"
            data-message='{{ __("messages.delete_msg") }}'>
            <i class="far fa-trash-alt"></i>
        </a>
        @endif

    @endif
    @if(auth()->user()->hasAnyRole(MainRoles()) && $vehicle->trashed())
        <a href="{{ route('vehicle.action',['vehicleId' => $vehicle->id, 'type' => 'restore']) }}"
            title="{{ __('messages.restore_form_title',['form' => __('messages.vehicle') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.restore_form_title',['form'=>  __('messages.vehicle') ]) }}"
            data-message='{{ __("messages.restore_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="fas fa-redo text-secondary"></i>
        </a>
        <a href="{{ route('vehicle.action',['id' => $vehicle->id, 'type' => 'forcedelete']) }}"
            title="{{ __('messages.forcedelete_form_title',['form' => __('messages.vehicle') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.forcedelete_form_title',['form'=>  __('messages.vehicle') ]) }}"
            data-message='{{ __("messages.forcedelete_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="far fa-trash-alt text-danger"></i>
        </a>
    @endif
    <input type="hidden" name="vehicleId" value="{{ $vehicle->id }}">
</div>
</form>
