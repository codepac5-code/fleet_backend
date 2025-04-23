<?php
    $auth_user = authSession();
?>

<div class="d-flex justify-content-end align-items-center">
    @if(!$data->trashed())
        {{-- @if($auth_user->can('update driver')) --}}
        <a class="mr-2" href="{{route('vehicle.create', ['id' => $data->id]) }}" title="{{ __('messages.update_form_title',['form' => __('messages.driver') ]) }}"><i class="fas fa-pen text-secondary"></i></a>
        {{-- @endif --}}
        {{-- @if($auth_user->can('subcategory delete')) --}}
            <a class="mr-3" href="javascript:void(0);" 
                onclick="event.preventDefault(); 
                         showDeleteConfirmation('{{ $data->id }}');"
                data--submit="{{$data->id}}" 
                data--confirmation='true' 
                data--ajax="true"
                data-datatable="reload"
                data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.subcategory') ]) }}"
                title="{{ __('messages.delete_form_title',['form'=>  __('messages.subcategory') ]) }}"
                data-message="{{ __('messages.delete_msg') }}">
                <i class="far fa-trash-alt text-danger"></i>
            </a>
        {{-- @endif --}}
    @endif
    
    @if( $data->trashed())
        {{-- @if(auth()->user()->hasAnyRole(['admin']) && $data->trashed()) --}}
        <a href="{{ route('sub-service.action',['id' => $data->id, 'type' => 'restore']) }}"
            title="{{ __('messages.restore_form_title',['form' => __('messages.subcategory') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.restore_form_title',['form'=>  __('messages.subcategory') ]) }}"
            data-message="{{ __('messages.restore_msg') }}"
            data-datatable="reload"
            class="mr-2">
            <i class="fas fa-redo text-secondary"></i>
        </a>
        <a href="{{ route('sub-service.action',['id' => $data->id, 'type' => 'forcedelete']) }}"
            title="{{ __('messages.forcedelete_form_title',['form' => __('messages.subcategory') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.forcedelete_form_title',['form'=>  __('messages.subcategory') ]) }}"
            data-message="{{ __('messages.forcedelete_msg') }}"
            data-datatable="reload"
            class="mr-2">
            <i class="far fa-trash-alt text-danger"></i>
        </a>
    @endif
</div>

<!-- The delete form (hidden form) -->
<form id="delete-form-{{ $data->id }}" action="{{ route('sub-service.destroy', $data->id) }}" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<script>
   
