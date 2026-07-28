<?php

namespace Tests\Feature\Fleet;

use App\Models\Admin;
use App\Models\SiteSetting;

class SiteContentTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_05_29_220120_create_infrastructure_nodes_table.php',
        '2026_07_01_000006_create_site_settings_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        SiteSetting::flush();
        $admin = new Admin();
        $admin->id = 1;
        $this->actingAs($admin, 'admin');
    }

    public function test_save_and_retrieve_settings(): void
    {
        $this->put('/admin/site-content', [
            'facebook_url' => 'https://facebook.com/fleetos',
            'contact_email' => 'hello@fleetos.app',
            'hero_title_ar' => 'أطلق مكتبك',
        ])->assertRedirect();

        SiteSetting::flush();
        $this->assertSame('https://facebook.com/fleetos', SiteSetting::val('facebook_url'));
        $this->assertSame('hello@fleetos.app', SiteSetting::val('contact_email'));
        $this->assertSame('أطلق مكتبك', SiteSetting::val('hero_title_ar'));
    }

    public function test_invalid_url_is_rejected(): void
    {
        $this->put('/admin/site-content', ['facebook_url' => 'not-a-url'])->assertSessionHasErrors('facebook_url');
    }

    public function test_brand_colors_saved_and_invalid_rejected(): void
    {
        $this->put('/admin/site-content', ['brand_primary' => '#123456', 'brand_secondary' => '#abcdef'])->assertRedirect();
        SiteSetting::flush();
        $this->assertSame('#123456', SiteSetting::val('brand_primary'));

        $this->put('/admin/site-content', ['brand_primary' => 'red'])->assertSessionHasErrors('brand_primary');
    }

    public function test_val_returns_default_when_unset(): void
    {
        $this->assertSame('fallback', SiteSetting::val('nonexistent_key', 'fallback'));
    }

    public function test_empty_value_returns_default(): void
    {
        SiteSetting::put('twitter_url', '');
        SiteSetting::flush();

        $this->assertSame('def', SiteSetting::val('twitter_url', 'def'));
    }
}
