
<form action="{{ route('banner.destroy') }}" method="POST" data--submit="banner{{ $banner->id }}">
    @csrf
    @method('DELETE')
    <div class="d-flex justify-content-end align-items-center">
        
    @if(!$banner->trashed())
    {{-- @if($auth_user->can('banner edit')) --}}
    <a class="mr-2" href="{{route('banner.create', ['id' => $banner->id]) }}" title="{{ __('messages.update_form_title',['form' => __('messages.office') ]) }}"><i class="fas fa-pen text-secondary"></i></a>
    {{-- @endif --}}

      {{-- @if($auth_user->can('handyman delete')) --}}
        <a class="mr-3 text-danger" href="{{ route('banner.destroy', $banner->id) }}" data--submit="banner{{$banner->id}}" 
            data--confirmation='true' 
            data--ajax="true"
            data-datatable="reload"
            data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.banner') ]) }}"
            title="{{ __('messages.delete_form_title',['form'=>  __('messages.banner') ]) }}"
            data-message='{{ __("messages.delete_msg") }}'>
            <i class="far fa-trash-alt"></i>
        </a>
        @endif

    {{-- @endif --}}
    {{-- @if(auth()->user()->hasAnyRole(['admin']) && $banner->trashed()) --}}
    @if($banner->trashed())
        <a href="{{ route('banner.action',['id' => $banner->id, 'type' => 'restore']) }}"
            title="{{ __('messages.restore_form_title',['form' => __('messages.banner') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.restore_form_title',['form'=>  __('messages.banner') ]) }}"
            data-message='{{ __("messages.restore_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="fas fa-redo text-secondary"></i>
        </a>
        <a href="{{ route('banner.action',['id' => $banner->id, 'type' => 'forcedelete']) }}"
            title="{{ __('messages.forcedelete_form_title',['form' => __('messages.banner') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.forcedelete_form_title',['form'=>  __('messages.banner') ]) }}"
            data-message='{{ __("messages.forcedelete_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="far fa-trash-alt text-danger"></i>
        </a>
    @endif
    <input type="hidden" name="id" value="{{ $banner->id }}">
</div>
</form>
