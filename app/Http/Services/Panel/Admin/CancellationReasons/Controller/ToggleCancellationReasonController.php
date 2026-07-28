<?php

namespace App\Http\Services\Panel\Admin\CancellationReasons\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\CancellationReason;
use Illuminate\Http\RedirectResponse;

class ToggleCancellationReasonController extends Controller
{
    public function __invoke(int $reason): RedirectResponse
    {
        $model = CancellationReason::on(TenantConnection::current())->find($reason);

        if ($model !== null) {
            $model->is_active = ! $model->is_active;
            $model->save();
        }

        return back()->with('status', textByLanguage('تم تحديث حالة السبب', 'Reason status updated'));
    }
}
