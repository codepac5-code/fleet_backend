<x-master-layout>
    <div class="container-fluid">
        <div class="row">
        <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                                <a href="{{ route('religion.index') }}" class="float-right btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::model($religiondata ?? '',['method' => 'POST','route'=>'religion.store', 'data-toggle'=>"validator" ,'id'=>'name'] ) }}
                            {{ Form::hidden('id') }}
                            <div class="row">
                                <div class="form-group col-md-4">
                                    {{ Form::label('name',__('Religion').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('name',old('religion'),['placeholder' => __('Religion'),'class' =>'form-control']) }}
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                            </div>
                            {{ Form::submit( __('messages.save'), ['class'=>'btn btn-md btn-primary float-right']) }}
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-master-layout>