<x-master-layout>
    <div class="container-fluid">
        <div class="row">
        <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ __('messages.Add_New_SubService') }}</h5>
                            {{-- @if($auth_user->can('category list')) --}}
                                <a href="{{ route('sub-service.index') }}" class="float-right btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                            {{-- @endif --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('sub-service.add') }}" enctype="multipart/form-data" data-toggle="validator" id="subservice">
                            @csrf
                            <input type="hidden" name="id" value="{{ old('id', $subservice->id) }}">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="name" class="form-control-label">{{ trans('messages.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" value="{{ old('name', $subservice->name) }}" placeholder="{{ trans('messages.name') }}" class="form-control" required>
                                    <small class="help-block with-errors text-danger"></small>
                                </div>

                              
                                <div class="form-group col-md-4">
                                    <label for="category_id" class="form-control-label">
                                        {{ __('messages.select_name', ['select' => __('messages.Service')]) }} <span class="text-danger">*</span>
                                    </label>
                                    <br />
                                    <select name="category_id" class="select2js form-group category" required data-placeholder="{{ __('messages.select_name', ['select' => __('messages.category')]) }}" data-ajax--url="{{ route('ajax-list', ['list_type' => 'services_list']) }}">
                                        <option value="{{ optional($subservice->service)->id }}" selected>{{ optional($subservice->service)->name }}</option>
                                    </select>
                                </div>
                                
                                <div class="form-group col-md-4">
                                    <label for="status" class="form-control-label">{{ trans('messages.status') }} <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control select2js" required>
                                        <option value="1" {{ old('status', $subservice->status) == '1' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                        <option value="0" {{ old('status', $subservice->status) == '0' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                                    </select>
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                    
                                <div class="form-group col-md-4">
                                    <label class="form-control-label" for="sub_service_image">{{ __('messages.image') }} <span class="text-danger">*</span></label>
                                    <div class="custom-file">
                                        <input type="file" name="sub_service_image" class="custom-file-input" accept="image/*">
                                        @if($subservice && getMediaFileExit($subservice, 'sub_service_image'))
                                            <label class="custom-file-label upload-label">{{ $subservice->getFirstMedia('sub_service_image')->file_name }}</label>
                                        @else
                                            <label class="custom-file-label upload-label">{{ __('messages.choose_file', ['file' => __('messages.image')]) }}</label>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">

                                @if(getMediaFileExit($subservice, 'sub_service_image'))
                                    <div class="col-md-2 mb-2">
                                        @php
                                            $extension = imageExtention(getSingleMedia($subservice, 'sub_service_image'));
                                        @endphp
                                        <img id="subcategory_image_preview" src="{{ getSingleMedia($subservice, 'sub_service_image') }}" alt="#" class="attachment-image mt-1">
                                        <a class="text-danger remove-file" href="{{ route('remove.file', ['id' => $subservice->id, 'type' => 'sub_service_image']) }}"
                                            data--submit="confirm_form"
                                            data--confirmation="true"
                                            data--ajax="true"
                                            title="{{ __('messages.remove_file_title', ['name' => __('messages.image')]) }}"
                                            data-title="{{ __('messages.remove_file_title', ['name' => __('messages.image')]) }}"
                                            data-message="{{ __('messages.remove_file_msg') }}">
                                            <i class="ri-close-circle-line"></i>
                                        </a>
                                    </div>
                                @endif



                                <div class="form-group" style="margin-bottom: 20px;"> 
                                    <label for="kmPrice">{{ __('messages.km_price') }}</label>
                                    <input 
                                        type="number" 
                                        id="kmPrice" 
                                        name="kmPrice" 
                                        class="form-control" 
                                        step="500.00" 
                                        min="0" 
                                        value="{{ old('kmPrice', $subservice->kmPrice ?? '') }}" 
                                        required>
                                </div>
                                <small class="help-block with-errors text-danger"></small>

                            </div>


                                <div class="row">
                            
                                     
                                <div class="form-group" style="margin-bottom: 20px;"> 
                                    <label for="minutePrice">{{ __('messages.minute_price') }}</label>
                                    <input 
                                        type="number" 
                                        id="minutePrice" 
                                        name="minutePrice" 
                                        class="form-control" 
                                        step="500.00" 
                                        min="0" 
                                        value="{{ old('minutePrice', $subservice->minutePrice ?? '') }}" 
                                        required>
                                </div>
                                
                                
                                <div class="form-group" style="margin-bottom: 20px;"> 
                                    <label for="minutePrice"></label>
                                    <input 
                                        type="number" 
                                        id="minutePrice" 
                                        name="minutePrice" 
                                        class="form-control" 
                                        step="500.00" 
                                        min="0" 
                                        value="{{ old('minutePrice', $subservice->minutePrice ?? '') }}" 
                                        required>
                                </div>
                                
                                
                    
                                <div class="form-group col-md-12">
                                    <label for="description" class="form-control-label">{{ trans('messages.description') }}</label>
                                    <textarea name="description" class="form-control textarea" rows="3" placeholder="{{ __('messages.description') }}">{{ old('description', $subservice->description) }}</textarea>
                                </div>
                            </div>
                        </div>
                    
                            <button type="submit" class="btn btn-md btn-primary float-right">{{ trans('messages.save') }}</button>
                        </form>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
    
</x-master-layout>