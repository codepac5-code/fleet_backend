
<form action="{{ route('employee.destroy', $employee->id) }}" method="POST" data--submit="employee{{ $employee->id }}">
    @csrf
    @method('DELETE')
    <div class="d-flex justify-content-end align-items-center">
        
    @if(!$employee->trashed())
    {{-- @if($auth_user->can('update employee')) --}}
    <a class="mr-2" href="{{route('employee.create', ['id' => $employee->id]) }}" title="{{ __('messages.update_form_title',['form' => __('messages.employee') ]) }}"><i class="fas fa-pen text-secondary"></i></a>
    {{-- @endif --}}
     @if($auth_user->can('employee changePassword'))
      <a class="mr-2" href="{{ route('employee.view.change-password',['id' => $employee->id]) }}" title="{{ __('messages.change_password',['form' => __('messages.employee') ]) }}"><i class="fa fa-lock text-success "></i></a>
      @endif
      @if($auth_user->can('delete employee'))
        <a class="mr-3 text-danger" href="{{ route('employee.destroy', $employee->id) }}" data--submit="employee{{$employee->id}}" 
            data--confirmation='true' 
            data--ajax="true"
            data-datatable="reload"
            data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.employee') ]) }}"
            title="{{ __('messages.delete_form_title',['form'=>  __('messages.employee') ]) }}"
            data-message='{{ __("messages.delete_msg") }}'>
            <i class="far fa-trash-alt"></i>
        </a>
        @endif

    @endif
    {{-- auth()->user()->hasAnyRole(['admin']) &&  --}}
    @if($employee->trashed())
        <a href="{{ route('employee.action',['id' => $employee->id, 'type' => 'restore']) }}"
            title="{{ __('messages.restore_form_title',['form' => __('messages.employee') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.restore_form_title',['form'=>  __('messages.employee') ]) }}"
            data-message='{{ __("messages.restore_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="fas fa-redo text-secondary"></i>
        </a>
        <a href="{{ route('employee.action',['id' => $employee->id, 'type' => 'forcedelete']) }}"
            title="{{ __('messages.forcedelete_form_title',['form' => __('messages.employee') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.forcedelete_form_title',['form'=>  __('messages.employee') ]) }}"
            data-message='{{ __("messages.forcedelete_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="far fa-trash-alt text-danger"></i>
        </a>
    @endif

</div>
</form>
