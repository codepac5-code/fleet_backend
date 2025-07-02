<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <h5 class="fw-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="" data-toggle="validator">
                        @csrf
                        <input type="hidden" name="id" value="{{ $setting_data->id ?? '' }}">

                        {{-- @include('partials._language_toggale') --}}

                        @foreach($language_array as $language)
                            <div id="form-language-{{ $language['id'] }}" class="language-form" style="display: {{ $language['id'] == app()->getLocale() ? 'block' : 'none' }};">
                                <div class="row">
                                    @php
                                        $field = 'value';
                                        $label = __('messages.data_deletion_request');

                                          $value ="dddddd";
                                        // $value = $language['id'] == 'en' 
                                        //     ? ($data_deletion_request ?? '') 
                                        //     : ($setting_data ? $setting_data->translate($field, $language['id']) : '');
                                        $name = $language['id'] == 'en' ? $field : "translations[{$language['id']}][$field]";
                                    @endphp

                                    <div class="form-group col-md-12">
                                        <label for="{{ $name }}" class="form-control-label language-label">
                                            {{ $label }} <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="{{ $name }}" class="form-control tinymce-data_deletion_request" rows="3" placeholder="{{ $label }}">{{ $value }}</textarea>
                                        <small class="help-block with-errors text-danger"></small>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="form-group col-md-4">
                            <label for="status" class="form-control-label">{{ __('messages.status') }}</label>
                            <div class="form-control d-flex align-items-center justify-content-between">
                                <label for="status" class="mb-0">{{ __('messages.status') }}</label>
                                <div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                                    <input type="checkbox" class="custom-control-input" name="status" id="status" value="1" {{ ($status ?? '1') == '1' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="status"></label>
                                </div>
                            </div>
                        </div>

                        @if(auth()->user()->hasRole(['admin', 'demo_admin']))
                            <button type="submit" class="btn btn-md btn-primary float-end">{{ __('messages.save') }}</button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    @section('bottom_script')
        <script>
            (function($) {
                $(document).ready(function(){
                    tinymceEditor('.tinymce-data_deletion_request', '', function (ed) {}, 450);
                });
            })(jQuery);
        </script>
    @endsection
</x-master-layout>
