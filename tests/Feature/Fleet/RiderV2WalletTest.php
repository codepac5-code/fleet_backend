<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Models\Currency;
use App\Models\RiderPaymentMethod;
use App\Models\User;

class RiderV2WalletTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_06_19_000002_create_currencies_table.php',
        '2026_07_11_000007_create_rider_account_tables.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_25_000003_create_ledger_payments_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2024_11_17_075900_create_coupons_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Currency::query()->create([
            'code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimals' => 2,
            'exchange_rate' => 1, 'is_default' => true, 'is_active' => true,
        ]);

        \App\Models\Coupon::query()->create([
            'code' => 'QATAR10', 'discountType' => 'percentage', 'discount' => 10,
            'isPercentage' => true, 'isActive' => true, 'limit' => 0, 'status' => 1,
        ]);
    }

    private function asUser(int $id = 7): self
    {
        $user = new User();
        $user->id = $id;

        return $this->actingAs($user, 'user');
    }

    private function fund(int $userId, int $amount): void
    {
        (new FleetWalletService(new LedgerService()))->topUp($userId, $amount, 'USD', 'fund:' . $userId, 'test', 1);
    }

    public function test_balance_in_decimal_with_currency(): void
    {
        $this->fund(7, 8050);

        $this->asUser()->getJson('user/wallet?currency_code=USD')
            ->assertStatus(200)
            ->assertJsonPath('data.balance', 80.5)
            ->assertJsonPath('data.currency', 'USD')
            ->assertJsonPath('data.symbol', '$')
            ->assertJsonPath('data.decimals', 2);
    }

    public function test_transactions_list(): void
    {
        $this->fund(7, 8050);

        $this->asUser()->getJson('user/wallet/transactions?currency_code=USD')
            ->assertStatus(200)
            ->assertJsonPath('data.items.0.amount', 80.5)
            ->assertJsonPath('data.items.0.balance_after', 80.5)
            ->assertJsonPath('data.items.0.transaction_type', 'topup');
    }

    public function test_payment_methods_crud(): void
    {
        $this->asUser()->postJson('user/payment-methods', ['stripePaymentMethodId' => 'pm_1', 'setDefault' => true])
            ->assertStatus(201)
            ->assertJsonPath('data.stripe_payment_method_id', 'pm_1')
            ->assertJsonPath('data.is_default', true)
            ->assertJsonPath('data.type', 'card');

        $second = $this->asUser()->postJson('user/payment-methods', ['stripePaymentMethodId' => 'pm_2'])
            ->assertStatus(201)
            ->assertJsonPath('data.is_default', false)
            ->json('data.id');

        $this->asUser()->getJson('user/payment-methods')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $this->asUser()->patchJson("user/payment-methods/{$second}", ['default' => true])
            ->assertStatus(200)
            ->assertJsonPath('data.is_default', true);

        $this->assertFalse((bool) RiderPaymentMethod::query()->where('stripe_payment_method_id', 'pm_1')->first()->is_default);

        $first = RiderPaymentMethod::query()->where('stripe_payment_method_id', 'pm_1')->first();
        $this->asUser()->deleteJson("user/payment-methods/{$first->id}")->assertStatus(204);
        $this->assertNull(RiderPaymentMethod::query()->where('stripe_payment_method_id', 'pm_1')->first());
    }

    public function test_promos_list_and_redeem(): void
    {
        $this->asUser()->getJson('user/promos')
            ->assertStatus(200)
            ->assertJsonPath('data.promos.0.code', 'QATAR10');

        $this->asUser()->postJson('user/promos/redeem', ['code' => 'QATAR10'])
            ->assertStatus(200)
            ->assertJsonPath('data.applied', true)
            ->assertJsonPath('data.discountType', 'percentage');

        $this->asUser()->postJson('user/promos/redeem', ['code' => 'NOPE'])
            ->assertStatus(200)
            ->assertJsonPath('data.applied', false);
    }
}
