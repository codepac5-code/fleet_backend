<?php

namespace Tests\Feature\Fleet;

use App\Models\Admin;
use App\Models\SiteFaq;

class SiteFaqTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_05_29_220120_create_infrastructure_nodes_table.php',
        '2026_07_01_000007_create_site_faqs_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $admin = new Admin();
        $admin->id = 1;
        $this->actingAs($admin, 'admin');
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'question_en' => 'What is FleetOS?', 'question_ar' => 'ما هو FleetOS؟',
            'answer_en' => 'A marketplace.', 'answer_ar' => 'سوق.', 'sort' => 1, 'is_active' => 1,
        ], $override);
    }

    public function test_store_creates_faq(): void
    {
        $this->post('/admin/site-faqs', $this->payload())->assertRedirect();

        $faq = SiteFaq::query()->first();
        $this->assertNotNull($faq);
        $this->assertSame('ما هو FleetOS؟', $faq->question_ar);
    }

    public function test_missing_field_fails_validation(): void
    {
        $this->post('/admin/site-faqs', $this->payload(['question_en' => '']))->assertSessionHasErrors('question_en');
        $this->assertSame(0, SiteFaq::query()->count());
    }

    public function test_toggle_and_active_scope(): void
    {
        $this->post('/admin/site-faqs', $this->payload());
        $faq = SiteFaq::query()->first();

        $this->assertCount(1, SiteFaq::active());

        $this->post('/admin/site-faqs/' . $faq->id . '/toggle');
        $this->assertCount(0, SiteFaq::active());
    }

    public function test_delete(): void
    {
        $this->post('/admin/site-faqs', $this->payload());
        $faq = SiteFaq::query()->first();

        $this->delete('/admin/site-faqs/' . $faq->id)->assertRedirect();
        $this->assertSame(0, SiteFaq::query()->count());
    }
}
