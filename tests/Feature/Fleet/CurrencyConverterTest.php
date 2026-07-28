<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\CurrencyConverter;
use App\Models\Currency;

/**
 * FX for the "top up in USD, spend in SYP" wallet path. The rate is admin-set;
 * an unset (0) rate must FAIL, never silently charge a wrong amount.
 * Convention: exchange_rate = units of this currency per 1 unit of the default.
 */
class CurrencyConverterTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_06_19_000002_create_currencies_table.php',
    ];

    private CurrencyConverter $fx;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fx = new CurrencyConverter();
        Currency::query()->create(['code' => 'USD', 'name' => 'US Dollar', 'decimals' => 2, 'exchange_rate' => 1, 'is_default' => true, 'is_active' => true]);
    }

    public function test_converts_usd_to_syp_at_the_admin_rate(): void
    {
        // 1 USD = 13,000 SYP.
        Currency::query()->create(['code' => 'SYP', 'name' => 'Syrian Pound', 'decimals' => 2, 'exchange_rate' => 13000, 'is_active' => true]);

        // $10.00 (1000 minor) → 130,000.00 SYP (13,000,000 minor).
        $this->assertSame(13_000_000, $this->fx->convertMinor(1000, 'USD', 'SYP'));
    }

    public function test_same_currency_is_identity(): void
    {
        $this->assertSame(2500, $this->fx->convertMinor(2500, 'USD', 'USD'));
    }

    public function test_an_unset_rate_refuses_to_convert(): void
    {
        // SYP seeded with rate 0 (as the migration does) — must not guess.
        Currency::query()->create(['code' => 'SYP', 'name' => 'Syrian Pound', 'decimals' => 2, 'exchange_rate' => 0, 'is_active' => true]);

        $this->expectExceptionMessage('fx rate unset');
        $this->fx->convertMinor(1000, 'USD', 'SYP');
        $this->assertFalse($this->fx->canConvert('USD', 'SYP'));
    }

    public function test_unknown_currency_is_rejected(): void
    {
        $this->expectExceptionMessage('currency not found');
        $this->fx->convertMinor(1000, 'USD', 'XYZ');
    }

    public function test_reverse_syp_to_usd_rounds_to_minor_units(): void
    {
        Currency::query()->create(['code' => 'SYP', 'name' => 'Syrian Pound', 'decimals' => 2, 'exchange_rate' => 13000, 'is_active' => true]);

        // 130,000.00 SYP → 10.00 USD.
        $this->assertSame(1000, $this->fx->convertMinor(13_000_000, 'SYP', 'USD'));
    }

    /**
     * The dashboard "Exchange rates" form does exactly this update. Proves that
     * setting the rate an admin enters turns a refused conversion into a real
     * one — the unblock the whole feature exists for.
     */
    public function test_admin_setting_the_rate_unblocks_conversion(): void
    {
        // Seeded UNSET, exactly like the SYP migration.
        Currency::query()->create(['code' => 'SYP', 'name' => 'Syrian Pound', 'decimals' => 2, 'exchange_rate' => 0, 'is_active' => true]);
        $this->assertFalse($this->fx->canConvert('USD', 'SYP'), 'unset rate must refuse');

        // The admin sets "1 USD = 15,000 SYP" from the dashboard.
        Currency::query()->where('code', 'SYP')->where('is_default', false)->update(['exchange_rate' => 15000]);

        $this->assertTrue($this->fx->canConvert('USD', 'SYP'));
        $this->assertSame(15_000_000, $this->fx->convertMinor(1000, 'USD', 'SYP')); // $10 → 150,000 SYP
    }
}
