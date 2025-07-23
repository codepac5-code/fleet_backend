
<form action="{{ route('user.destroy', $user->id) }}" method="POST" data--submit="user{{ $user->id }}">
    @csrf
    @method('DELETE')
    <div class="d-flex justify-content-end align-items-center">
        
    @if(!$user->trashed())
    @if(auth()->user()->can('edit user'))
    <a class="mr-2" href="{{route('user.create', ['id' => $user->id]) }}" title="{{ __('messages.update_form_title',['form' => __('messages.office') ]) }}"><i class="fas fa-pen text-secondary"></i></a>
    @endif
     @if($auth_user->can('user changePassword'))
      <a class="mr-2" href="{{ route('user.view.change-password',['id' => $user->id]) }}" title="{{ __('messages.change_password',['form' => __('messages.user') ]) }}"><i class="fa fa-lock text-success "></i></a>
      @endif
      @if($auth_user->can('delete user'))
        <a class="mr-3 text-danger" href="{{ route('user.destroy', ['id' => $user->id] ) }}" data--submit="user{{$user->id}}" 
            data--confirmation='true' 
            data--ajax="true"
            data-datatable="reload"
            data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.user') ]) }}"
            title="{{ __('messages.delete_form_title',['form'=>  __('messages.user') ]) }}"
            data-message='{{ __("messages.delete_msg") }}'>
            <i class="far fa-trash-alt"></i>
        </a>
        @endif
    @endif

    
    @if(auth()->user()->hasAnyRole(MainRoles()) && $user->trashed())
        <a href="{{ route('user.action',['id' => $user->id, 'type' => 'restore']) }}"
            title="{{ __('messages.restore_form_title',['form' => __('messages.user') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.restore_form_title',['form'=>  __('messages.user') ]) }}"
            data-message='{{ __("messages.restore_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="fas fa-redo text-secondary"></i>
        </a>
        <a href="{{ route('user.action',['id' => $user->id, 'type' => 'forcedelete']) }}"
            title="{{ __('messages.forcedelete_form_title',['form' => __('messages.user') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.forcedelete_form_title',['form'=>  __('messages.user') ]) }}"
            data-message='{{ __("messages.forcedelete_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="far fa-trash-alt text-danger"></i>
        </a>
    @endif

</div>
</form>
