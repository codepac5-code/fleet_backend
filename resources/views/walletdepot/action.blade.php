<!--owel action blade depot wallet-->
<?php
    $auth_user= authSession();
?>
{{ Form::open(['route' => ['wallet.destroy', $query->user_id], 'method' => 'delete','data--submit'=>'wallet'.$query->user_id]) }}
@if(auth()->user()->hasAnyRole(['admin','demo_admin']))
<div class="d-flex justify-content-end align-items-center">
        <a class="mr-2" href="{{ route('walletdepot.create',['id' => $query->user_id]) }}" title="{{ __('messages.update_form_title',['form' => __('messages.wallet') ]) }}"><i class="fas fa-pen text-secondary"></i></a>
        <a class="mr-2" href="{{ route('walletdepot.destroy', $query->user_id) }}" data--submit="wallet{{$query->user_id}}"
            data--confirmation='true'
            data--ajax="true"
            data-datatable="reload"
            data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.wallet') ]) }}"
            title="{{ __('messages.delete_form_title',['form'=>  __('messages.wallet') ]) }}"
            data-message='{{ __("messages.delete_msg") }}'>
            <i class="far fa-trash-alt text-danger"></i>
        </a>
    </div>
@endif
{{ Form::close() }}
