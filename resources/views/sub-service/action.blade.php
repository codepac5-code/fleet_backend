
<form action="{{ route('sub-service.destroy') }}" method="POST" data--submit="subservice{{ $subservice->id }}">
    @csrf
    @method('DELETE')
    <div class="d-flex justify-content-end align-items-center">
        
    @if(!$subservice->trashed())
    {{-- @if($auth_user->can('subservice edit')) --}}
    <a class="mr-2" href="{{route('sub-service.create', ['id' => $subservice->id]) }}" title="{{ __('messages.update_form_title',['form' => __('messages.office') ]) }}"><i class="fas fa-pen text-secondary"></i></a>
    {{-- @endif --}}

      {{-- @if($auth_user->can('handyman delete')) --}}
        <a class="mr-3 text-danger" href="{{ route('sub-service.destroy', $subservice->id) }}" data--submit="subservice{{$subservice->id}}" 
            data--confirmation='true' 
            data--ajax="true"
            data-datatable="reload"
            data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.subservice') ]) }}"
            title="{{ __('messages.delete_form_title',['form'=>  __('messages.subservice') ]) }}"
            data-message='{{ __("messages.delete_msg") }}'>
            <i class="far fa-trash-alt"></i>
        </a>
        @endif

    {{-- @endif --}}
    {{-- @if(auth()->user()->hasAnyRole(['admin']) && $subservice->trashed()) --}}
    @if($subservice->trashed())
        <a href="{{ route('sub-service.action',['id' => $subservice->id, 'type' => 'restore']) }}"
            title="{{ __('messages.restore_form_title',['form' => __('messages.subservice') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.restore_form_title',['form'=>  __('messages.subservice') ]) }}"
            data-message='{{ __("messages.restore_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="fas fa-redo text-secondary"></i>
        </a>
        <a href="{{ route('sub-service.action',['id' => $subservice->id, 'type' => 'forcedelete']) }}"
            title="{{ __('messages.forcedelete_form_title',['form' => __('messages.subservice') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.forcedelete_form_title',['form'=>  __('messages.subservice') ]) }}"
            data-message='{{ __("messages.forcedelete_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="far fa-trash-alt text-danger"></i>
        </a>
    @endif
    <input type="hidden" name="id" value="{{ $subservice->id }}">
</div>
</form>
