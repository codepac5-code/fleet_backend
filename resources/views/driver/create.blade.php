<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ __('messages.Add_New_Driver') }}</h5>
                            <a href="{{ route('driver.index') }}" class="float-right btn btn-sm btn-primary">
                                <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ isset($driver) ? route('driver.store', $driver->id) : route('driver.store') }}" enctype="multipart/form-data">
                            @csrf
                                @method('POST')
                            @if (isset($driver->id))
                            <input type="hidden" name="id" value="{{$driver->id}}">
                           @endif
    
                            <div class="card card-block card-stretch">
                                <div class="card-body p-1">
                                    <div class="d-flex flex-column align-items-center my-4">
                                        <div class="border p-2" style="height: 150px; width: 150px; border-radius: 50%; overflow: hidden; display: flex; justify-content: center; align-items: center;">
                                            <img id="imagePreview" src="{{ isset($driver) && $driver->photo ? $driver->photo : get_default_image('driver') }}" alt="Preview" style="height: 100%; width: 100%; object-fit: cover; border-radius: 50%;">
                                        </div>
                                        <div class="form-group mt-3" style="width: 25%;"> 
                                            <div class="custom-file">
                                                <input type="file" name="image" id="banner_image" class="custom-file-input" accept="image/*" onchange="previewImage(event)">
                                                <label class="custom-file-label upload-label text-center">{{ __('messages.choose_file', ['file' => __('messages.image')]) }}</label>
                                            </div>
                                            @error('image')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
    
                            <div class="row">
                                @if(auth()->user()->hasAnyRole(['super-admin']))
                                <div class="form-group col-md-4">
                                    <label for="officeId" class="form-control-label">{{ __('messages.office') }} <span class="text-danger">*</span></label>
                                    <select name="officeId" id="officeId" class="select2js form-control" required>
                                        <option value="">{{ __('messages.select_name', ['select' => __('messages.office')]) }}</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}" {{ old('officeId', $driver->officeId ?? '') == $office->id ? 'selected' : '' }}>
                                                {{ $office->officeName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('officeId')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                @endif
                                @if(auth()->user()->hasAnyRole(['office']))
                                
                                    <input type="hidden" name="officeId" id='officeId' value="{{auth()->user()->id}}">
                                @endif
    
                                <div class="form-group col-md-4">
                                    <label for="firstName" class="form-control-label">{{ __('messages.first_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="firstName" id="firstName" class="form-control" value="{{ old('firstName', $driver->firstName ?? '') }}" placeholder="{{ __('messages.first_name') }}" required>
                                    @error('firstName')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
    
                                <div class="form-group col-md-4">
                                    <label for="lastName" class="form-control-label">{{ __('messages.last_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="lastName" id="lastName" class="form-control" value="{{ old('lastName', $driver->lastName ?? '') }}" placeholder="{{ __('messages.last_name') }}" required>
                                    @error('lastName')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
    
                                <div class="form-group col-md-4">
                                    <label for="gender" class="form-control-label">{{ __('messages.gender') }}</label>
                                    <select name="gender" id="gender" class="select2js form-control">
                                        {{-- <option value="">{{ __('messages.select_name', ['select' => __('messages.gender')]) }}</option> --}}
                                        <option value="male" {{ old('gender', $driver->gender ?? '') == 'male' ? 'selected' : '' }}>{{ __('messages.male') }}</option>
                                        <option value="female" {{ old('gender', $driver->gender ?? '') == 'female' ? 'selected' : '' }}>{{ __('messages.female') }}</option>
                                    </select>
                                </div>
    
                                <div class="form-group col-md-4">
                                    <label for="phoneNumber" class="form-control-label">{{ __('messages.phone_number') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="phoneNumber" id="phoneNumber" class="form-control" value="{{ old('phoneNumber', $driver->phoneNumber ?? '') }}" placeholder="{{ __('messages.phone_number') }}" required pattern="[0-9]{10}">
                                    @error('phoneNumber')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
    
                                @if(!isset($driver->id))
                                <div class="form-group col-md-4">
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
                                
                                <div class="form-group col-md-4">
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

                                <div class="form-group col-md-4">
                                <label for="vehicle" class="form-control-label">{{ __('messages.vehicle_number') }} <span class="text-danger">*</span></label>
                                <select name="vehicleId" id="vehicleId" class="select2js form-control" required>
                                    <option value="null">{{ __('messages.select_name', ['select' => __('messages.vehicle_number')]) }}</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicleId', $driver->vehicleId ?? '') == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->plate }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicleId')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
    
    
                                <div class="form-group col-md-4">
                                    <label for="country" class="form-control-label">{{ __('messages.country') }} <span class="text-danger">*</span></label>
                                    <select name="country" id="country" class="select2js form-control" required>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->name }}" {{ old('country', $driver->country ?? '') == $country->name ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
    
                    <!-- City -->
                    <div class="form-group col-md-4">
                        <label for="city" class="form-control-label">
                            {{ __('messages.city') }} <span class="text-danger">*</span>
                        </label>
                        <select name="city" id="city" class="select2js form-control" required>
                            <option value="">{{ __('messages.select_name', ['select' => __('messages.city')]) }}</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->name }}" 
                                    {{ old('city', $vehicledata->city ?? '') == $city->name ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                                <div class="form-group col-md-4">
                                    <label for="region" class="form-control-label">{{ __('messages.region') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="region" id="region" class="form-control" value="{{ old('region', $driver->region ?? '') }}" placeholder="{{ __('messages.region') }}" required>
                                    @error('region')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
    
                                {{-- <div class="form-group col-md-4">
                                    <label for="walletBalance" class="form-control-label">{{ __('messages.wallet_balance') }}</label>
                                    <input type="number" name="walletBalance" id="walletBalance" class="form-control" value="{{ old('walletBalance', $driver->walletBalance ?? '') }}" placeholder="{{ __('messages.wallet_balance') }}" step="500" min="0">
                                    @error('walletBalance')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>--}}
                            </div> 
    
                            <button type="submit" class="btn btn-md btn-primary float-right">{{ __('messages.Add_New_Driver') }}</button>
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
