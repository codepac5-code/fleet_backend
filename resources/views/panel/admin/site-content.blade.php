@php
    $ar = app()->getLocale() === 'ar';
    $t = fn($en, $arText) => $ar ? $arText : $en;
    $social = [
        'facebook_url' => ['Facebook', 'fa-facebook-f'], 'instagram_url' => ['Instagram', 'fa-instagram'],
        'twitter_url' => ['X (Twitter)', 'fa-x-twitter'], 'linkedin_url' => ['LinkedIn', 'fa-linkedin-in'],
        'youtube_url' => ['YouTube', 'fa-youtube'], 'tiktok_url' => ['TikTok', 'fa-tiktok'], 'whatsapp_url' => ['WhatsApp', 'fa-whatsapp'],
    ];
@endphp
<x-master-layout>
<div class="dash">
    <div class="head">
        <div>
            <h1>{{ $t('Website content', 'محتوى الموقع') }}</h1>
            <p>{{ $t('Manage social links, contact info and key text shown on the public website.', 'أدِر روابط التواصل ومعلومات الاتّصال والنصوص الأساسيّة الظاهرة في الموقع العامّ.') }}</p>
        </div>
        <a class="btn-soft" href="{{ route('login') }}" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> {{ $t('View site', 'عرض الموقع') }}</a>
    </div>

    @if(session('status'))<div class="flash ok"><i class="fa-solid fa-circle-check"></i> {{ $t('Saved successfully.', 'تمّ الحفظ بنجاح.') }}</div>@endif
    @if($errors->any())<div class="flash bad"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('admin.site-content.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="sec">
            <h3><i class="fa-solid fa-palette"></i> {{ $t('Brand identity', 'هويّة العلامة') }}</h3>
            <p class="sec-hint">{{ $t('Upload a logo and set your colors — applied across the website.', 'ارفع شعاراً واضبط ألوانك — تُطبَّق على الموقع كلّه.') }}</p>
            <div class="grid2">
                <div class="fld col2">
                    <label>{{ $t('Logo', 'الشعار') }}</label>
                    <div style="display:flex; align-items:center; gap:1rem">
                        @if(!empty($s['brand_logo']))<img src="{{ asset('storage/' . $s['brand_logo']) }}" style="height:44px; width:auto; border-radius:8px; background:#f3f0fb; padding:4px">@endif
                        <input type="file" name="brand_logo" accept="image/*">
                    </div>
                </div>
                <div class="fld"><label>{{ $t('Primary color', 'اللون الأساسيّ') }}</label>
                    <div style="display:flex; gap:.5rem; align-items:center"><input type="color" value="{{ $s['brand_primary'] ?: '#F29C0B' }}" oninput="this.nextElementSibling.value=this.value" style="width:48px;height:42px;padding:2px"><input name="brand_primary" value="{{ old('brand_primary', $s['brand_primary'] ?? '') }}" placeholder="#F29C0B"></div>
                </div>
                <div class="fld"><label>{{ $t('Secondary color', 'اللون الثانويّ') }}</label>
                    <div style="display:flex; gap:.5rem; align-items:center"><input type="color" value="{{ $s['brand_secondary'] ?: '#312873' }}" oninput="this.nextElementSibling.value=this.value" style="width:48px;height:42px;padding:2px"><input name="brand_secondary" value="{{ old('brand_secondary', $s['brand_secondary'] ?? '') }}" placeholder="#312873"></div>
                </div>
            </div>
        </div>

        <div class="sec">
            <h3><i class="fa-solid fa-share-nodes"></i> {{ $t('Social media links', 'روابط التواصل الاجتماعيّ') }}</h3>
            <p class="sec-hint">{{ $t('Add full URLs. Empty ones are hidden from the site.', 'أضف الروابط الكاملة. الفارغة تُخفى من الموقع.') }}</p>
            <div class="grid2">
                @foreach($social as $key => $meta)
                    <div class="fld">
                        <label><i class="fa-brands {{ $meta[1] }}"></i> {{ $meta[0] }}</label>
                        <input name="{{ $key }}" value="{{ old($key, $s[$key] ?? '') }}" placeholder="https://">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="sec">
            <h3><i class="fa-solid fa-address-book"></i> {{ $t('Contact info', 'معلومات التواصل') }}</h3>
            <div class="grid2">
                <div class="fld"><label>{{ $t('Email', 'البريد') }}</label><input name="contact_email" value="{{ old('contact_email', $s['contact_email'] ?? '') }}" placeholder="hello@fleetos.app"></div>
                <div class="fld"><label>{{ $t('Phone', 'الهاتف') }}</label><input name="contact_phone" value="{{ old('contact_phone', $s['contact_phone'] ?? '') }}" placeholder="+974..."></div>
                <div class="fld col2"><label>{{ $t('Address', 'العنوان') }}</label><input name="contact_address" value="{{ old('contact_address', $s['contact_address'] ?? '') }}"></div>
            </div>
        </div>

        <div class="sec">
            <h3><i class="fa-solid fa-pen-to-square"></i> {{ $t('Homepage content', 'محتوى الصفحة الرئيسيّة') }}</h3>
            <p class="sec-hint">{{ $t('Leave empty to keep the built-in default text.', 'اتركه فارغاً للإبقاء على النصّ الافتراضيّ.') }}</p>
            <div class="grid2">
                <div class="fld"><label>{{ $t('Hero title (EN)', 'عنوان الواجهة (إنجليزي)') }}</label><input name="hero_title_en" value="{{ old('hero_title_en', $s['hero_title_en'] ?? '') }}"></div>
                <div class="fld"><label>{{ $t('Hero title (AR)', 'عنوان الواجهة (عربي)') }}</label><input name="hero_title_ar" value="{{ old('hero_title_ar', $s['hero_title_ar'] ?? '') }}" dir="rtl"></div>
                <div class="fld col2"><label>{{ $t('Hero subtitle (EN)', 'وصف الواجهة (إنجليزي)') }}</label><textarea name="hero_sub_en">{{ old('hero_sub_en', $s['hero_sub_en'] ?? '') }}</textarea></div>
                <div class="fld col2"><label>{{ $t('Hero subtitle (AR)', 'وصف الواجهة (عربي)') }}</label><textarea name="hero_sub_ar" dir="rtl">{{ old('hero_sub_ar', $s['hero_sub_ar'] ?? '') }}</textarea></div>
                <div class="fld col2"><label>{{ $t('Footer about (EN)', 'نبذة التذييل (إنجليزي)') }}</label><textarea name="footer_about_en">{{ old('footer_about_en', $s['footer_about_en'] ?? '') }}</textarea></div>
                <div class="fld col2"><label>{{ $t('Footer about (AR)', 'نبذة التذييل (عربي)') }}</label><textarea name="footer_about_ar" dir="rtl">{{ old('footer_about_ar', $s['footer_about_ar'] ?? '') }}</textarea></div>
            </div>
        </div>

        <div class="save-bar">
            <button type="submit" class="btn-main"><i class="fa-solid fa-floppy-disk"></i> {{ $t('Save changes', 'حفظ التعديلات') }}</button>
        </div>
    </form>
</div>

<style>
    .dash { max-width: 1000px; margin: auto; padding: 40px 20px; font-family: 'Plus Jakarta Sans','Cairo',sans-serif; }
    .head { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.4rem; }
    .head h1 { font-size: 1.9rem; font-weight: 800; color: #312873; } .head p { color: #6b7280; margin-top: .3rem; max-width: 560px; }
    .btn-soft { background: #312873; color: #fff; padding: .65rem 1.05rem; border-radius: 10px; font-weight: 700; display: inline-flex; gap: .5rem; align-items: center; font-size: .85rem; }
    .flash { display: flex; align-items: center; gap: .6rem; padding: .85rem 1.1rem; border-radius: 12px; font-weight: 700; margin-bottom: 1rem; }
    .flash.ok { background: #ecfdf5; color: #047857; } .flash.bad { background: #fef2f2; color: #b91c1c; }
    .sec { background: #fff; border: 1px solid #eceefb; border-radius: 18px; padding: 1.5rem; margin-bottom: 1.2rem; box-shadow: 0 10px 30px rgba(49,40,115,.05); }
    .sec h3 { font-size: 1.05rem; font-weight: 800; color: #312873; display: flex; align-items: center; gap: .6rem; }
    .sec h3 i { color: #F29C0B; }
    .sec-hint { color: #9aa1bd; font-size: .8rem; margin: .4rem 0 1rem; }
    .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .fld { display: flex; flex-direction: column; gap: .35rem; } .fld.col2 { grid-column: 1/-1; }
    .fld label { font-size: .8rem; font-weight: 700; color: #312873; display: flex; align-items: center; gap: .45rem; }
    .fld label i { color: #6b7280; }
    .fld input, .fld textarea { border: 1.5px solid #eceefb; border-radius: 10px; padding: .7rem .8rem; font-size: .9rem; background: #fbfcff; font-family: inherit; }
    .fld textarea { min-height: 74px; resize: vertical; }
    .fld input:focus, .fld textarea:focus { outline: none; border-color: #F29C0B; background: #fff; box-shadow: 0 0 0 3px rgba(242,156,11,.12); }
    .save-bar { position: sticky; bottom: 16px; display: flex; justify-content: flex-end; margin-top: 1rem; }
    .btn-main { background: linear-gradient(135deg,#F29C0B,#FFB43B); color: #fff; border: none; padding: .9rem 1.8rem; border-radius: 12px; font-weight: 800; cursor: pointer; box-shadow: 0 14px 30px rgba(242,156,11,.32); display: inline-flex; gap: .6rem; align-items: center; }
    @media (max-width: 700px) { .grid2 { grid-template-columns: 1fr; } }
</style>
</x-master-layout>
