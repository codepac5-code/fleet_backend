<?php

namespace App\Http\Services\Panel\Support\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Support\LostFoundService;
use App\Http\Core\Const\LostItemStatus as St;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\LostItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The office drives the lost & found lifecycle through the GOVERNED
 * [LostFoundService] — acknowledge / ready-for-handback / returned / unresolved,
 * each gated by the state machine and scoped to the office. (Matching is a
 * separate confirm action; see ConfirmLostItemMatchController.) Admins act on
 * behalf of the item's own office.
 */
class UpdateLostItemStatusController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, LostFoundService $lostFound, int $item): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', [St::ACKNOWLEDGED, St::READY, St::RETURNED, St::UNRESOLVED])],
            'resolution' => ['nullable', 'string', 'max:64'],
        ]);

        $record = LostItem::query()->findOrFail($item);

        // Country guard: lost_items is global with a repeating office_id, so an
        // office (or admin) may only touch a record from the active country. A
        // country mismatch is a cross-shard reach → 404.
        $country = LostItem::activeCountryCode();
        if ($country !== null && $record->country_code !== null && $record->country_code !== $country) {
            abort(404);
        }

        $officeId = $scope->isAdmin() ? (int) $record->office_id : (int) $scope->officeId();

        try {
            $lostFound->officeTransition($officeId, $item, $data['status'], $data['resolution'] ?? null);
        } catch (DomainException $e) {
            return back()->withErrors(['status' => textByLanguage('انتقال غير مسموح لهذه الحالة.', 'That status change is not allowed.')]);
        }

        return back()->with('status', textByLanguage('تم تحديث حالة المفقود.', 'Lost item status updated.'));
    }
}
