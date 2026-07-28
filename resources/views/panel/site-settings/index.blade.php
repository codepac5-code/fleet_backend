@extends('panel.layouts.master')

@section('title', textByLanguage('الموقع والمحتوى', 'Site & content'))
@section('page-title', textByLanguage('الموقع والمحتوى', 'Site & content'))

@php
    $groupHints = [
        'brand'   => [textByLanguage('الاسم والألوان والشعار الظاهرة في التطبيقات والموقع', 'Name, colors and logo shown across the apps and website')],
        'contact' => [textByLanguage('بيانات التواصل وروابط التواصل الاجتماعي في تذييل الموقع', 'Contact details and social links used in the site footer')],
        'landing' => [textByLanguage('نصوص صفحة الهبوط بالعربية والإنجليزية', 'Landing page copy in Arabic and English')],
        'app'     => [textByLanguage('قيم مربوطة مباشرةً بالخدمات: الإلغاء، التوزيع، OTP', 'Values wired live into services: cancellation, dispatch, OTP')],
    ];
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('إعدادات الموقع والتطبيق', 'Site & app settings')"
        :subtitle="textByLanguage('تحكّم بالعلامة والمحتوى والتواصل وإعدادات التطبيق من مكان واحد', 'Control brand, content, contact and app config in one place')" />

    <nav class="p-set-nav">
        @foreach($groups as $gkey => $group)
            <a href="#grp-{{ $gkey }}" class="p-set-nav__link @if($loop->first) is-active @endif" data-set-tab="{{ $gkey }}">
                <i class="bi {{ $group['icon'] }}"></i> {{ textByLanguage($group['label'][0], $group['label'][1]) }}
            </a>
        @endforeach
    </nav>

    <form method="POST" action="{{ route('panel.admin.settings.site.save') }}" enctype="multipart/form-data">
        @csrf

        @foreach($groups as $gkey => $group)
            <x-panel.card id="grp-{{ $gkey }}" class="p-set-group" data-set-group="{{ $gkey }}">
                <div class="p-set-group__head">
                    <div class="p-set-group__ic"><i class="bi {{ $group['icon'] }}"></i></div>
                    <div class="p-set-group__tx">
                        <h3>{{ textByLanguage($group['label'][0], $group['label'][1]) }}</h3>
                        <p>{{ $groupHints[$gkey][0] ?? '' }}</p>
                    </div>
                </div>

                <div class="p-form-grid">
                    @foreach($group['fields'] as $f)
                        @php $val = $values[$f['key']] ?? ''; @endphp

                        @if($f['key'] === 'brand_logo')
                            <div class="p-field p-field--full">
                                <label>{{ textByLanguage($f['label'][0], $f['label'][1]) }}</label>
                                <div class="p-set-logo">
                                    <span class="p-set-logo__preview" data-logo-preview>
                                        @if($val)<img src="{{ asset('storage/' . $val) }}" alt="logo">@else<i class="bi bi-image"></i>@endif
                                    </span>
                                    <label class="p-set-logo__pick">
                                        <i class="bi bi-upload"></i> {{ textByLanguage('اختيار صورة', 'Choose image') }}
                                        <input type="file" name="brand_logo" accept="image/*" data-logo-input>
                                    </label>
                                    <span class="p-set-logo__name" data-logo-name>{{ textByLanguage('PNG أو SVG، حتى 1MB', 'PNG or SVG, up to 1MB') }}</span>
                                </div>
                            </div>
                        @elseif(($f['type'] ?? '') === 'color')
                            <div class="p-field">
                                <label for="{{ $f['key'] }}">{{ textByLanguage($f['label'][0], $f['label'][1]) }}</label>
                                <div class="p-set-color">
                                    @php $cv = old($f['key'], $val ?: ($f['key'] === 'brand_secondary' ? '#F8A609' : '#312873')); @endphp
                                    <input type="color" id="{{ $f['key'] }}" value="{{ $cv }}" data-color-src="{{ $f['key'] }}">
                                    <input type="text" name="{{ $f['key'] }}" class="p-set-color__hex" value="{{ $cv }}" data-color-hex="{{ $f['key'] }}" maxlength="7" pattern="#[0-9A-Fa-f]{6}">
                                </div>
                                @error($f['key'])<small class="p-field__error">{{ $message }}</small>@enderror
                            </div>
                        @else
                            <x-panel.field
                                :name="$f['key']"
                                :type="$f['type']"
                                :label="textByLanguage($f['label'][0], $f['label'][1])"
                                :value="$val"
                                :full="$f['full'] ?? false" />
                        @endif
                    @endforeach
                </div>
            </x-panel.card>
        @endforeach

        <div class="p-savebar">
            <span class="p-savebar__note"><i class="bi bi-shield-check"></i> {{ textByLanguage('تُطبَّق التغييرات فوراً عبر التطبيقات والموقع', 'Changes apply instantly across the apps and website') }}</span>
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ textByLanguage('حفظ الإعدادات', 'Save settings') }}</button>
        </div>
    </form>

    <script>
        (function () {
            document.querySelectorAll('[data-color-src]').forEach(function (picker) {
                var key = picker.getAttribute('data-color-src');
                var hex = document.querySelector('[data-color-hex="' + key + '"]');
                if (!hex) return;
                picker.addEventListener('input', function () { hex.value = picker.value.toUpperCase(); });
                hex.addEventListener('input', function () { if (/^#[0-9A-Fa-f]{6}$/.test(hex.value)) picker.value = hex.value; });
            });

            var input = document.querySelector('[data-logo-input]');
            if (input) {
                input.addEventListener('change', function () {
                    var file = input.files && input.files[0];
                    if (!file) return;
                    var name = document.querySelector('[data-logo-name]');
                    if (name) name.textContent = file.name;
                    var box = document.querySelector('[data-logo-preview]');
                    var reader = new FileReader();
                    reader.onload = function (e) { if (box) box.innerHTML = '<img src="' + e.target.result + '" alt="logo">'; };
                    reader.readAsDataURL(file);
                });
            }

            var links = Array.prototype.slice.call(document.querySelectorAll('[data-set-tab]'));
            var groups = Array.prototype.slice.call(document.querySelectorAll('[data-set-group]'));
            function setActive(key) {
                links.forEach(function (l) { l.classList.toggle('is-active', l.getAttribute('data-set-tab') === key); });
            }
            if ('IntersectionObserver' in window && groups.length) {
                var obs = new IntersectionObserver(function (entries) {
                    entries.forEach(function (en) { if (en.isIntersecting) setActive(en.target.getAttribute('data-set-group')); });
                }, { rootMargin: '-45% 0px -50% 0px', threshold: 0 });
                groups.forEach(function (g) { obs.observe(g); });
            }
            links.forEach(function (l) { l.addEventListener('click', function () { setActive(l.getAttribute('data-set-tab')); }); });
        })();
    </script>

@endsection
