<?php

namespace App\Http\Services\Panel\Support\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Support\LostFoundService;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\LostItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The office CONFIRMS a suggested lost ↔ found pair (the rider's lost report and
 * a driver's found report on the same trip). Links both and moves them to
 * `matched`, ready for the hand-back — verifying before any property changes
 * hands. Scoped to the office; admins act on the item's own office.
 */
class ConfirmLostItemMatchController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, LostFoundService $lostFound, int $item): RedirectResponse
    {
        $data = $request->validate([
            'matched_item_id' => ['required', 'integer', 'min:1'],
        ]);

        $record = LostItem::query()->findOrFail($item);

        // Country guard (see UpdateLostItemStatusController): both the record and
        // the counterpart it is matched with must belong to the active country.
        $country = LostItem::activeCountryCode();
        if ($country !== null && $record->country_code !== null && $record->country_code !== $country) {
            abort(404);
        }

        $officeId = $scope->isAdmin() ? (int) $record->office_id : (int) $scope->officeId();

        try {
            $lostFound->confirmMatch($officeId, $item, (int) $data['matched_item_id']);
        } catch (DomainException $e) {
            return back()->withErrors(['match' => textByLanguage('تعذّر تأكيد المطابقة.', 'Could not confirm that match.')]);
        }

        return back()->with('status', textByLanguage('تمّت مطابقة المفقود بالموجود.', 'Lost item matched to the found report.'));
    }
}
