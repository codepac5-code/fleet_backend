<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ __('messages.Add_New_SubService') }}</h5>
                            <a href="{{ route('sub-service.index') }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('sub-service.add') }}" id="subservice" enctype="multipart/form-data">
                            @csrf
                            
                            <input type="hidden" name="id" value="{{ old('id', $subservice->id ) }}">
            
                            <div class="row">
                                                                        
                                <div class="form-group col-md-4">
                                    <label for="name" class="form-control-label">
                                        {{ __('messages.name_arabic') }} <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        name="name" 
                                        id="name" 
                                        value="{{ old('name', $subservice->name ?? '') }}" 
                                        placeholder="{{ __('messages.name_arabic') }}" 
                                        class="form-control required-field">
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="name_en" class="form-control-label">
                                        {{ __('messages.name_en') }} <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        name="name_en" 
                                        id="name_en" 
                                        value="{{ old('name_en', $service->title ?? '') }}" 
                                        placeholder="{{ __('messages.name_en') }}" 
                                        class="form-control" 
                                        required>
                                    @error('name_en')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                {{-- <div class="form-group col-md-4">
                                    <label for="name" class="form-control-label">{{ trans('messages.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" value="{{ old('name', $subservice->name ?? '') }}" placeholder="{{ trans('messages.name') }}" class="form-control" required>
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div> --}}
            
                                <div class="form-group col-md-4">
                                    <label for="serviceId" class="form-control-label">
                                        {{ __('messages.service') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="serviceId" id="serviceId" class="select2js form-control" required>
                                        <option value="">{{ __('messages.select_name', ['select' => __('messages.service')]) }}</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" {{ old('serviceId', $subservice->serviceId) == $service->id ? 'selected' : '' }}>{{ $service->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('serviceId')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
            
                        
                            </div>
            
                            <!-- price -->
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="openPrice"> {{ __('messages.open_price') }}</label> <span class="text-danger">*</span>
                                    <input type="number" id="openPrice" name="openPrice" class="form-control price-input" step="100" min="0" value="{{ old('openPrice', $subservice->openPrice ?? 0.00) }}" required>
                                    @error('openPrice')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
            
                                <div class="form-group col-md-3">
                                    <label for="kmPrice">{{ __('messages.km_price') }} </label> <span class="text-danger">*</span>
                                    <input type="number" id="kmPrice" name="kmPrice" class="form-control price-input" step="50" min="0" value="{{ old('kmPrice', $subservice->kmPrice ?? 0.00) }}" required>
                                    @error('kmPrice')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
            
                                <div class="form-group col-md-3">
                                    <label for="minutePrice"> {{ __('messages.minute_price') }}</label><span class="text-danger">*</span>
                                    <input type="number" id="minutePrice" name="minutePrice" class="form-control price-input" step="5" min="0" value="{{ old('minutePrice', $subservice->minutePrice ?? 0.00) }}" required>
                                    @error('minutePrice')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>


                                <div class="form-group col-md-3">
                                    <label for="status" class="form-control-label">{{ trans('messages.status') }} <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control select2js" required>
                                        <option value="1" {{ old('status', $subservice->status) == '1' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                        <option value="0" {{ old('status', $subservice->status) == '0' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                                    </select>
                                    @error('status')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
            
                            <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                                <!-- Profile Image Preview -->
                                <div class="card card-block card-stretch">
                                    <div class="card-body p-0">
                                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                                            <div class="d-flex align-items-center gap-3">
                                                
                                                <div class="border p-2 d-flex justify-content-center align-items-center" 
                                                    style="height: 85px; width: 130px; border-radius: 10px; overflow: hidden;">
                                                    <img id="imagePreview" 
                                                        src="{{ $subservice->image ?? get_default_image('sub_service') }}" 
                                                        alt="Preview" 
                                                        style="height: 100%; width: 100%; object-fit: cover;">
                                                </div>
                            
            
                                                     <!-- image -->
                                                <div class="form-group mb-0">
                                                    <label class="form-control-label" for="banner_image">
                                                        {{ __('messages.image') }} <span class="text-danger">*</span>
                                                    </label>
                                                    
                                                    <div class="custom-file">
                                                        <input type="hidden" name="current_image" value="{{ $subservice->image ?? '' }}">
                                                
                                                        <input type="file" name="image" id="banner_image" class="custom-file-input" accept="image/*" onchange="previewImage(event)">
                                                        
                                                        <label class="custom-file-label upload-label" id="fileLabel">
                                                            {{ $subservice->image ? basename($subservice->image) : __('messages.choose_file', ['file' => __('messages.image')]) }}
                                                        </label>
                                                    </div>
                                                
                                                    @error('image')
                                                        <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
            
                            <div class="row">
                            <div class="form-group col-md-4">
                                <label for="description" class="form-control-label">{{ trans('messages.description') }}</label>
                                <textarea name="description" class="form-control textarea" rows="3" placeholder="{{ __('messages.description') }}">{{ old('description', $subservice->description) }}</textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <label for="description_en" class="form-control-label">{{ trans('messages.description_en') }}</label>
                                <textarea name="description_en" class="form-control textarea" rows="3" placeholder="{{ __('messages.description_en') }}">{{ old('description', $subservice->description) }}</textarea>
                                @error('description_en')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            </div>
                    
                            <button type="submit" id="saveButton" class="btn btn-md btn-primary float-right" >{{ __('messages.save') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>

function previewImage(event) {
            const input = event.target;
            const reader = new FileReader();
            const preview = document.getElementById('imagePreview');
    
            reader.onload = function(e) {
                preview.src = e.target.result;
            };
    
            if (input.files && input.files[0]) {
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = "{{ asset('images/default-placeholder.png') }}";
            }
        }

     document.querySelectorAll('.price-input').forEach(input => {
            input.addEventListener('input', () => {
                input.style.color = 'green';
                setTimeout(() => input.style.color = '', 2000); 
            });
        });

    </script>

    
</x-master-layout>




{{-- 


          <!-- Image Upload -->
          <div class="form-group mb-0">
                                        
            <label class="form-control-label" for="banner_image">
                {{ __('messages.image') }} <span class="text-danger">*</span>
            </label>
            <div class="custom-file">

                <input type="file" name="image" id="banner_image" class="custom-file-input" accept="image/*" onchange="previewImage(event) " >
                <label class="custom-file-label upload-label">
                    {{ __('messages.choose_file', ['file' => __('messages.image')]) }}
                </label>
            </div>
            @error('image')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
             --}}