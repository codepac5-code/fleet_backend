<form action="{{ route('driver.destroy', $driver->id) }}" method="POST" data--submit="driver{{ $driver->id }}">
    @csrf
    @method('DELETE')
    <div class="d-flex justify-content-end align-items-center gap-2">

        @if(!$driver->trashed())
            @if(auth()->user()->can('edit driver'))
                <a href="{{route('driver.create', ['id' => $driver->id]) }}" title="{{ __('messages.update_form_title',['form' => __('messages.driver') ]) }}">
                    <i class="fas fa-pen text-secondary"></i>
                </a>
            @endif

            @if(auth()->user()->can('driver changePassword'))
                <a href="{{ route('driver.view.change-password',['id' => $driver->id]) }}" title="{{ __('messages.change_password',['form' => __('messages.driver') ]) }}">
                    <i class="fa fa-lock text-success"></i>
                </a>
            @endif

            @if(auth()->user()->can('delete driver'))
                <a href="{{ route('driver.destroy', ['id' => $driver->id, 'type' => 'delete']) }}" data--submit="driver{{$driver->id}}"
                   data--confirmation='true'
                   data--ajax="true"
                   data-datatable="reload"
                   data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.driver') ]) }}"
                   title="{{ __('messages.delete_form_title',['form'=>  __('messages.driver') ]) }}"
                   data-message='{{ __("messages.delete_msg") }}'>
                    <i class="far fa-trash-alt text-danger"></i>
                </a>
            @endif
        @endif

        @if ( auth()->user()->hasAnyRole(MainRoles()) && $driver->trashed())
            <a href="{{ route('driver.destroy',['id' => $driver->id, 'type' => 'restore']) }}"
               title="{{ __('messages.restore_form_title',['form' => __('messages.driver') ]) }}"
               data--submit="confirm_form"
               data--confirmation='true'
               data--ajax='true'
               data-title="{{ __('messages.restore_form_title',['form'=>  __('messages.driver') ]) }}"
               data-message='{{ __("messages.restore_msg") }}'
               data-datatable="reload">
                <i class="fas fa-redo text-secondary"></i>
            </a>


                <a href="{{ route('driver.destroy',['id' => $driver->id, 'type' => 'forcedelete']) }}"
                   title="{{ __('messages.forcedelete_form_title',['form' => __('messages.driver') ]) }}"
                   data--submit="confirm_form"
                   data--confirmation='true'
                   data--ajax='true'
                   data-title="{{ __('messages.forcedelete_form_title',['form'=>  __('messages.driver') ]) }}"
                   data-message='{{ __("messages.forcedelete_msg") }}'
                   data-datatable="reload">
                    <i class="far fa-trash-alt text-danger"></i>
                </a>
        @endif
  @if(auth()->user()->can('driver change custom commission'))
        <a class="edit-commission-btn text-primary"
           href="javascript:void(0);"
           data-driver-id="{{ $driver->id }}"
           data-has-custom-commission={{$isCustom}}
           data-is-office={{ $isOffice?'yes':'no' }}
           data-office-commission="{{ $officeCommission}}"
           data-driver-commission="{{ $driverCommission}}"
           title="{{ __('messages.edit_commission') }}">
           <i class="fas fa-percentage"></i>
        </a>
    @endif

    </div>
</form>
