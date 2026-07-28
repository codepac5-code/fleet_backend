<?php

namespace Tests\Feature\Fleet;

use App\Http\Services\Panel\Admin\RatingTags\Controller\SaveRatingTagController;
use App\Http\Services\Panel\Admin\RatingTags\Controller\ToggleRatingTagController;
use App\Http\Services\User\Support\Presenters\RatingTagPresenter;
use App\Models\RatingTag;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RatingTagTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_07_28_000002_create_rating_tags_table.php',
    ];

    private function makeTag(string $code, string $audience, int $min = 1, int $max = 5, bool $active = true, int $sort = 0): RatingTag
    {
        return RatingTag::query()->create([
            'code' => $code, 'label_en' => strtoupper($code), 'label_ar' => 'ع_' . $code,
            'audience' => $audience, 'stars_min' => $min, 'stars_max' => $max,
            'sort' => $sort, 'is_active' => $active,
        ]);
    }

    private function save(array $input, ?int $tag = null)
    {
        return (new SaveRatingTagController())(Request::create('/', 'POST', $input), $tag);
    }

    public function test_for_audience_returns_own_plus_both_active_only(): void
    {
        $this->makeTag('a_rider', 'rider', 1, 5, true, 2);
        $this->makeTag('b_both', 'both', 1, 5, true, 1);
        $this->makeTag('c_driver', 'driver');
        $this->makeTag('d_off', 'rider', 1, 5, false);

        $codes = RatingTag::query()->forAudience('rider')->pluck('code')->all();

        $this->assertSame(['b_both', 'a_rider'], $codes);
    }

    public function test_star_range_narrows_the_offered_tags(): void
    {
        $this->makeTag('praise', 'rider', 4, 5);
        $this->makeTag('complaint', 'rider', 1, 3);

        $this->assertSame(['complaint'], RatingTag::query()->forAudience('rider', 2)->pluck('code')->all());
        $this->assertSame(['praise'], RatingTag::query()->forAudience('rider', 5)->pluck('code')->all());
        $this->assertCount(2, RatingTag::query()->forAudience('rider')->get(), 'no star filter offers the whole list');
    }

    public function test_presenter_localizes_by_locale(): void
    {
        $this->makeTag('clean_car', 'rider');

        app()->setLocale('ar');
        $ar = RatingTagPresenter::forAudience('rider');
        $this->assertSame('ع_clean_car', $ar[0]['label']);
        $this->assertSame('clean_car', $ar[0]['code']);

        app()->setLocale('en');
        $this->assertSame('CLEAN_CAR', RatingTagPresenter::forAudience('rider')[0]['label']);
    }

    public function test_panel_save_creates_an_active_tag(): void
    {
        $this->save([
            'code' => 'safe_driving', 'label_en' => 'Safe driving', 'label_ar' => 'قيادة آمنة',
            'audience' => 'rider', 'stars_min' => 4, 'stars_max' => 5, 'sort' => 3,
        ]);

        $tag = RatingTag::query()->where('code', 'safe_driving')->first();
        $this->assertNotNull($tag);
        $this->assertTrue((bool) $tag->is_active);
        $this->assertSame(4, $tag->stars_min);
        $this->assertSame(5, $tag->stars_max);
    }

    public function test_panel_save_rejects_a_bad_code(): void
    {
        $this->expectException(ValidationException::class);
        $this->save(['code' => 'Bad Tag!', 'label_en' => 'x', 'label_ar' => 'x', 'audience' => 'rider']);
    }

    public function test_panel_save_rejects_an_inverted_star_range(): void
    {
        $this->save([
            'code' => 'inverted', 'label_en' => 'x', 'label_ar' => 'x',
            'audience' => 'rider', 'stars_min' => 5, 'stars_max' => 2,
        ]);

        $this->assertNull(RatingTag::query()->where('code', 'inverted')->first());
    }

    public function test_toggle_flips_status_without_deleting(): void
    {
        $tag = $this->makeTag('polite', 'both');

        (new ToggleRatingTagController())((int) $tag->id);

        $this->assertFalse((bool) $tag->refresh()->is_active);
        $this->assertNotNull(RatingTag::query()->find($tag->id));
    }
}
