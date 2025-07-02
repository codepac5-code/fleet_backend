<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ __('messages.Add New Office') }}</h5>
                            <a href="{{ route('office.index') }}" class="float-right btn btn-sm btn-primary">
                                <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{-- ($office->id != null) ? route('office.update', $office->id) : route('office.store') --}}
                  
                            @if(isset($officedata->id))
                            <form method="POST" action="{{ route('office.update') }}" enctype="multipart/form-data" id="provider">
                                @csrf
                                <input type="hidden" name="id" value="{{ $officedata->id }}">
                            @else
                             <form method="POST" action="{{ route('office.store') }}" enctype="multipart/form-data" id="provider">
                                @csrf
                            @endif

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="name" class="form-control-label">
                                        {{ __('messages.officeName') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="officeName" id="name" class="form-control" placeholder="{{ __('messages.officeName') }}" value="{{ old('officeName', $officedata->officeName ?? '') }}" required>
                                    @error('officeName') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="email" class="form-control-label">
                                        {{ __('messages.email') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="{{ __('messages.email') }}" value="{{ old('email', $officedata->email ?? '') }}" required>
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                @if(!isset($officedata->id))
                                <div class="form-group col-md-6">
                                    <label for="password" class="form-control-label">
                                        {{ __('messages.password') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control" placeholder="{{ __('messages.password') }}" required>
                                        <span class="input-group-text toggle-password" data-target="password" style="cursor: pointer;">
                                            <i class="fa fa-eye"></i>
                                        </span>
                                    </div>
                                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <label for="password_confirmation" class="form-control-label">
                                        {{ __('messages.confirm_password') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="{{ __('messages.confirm_password') }}" required>
                                        <span class="input-group-text toggle-password" data-target="password_confirmation" style="cursor: pointer;">
                                            <i class="fa fa-eye"></i>
                                        </span>
                                    </div>
                                    @error('password_confirmation') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                @endif
                                <div class="form-group col-md-6">
                                    <label for="contact_number" class="form-control-label">
                                        {{ __('messages.contact_number') }}
                                    </label>
                                    <input type="text" name="contact_number" id="contact_number" class="form-control" placeholder="{{ __('messages.contact_number') }}" value="{{ old('contact_number', $officedata->contact_number ?? '') }}">
                                    @error('contact_number') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            
                            @php
                                $selected_services = isset($officedata) ? $officedata->services->pluck('serviceId')->toArray() : [];
                            @endphp
                                <div class="form-group col-md-8">
                                    <label for="services" class="form-control-label">
                                        {{ __('messages.services') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="serviceIds[]" class="select2js form-control" multiple="multiple" required
                                            data-placeholder="{{ __('messages.select_name', ['select' => __('messages.service')]) }}">
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" 
                                                {{ in_array($service->id, $selected_services) ? 'selected' : '' }}>
                                                {{ $service->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                

                                <div class="form-group col-md-6">
                                    <label for="country" class="form-control-label">
                                        {{ __('messages.country') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="country" id="country" class="form-control" required>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->name }}" {{ old('country', $officedata->country ?? '') == $country->name ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="city" class="form-control-label">
                                        {{ __('messages.city') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="city" id="city" class="form-control" required>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->name }}" {{ old('city', $officedata->city ?? '') == $city->name ? 'selected' : '' }}>
                                                {{ $city->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('city') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="region" class="form-control-label">
                                        {{ __('messages.region') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="region" id="region" class="form-control" placeholder="{{ __('messages.region') }}" value="{{ old('region', $officedata->region ?? '') }}" required>
                                    @error('region') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="address" class="form-control-label">{{ __('messages.address') }}</label>
                                    <textarea name="address" id="address" class="form-control" rows="3" placeholder="{{ __('messages.address') }}">{{ old('address', $officedata->address ?? '') }}</textarea>
                                    @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-control-label" for="banner_image">
                                        {{ __('messages.image') }} 
                                    </label>
                                    <div class="custom-file">
                                        <input type="file" name="logo" id="banner_image" class="custom-file-input" accept="image/*" onchange="previewImage(event)">
                                        <label class="custom-file-label upload-label">
                                            {{ __('messages.choose_file', ['file' => __('messages.logo')]) }}
                                        </label>
                                    </div>
                                    @error('logo') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6 text-center">
                                    <div class="border p-2 d-flex justify-content-center align-items-center" 
                                         style="height: 150px; width: 150px; max-width: 150px; margin: 0 auto; border-radius: 50%; overflow: hidden;">
                                        <img id="imagePreview" 
                                             src="{{ $officedata->logo ? $officedata->logo : '/storage/images/system/caver_no_photo.png' }}" 
                                             alt="Preview" 
                                             style="height: 100%; width: 100%; object-fit: cover;">
                                    </div>
                                </div>
                                {{-- <div class="form-group col-md-6">
                                    <label for="wallet_balance" class="form-control-label">
                                        {{ __('messages.walletBalance') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="walletBalance" id="wallet_balance" class="form-control" value="{{ old('walletBalance', $officedata->walletBalance ?? '') }}" required min="0" step="500">
                                </div> --}}
                            </div>
                            <button type="submit" class="btn btn-md btn-primary float-right">{{ __('messages.save') }}</button>
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


<script>
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            let input = document.getElementById(this.getAttribute('data-target'));
            let icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
</script>
</x-master-layout>
