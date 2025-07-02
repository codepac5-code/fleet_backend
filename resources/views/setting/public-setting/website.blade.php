<form action="" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        {{-- 🟦 الرئيسية --}}
        <div class="col-md-12">
            <div class="card card-block card-stretch mb-4">
                <div class="card-header">
                    <h5 class="text-primary mb-0">الرئيسية</h5>
                </div>
                <div class="card-body row">
                    <div class="form-group col-md-6">
                        <label for="HomeTitle" class="form-control-label">عنوان الصفحة الرئيسية</label>
                        <input type="text" name="HomeTitle" id="HomeTitle" class="form-control" placeholder="عنوان الصفحة" value="{{ old('HomeTitle', $settings['HomeTitle'] ?? '') }}">
                        @error('HomeTitle') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label for="HomeSubTitle" class="form-control-label">العنوان الفرعي</label>
                        <input type="text" name="HomeSubTitle" id="HomeSubTitle" class="form-control" placeholder="العنوان الفرعي" value="{{ old('HomeSubTitle', $settings['HomeSubTitle'] ?? '') }}">
                        @error('HomeSubTitle') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group col-md-12">
                        <label for="HomeContent" class="form-control-label">محتوى الصفحة الرئيسية</label>
                        <textarea name="HomeContent" id="HomeContent" class="form-control" rows="4" placeholder="محتوى الصفحة">{{ old('HomeContent', $settings['HomeContent'] ?? '') }}</textarea>
                        @error('HomeContent') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- رفع صورة ومعاينة --}}
                    <div class="form-group col-md-6">
                        <label class="form-control-label" for="HomePhoto">صورة الصفحة الرئيسية</label>
                        <div class="custom-file">
                            <input type="file" name="HomePhoto" id="HomePhoto" class="custom-file-input" accept="image/*" onchange="previewImage(event, 'HomePhotoPreview')">
                            <label class="custom-file-label upload-label">اختر صورة</label>
                        </div>
                        @error('HomePhoto') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group col-md-6 d-flex justify-content-center align-items-center">
                        <div class="border p-2" style="height: 170px; width: 200px; border-radius: 10px; overflow: hidden;">
                            <img id="HomePhotoPreview" src="{{ !empty($settings['HomePhoto']) ? asset('storage/' . $settings['HomePhoto']) : asset('images/default-placeholder.png') }}" style="height: 100%; width: 100%; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🟨 السلايدر الرئيسي --}}
        <div class="col-md-12">
            <div class="card card-block card-stretch mb-4">
                <div class="card-header">
                    <h5 class="text-primary mb-0">السلايدر الرئيسي</h5>
                </div>
                <div class="card-body row">
                    <div class="form-group col-md-6">
                        <label for="SliderTitleBlack" class="form-control-label">عنوان أسود</label>
                        <input type="text" name="SliderTitleBlack" id="SliderTitleBlack" class="form-control" placeholder="عنوان أسود" value="{{ old('SliderTitleBlack', $settings['SliderTitleBlack'] ?? '') }}">
                        @error('SliderTitleBlack') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="SliderTitleYellow" class="form-control-label">عنوان أصفر</label>
                        <input type="text" name="SliderTitleYellow" id="SliderTitleYellow" class="form-control" placeholder="عنوان أصفر" value="{{ old('SliderTitleYellow', $settings['SliderTitleYellow'] ?? '') }}">
                        @error('SliderTitleYellow') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- 🟥 السلايدرات الديناميكية --}}
        @foreach(['First', 'Second', 'Third'] as $index => $prefix)
        <div class="col-md-12">
            <div class="card card-block card-stretch mb-4">
                <div class="card-header">
                    <h5 class="text-primary mb-0">السلايدر {{ ['الأول', 'الثاني', 'الثالث'][$index] }}</h5>
                </div>
                <div class="card-body row">
                    <div class="form-group col-md-6">
                        <label class="form-control-label" for="{{ $prefix }}SliderTitle">العنوان</label>
                        <input type="text" name="{{ $prefix }}SliderTitle" id="{{ $prefix }}SliderTitle" class="form-control" placeholder="عنوان" value="{{ old($prefix.'SliderTitle', $settings[$prefix.'SliderTitle'] ?? '') }}">
                        @error($prefix.'SliderTitle') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label class="form-control-label" for="{{ $prefix }}SliderContent">المحتوى</label>
                        <textarea name="{{ $prefix }}SliderContent" id="{{ $prefix }}SliderContent" class="form-control" rows="2" placeholder="محتوى">{{ old($prefix.'SliderContent', $settings[$prefix.'SliderContent'] ?? '') }}</textarea>
                        @error($prefix.'SliderContent') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- صورة السلايدر --}}
                    <div class="form-group col-md-6">
                        <label class="form-control-label" for="{{ $prefix }}SliderImage">الصورة</label>
                        <div class="custom-file">
                            <input type="file" name="{{ $prefix }}SliderImage" id="{{ $prefix }}SliderImage" class="custom-file-input" accept="image/*" onchange="previewImage(event, '{{ $prefix }}SliderImagePreview')">
                            <label class="custom-file-label upload-label">اختر صورة</label>
                        </div>
                        @error($prefix.'SliderImage') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group col-md-6 d-flex justify-content-center align-items-center">
                        <div class="border p-2" style="height: 170px; width: 200px; border-radius: 10px; overflow: hidden;">
                            <img id="{{ $prefix }}SliderImagePreview"
                                 src="{{ !empty($settings[$prefix.'SliderImage']) ? asset('storage/' . $settings[$prefix.'SliderImage']) : asset('images/default-placeholder.png') }}"
                                 style="height: 100%; width: 100%; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-primary px-5">{{ __('messages.save') }}</button>
        </div>
    </div>
</form>
<script>
    function previewImage(event, previewId) {
        const reader = new FileReader();
        const preview = document.getElementById(previewId);
    
        reader.onload = function (e) {
            preview.src = e.target.result;
        };
    
        if (event.target.files && event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        } else {
            preview.src = "{{ asset('images/default-placeholder.png') }}";
        }
    }
    </script>
    