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

                            {{-- Tabs Navigation --}}
                            <ul class="nav nav-tabs mb-3" id="langTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="arabic-tab" data-toggle="tab" href="#arabic" role="tab" aria-controls="arabic" aria-selected="true">العربية</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="english-tab" data-toggle="tab" href="#english" role="tab" aria-controls="english" aria-selected="false">English</a>
                                </li>
                            </ul>

                            {{-- Tabs Content --}}
                            <div class="tab-content" id="langTabsContent">
                                <div class="tab-pane fade show active" id="arabic" role="tabpanel" aria-labelledby="arabic-tab">
                                    <div class="form-group">
                                        <label for="terms_condition_ar" class="form-control-label">{{ __('messages.terms_condition') }} (العربية)</label>
                                        <textarea name="value" class="form-control tinymce-terms_condition" placeholder="{{ __('messages.terms_condition') }}">{{ old('value', $setting_data->value ?? '') }}</textarea>
                                        @error('value')
                                        <span class="text-danger">{{ $message }}</span>
                                             @enderror
                                    </div>
                                  

                                </div>
                                <div class="tab-pane fade" id="english" role="tabpanel" aria-labelledby="english-tab">
                                    <div class="form-group">
                                        <label for="terms_condition_en" class="form-control-label">{{ __('messages.terms_condition') }} (English)</label>
                                        <textarea name="value_en" class="form-control tinymce-terms_condition" placeholder="{{ __('messages.terms_condition') }}">{{ old('value_en', $setting_data->value_en ?? '') }}</textarea>
                                        @error('value_en')
                                        <span class="text-danger">{{ $message }}</span>
                                             @enderror
                                    </div>
  
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

                $('form').on('submit', function() {
                    tinymce.triggerSave();
                });
            });
        })(jQuery);
    </script>
@endsection


</x-master-layout>
