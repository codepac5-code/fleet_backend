<?php

namespace App\Http\Services\Panel\Admin\Offices\Logic;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\FleetOffice;
use App\Models\Office;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfficeWalletService
{
    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    private function platformId(): int
    {
        return (int) (FleetOffice::on($this->connection())->value('id') ?? 1);
    }

    public function addBalance(Office $office, float $amount, ?string $note): void
    {
        $conn = $this->connection();

        DB::connection($conn)->transaction(function () use ($office, $amount, $note, $conn) {
            $before = (float) $office->walletBalance;
            $after = $before + $amount;

            $office->walletBalance = $after;
            $office->save();

            $this->record($conn, [
                'from_type'   => FleetOffice::class,
                'from_id'     => $this->platformId(),
                'to_type'     => Office::class,
                'to_id'       => $office->id,
                'amount'      => $amount,
                'before'      => $before,
                'after'       => $after,
                'description' => trim('إضافة رصيد للمحفظة ' . ($note ?? '')),
                'description_en' => trim('Wallet top-up ' . ($note ?? '')),
            ]);
        });
    }

    public function settle(Office $office, float $amount, ?string $note): float
    {
        $conn = $this->connection();
        $applied = 0.0;

        DB::connection($conn)->transaction(function () use ($office, $amount, $note, $conn, &$applied) {
            $before = (float) $office->fleetDues;
            $applied = min($amount, $before);
            $after = $before - $applied;

            $office->fleetDues = $after;
            $office->save();

            if ($applied > 0) {
                $this->record($conn, [
                    'from_type'   => Office::class,
                    'from_id'     => $office->id,
                    'to_type'     => FleetOffice::class,
                    'to_id'       => $this->platformId(),
                    'amount'      => $applied,
                    'before'      => $before,
                    'after'       => $after,
                    'description' => trim('تسوية مستحقات المنصّة ' . ($note ?? '')),
                    'description_en' => trim('Fleet dues settlement ' . ($note ?? '')),
                ]);
            }
        });

        return $applied;
    }

    private function record(?string $conn, array $data): void
    {
        $transaction = new WalletTransaction([
            'from_type'             => $data['from_type'],
            'from_id'               => $data['from_id'],
            'to_type'               => $data['to_type'],
            'to_id'                 => $data['to_id'],
            'amount'                => $data['amount'],
            'balance_before'        => $data['before'],
            'balance_after'         => $data['after'],
            'status'                => 'completed',
            'description'           => $data['description'],
            'description_en'        => $data['description_en'],
            'paymentName'           => 'panel',
            'transaction_reference' => 'PNL-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5)),
        ]);

        if ($conn) {
            $transaction->setConnection($conn);
        }

        $transaction->save();
    }
}
