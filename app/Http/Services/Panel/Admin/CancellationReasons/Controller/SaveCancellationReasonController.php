<?php

namespace App\Http\Services\Panel\Admin\CancellationReasons\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\CancellationReason;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SaveCancellationReasonController extends Controller
{
    public function __invoke(Request $request, ?int $reason = null): RedirectResponse
    {
        $conn = TenantConnection::current();
        $uniqueTable = ($conn ? $conn . '.' : '') . 'cancellation_reasons';

        $data = $request->validate([
            'code' => [
                'required', 'string', 'max:40', 'regex:/^[a-z0-9_]+$/',
                Rule::unique($uniqueTable, 'code')->ignore($reason),
            ],
            'label_en' => ['required', 'string', 'max:120'],
            'label_ar' => ['required', 'string', 'max:120'],
            'audience' => ['required', Rule::in([CancellationReason::AUDIENCE_RIDER, CancellationReason::AUDIENCE_DRIVER, CancellationReason::AUDIENCE_BOTH])],
            'sort' => ['nullable', 'integer', 'min:0', 'max:65000'],
        ], [
            'code.regex' => textByLanguage('الرمز: أحرف إنجليزية صغيرة وأرقام و_ فقط', 'Code: lowercase letters, digits and _ only'),
        ]);

        $model = $reason !== null
            ? CancellationReason::on($conn)->findOrFail($reason)
            : (new CancellationReason())->setConnection($conn);

        $model->code = $data['code'];
        $model->label_en = $data['label_en'];
        $model->label_ar = $data['label_ar'];
        $model->audience = $data['audience'];
        $model->sort = (int) ($data['sort'] ?? 0);

        if ($reason === null) {
            $model->is_active = true;
        }

        $model->save();

        return back()->with('status', $reason !== null
            ? textByLanguage('تم تحديث السبب', 'Reason updated')
            : textByLanguage('تمت إضافة السبب', 'Reason added'));
    }
}
