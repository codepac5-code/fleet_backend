<x-master-layout>
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-stretch shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold">{{ __('messages.Add_new_Department') }}</h5>
                        <a href="{{ route('department.index') }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('departments.store') }}" id="departmentForm">
                            @csrf
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="name_en" class="form-control-label">{{ __('messages.department_name_en') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name_en" id="name_en" class="form-control" value="{{ old('name_en') }}" required>
                                    @error('name_en')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="name_ar" class="form-control-label">{{ __('messages.department_name_ar') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name_ar" id="name_ar" class="form-control" value="{{ old('name_ar') }}" required>
                                    @error('name_ar')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="employees" class="form-control-label">{{ __('messages.assign_employees') }}</label>
                                <select name="employees[]" id="employees" class="select2js form-control" multiple style="height: 180px;">
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ in_array($employee->id, old('employees', $selected_employees ?? [])) ? 'selected' : '' }}>
                                            {{ $employee->firstName }} {{ $employee->lastName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employees')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary float-right">{{ __('messages.save') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-master-layout>
