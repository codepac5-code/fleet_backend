
<?php
    $auth_user= authSession();
?>
{{ Form::open(['route' => ['religion.destroy', $religion->id], 'method' => 'delete','data--submit'=>'religion'.$religion->id]) }}
<div class="d-flex justify-content-end align-items-center">

    <a class="mr-3" href="{{ route('religion.destroy', $religion->id) }}" data--submit="religion{{$religion->id}}" 
        data--confirmation='true' 
        data--ajax="true"
        data-datatable="reload"
        data-title="{{ __('messages.delete_form_title',['form'=>  __('Religion') ]) }}"
        title="{{ __('messages.delete_form_title',['form'=>  __('Religion') ]) }}"
        data-message='{{ __("messages.delete_msg") }}'>
        <i class="far fa-trash-alt text-danger"></i>
    </a>
</div>
{{ Form::close() }}