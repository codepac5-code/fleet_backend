<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                            <a href="{{ route('tag.all','all') }}" class="float-right btn btn-sm btn-primary"><i
                                    class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                            @if($auth_user->can('user list'))
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::model($customerdata,['method' => 'POST','route'=>'user.store', 'enctype'=>'multipart/form-data', 'data-toggle'=>"validator" ,'id'=>'user'] ) }}
                        {{ Form::hidden('id') }}
                        {{ Form::hidden('user_type','user') }}
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ Form::label('first_name',__('messages.first_name').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('first_name',old('first_name'),['placeholder' => __('messages.first_name'),'class' =>'form-control','disabled']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('last_name',__('messages.last_name').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('last_name',old('last_name'),['placeholder' => __('messages.last_name'),'class' =>'form-control','disabled']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('username',__('messages.username').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('username',old('username'),['placeholder' => __('messages.username'),'class' =>'form-control','disabled']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            @if(auth()->user()->hasAnyRole(['admin','demo_admin', 'Viewing']))
                            <div class="form-group col-md-4">
                                {{ Form::label('user_type',__('messages.user_type').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                <select class='form-control select2js' id='user_type' name="user_type" disabled>
                                    @foreach($roles as $value)
                                    <option value="{{$value->name}}" data-type="{{$value->id}}"
                                        {{ $customerdata->user_type == $value->name ? 'selected' : '' }}>
                                        {{$value->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="form-group col-md-4">
                                {{ Form::label('email', __('messages.email').' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                {{ Form::email('email', old('email'), ['placeholder' => __('messages.email'), 'class' => 'form-control', 'disabled', 'pattern' => '[^@]+@[^@]+\.[a-zA-Z]{2,}', 'title' => 'Please enter a valid email address']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>


                            @if (!isset($customerdata->id) || $customerdata->id == null)
                            <div class="form-group col-md-4">
                                {{ Form::label('password', __('messages.password').' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('messages.password'), 'disabled', 'autocomplete' => 'new-password']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            @endif


                            <div class="form-group col-md-4">
                                {{ Form::label('contact_number',__('messages.contact_number').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('contact_number',old('contact_number'),['placeholder' => __('messages.contact_number'),'class' =>'form-control','disabled']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('status',__('messages.status').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                {{ Form::select('status',['1' => __('messages.active') , '0' => __('messages.inactive') ],old('status'),[ 'class' =>'form-control select2js','disabled']) }}
                            </div>
                            <div class="form-group col-md-8">
                                {{ Form::label('address',__('messages.address'), ['class' => 'form-control-label']) }}
                                {{ Form::textarea('address', null, ['class'=>"form-control textarea" , 'rows'=>1  , 'placeholder'=> __('messages.address'), 'disabled' ]), }}
                            </div>
                        </div>

                 
                        {{ Form::close() }}
                        
                    </div>
                </div>
                <!--jabu-->
                @if($customerdata->user_type == "provider")
                    <div class="card">
                        <div class="card-body">
                            <div class="row " id="accordion">
                                <div class="col-sm-12 col-md-6">
                                    <label>Service Provider Neopreneur</label>
                                    <a id="neoremove" class="neoremove mr-2 float-right " href="javascript:void(0)" title="{{ __('messages.update_form_title',['form' => __('messages.user') ]) }}"  {{isset($dataFirst->display_name) ? 'style=display:block' : 'style=display:none'}}><i class="fa fa-times text-danger "></i></a>
                                    <a id="neosearchBtn" class="mr-2 float-right " href="javascript:void(0)" title="{{ __('messages.update_form_title',['form' => __('messages.user') ]) }}"><i class="fas fa-pen text-primary "></i></a>
                                      <div class="card shadow-none bg-white rounded border border-dark">
                                        <div class="card-header shadow-none rounded bg-white border-bottom">
                                          <h5>
                                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" {{isset($dataFirst->display_name) ? $dataFirst->display_name : 'disabled'}}>
                                              {{isset($dataFirst->display_name) ? $dataFirst->display_name : 'N/A'}}
                                            </button>
                                          </h5>
                                        </div>
                                        <div id="collapseOne" class="collapse " data-parent="#accordion">
                                          <div class="card-body ">
                                                <span>Username:  {{isset($dataFirst->username) ? $dataFirst->username : 'N/A'}}</span><br>
                                                <span>Name:  {{isset($dataFirst->display_name) ? $dataFirst->display_name : 'N/A'}}</span><br>
                                                <span>Contact no.:  {{isset($dataFirst->contact_number) ? $dataFirst->contact_number : 'N/A'}}</span><br>
                                                <span>Email:  {{isset($dataFirst->email) ? $dataFirst->email : 'N/A'}}</span><br>
                                                <span>Address:  {{isset($dataFirst->address) ? $dataFirst->address : 'N/A'}}</span><br>
                                          </div>
                                        </div>
                                      </div>
                                   
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <label>Service Provider Upline</label>
                                    <a id="uplineSearchBtn" href="javascript:void(0)"  class="mr-2 float-right" title="{{ __('messages.update_form_title',['form' => __('messages.user') ]) }}"><i class="fas fa-pen text-primary "></i></a>
                                    <div class="card shadow-none bg-white rounded border border-dark">
                                        <div class="card-header shadow-none rounded bg-white border-bottom">
                                          <h5>
                                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapseTwo" {{isset($dataLast->display_name) ? $dataLast->display_name : 'disabled'}}>
                                              {{isset($dataLast->display_name) ? $dataLast->display_name : 'N/A'}}
                                            </button>
                                          </h5>
                                        </div>
                                        <div id="collapseTwo" class="collapse " data-parent="#accordion">
                                          <div class="card-body ">
                                                <span>Username:  {{isset($dataLast->username) ? $dataLast->username : 'N/A'}}</span><br>
                                                <span>Name:  {{isset($dataLast->display_name) ? $dataLast->display_name : 'N/A'}}</span><br>
                                                <span>Contact no.:  {{isset($dataLast->contact_number) ? $dataLast->contact_number : 'N/A'}}</span><br>
                                                <span>Email:  {{isset($dataLast->email) ? $dataLast->email : 'N/A'}}</span><br>
                                                <span>Address:  {{isset($dataLast->address) ? $dataLast->address : 'N/A'}}</span><br>
                                          </div>
                                        </div>
                                      </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card" id="neopreneurSearchContainer" style="display:none">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <label>Neopreneur Search</label>
                                    <input type="text" class="form-control" value="" id="inputNeoSearch">
                                    <label id="neoSearchError" class="text-danger"></label>
                                </div>
                                
                            </div>
                            <div class="row mt-2" id="accordionNeo">
                                
                            </div>
                        </div>
                    </div>
                    <div class="card" id="uplineSearchContainer" style="display:none">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <label>Upline Search</label>
                                    <input type="text" class="form-control" value="" id="inputUplineSearch">
                                    <label id="uplineSearchError" class="text-danger"></label>
                                </div>
                            </div>
                            <div class="row" id="accordionUpline">
                                
                            </div>
                        </div>
                    </div>
                @elseif($customerdata->user_type == "Neopreneur")
                    <div class="card">
                        <div class="card-body">
                            <div class="row " id="accordionForNeopreneur">
                                <div class="col-sm-12 col-md-6">
                                    <label>Referral</label>
                                    <a id="neoremovee" class="neoremove mr-2 float-right " href="javascript:void(0)" title="{{ __('messages.update_form_title',['form' => __('messages.user') ]) }}"  {{isset($dataFirst->display_name) ? 'style=display:block' : 'style=display:none'}}><i class="fa fa-times text-danger "></i></a>
                                    <a id="neosearchBtnForNeo" class="mr-2 float-right " href="javascript:void(0)" title="{{ __('messages.update_form_title',['form' => __('messages.user') ]) }}"><i class="fas fa-pen text-primary "></i></a>
                                      <div class="card shadow-none bg-white rounded border border-dark">
                                        <div class="card-header shadow-none rounded bg-white border-bottom">
                                          <h5>
                                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOneNeo" {{isset($dataFirst->display_name) ? $dataFirst->display_name : 'disabled'}}>
                                              {{isset($dataFirst->display_name) ? $dataFirst->display_name : 'N/A'}}
                                            </button>
                                          </h5>
                                        </div>
                                        <div id="collapseOneNeo" class="collapse " data-parent="#accordionForNeopreneur">
                                          <div class="card-body ">
                                                <span>Username:  {{isset($dataFirst->username) ? $dataFirst->username : 'N/A'}}</span><br>
                                                <span>Name:  {{isset($dataFirst->display_name) ? $dataFirst->display_name : 'N/A'}}</span><br>
                                                <span>Contact no.:  {{isset($dataFirst->contact_number) ? $dataFirst->contact_number : 'N/A'}}</span><br>
                                                <span>Email:  {{isset($dataFirst->email) ? $dataFirst->email : 'N/A'}}</span><br>
                                                <span>Address:  {{isset($dataFirst->address) ? $dataFirst->address : 'N/A'}}</span><br>
                                          </div>
                                        </div>
                                      </div>
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card" id="searchContainerForNeo" style="display:none">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <label>Neopreneur Search</label>
                                    <input type="text" class="form-control" value="" id="inputSearchForNeo">
                                    <label id="neoSearchErrorForNeo" class="text-danger"></label>
                                </div>
                                
                            </div>
                            <div class="row mt-2" id="accordionForNeo">
                                
                            </div>
                        </div>
                    </div>
                    
                @endif
                
            </div>
        </div>
    </div>
</x-master-layout>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).on('keyup', '.contact_number', function() {
        var contactNumberInput = document.getElementById('contact_number');
        var inputValue = contactNumberInput.value;
        inputValue = inputValue.replace(/[^0-9+\- ]/g, '');
        if (inputValue.length > 15) {
            inputValue = inputValue.substring(0, 15);
            $('#contact_number_err').text('Contact number should not exceed 15 characters');
        } else {
                $('#contact_number_err').text('');
        }
        contactNumberInput.value = inputValue;
        if (inputValue.match(/^[0-9+\- ]+$/)) {
            $('#contact_number_err').text('');
        } else {
            $('#contact_number_err').text('Please enter a valid mobile number');
        }
    });
    // jabu
    $('#neosearchBtn').on('click', function(){
        $('#neopreneurSearchContainer').show()
        $('#uplineSearchContainer').hide()
    })
    $('#uplineSearchBtn').on('click', function(){
        var ifHasUpline = "{{isset($dataLast->display_name) ? $dataLast->display_name : ''}}";
        if(ifHasUpline){
            return false;
        }
        $('#neopreneurSearchContainer').hide()
        $('#uplineSearchContainer').show()
    })
    $('#neosearchBtnForNeo').on('click', function(){
        $('#searchContainerForNeo').show()
    })
    $('#inputSearchForNeo').on('keyup', function(e){
        var data = {
            display_name: $('#inputSearchForNeo').val()
        }
        if(e.keyCode == 13){
            $.ajax({
                type: 'GET',
                url: '{{ route("tag.search_neo") }}',
                data: data,
                dataType: 'JSON',
                success: function(data){
                    $('#accordionForNeo').html("");
                    $('#neoSearchErrorForNeo').html('')
                    if(data.status == "success"){
                        var nData = data.data;
                        $('#accordionForNeo').append(`
                        <div class="col-sm-12 col-md-6">
                            <div class="card shadow-none bg-white rounded border border-dark">
                                <div class="card-header shadow-none rounded bg-white border-bottom">
                                    <h5>
                                        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseHead${nData.id}">
                                            ${nData.display_name}
                                        </button>
                                        <button class="btn-primary float-right addTag" data-data-tagid="${nData.id}" style="border-radius: 5px;" onclick="neoTagging('${nData.id}', '{{ $customerdata->id }}', 'neopage')">
                                            Apply
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseHead${nData.id}" class="collapse" data-parent="#accordionForNeo">
                                    <div class="card-body ">
                                        <span>Username: ${nData.username}</span><br>
                                        <span>Name: ${nData.first_name} ${nData.last_name}</span><br>
                                        <span>Contact no.: ${nData.contact_number}</span><br>
                                        <span>Email: ${nData.email}</span><br>
                                        <span>Address: ${nData.address}</span><br>
                                        <sm
                                    </div>
                                </div>
                            </div>
                        </div>
                        `)
                
                    }else{
                        $('#neoSearchErrorForNeo').html('Name do not match!')
                    }
                }
            })
        }
    })
    $('#inputNeoSearch').on('keyup', function(e){
        var data = {
            display_name: $('#inputNeoSearch').val()
        }
        if(e.keyCode == 13){
            $.ajax({
                type: 'GET',
                url: '{{ route("tag.search_neo") }}',
                data: data,
                dataType: 'JSON',
                success: function(data){
                    $('#accordionNeo').html("");
                    $('#neoSearchError').html('')
                    if(data.status == "success"){
                        var nData = data.data;
                        $('#accordionNeo').append(`
                        <div class="col-sm-12 col-md-6">
                            <div class="card shadow-none bg-white rounded border border-dark">
                                <div class="card-header shadow-none rounded bg-white border-bottom">
                                    <h5>
                                        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseHead${nData.id}">
                                            ${nData.display_name}
                                        </button>
                                        <button class="btn-primary float-right addTag" data-data-tagid="${nData.id}" style="border-radius: 5px;" onclick="neoTagging('${nData.id}', '{{ $customerdata->id }}', 'neo')">
                                            Apply
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseHead${nData.id}" class="collapse" data-parent="#accordionNeo">
                                    <div class="card-body ">
                                        <span>Username: ${nData.username}</span><br>
                                        <span>Name: ${nData.first_name} ${nData.last_name}</span><br>
                                        <span>Contact no.: ${nData.contact_number}</span><br>
                                        <span>Email: ${nData.email}</span><br>
                                        <span>Address: ${nData.address}</span><br>
                                        <sm
                                    </div>
                                </div>
                            </div>
                        </div>
                        `)
                
                    }else{
                        $('#neoSearchError').html('Name do not match!')
                    }
                }
            })
        }
    })
    $('#inputUplineSearch').on('keyup', function(e){
        var data = {
            display_name: $('#inputUplineSearch').val()
        }
        if(e.keyCode == 13){
            $.ajax({
                type: 'GET',
                url: '{{ route("tag.search_neo") }}',
                data: data,
                dataType: 'JSON',
                success: function(data){
                    $('#accordionUpline').html("");
                    $('#uplineSearchError').html('')
                    if(data.status == "success"){
                        var nData = data.data;
                        $('#accordionUpline').append(`
                        <div class="col-sm-12 col-md-6">
                            <div class="card shadow-none bg-white rounded border border-dark">
                                <div class="card-header shadow-none rounded bg-white border-bottom">
                                    <h5>
                                        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseHead${nData.id}">
                                            ${nData.display_name}
                                        </button>
                                        <button class="btn-primary float-right addTag" data-data-tagid="${nData.id}" style="border-radius: 5px;" onclick="neoTagging('${nData.id}', '{{ $customerdata->id }}', 'upline')">
                                            Apply
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseHead${nData.id}" class="collapse" data-parent="#accordionUpline">
                                    <div class="card-body ">
                                        <span>Username: ${nData.username}</span><br>
                                        <span>Name: ${nData.first_name} ${nData.last_name}</span><br>
                                        <span>Contact no.: ${nData.contact_number}</span><br>
                                        <span>Email: ${nData.email}</span><br>
                                        <span>Address: ${nData.address}</span><br>
                                        <sm
                                    </div>
                                </div>
                            </div>
                        </div>
                        `)
                
                    }else{
                        $('#uplineSearchError').html('Name do not match!')
                    }
                }
            })
        }
    })
    $('.neoremove').on('click', function(){
        
        var token = "{{ csrf_token() }}";
        var data = {
            neo_id : "{{isset($customerdata->id) ? $customerdata->id : ''}}",
            type: "{{isset($customerdata->user_type) ? $customerdata->user_type : ''}}",
            _token: token
        }
        Swal.fire({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, remove it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({     
                    type: 'GET',
                    url: '{{ route("tag.remove_neo") }}',
                    data: data,
                    dataType: 'JSON',
                    success: function(data){
                        if(data.status == "success"){
                            Swal.fire({
                              title: "Removed!",
                              text: "Provider neopreneur is successfully removed.",
                              icon: "success"
                            }).then(()=>{
                                location.reload();
                            });
                           
                        }else{
                            Swal.fire({
                              title: "Error!",
                              text: "Error.",
                              icon: "error"
                            });
                        }
                       
                       
                    }
                })
            }
        })
    })
    function neoTagging(neo_id,sp_id,type){
        
        var token = "{{ csrf_token() }}";
        var data = {
            neo_id : neo_id,
            sp_id : sp_id,
            type: type,
            _token: token
        }
        Swal.fire({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, apply it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({     
                    type: 'GET',
                    url: '{{ route("tag.add_neo") }}',
                    data: data,
                    dataType: 'JSON',
                    success: function(data){
                        if(data.status == "success"){
                           Swal.fire({
                              title: "Tagged!",
                              text: "Provider neopreneur is successfully tagged.",
                              icon: "success"
                            }).then(()=>{
                                location.reload();
                            });
                           
                        }else{
                           Swal.fire({
                              title: "Error!",
                              text: "Error.",
                              icon: "error"
                            });
                        }
                       
                       
                    }
                })
            }
        })
    }
    
</script>