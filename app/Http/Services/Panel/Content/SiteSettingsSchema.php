<?php

namespace App\Http\Services\Panel\Content;

class SiteSettingsSchema
{
    public const GROUPS = [
        'brand' => [
            'icon' => 'bi-palette',
            'label' => ['العلامة والهوية', 'Brand & identity'],
            'fields' => [
                ['key' => 'app_name_en', 'label' => ['اسم التطبيق (EN)', 'App name (EN)'], 'type' => 'text'],
                ['key' => 'app_name_ar', 'label' => ['اسم التطبيق (AR)', 'App name (AR)'], 'type' => 'text'],
                ['key' => 'brand_primary', 'label' => ['اللون الأساسي', 'Primary color'], 'type' => 'color'],
                ['key' => 'brand_secondary', 'label' => ['اللون الثانوي', 'Secondary color'], 'type' => 'color'],
                ['key' => 'brand_logo', 'label' => ['الشعار', 'Logo'], 'type' => 'file'],
            ],
        ],
        'contact' => [
            'icon' => 'bi-telephone',
            'label' => ['التواصل والروابط', 'Contact & social'],
            'fields' => [
                ['key' => 'contact_email', 'label' => ['البريد', 'Email'], 'type' => 'email'],
                ['key' => 'contact_phone', 'label' => ['الهاتف', 'Phone'], 'type' => 'text'],
                ['key' => 'contact_address', 'label' => ['العنوان', 'Address'], 'type' => 'text', 'full' => true],
                ['key' => 'facebook_url', 'label' => ['فيسبوك', 'Facebook'], 'type' => 'url'],
                ['key' => 'instagram_url', 'label' => ['إنستغرام', 'Instagram'], 'type' => 'url'],
                ['key' => 'twitter_url', 'label' => ['إكس (تويتر)', 'X (Twitter)'], 'type' => 'url'],
                ['key' => 'whatsapp_url', 'label' => ['واتساب', 'WhatsApp'], 'type' => 'url'],
                ['key' => 'youtube_url', 'label' => ['يوتيوب', 'YouTube'], 'type' => 'url'],
            ],
        ],
        'landing' => [
            'icon' => 'bi-window',
            'label' => ['محتوى صفحة الهبوط', 'Landing content'],
            'fields' => [
                ['key' => 'hero_title_ar', 'label' => ['العنوان الرئيسي (AR)', 'Hero title (AR)'], 'type' => 'text', 'full' => true],
                ['key' => 'hero_title_en', 'label' => ['العنوان الرئيسي (EN)', 'Hero title (EN)'], 'type' => 'text', 'full' => true],
                ['key' => 'hero_sub_ar', 'label' => ['الوصف (AR)', 'Hero subtitle (AR)'], 'type' => 'textarea', 'full' => true],
                ['key' => 'hero_sub_en', 'label' => ['الوصف (EN)', 'Hero subtitle (EN)'], 'type' => 'textarea', 'full' => true],
                ['key' => 'footer_about_ar', 'label' => ['نبذة التذييل (AR)', 'Footer about (AR)'], 'type' => 'textarea', 'full' => true],
                ['key' => 'footer_about_en', 'label' => ['نبذة التذييل (EN)', 'Footer about (EN)'], 'type' => 'textarea', 'full' => true],
            ],
        ],
        'app' => [
            'icon' => 'bi-sliders',
            'label' => ['إعدادات التطبيق والحجز', 'App & booking config'],
            'fields' => [
                ['key' => 'support_phone', 'label' => ['هاتف الدعم', 'Support phone'], 'type' => 'text'],
                ['key' => 'android_app_url', 'label' => ['رابط تطبيق أندرويد', 'Android app URL'], 'type' => 'url'],
                ['key' => 'ios_app_url', 'label' => ['رابط تطبيق iOS', 'iOS app URL'], 'type' => 'url'],
                ['key' => 'default_currency', 'label' => ['العملة الافتراضية', 'Default currency'], 'type' => 'text'],
                ['key' => 'cancellation_fee_minor', 'label' => ['رسوم الإلغاء (وحدة صغرى)', 'Cancellation fee (minor)'], 'type' => 'number'],
                ['key' => 'office_booking_fleet_rate', 'label' => ['عمولة الرحلة المكتبية % (اختياري)', 'Office-booking commission % (optional)'], 'type' => 'number'],
                ['key' => 'free_wait_minutes', 'label' => ['دقائق الانتظار المجاني', 'Free wait minutes'], 'type' => 'number'],
                ['key' => 'otp_ttl_seconds', 'label' => ['مدة صلاحية OTP (ثانية)', 'OTP TTL (seconds)'], 'type' => 'number'],
                ['key' => 'dispatch_radius_m', 'label' => ['نطاق التوزيع (متر)', 'Dispatch radius (m)'], 'type' => 'number'],
                ['key' => 'dispatch_offer_ttl_s', 'label' => ['مهلة العرض (ثانية)', 'Offer TTL (seconds)'], 'type' => 'number'],
            ],
        ],
    ];

    public static function allKeys(): array
    {
        $keys = [];

        foreach (self::GROUPS as $group) {
            foreach ($group['fields'] as $field) {
                $keys[] = $field['key'];
            }
        }

        return $keys;
    }

    public static function rules(): array
    {
        return [
            'contact_email' => ['nullable', 'email', 'max:190'],
            'facebook_url' => ['nullable', 'url'], 'instagram_url' => ['nullable', 'url'],
            'twitter_url' => ['nullable', 'url'], 'whatsapp_url' => ['nullable', 'url'], 'youtube_url' => ['nullable', 'url'],
            'brand_primary' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_secondary' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_logo' => ['nullable', 'image', 'max:1024'],
            'cancellation_fee_minor' => ['nullable', 'integer', 'min:0'],
            'office_booking_fleet_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'android_app_url' => ['nullable', 'url'],
            'ios_app_url' => ['nullable', 'url'],
            'free_wait_minutes' => ['nullable', 'integer', 'min:0'],
            'otp_ttl_seconds' => ['nullable', 'integer', 'min:30'],
            'dispatch_radius_m' => ['nullable', 'integer', 'min:100'],
            'dispatch_offer_ttl_s' => ['nullable', 'integer', 'min:5'],
        ];
    }
}
