<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Core\Classes\Ledger\DriverDuesService;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Settles what a driver owes the fleet out of their own wallet, through the
 * ledger (never a direct balance edit). Idempotent per driver + amount + day, so
 * a double-submit cannot debit twice.
 */
class SettleDriverDuesController extends Controller
{
    public function __invoke(Request $request, int $driver, EntityScope $scope, DriverRepository $drivers, DriverDuesService $dues, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $model = $drivers->findOrFail($driver);
        $currency = ShardManager::currency();
        $amountMinor = isset($data['amount']) ? (int) round(((float) $data['amount']) * 100) : null;

        try {
            $result = $dues->settleFromWallet(
                (int) $model->id,
                $amountMinor,
                $currency,
                'panel_dues:' . $model->id . ':' . ($amountMinor ?? 'all') . ':' . now()->format('Ymd')
            );
        } catch (Throwable $e) {
            return back()->with('error', match ($e->getMessage()) {
                'no_dues' => textByLanguage('لا توجد مستحقات على هذا السائق.', 'This driver owes nothing.'),
                'insufficient_balance' => textByLanguage('رصيد محفظة السائق لا يكفي.', 'The driver wallet does not cover that.'),
                default => textByLanguage('تعذّر تسوية المستحقات.', 'Could not settle the dues.'),
            });
        }

        $audit->record(
            'driver.dues_settled',
            $scope->guard(),
            $scope->user()?->id,
            'driver',
            (int) $model->id,
            ['settled_minor' => $result['settled_minor'], 'remaining_minor' => $result['remaining_dues_minor'], 'currency' => $currency],
            $request->ip()
        );

        return back()->with('status', textByLanguage(
            'تمت تسوية ' . number_format($result['settled_minor'] / 100, 2) . ' ' . $currency,
            'Settled ' . number_format($result['settled_minor'] / 100, 2) . ' ' . $currency
        ));
    }
}
