

<form action="{{ route('driver.destroy', $driver->id) }}" method="POST" data--submit="driver{{ $driver->id }}">
    @csrf
    @method('DELETE')
    <div class="d-flex justify-content-end align-items-center">
        
    @if(!$driver->trashed())
    @if(auth()->user()->can('edit driver'))
    <a class="mr-2" href="{{route('driver.create', ['id' => $driver->id]) }}" title="{{ __('messages.update_form_title',['form' => __('messages.driver') ]) }}"><i class="fas fa-pen text-secondary"></i></a>
    @endif
     @if(auth()->user()->can('driver changePassword'))
      <a class="mr-2" href="{{ route('driver.view.change-password',['id' => $driver->id]) }}" title="{{ __('messages.change_password',['form' => __('messages.driver') ]) }}"><i class="fa fa-lock text-success "></i></a>
      @endif
      @if(auth()->user()->can('delete driver'))
        <a class="mr-3 text-danger" href="{{ route('driver.destroy', $driver->id) }}" data--submit="driver{{$driver->id}}" 
            data--confirmation='true' 
            data--ajax="true"
            data-datatable="reload"
            data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.driver') ]) }}"
            title="{{ __('messages.delete_form_title',['form'=>  __('messages.driver') ]) }}"
            data-message='{{ __("messages.delete_msg") }}'>
            <i class="far fa-trash-alt"></i>
        </a>
        @endif

    @endif
   @if ( auth()->user()->hasAnyRole(MainRoles()) && $driver->trashed())
        <a href="{{ route('driver.action',['id' => $driver->id, 'type' => 'restore']) }}"
            title="{{ __('messages.restore_form_title',['form' => __('messages.driver') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.restore_form_title',['form'=>  __('messages.driver') ]) }}"
            data-message='{{ __("messages.restore_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="fas fa-redo text-secondary"></i>
        </a>

        @if(auth()->user()->can('driver change custom commission'))

        <a href="{{ route('driver.action',['id' => $driver->id, 'type' => 'forcedelete']) }}"
            title="{{ __('messages.forcedelete_form_title',['form' => __('messages.driver') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.forcedelete_form_title',['form'=>  __('messages.driver') ]) }}"
            data-message='{{ __("messages.forcedelete_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="far fa-trash-alt text-danger"></i>
        </a>
        @endif

    @endif

    


    {{-- <a class="edit-commission-btn text-primary" 
   href="javascript:void(0);" 
   data-driver-id="{{ $driver->id }}" 
   data-has-custom-commission="{{ $driver->has_custom_commission ? 'true' : 'false' }}" 
   title="تعديل العمولة">
   <i class="fas fa-percentage"></i>
</a> --}}


<a class="edit-commission-btn text-primary d-flex align-items-center" 
   href="javascript:void(0);" 
   data-driver-id="{{ $driver->id }}" 
   data-has-custom-commission="{{$isCustom}}"
   data-is-office="{{ $isOffice}}" 
   data-office-commission="{{ $officeCommission}}" 
   data-driver-commission="{{ $driverCommission}}" 

   title="{{ __('messages.edit_commission') }}"
   style="margin-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}:5px;">
   <i class="fas fa-percentage"></i>
</a>
   
</div>
</form>

    