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
                          <label for="name" class="form-control-label">{{ __('messages.name_arabic') }} <span class="text-danger">*</span></label>
                          <input type="text" name="name" id="name" value="{{ old('name', $subservice->name ?? '') }}" placeholder="{{ __('messages.name_arabic') }}" class="form-control required-field">
                          @error('name')
                            <small class="text-danger">{{ $message }}</small>
                          @enderror
                        </div>

                        <div class="form-group col-md-4">
                          <label for="name_en" class="form-control-label">{{ __('messages.name_en') }} <span class="text-danger">*</span></label>
                          <input type="text" name="name_en" id="name_en" value="{{ old('name_en', $subservice->name_en ?? '') }}" placeholder="{{ __('messages.name_en') }}" class="form-control" required>
                          @error('name_en')
                            <small class="text-danger">{{ $message }}</small>
                          @enderror
                        </div>

                        <div class="form-group col-md-4">
                          <label for="serviceId" class="form-control-label">{{ __('messages.service') }} <span class="text-danger">*</span></label>
                          <select name="serviceId" id="serviceId" class="select2js form-control" required>
                            <option value="">{{ __('messages.select_name', ['select' => __('messages.service')]) }}</option>
                            @foreach($services as $service)
                              <option value="{{ $service->id }}"
                                {{ old('serviceId', $subservice->serviceId ?? null) == $service->id ? 'selected' : '' }}
                                data-travel-service="{{ $service->travel_serviec ?? $service->travel_service }}">
                                {{ $service->title }}
                              </option>
                            @endforeach
                          </select>
                          @error('serviceId')
                            <small class="text-danger">{{ $message }}</small>
                          @enderror
                        </div>
                      </div>

                      <div class="row" id="priceInputsContainer" style="display: none;">
                        <div class="col-md-12">
                          <div class="alert alert-info mb-3">{{ __('messages.travel_service') }}</div>
                          <div id="travelRoutesContainer">
                            <div class="route-item mb-4">
                              <div class="row">
                                <div class="form-group col-md-4">
                                  <label for="departureCity_0" class="form-control-label">{{ __('messages.departure_city') }} <span class="text-danger">*</span></label>
                                  <select name="routes[0][departureCity]" id="departureCity_0" class="select2js form-control" required>
                                    <option value="">{{ __('messages.select_name', ['select' => __('messages.departure_city')]) }}</option>
                                    @foreach($cities as $city)
                                      <option value="{{ $city->name }}" {{ old('routes.0.departureCity', $subservice->routes[0]['departureCity'] ?? '') == $city->name ? 'selected' : '' }}>{{ $city->name }}</option>
                                    @endforeach
                                  </select>
                                  @error('routes.0.departureCity')
                                    <small class="text-danger">{{ $message }}</small>
                                  @enderror
                                </div>

                                <div class="form-group col-md-1 d-flex align-items-center justify-content-center">
                                  <button type="button" class="btn btn-sm btn-outline-secondary p-2 swap-btn" title="{{ __('messages.swap_cities') }}">
                                    <i class="fas fa-exchange-alt"></i>
                                  </button>
                                </div>

                                <div class="form-group col-md-4">
                                  <label for="arrivalCity_0" class="form-control-label">{{ __('messages.arrival_city') }} <span class="text-danger">*</span></label>
                                  <select name="routes[0][arrivalCity]" id="arrivalCity_0" class="select2js form-control" required>
                                    <option value="">{{ __('messages.select_name', ['select' => __('messages.arrival_city')]) }}</option>
                                    @foreach($cities as $city)
                                      <option value="{{ $city->name }}" {{ old('routes.0.arrivalCity', $subservice->routes[0]['arrivalCity'] ?? '') == $city->name ? 'selected' : '' }}>{{ $city->name }}</option>
                                    @endforeach
                                  </select>
                                  @error('routes.0.arrivalCity')
                                    <small class="text-danger">{{ $message }}</small>
                                  @enderror
                                </div>

                                <div class="form-group col-md-3">
                                  <label for="tripPrice_0" class="form-control-label">{{ __('messages.trip_price') }} <span class="text-danger">*</span></label>
                                  <div class="input-group">
                                    <input type="number" id="tripPrice_0" name="routes[0][tripPrice]" class="form-control" step="100" min="0" value="{{ old('routes.0.tripPrice', $subservice->routes[0]['tripPrice'] ?? 0.00) }}" required>
                                    <div class="input-group-append">
                                      <button class="btn btn-outline-danger remove-route" type="button" disabled>
                                        <i class="fas fa-trash"></i>
                                      </button>
                                    </div>
                                  </div>
                                  @error('routes.0.tripPrice')
                                    <small class="text-danger">{{ $message }}</small>
                                  @enderror
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="form-group">
                            <button type="button" id="addRoute" class="btn btn-primary"><i class="fas fa-plus"></i> {{ __('messages.add_new_route') }}</button>
                          </div>
                        </div>
                      </div>

                      <div class="row" id="regularServiceMessage" style="display: none;">
                        <div class="col-md-12">
                          <div class="alert alert-success mb-3">{{ __('messages.regular_service') }}</div>
                        </div>
                        <div class="form-group col-md-3">
                          <label for="openPrice">{{ __('messages.open_price') }} <span class="text-danger">*</span></label>
                          <input type="number" id="openPrice" name="openPrice" class="form-control price-input" step="100" min="0" value="{{ old('openPrice', $subservice->openPrice ?? 0.00) }}">
                          @error('openPrice')
                            <small class="text-danger">{{ $message }}</small>
                          @enderror
                        </div>

                        <div class="form-group col-md-3">
                          <label for="kmPrice">{{ __('messages.km_price') }} <span class="text-danger">*</span></label>
                          <input type="number" id="kmPrice" name="kmPrice" class="form-control price-input" step="50" min="0" value="{{ old('kmPrice', $subservice->kmPrice ?? 0.00) }}">
                          @error('kmPrice')
                            <small class="text-danger">{{ $message }}</small>
                          @enderror
                        </div>

                        <div class="form-group col-md-3">
                          <label for="minutePrice">{{ __('messages.minute_price') }} <span class="text-danger">*</span></label>
                          <input type="number" id="minutePrice" name="minutePrice" class="form-control price-input" step="5" min="0" value="{{ old('minutePrice', $subservice->minutePrice ?? 0.00) }}">
                          @error('minutePrice')
                            <small class="text-danger">{{ $message }}</small>
                          @enderror
                        </div>
                      </div>

                      <div class="row mt-3">
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

                        <div class="form-group col-md-4">
                          <label class="form-control-label" for="banner_image">{{ __('messages.image') }} <span class="text-danger">*</span></label>
                          <div class="d-flex align-items-center gap-3">
                            <div class="border p-2 d-flex justify-content-center align-items-center" style="height: 85px; width: 130px; border-radius: 10px; overflow: hidden;">
                              <img id="imagePreview" src="{{ $subservice->image ?? get_default_image('sub_service') }}" alt="Preview" style="height: 100%; width: 100%; object-fit: cover;">
                            </div>
                            <div class="custom-file">
                              <input type="hidden" name="current_image" value="{{ $subservice->image ?? '' }}">
                              <input type="file" name="image" id="banner_image" class="custom-file-input" accept="image/*" onchange="previewImage(event)">
                              <label class="custom-file-label upload-label" id="fileLabel">{{ $subservice->image ? basename($subservice->image) : __('messages.choose_file', ['file' => __('messages.image')]) }}</label>
                            </div>
                          </div>
                          @error('image')
                            <small class="text-danger">{{ $message }}</small>
                          @enderror
                        </div>
                      </div>

                      <div class="row mt-3">
                        <div class="form-group col-md-6">
                          <label for="description" class="form-control-label">{{ trans('messages.description') }}</label>
                          <textarea name="description" class="form-control textarea" rows="3" placeholder="{{ __('messages.description') }}">{{ old('description', $subservice->description) }}</textarea>
                          @error('description')
                            <small class="text-danger">{{ $message }}</small>
                          @enderror
                        </div>

                        <div class="form-group col-md-6">
                          <label for="description_en" class="form-control-label">{{ trans('messages.description_en') }}</label>
                          <textarea name="description_en" class="form-control textarea" rows="3" placeholder="{{ __('messages.description_en') }}">{{ old('description_en', $subservice->description_en) }}</textarea>
                          @error('description_en')
                            <small class="text-danger">{{ $message }}</small>
                          @enderror
                        </div>
                      </div>

                      <div class="row mt-3">
                        <div class="col-md-12 text-right">
                          <button type="submit" id="saveButton" class="btn btn-md btn-primary">{{ __('messages.save') }}</button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

<script>
$(document).ready(function() {
    let routeCounter = 1;

    $('#addRoute').click(function() {
        const newRoute = `
        <div class="route-item mb-4">
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="departureCity_${routeCounter}" class="form-control-label">{{ __('messages.departure_city') }} <span class="text-danger">*</span></label>
                    <select name="routes[${routeCounter}][departureCity]" id="departureCity_${routeCounter}" class="select2js form-control" required>
                        <option value="">{{ __('messages.select_name', ['select' => __('messages.departure_city')]) }}</option>
                        @foreach($cities as $city)
                        <option value="{{ $city->name }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-1 d-flex align-items-center justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary p-2 swap-btn" title="{{ __('messages.swap_cities') }}">
                        <i class="fas fa-exchange-alt"></i>
                    </button>
                </div>
                <div class="form-group col-md-4">
                    <label for="arrivalCity_${routeCounter}" class="form-control-label">{{ __('messages.arrival_city') }} <span class="text-danger">*</span></label>
                    <select name="routes[${routeCounter}][arrivalCity]" id="arrivalCity_${routeCounter}" class="select2js form-control" required>
                        <option value="">{{ __('messages.select_name', ['select' => __('messages.arrival_city')]) }}</option>
                        @foreach($cities as $city)
                        <option value="{{ $city->name }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="tripPrice_${routeCounter}" class="form-control-label">{{ __('messages.trip_price') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" id="tripPrice_${routeCounter}" name="routes[${routeCounter}][tripPrice]" class="form-control" step="100" min="0" value="0.00" required>
                        <div class="input-group-append">
                            <button class="btn btn-outline-danger remove-route" type="button"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        `;
        $('#travelRoutesContainer').append(newRoute);
        $(`#departureCity_${routeCounter}, #arrivalCity_${routeCounter}`).select2();
        routeCounter++;
    });

    $(document).on('click', '.remove-route', function() {
        if($('.route-item').length > 1) {
            $(this).closest('.route-item').remove();
            reindexRoutes();
        }
    });

    $(document).on('click', '.swap-btn', function() {
        const routeItem = $(this).closest('.route-item');
        const departureCity = routeItem.find('select[name*="departureCity"]');
        const arrivalCity = routeItem.find('select[name*="arrivalCity"]');
        const tempVal = departureCity.val();
        departureCity.val(arrivalCity.val()).trigger('change');
        arrivalCity.val(tempVal).trigger('change');
    });

    function reindexRoutes() {
        $('.route-item').each(function(index) {
            $(this).find('select, input').each(function() {
                const name = $(this).attr('name').replace(/\[\d+\]/, `[${index}]`);
                $(this).attr('name', name);
                $(this).attr('id', $(this).attr('id').replace(/\d+$/, index));
            });
        });
    }

    const $serviceSelect = $('#serviceId');
    const $priceInputsContainer = $('#priceInputsContainer');
    const $regularServiceMessage = $('#regularServiceMessage');
    const $priceInputs = $('.price-input');

    $serviceSelect.on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const isTravelService = selectedOption.data('travel-service') == true || selectedOption.data('travel-service') == 1;

        $priceInputsContainer.toggle(isTravelService);
        $regularServiceMessage.toggle(!isTravelService);
        $priceInputs.prop('required', isTravelService);

        // Enable/disable fields to send only needed ones
        $('#priceInputsContainer select, #priceInputsContainer input').prop('disabled', !isTravelService);
        $('#openPrice, #kmPrice, #minutePrice').prop('disabled', isTravelService);
    });

    if ($serviceSelect.val()) { $serviceSelect.trigger('change'); }

    $('#subservice').on('submit', function() {
        const selectedOption = $serviceSelect.find('option:selected');
        const isTravelService = selectedOption.data('travel-service') == true || selectedOption.data('travel-service') == 1;

        $('#priceInputsContainer select, #priceInputsContainer input').prop('disabled', !isTravelService);
        $('#openPrice, #kmPrice, #minutePrice').prop('disabled', isTravelService);
    });
});

function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const output = document.getElementById('imagePreview');
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
    document.getElementById('fileLabel').textContent = event.target.files[0].name;
}
</script>

</x-master-layout>
