<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ __('messages.Add_New_Employee') }}</h5>
                            <a href="{{ route('employee.index') }}" class="float-right btn btn-sm btn-primary">
                                <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ isset($employee) ? route('employee.store', $employee->id) : route('employee.store') }}" enctype="multipart/form-data">
                            @csrf
                            @method('POST')

                            @if (isset($employee->id))
                                <input type="hidden" name="id" value="{{$employee->id}}">
                            @endif

                            {{-- صورة الموظف --}}
                            <div class="card card-block card-stretch mb-4">
                                <div class="card-body p-1">
                                    <div class="d-flex flex-column align-items-center my-4">
                                        <div class="border p-2" style="height: 150px; width: 150px; border-radius: 50%; overflow: hidden; display: flex; justify-content: center; align-items: center;">
                                            <img id="imagePreview" src="{{ isset($employee) && $employee->photo ? $employee->photo : get_default_image('employee') }}" alt="Preview" style="height: 100%; width: 100%; object-fit: cover; border-radius: 50%;">
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

                            {{-- القسم 1: معلومات الحساب --}}
                            <div class="border rounded p-3 mb-4">
                                <h5 class="mb-3">{{ __('messages.account_information') }}</h5>
                                <div class="row">
                                    @if(auth()->user()->hasAnyRole(['super-admin']))
                                        <div class="form-group col-md-4">
                                            <label>{{ __('messages.office') }} <span class="text-danger">*</span></label>
                                            <select name="officeId" class="select2js form-control" required>
                                                <option value="">{{ __('messages.select_name', ['select' => __('messages.office')]) }}</option>
                                                @foreach($offices as $office)
                                                    <option value="{{ $office->id }}" {{ old('officeId', $employee->officeId ?? '') == $office->id ? 'selected' : '' }}>
                                                        {{ $office->officeName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    @if(auth()->user()->hasAnyRole(['office']))
                                        <input type="hidden" name="officeId" value="{{ auth()->user()->id }}">
                                    @endif

                                    <div class="form-group col-md-4">
                                        <label>{{ __('messages.first_name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="firstName" class="form-control" value="{{ old('firstName', $employee->firstName ?? '') }}" required>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('messages.last_name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="lastName" class="form-control" value="{{ old('lastName', $employee->lastName ?? '') }}" required>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('messages.email') }} <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email ?? '') }}" required>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('messages.phone_number') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="phoneNumber" class="form-control" value="{{ old('phoneNumber', $employee->phoneNumber ?? '') }}" required>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('messages.gender') }}</label>
                                        <select name="gender" class="select2js form-control">
                                            <option value="male" {{ old('gender', $employee->gender ?? '') == 'male' ? 'selected' : '' }}>{{ __('messages.male') }}</option>
                                            <option value="female" {{ old('gender', $employee->gender ?? '') == 'female' ? 'selected' : '' }}>{{ __('messages.female') }}</option>
                                        </select>
                                    </div>

                                    @if(!isset($employee->id))
                                        <div class="form-group col-md-4">
                                            <label>{{ __('messages.password') }} <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" name="password" class="form-control" required>
                                                <span class="input-group-text toggle-password" data-target="password"><i class="fa fa-eye"></i></span>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label>{{ __('messages.confirm_password') }} <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" name="password_confirmation" class="form-control" required>
                                                <span class="input-group-text toggle-password" data-target="password_confirmation"><i class="fa fa-eye"></i></span>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="form-group col-md-4">
                                        <label>{{ __('messages.role') }} <span class="text-danger">*</span></label>
                                        <select name="role" class="select2js form-control" required>
                                            <option value="">{{ __('messages.select_name', ['select' => __('messages.role')]) }}</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ old('roleId', $employee->roleId ?? '') == $role->id ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- القسم 2: معلومات الوظيفة --}}
                            <div class="border rounded p-3 mb-4">
                                <h5 class="mb-3">{{ __('messages.job_information') }}</h5>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>{{ __('messages.job_title_en') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="employeeJobName_en" class="form-control" value="{{ old('employeeJobName_en', $employee->employeeJobName_en ?? '') }}" required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>{{ __('messages.job_title_ar') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="employeeJobName_ar" class="form-control" value="{{ old('employeeJobName_ar', $employee->employeeJobName_ar ?? '') }}" required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>{{ __('messages.job_description_en') }}</label>
                                        <textarea name="job_description_en" class="form-control">{{ old('job_description_en', $employee->job_description_en ?? '') }}</textarea>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>{{ __('messages.job_description_ar') }}</label>
                                        <textarea name="job_description_ar" class="form-control">{{ old('job_description_ar', $employee->job_description_ar ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- القسم 3: معلومات العنوان --}}
                            <div class="border rounded p-3 mb-4">
                                <h5 class="mb-3">{{ __('messages.address_information') }}</h5>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>{{ __('messages.country') }} <span class="text-danger">*</span></label>
                                        <select name="country" class="select2js form-control" required>
                                            @foreach($countries as $country)
                                                <option value="{{ $country->name }}" {{ old('country', $employee->country ?? '') == $country->name ? 'selected' : '' }}>
                                                    {{ $country->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('messages.city') }} <span class="text-danger">*</span></label>
                                        <select name="city" class="select2js form-control" required>
                                            @foreach($cities as $city)
                                                <option value="{{ $city->name }}" {{ old('city', $employee->city ?? '') == $city->name ? 'selected' : '' }}>
                                                    {{ $city->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>{{ __('messages.region') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="region" class="form-control" value="{{ old('region', $employee->region ?? '') }}" required>
                                    </div>

                                    <div class="form-group col-md-12">
                                        <label>{{ __('messages.address') }} <span class="text-danger">*</span></label>
                                        <textarea name="address" class="form-control" required>{{ old('address', $employee->address ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-md btn-primary">{{ __('messages.Add_New_Employee') }}</button>
                            </div>
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
            }
        }

        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                let input = document.getElementsByName(this.dataset.target)[0];
                let icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });
    </script>
</x-master-layout>
