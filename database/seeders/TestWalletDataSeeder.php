<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\FleetOffice;
use App\Models\Office;
use App\Models\WalletBalance;
use Illuminate\Database\Seeder;

class TestWalletDataSeeder extends Seeder
{
    public function run(): void
    {
        Currency::updateOrCreate(['code' => 'SAR'], ['name' => 'Saudi Riyal', 'symbol' => 'ر.س', 'decimals' => 2, 'exchange_rate' => 1, 'is_default' => true, 'is_active' => true]);
        Currency::updateOrCreate(['code' => 'USD'], ['name' => 'US Dollar', 'symbol' => '$', 'decimals' => 2, 'exchange_rate' => 0.27, 'is_default' => false, 'is_active' => true]);
        Currency::updateOrCreate(['code' => 'QAR'], ['name' => 'Qatari Riyal', 'symbol' => 'ر.ق', 'decimals' => 2, 'exchange_rate' => 0.98, 'is_default' => false, 'is_active' => true]);

        $fleet = FleetOffice::first();
        if ($fleet) {
            $this->setBalance(FleetOffice::class, $fleet->id, 'SAR', 152000.50);
            $this->setBalance(FleetOffice::class, $fleet->id, 'USD', 41250.00);
        }

        $office = Office::first();
        if ($office) {
            $this->setBalance(Office::class, $office->id, 'SAR', 38400.00);
            $this->setBalance(Office::class, $office->id, 'QAR', 12750.25);
        }
    }

    private function setBalance(string $ownerType, int $ownerId, string $code, float $balance): void
    {
        WalletBalance::updateOrCreate(
            ['owner_type' => $ownerType, 'owner_id' => $ownerId, 'currency_code' => $code],
            ['balance' => $balance]
        );
    }
}
