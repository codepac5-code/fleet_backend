<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">

                            <h5 class="font-weight-bold">{{ __('messages.Add_new_Service') }}</h5>
                            <a href="{{ route('service.index') }}" class="float-right btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('service.add') }}" enctype="multipart/form-data" id="service">
                    @csrf
                    <input type="hidden" name="id" value="{{ old('id', $service->id ?? null) }}">
                
                    <div class="card card-block card-stretch">
                        <div class="card-body p-0">
                            <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    
                                    <div class="border p-2 d-flex justify-content-center align-items-center" 
                                        style="height: 85px; width: 130px; border-radius: 10px; overflow: hidden;">
                                        <img id="imagePreview" 
                                            src="{{ $service->image ?? get_default_image('service') }}" 
                                            alt="Preview" 
                                            style="height: 100%; width: 100%; object-fit: cover;">
                                    </div>
                

                                         <!-- image -->
                                    <div class="form-group mb-0">
                                        <label class="form-control-label" for="banner_image">
                                            {{ __('messages.image') }} <span class="text-danger">*</span>
                                        </label>
                                        
                                        <div class="custom-file">
                                            <input type="hidden" name="current_image" value="{{ $service->image ?? '' }}">
                                    
                                            <input type="file" name="image" id="banner_image" class="custom-file-input" accept="image/*" onchange="previewImage(event)">
                                            
                                            <label class="custom-file-label upload-label" id="fileLabel">
                                                {{ $service->image ? basename($service->image) : __('messages.choose_file', ['file' => __('messages.image')]) }}
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
                
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">

                                    <div class="form-group col-md-4">
                                        <label for="name" class="form-control-label">
                                            {{ __('messages.name') }} <span class="text-danger">*</span>
                                        </label>
                                        <input 
                                            type="text" 
                                            name="name" 
                                            id="name" 
                                            value="{{ old('name', $service->title ?? '') }}" 
                                            placeholder="{{ __('messages.name') }}" 
                                            class="form-control" 
                                            required>
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


                                    <div class="form-group col-md-4">
                                        <label for="status" class="form-control-label">
                                            {{ trans('messages.status') }} <span class="text-danger">*</span>
                                        </label>
                                        <select name="status" id="status" class="form-control select2js" required>
                                            <option value="1" {{ old('status', $service->status ?? '') == '1' ? 'selected' : '' }}>
                                                {{ __('messages.active') }}
                                            </option>
                                            <option value="0" {{ old('status', $service->status ?? '') == '0' ? 'selected' : '' }}>
                                                {{ __('messages.inactive') }}
                                            </option>
                                        </select>
                                        @error('status')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                            </div>
                                <div class="form-group col-md-6">
                                        <label for="description" class="form-control-label">
                                            {{ trans('messages.description') }}
                                        </label>
                                        <textarea name="description" class="form-control textarea" rows="3" placeholder="{{ __('messages.description') }}">{{ old('description', $service->description ?? '') }}</textarea>
                                        @error('description')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="description_en" class="form-control-label">
                                            {{ trans('messages.description_en') }}
                                        </label>
                                        <textarea name="description_en" class="form-control textarea" rows="3" placeholder="{{ __('messages.description_en') }}">{{ old('description_en', $service->description_en ?? '') }}</textarea>
                                        @error('description_en')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                    <button type="submit" class="btn btn-md btn-primary float-right">
                                        {{ trans('messages.save') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                     </form>
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
    </script>
</x-master-layout>




