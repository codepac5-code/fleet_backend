{{ Form::model($payment_data, ['method' => 'POST','route' => ['paymentsettingsUpdates'],'enctype'=>'multipart/form-data','data-toggle'=>'validator']) }}

{{ Form::hidden('id', null, array('placeholder' => 'id','class' => 'form-control')) }}
{{ Form::hidden('type', $tabpage, array('placeholder' => 'id','class' => 'form-control')) }}
 <div class="row">
    <div class="form-group col-md-12" >
        <label for="enable_maya">{{__('messages.payment_on',['gateway'=>__('messages.maya')])}}</label>
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" name="status" id="enable_maya" {{!empty($payment_data) && $payment_data->status == 1 ? 'checked' : ''}}>
            <label class="custom-control-label" for="enable_maya"></label>
        </div>
    </div>
 </div>
 <div class="row" id='enable_maya_payment'>
    <div class="form-group col-md-12">
        <label class="form-control-label">{{__('messages.payment_option',['gateway'=>__('messages.maya')])}}</label><br/>
        <div class="form-check-inline">
            <label class="form-check-label">
                <input type="radio" class="form-check-input is_test" value="on" name="is_test" data-type="is_test_mode" {{!empty($payment_data) && $payment_data->is_test == 1 ? 'checked' :''}}>{{__('messages.is_test_mode')}}
            </label>
        </div>
        <div class="form-check-inline">
            <label class="form-check-label">
                <input type="radio" class="form-check-input is_test" value="off" name="is_test" data-type="is_live_mode" {{!empty($payment_data) && $payment_data->is_test == 0 ? 'checked' :''}}>{{__('messages.is_live_mode')}}
            </label>
        </div>
        <small class="help-block with-errors text-danger"></small>
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('title',trans('messages.gateway_name').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
        {{ Form::text('title',old('title'),['id'=>'title','placeholder' => trans('messages.title'),'class' =>'form-control']) }}
        <small class="help-block with-errors text-danger"></small>
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('maya_key',trans('messages.maya_key').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
        {{ Form::text('maya_key',old('maya_key'),['id'=>'maya_key','placeholder' => trans('messages.maya_key'),'class' =>'form-control']) }}
        <small class="help-block with-errors text-danger"></small>
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('maya_publickey',trans('messages.maya_publickey').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
        {{ Form::text('maya_publickey',old('maya_publickey'),['id'=>'maya_publickey','placeholder' => trans('messages.maya_publickey'),'class' =>'form-control']) }}
        <small class="help-block with-errors text-danger"></small>
    </div>
 </div>
{{ Form::submit(__('messages.save'), ['class'=>"btn btn-md btn-primary float-md-right"]) }}
{{ Form::close() }}
<script>
var enable_maya = $("input[name='status']").prop('checked');
checkPaymentTabOption(enable_maya);

$('#enable_maya').change(function(){
    value = $(this).prop('checked') == true ? true : false;
    checkPaymentTabOption(value);
});
function checkPaymentTabOption(value){
    if(value == true){
        $('#enable_maya_payment').removeClass('d-none');
        $('#title').prop('required', true);
        $('#maya_key').prop('required', true);
        $('#maya_publickey').prop('required', true);
    }else{
        $('#enable_maya_payment').addClass('d-none');
        $('#title').prop('required', false);
        $('#maya_key').prop('required', false);
        $('#maya_publickey').prop('required', false);
    }
}

var get_value = $('input[name="is_test"]:checked').data("type");
getConfig(get_value)
$('.is_test').change(function(){
    value = $(this).prop('checked') == true ? true : false;
    type = $(this).data("type");
    getConfig(type)

});

function getConfig(type){
    var _token   = $('meta[name="csrf-token"]').attr('content');
    var page =  "{{$tabpage}}";
    $.ajax({
        url: "/get_payment_config",
        type:"POST",
        data:{
          type:type,
          page:page,
          _token: _token
        },
        success:function(response){
            var obj = '';
            var maya_key=maya_publickey=title = '';

            if(response){
            
                if(response.data.type == 'is_test_mode'){
                    obj = JSON.parse(response.data.value);
                }else{
                    obj = JSON.parse(response.data.live_value);
                }

                if(response.data.title != ''){
                    title = response.data.title
                }
                
                if(obj !== null){
                    var maya_key = obj.maya_key;
                    var maya_publickey = obj.maya_publickey;
                }

                $('#maya_key').val(maya_key)
                $('#maya_publickey').val(maya_publickey)
                $('#title').val(title)
            
            }
        },
        error: function(error) {
         console.log(error);
        }
    });
}

</script>