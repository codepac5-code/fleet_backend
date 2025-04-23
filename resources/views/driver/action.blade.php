
<form action="{{ route('driver.destroy', $driver->id) }}" method="POST" data--submit="driver{{ $driver->id }}">
    @csrf
    @method('DELETE')
    <div class="d-flex justify-content-end align-items-center">
        
    @if(!$driver->trashed())
    {{-- @if($auth_user->can('update driver')) --}}
    <a class="mr-2" href="{{route('driver.create', ['id' => $driver->id]) }}" title="{{ __('messages.update_form_title',['form' => __('messages.driver') ]) }}"><i class="fas fa-pen text-secondary"></i></a>
    {{-- @endif --}}
     @if($auth_user->can('driver changePassword'))
      <a class="mr-2" href="{{ route('driver.getchangepassword',['id' => $driver->id]) }}" title="{{ __('messages.change_password',['form' => __('messages.driver') ]) }}"><i class="fa fa-lock text-success "></i></a>
      @endif
      @if($auth_user->can('delete driver'))
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
    {{-- auth()->user()->hasAnyRole(['admin']) &&  --}}
    @if($driver->trashed())
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

</div>
</form>
