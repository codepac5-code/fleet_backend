<?php

namespace Tests\Feature\Fleet;

use App\Http\Services\Panel\Admin\CancellationReasons\Controller\SaveCancellationReasonController;
use App\Http\Services\User\Support\Presenters\CancellationReasonPresenter;
use App\Models\CancellationReason;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CancellationReasonTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_07_25_000006_create_cancellation_reasons_table.php',
    ];

    private function makeReason(string $code, string $audience, bool $active = true, int $sort = 0): CancellationReason
    {
        return CancellationReason::query()->create([
            'code' => $code, 'label_en' => strtoupper($code), 'label_ar' => 'ع_' . $code,
            'audience' => $audience, 'sort' => $sort, 'is_active' => $active,
        ]);
    }

    public function test_for_audience_returns_own_plus_both_active_only(): void
    {
        $this->makeReason('a_rider', 'rider', true, 2);
        $this->makeReason('b_both', 'both', true, 1);
        $this->makeReason('c_driver', 'driver', true);
        $this->makeReason('d_off', 'rider', false);

        $codes = CancellationReason::query()->forAudience('rider')->pluck('code')->all();

        // ordered by sort → both(1) before rider(2); driver + inactive excluded.
        $this->assertSame(['b_both', 'a_rider'], $codes);
    }

    public function test_presenter_localizes_by_locale(): void
    {
        $this->makeReason('driver_late', 'rider');

        app()->setLocale('ar');
        $ar = CancellationReasonPresenter::forAudience('rider');
        $this->assertSame('ع_driver_late', $ar[0]['label']);
        $this->assertSame('driver_late', $ar[0]['code']);

        app()->setLocale('en');
        $en = CancellationReasonPresenter::forAudience('rider');
        $this->assertSame('DRIVER_LATE', $en[0]['label']);
    }

    public function test_panel_save_creates_an_active_reason(): void
    {
        (new SaveCancellationReasonController())(Request::create('/', 'POST', [
            'code' => 'wrong_pickup', 'label_en' => 'Wrong pickup', 'label_ar' => 'موقع خاطئ',
            'audience' => 'rider', 'sort' => 5,
        ]));

        $reason = CancellationReason::query()->where('code', 'wrong_pickup')->first();
        $this->assertNotNull($reason);
        $this->assertTrue((bool) $reason->is_active);
        $this->assertSame('rider', $reason->audience);
    }

    public function test_panel_save_rejects_a_bad_code(): void
    {
        $this->expectException(ValidationException::class);
        (new SaveCancellationReasonController())(Request::create('/', 'POST', [
            'code' => 'Bad Code!', 'label_en' => 'x', 'label_ar' => 'x', 'audience' => 'rider',
        ]));
    }
}
