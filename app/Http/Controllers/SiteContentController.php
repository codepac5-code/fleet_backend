<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SiteContentController extends Controller
{
    private const SOCIAL = ['facebook_url', 'instagram_url', 'twitter_url', 'linkedin_url', 'youtube_url', 'tiktok_url', 'whatsapp_url'];
    private const CONTACT = ['contact_email', 'contact_phone', 'contact_address'];
    private const CONTENT = ['hero_title_en', 'hero_title_ar', 'hero_sub_en', 'hero_sub_ar', 'footer_about_en', 'footer_about_ar'];

    private const BRAND = ['brand_primary', 'brand_secondary'];

    public function index()
    {
        $keys = array_merge(self::SOCIAL, self::CONTACT, self::CONTENT, self::BRAND, ['brand_logo']);
        $s = collect($keys)->mapWithKeys(fn ($k) => [$k => SiteSetting::val($k)]);

        return view('panel.admin.site-content', compact('s'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'facebook_url' => 'nullable|url', 'instagram_url' => 'nullable|url', 'twitter_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url', 'youtube_url' => 'nullable|url', 'tiktok_url' => 'nullable|url', 'whatsapp_url' => 'nullable|url',
            'contact_email' => 'nullable|email', 'contact_phone' => 'nullable|string|max:40', 'contact_address' => 'nullable|string|max:200',
            'hero_title_en' => 'nullable|string|max:180', 'hero_title_ar' => 'nullable|string|max:180',
            'hero_sub_en' => 'nullable|string|max:400', 'hero_sub_ar' => 'nullable|string|max:400',
            'footer_about_en' => 'nullable|string|max:400', 'footer_about_ar' => 'nullable|string|max:400',
            'brand_primary' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_secondary' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_logo' => 'nullable|image|max:1024',
        ]);

        foreach (array_merge(self::SOCIAL, self::CONTACT, self::CONTENT, self::BRAND) as $key) {
            SiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $request->input($key)]);
        }

        if ($request->hasFile('brand_logo')) {
            $path = $request->file('brand_logo')->store('site', 'public');
            SiteSetting::query()->updateOrCreate(['key' => 'brand_logo'], ['value' => $path]);
        }

        SiteSetting::flush();

        return back()->with('status', 'saved');
    }
}
