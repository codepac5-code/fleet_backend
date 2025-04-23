<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ __('messages.Add_New_User') }}</h5>
                            <a href="{{ route('user.index') }}" class="float-right btn btn-sm btn-primary">
                                <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
    
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('user.store') }}" enctype="multipart/form-data" data-toggle="validator">
                            @csrf
                            @if (isset($userData->id))
                             <input type="hidden" name="id" value="{{$userData->id}}">
                            @endif
    
                            <div class="d-flex flex-column align-items-center my-4">
                                <div class="border p-2"
                                     style="height: 150px; width: 150px; border-radius: 50%; overflow: hidden; display: flex; justify-content: center; align-items: center;">
                                    <img id="imagePreview"
                                         src="{{ old('photo', $userData->photo ?? get_default_image('user')) }}"
                                         alt="Preview"
                                         style="height: 100%; width: 100%; object-fit: cover; border-radius: 50%;">
                                </div>
    
                                <div class="form-group mt-3" style="width: 25%;"> 
                                    <div class="custom-file">
                                        <input type="file" name="photo" id="photo" class="custom-file-input" accept="image/*" onchange="previewImage(event)">
                                        <label class="custom-file-label upload-label text-center">
                                            {{ __('messages.choose_file', ['file' => __('messages.image')]) }}
                                        </label>
                                    </div>
                                    @error('photo')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
    
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="firstName" class="form-control-label">{{ __('messages.first_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="firstName" id="firstName" class="form-control" value="{{ old('firstName', $userData->firstName ?? '') }}" placeholder="{{ __('messages.first_name') }}" required>
                                    @error('firstName')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
    
                                <div class="form-group col-md-4">
                                    <label for="lastName" class="form-control-label">{{ __('messages.last_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="lastName" id="lastName" class="form-control" value="{{ old('lastName', $userData->lastName ?? '') }}" placeholder="{{ __('messages.last_name') }}" required>
                                    @error('lastName')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
    
                                <div class="form-group col-md-4">
                                    <label for="phoneNumber" class="form-control-label">{{ __('messages.phone_number') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="phoneNumber" id="phoneNumber" class="form-control" value="{{ old('phoneNumber', $userData->phoneNumber ?? '') }}" placeholder="{{ __('messages.phone_number') }}" required pattern="[0-9]{10}" title="Enter a valid 10-digit phone number">
                                    @error('phoneNumber')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
    
                                @if(!isset($userData->id))
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
                                    <label for="gender" class="form-control-label">{{ __('messages.gender') }}</label>
                                    <select name="gender" id="gender" class="select2js form-control">
                                        {{-- <option value="">{{ __('messages.select_name', ['select' => __('messages.gender')]) }}</option> --}}
                                        <option value="male" {{ old('gender', $userData->gender ?? '') == 'male' ? 'selected' : '' }}>{{ __('messages.male') }}</option>
                                        <option value="female" {{ old('gender', $userData->gender ?? '') == 'female' ? 'selected' : '' }}>{{ __('messages.female') }}</option>
                                    </select>
                                </div>
    
                                {{-- <div class="form-group col-md-4">
                                    <label for="isRegistered" class="form-control-label">{{ __('messages.is_registered') }} <span class="text-danger">*</span></label>
                                    <select name="isRegistered" id="isRegistered" class="form-control" required>
                                        <option value="1" {{ old('isRegistered', $userData->isRegistered ?? '') == '1' ? 'selected' : '' }}>{{ __('messages.yes') }}</option>
                                        <option value="0" {{ old('isRegistered', $userData->isRegistered ?? '') == '0' ? 'selected' : '' }}>{{ __('messages.no') }}</option>
                                    </select>
                                    @error('isRegistered')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div> --}}
    
                                {{-- <div class="form-group col-md-4">
                                    <label for="walletBalance" class="form-control-label">{{ __('messages.wallet_balance') }}</label>
                                    <input type="number" name="walletBalance" id="walletBalance" class="form-control" value="{{ old('walletBalance', $userData->walletBalance ?? '') }}" placeholder="{{ __('messages.wallet_balance') }}" step="0.01" min="0">
                                    @error('walletBalance')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div> --}}
                        </div>
                            <button type="submit" class="btn btn-md btn-primary float-right">{{ __('messages.Add_New_user') }}</button>
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
