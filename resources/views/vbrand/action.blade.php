
<form action="{{ route('vbrand.destroy') }}" method="POST" data--submit="vbrand{{ $vbrand->id }}">
    @csrf
    @method('DELETE')
    <div class="d-flex justify-content-end align-items-center">
        
    @if(!$vbrand->trashed())
    {{-- @if($auth_user->can('vbrand edit')) --}}
    <a class="mr-2" href="{{route('vbrand.create', ['id' => $vbrand->id]) }}" title="{{ __('messages.update_form_title',['form' => __('messages.office') ]) }}"><i class="fas fa-pen text-secondary"></i></a>
    {{-- @endif --}}

      {{-- @if($auth_user->can('handyman delete')) --}}
        <a class="mr-3 text-danger" href="{{ route('vbrand.destroy', ['vbrandId'=>$vbrand->id]) }}" data--submit="vbrand{{$vbrand->id}}" 
            data--confirmation='true' 
            data--ajax="true"
            data-datatable="reload"
            data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.vbrand') ]) }}"
            title="{{ __('messages.delete_form_title',['form'=>  __('messages.vbrand') ]) }}"
            data-message='{{ __("messages.delete_msg") }}'>
            <i class="far fa-trash-alt"></i>
        </a>
        @endif

    {{-- @endif --}}
    {{-- @if(auth()->user()->hasAnyRole(['admin']) && $vbrand->trashed()) --}}
    @if($vbrand->trashed())
        <a href="{{ route('vbrand.action',['id' => $vbrand->id, 'type' => 'restore']) }}"
            title="{{ __('messages.restore_form_title',['form' => __('messages.vbrand') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.restore_form_title',['form'=>  __('messages.vbrand') ]) }}"
            data-message='{{ __("messages.restore_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="fas fa-redo text-secondary"></i>
        </a>
        <a href="{{ route('vbrand.action',['id' => $vbrand->id, 'type' => 'forcedelete']) }}"
            title="{{ __('messages.forcedelete_form_title',['form' => __('messages.vbrand') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.forcedelete_form_title',['form'=>  __('messages.vbrand') ]) }}"
            data-message='{{ __("messages.forcedelete_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="far fa-trash-alt text-danger"></i>
        </a>
    @endif
    <input type="hidden" name="id" value="{{ $vbrand->id }}">
</div>
</form>
