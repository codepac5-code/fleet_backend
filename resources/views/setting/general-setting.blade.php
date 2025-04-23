<form action="{{ route('generalsetting') }}" method="POST" enctype="multipart/form-data" data-toggle="validator">
    @csrf

    <input type="hidden" name="id" value="{{ $generalsetting->id ?? '' }}">
    <input type="hidden" name="page" value="{{ $page }}">
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-control-label">{{ __('messages.name') }}</label>
                <input type="text" name="site_name" class="form-control" placeholder="{{ __('messages.site_name') }}" value="{{ old('site_name', $generalsetting->site_name ?? '') }}">
            </div>

            <div class="form-group">
                <label class="form-control-label">{{ __('messages.description') }}</label>
                <textarea name="site_description" class="form-control textarea" rows="3" placeholder="{{ __('messages.site_description') }}">{{ old('site_description', $generalsetting->site_description ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-control-label">{{ __('messages.website') }}</label>
                <input type="text" name="website" class="form-control" placeholder="{{ __('messages.website') }}" value="{{ old('website', $generalsetting->website ?? '') }}">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="form-control-label">{{ __('messages.email') }}</label>
                <input type="email" name="inquriy_email" class="form-control" placeholder="{{ __('messages.inquriy_email') }}" value="{{ old('inquriy_email', $generalsetting->inquriy_email ?? '') }}">
            </div>

            <div class="form-group">
                <label class="form-control-label">{{ __('messages.phone') }}</label>
                <input type="text" name="helpline_number" class="form-control" placeholder="{{ __('messages.helpline_number') }}" value="{{ old('helpline_number', $generalsetting->helpline_number ?? '') }}">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="country" class="form-control-label">{{ __('messages.country') }} <span class="text-danger">*</span></label>
                <select name="country" id="country" class="form-control select2js" required>
                    <option value="">{{ __('messages.select_name', ['select' => __('messages.country')]) }}</option>
                    @foreach($countries as $id => $name)
                        <option value="{{ $name }}" {{ old('country', $generalsetting->country ?? '') == $name ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="city" class="form-control-label">{{ __('messages.city') }} <span class="text-danger">*</span></label>
                <select name="city" id="city" class="form-control select2js" required>
                    <option value="">{{ __('messages.select_name', ['select' => __('messages.city')]) }}</option>
                    @foreach($cities as $id => $name)
                        <option value="{{ $name }}" {{ old('city', $generalsetting->city ?? '') == $name ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="region" class="form-control-label">{{ __('messages.region') }} <span class="text-danger">*</span></label>
                <input type="text" name="region" id="region" class="form-control" placeholder="{{ __('messages.region') }}" value="{{ old('region', $generalsetting->region?? '') }}" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-control-label">{{ __('messages.address') }}</label>
                <textarea name="address" class="form-control textarea" rows="3" placeholder="{{ __('messages.address') }}">{{ old('address', $generalsetting->address ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 text-right">
            <button type="submit" class="btn btn-md btn-primary">{{ __('messages.save') }}</button>
        </div>
    </div>
</form>


{{-- 
<script>
   

    $(document).on('keyup', '.helpline_number', function() {
        var contactNumberInput = document.getElementById('helpline_number');
        var inputValue = contactNumberInput.value;
        inputValue = inputValue.replace(/[^0-9+\- ]/g, '');
        if (inputValue.length > 15) {
            inputValue = inputValue.substring(0, 15);
        } 
        contactNumberInput.value = inputValue;
        if (inputValue.match(/^[0-9+\- ]+$/)) {
            $('.helpline_number').text('');
        } else {
            $('.helpline_number').text('Please enter a valid mobile number');
        }
    });

    $(document).ready(function() {
        loadCountry(); 
        var state_id = "{{ isset($generalsetting->state_id) ? $generalsetting->state_id : '' }}";
        var city_id = "{{ isset($generalsetting->city_id) ? $generalsetting->city_id : '' }}";
        
        stateName(country_id, state_id);
        $(document).on('change', '#country_id', function() {
            var country = $(this).val();
            $('#state_id').empty();
            $('#city_id').empty();
            stateName(country,state_id);
        })
        $(document).on('change', '#state_id', function() {
            var state = $(this).val();
            $('#city_id').empty();
            cityName(state, city_id);
        })
    })

        function loadCountry() {
            var country_id = "{{ isset($generalsetting->country_id) ? $generalsetting->country_id : '' }}";
            var country_route = "{{ route('ajax-list', ['type' => 'country']) }}";
            country_route = country_route.replace('amp;', '');

            $.ajax({
                url: country_route,
                success: function (result) {
                    $('#country_id').select2({
                        width: '100%',
                        placeholder: "{{ trans('messages.select_name', ['select' => trans('messages.state')]) }}",
                        data: result.results
                    });

                    if (country_id != null) {
                        $("#country_id").val(country_id).trigger('change');
                    }
                }
            });
        }
    function stateName(country, state = "") {
            var state_route = "{{ route('ajax-list', [ 'type' => 'state','country_id' =>'']) }}" + country;
            state_route = state_route.replace('amp;', '');

            $.ajax({
                url: state_route,
                success: function(result) {
                    $('#state_id').select2({
                        width: '100%',
                        placeholder: "{{ trans('messages.select_name',['select' => trans('messages.state')]) }}",
                        data: result.results
                    });
                    if (state != null || state != 0) {
                        $("#state_id").val(state).trigger('change');
                    }
                }
            });
        }

        function cityName(state, city = "") {
            var city_route = "{{ route('ajax-list', [ 'type' => 'city' ,'state_id' =>'']) }}" + state;
            city_route = city_route.replace('amp;', '');

            $.ajax({
                url: city_route,
                success: function(result) {
                    $('#city_id').select2({
                        width: '100%',
                        placeholder: "{{ trans('messages.select_name',['select' => trans('messages.city')]) }}",
                        data: result.results
                    });
                    if (city != null || city != 0) {
                        $("#city_id").val(city).trigger('change');
                    }
                }
            });
        }
</script> --}}
