<?php
    $auth_user= authSession();
?>

<div class="d-flex justify-content-center align-items-center">


        @if($auth_user->can('user edit'))
            <a class="mr-2" href="{{ route('tag.tag_update_page',['id' => $user->id]) }}" title="{{ __('messages.update_form_title',['form' => __('messages.user') ]) }}"><i class="fas fa-pen text-primary "></i></a>
        @endif
        
 
    
</div>
