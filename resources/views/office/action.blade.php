
<?php
$auth_user= authSession();
?>
<form action="{{ route('office.destroy', $office->id) }}" method="POST" data--submit="office{{ $office->id }}">
    @csrf
    @method('DELETE')

<div class="d-flex justify-content-end align-items-center">
@if(!$office->trashed())
{{-- <a class="mr-2" href="{{ route('provider.time-slot',['id' => $provider->id]) }}" title="{{ __('messages.My_time_slot',['form' => __('messages.provider') ]) }}"><i class="fa fa-clock text-primary "></i></a> --}}


    {{-- @if($auth_user->can('provider edit')) --}}
    <a class="mr-2" href="{{ route('office.create-page',['id' => $office->id]) }}" title="{{ __('messages.update_form_title',['form' => __('messages.office') ]) }}"><i class="fas fa-pen text-secondary"></i></a>
    {{-- @endif --}}
    {{-- @if($auth_user->can('provider delete')) --}}
    <a class="mr-2 text-danger" href="{{ route('office.destroy', $office->id) }}" data--submit="provider{{$office->id}}" 
        data--confirmation='true'
        data--ajax="true"
        data-datatable="reload"
        data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.office') ]) }}"
        title="{{ __('messages.delete_form_title',['form'=>  __('messages.office') ]) }}"
        data-message='{{ __("messages.delete_msg") }}'>
        <i class="far fa-trash-alt"></i>
    </a>
    {{-- @endif --}}
@endif
{{-- @if(auth()->user()->hasAnyRole(['admin']) && $provider->trashed()) --}}
@if( $office->trashed())
    <a href="{{ route('office.action',['id' => $office->id, 'type' => 'restore']) }}"
        title="{{ __('messages.restore_form_title',['form' => __('messages.office') ]) }}"
        data--submit="confirm_form"
        data--confirmation='true'
        data--ajax='true'
        data-title="{{ __('messages.restore_form_title',['form'=>  __('messages.office') ]) }}"
        data-message='{{ __("messages.restore_msg") }}'
        data-datatable="reload"
        class="mr-2">
        <i class="fas fa-redo text-secondary"></i>
    </a>
    <a href="{{ route('office.action',['id' => $office->id, 'type' => 'forcedelete']) }}"
        title="{{ __('messages.forcedelete_form_title',['form' => __('messages.office') ]) }}"
        data--submit="confirm_form"
        data--confirmation='true'
        data--ajax='true'
        data-title="{{ __('messages.forcedelete_form_title',['form'=>  __('messages.office') ]) }}"
        data-message='{{ __("messages.forcedelete_msg") }}'
        data-datatable="reload"
        class="mr-2">
        <i class="far fa-trash-alt text-danger"></i>
    </a>
@endif
</div>
</form>
