<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ __('messages.Add_New_Driver') }}</h5>
                            <a href="{{ route('vbrand.index') }}" class="float-right btn btn-sm btn-primary">
                                <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('vbrand.store') }}" enctype="multipart/form-data" id="vbrandForm">
                            @csrf
                            <input type="hidden" name="id" value="{{ old('id', $vbrand->id ?? '') }}">


                            <!-- image -->
                            <div class="row justify-content-center my-4">
                                <div class="col-md-6 text-center">
                                    <div class="border p-2 d-flex justify-content-center align-items-center" 
                                         style="height: 250px; width: 100%; max-width: 500px; margin: 0 auto;">
                                        <img id="imagePreview" 
                                             src="{{ $vbrand->image ?  $vbrand->image : '\storage\images\system\caver_no_photo.png' }}" 
                                             alt="Preview" 
                                             style="height: 100%; width: 100%; object-fit: contain;">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row justify-content-center">
                                <div class="form-group col-md-6">
                                    <label class="form-control-label" for="vbrand_image">
                                        {{ __('messages.image') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="custom-file">
                                        <input type="file" name="image" id="vbrand_image" class="custom-file-input" accept="image/*" onchange="previewImage(event)" required>
                                        <label class="custom-file-label upload-label">
                                            {{ __('messages.choose_file', ['file' => __('messages.image')]) }}
                                        </label>
                                    </div>
                                    @error('image')
                                    <small class="text-danger" style="font-size: 15px;">{{ $message }}</small>
                                @enderror                                
                            </div>
                            </div>
                            


                            <!-- name -->
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="name" class="form-control-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" value="{{ old('name', $vbrand->name ?? '') }}" placeholder="{{ __('messages.name') }}" class="form-control" required>
                                    @error('name')
                                    <small class="text-danger" style="font-size: 15px;">{{ $message }}</small>
                                @enderror
                                </div>


                            <!-- name_en -->

                                <div class="form-group col-md-6">
                                    <label for="name_en" class="form-control-label">{{ __('messages.name_en') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name_en" value="{{ old('name_en', $vbrand->name_en ?? '') }}" placeholder="{{ __('messages.name_en') }}" class="form-control" required>
                                    @error('name_en')
                                    <small class="text-danger" style="font-size: 15px;">{{ $message }}</small>
                                @enderror
                                </div>
                            </div>

                          

                     <!-- description -->
                     <div class="row">
                        <div class="form-group col-md-6">
                            <label for="description" class="form-control-label">
                                {{ __('messages.description') }} <span class="text-danger">*</span>
                            </label>
                            <textarea name="description" class="form-control textarea" rows="3" placeholder="{{ __('messages.description') }}">{{ old('description', $vbrand->description ?? '') }}</textarea>
                            
                            @error('description')
                            <small class="text-danger" style="font-size: 15px;">{{ $message }}</small>
                        @enderror
                        
                        </div>

                            <!-- description_en -->
                             <div class="form-group col-md-6">
                                <label for="description_en" class="form-control-label">
                                    {{ __('messages.description_en') }} <span class="text-danger">*</span>
                                </label>
                                <textarea name="description_en" class="form-control textarea" rows="3" placeholder="{{ __('messages.description_en') }}">{{ old('description_en', $vbrand->description_en ?? '') }}</textarea>
                                
                                @error('description_en')
                                <small class="text-danger" style="font-size: 15px;">{{ $message }}</small>
                            @enderror
                            
                            </div>

                        </div>
                            <button type="submit" class="btn btn-md btn-primary float-right">{{ trans('messages.save') }}</button>
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
    </script>
</x-master-layout>
