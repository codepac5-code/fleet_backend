<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('term-condition-save') }}" data-toggle="validator">
                            @csrf
                            <input type="hidden" name="id" value="{{ old('id', $setting_data->id ?? '') }}">

                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="terms_condition" class="form-control-label">{{ __('messages.terms_condition') }}</label>
                                    <textarea name="value" class="form-control tinymce-terms_condition" placeholder="{{ __('messages.terms_condition') }}">{{ old('value', $setting_data->value ?? '') }}</textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-md btn-primary float-right">{{ __('messages.save') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('bottom_script')
        <script>
            (function($) {
                $(document).ready(function(){
                    tinymceEditor('.tinymce-terms_condition', ' ', function (ed) {}, 450);
                });
            })(jQuery);
        </script>
    @endsection
</x-master-layout>
