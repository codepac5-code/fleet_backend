<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle}}</h5>
                            <a href="{{ route('vehicle.index') }}" class="float-right btn btn-sm btn-primary">
                                <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @php
            $selected_services = isset($vehicledata) ? $vehicledata->subServices->pluck('id')->toArray() : [];
        @endphp
            <form method="POST" action="{{ route('vehicle.store') }}" enctype="multipart/form-data" id="vehicleForm">
                @csrf
                @if(isset($vehicledata->id))
                    <input type="hidden" name="id" value="{{ $vehicledata->id }}">
                @endif   
                <div class="row">
                    <!-- Office -->
                    <div class="form-group col-md-6">
                        <label for="office_id" class="form-control-label">
                            {{ __('messages.office') }} <span class="text-danger">*</span>
                        </label>
                        <select name="office_id" id="office_id" class="select2js form-control" required>
                            <option value="">{{ __('messages.select_name', ['select' => __('messages.office')]) }}</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id }}" 
                                    {{ old('office_id', $vehicledata->officeId ?? '') == $office->id ? 'selected' : '' }}>
                                    {{ $office->officeName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
            
                    <!-- City -->
                    <div class="form-group col-md-6">
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
            
                    <!-- Vehicle Brand & Model Year -->
                    <div class="form-group col-md-6">
                        <label for="vehicle_brand" class="form-control-label">
                            {{ __('messages.vehicle_brand') }} <span class="text-danger">*</span>
                        </label>
                        <select name="vehicle_brand" id="vehicle_brand" class="select2js form-control" required>
                            <option value="">{{ __('messages.select_name', ['select' => __('messages.vehicle_brand')]) }}</option>
                            @foreach($vehicleBrands as $brand)
                                <option value="{{ $brand->name }}" 
                                    {{ old('vehicle_brand', $vehicledata->vehicleBrand ?? '') == $brand->name ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="model_year" class="form-control-label">
                            {{ __('messages.model_year') }} <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="model_year" id="model_year" class="form-control" required
                               min="2000" max="{{ date('Y') }}" step="1"
                               value="{{ old('model_year', $vehicledata->modelYear ?? '') }}">
                    </div>
            

                <!-- License Number -->
                <div class="form-group col-md-6">
                <label for="license_number" class="form-control-label">
                    {{ __('messages.license_number') }}
                </label>
                <input type="text" name="license_number" id="license_number" class="form-control" 
                        value="{{ old('license_number', $vehicledata->licenseNumber ?? '') }}">
                @error('license_number')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
                    <!-- Plate Number & Color -->
                    <div class="form-group col-md-6">
                        <label for="plate" class="form-control-label">
                            {{ __('messages.plate') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="plate" id="plate" class="form-control" required 
                               value="{{ old('plate', $vehicledata->plate ?? '') }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="color" class="form-control-label">
                            {{ __('messages.color') }} <span class="text-danger">*</span>
                        </label>
                        <select name="color" id="color" class="select2js form-control" required>
                            <option value="">{{ __('messages.select_name', ['select' => __('messages.color')]) }}</option>
                            @foreach($colors as $color)
                                <option value="{{ $color }}" {{ old('color', $vehicledata->color ?? '') == $color ? 'selected' : '' }}>
                                    {{ ucfirst($color) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
            
                    <!-- Seats Count & Sub Services -->
                    <div class="form-group col-md-4">
                        <label for="seats_count" class="form-control-label">{{ __('messages.seats_count') }}</label>
                        <input type="number" name="seats_count" id="seats_count" class="form-control"
                               value="{{ old('seats_count', $vehicledata->seatsCount ?? '') }}" step="1" min="2" max="20">
                    </div>
                    <div class="form-group col-md-8">
                        <label for="sub_service" class="form-control-label">
                            {{ __('messages.sub_service') }} <span class="text-danger">*</span>
                        </label>
                        <select name="serviceIds[]" class="select2js form-control" multiple="multiple" required
                                data-placeholder="{{ __('messages.select_name', ['select' => __('messages.service')]) }}">
                            @foreach($subServices as $service)
                                <option value="{{ $service->id }}" 
                                    {{ in_array($service->id, $selected_services) ? 'selected' : '' }}>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
            
                    <!-- Image Upload -->
                    <div class="form-group col-md-6">
                        <label class="form-control-label" for="banner_image">
                            {{ __('messages.image') }} 
                        </label>
                        <div class="custom-file">
                            <input type="file" name="image" id="banner_image" class="custom-file-input" accept="image/*" onchange="previewImage(event)">
                            <label class="custom-file-label upload-label">
                                {{ __('messages.choose_file', ['file' => __('messages.image')]) }}
                            </label>
                        </div>
                    </div>
                    <div class="form-group col-md-6 d-flex justify-content-center align-items-center">
                        <div class="border p-2" style="height: 170px; width: 200px; border-radius: 10px; overflow: hidden;">
                            <img id="imagePreview" 
                                 src="{{ old('image', $vehicledata->photo ?? get_default_image('service')) }}" 
                                 alt="Preview" 
                                 style="height: 100%; width: 100%; object-fit: cover;">
                        </div>
                    </div>
            
                    <!-- Description -->
                    <div class="form-group col-md-12">
                        <label for="description" class="form-control-label">{{ trans('messages.description') }}</label>
                        <textarea name="description" class="form-control textarea" rows="3" placeholder="{{ __('messages.description') }}">
                            {{ old('description', $vehicledata->description ?? '') }}
                        </textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-md btn-primary float-right">{{ __('messages.save') }}</button>
            </form>
            
                    </div>
                </div>
            </div>
        </div>
    </div>
    
        
</x-master-layout>

    <script>
$(document).ready(function () {
    $('#office_id').change(function () {
        let officeId = $(this).val();
        let driverSelect = $('#driver');

        if (officeId) {
            $.ajax({
                url: "{{ route('ajax-list', ['list_type' => 'drivers_list']) }}",
                type: "GET",
                data: { officeId: officeId },
                beforeSend: function () {
                    driverSelect.html('<option value="">{{ __("messages.loading") }}</option>');
                },
                success: function (response) {
                    console.log(response.results); 
                    driverSelect.empty();
                    driverSelect.empty().append('<option value="">{{ __("messages.select_name", ["select" => __("messages.driver")]) }}</option>');

                    if (response.status === "true" && response.results.length > 0) {
                        $.each(response.results, function (key, driver) {
                            let fullName = driver.firstName + ' ' + driver.lastName;
                            driverSelect.append('<option value="' + driver.id + '">' + fullName + '</option>');
                        });
                    } else {
                        driverSelect.append('<option value="">{{ __("messages.no_drivers_available") }}</option>');
                    }

                    driverSelect.trigger('change');
                },
                error: function () {
                    alert("{{ __('messages.error_loading_data') }}");
                }
            });
        } else {
            driverSelect.empty().append('<option value="">{{ __("messages.select_name", ["select" => __("messages.driver")]) }}</option>');
            driverSelect.trigger('change');
        }
    });
});

    </script>

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

