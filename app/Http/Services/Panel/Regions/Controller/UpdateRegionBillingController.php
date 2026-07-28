<?php

namespace App\Http\Services\Panel\Regions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Billing\BillingMode;
use App\Models\InfrastructureNode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateRegionBillingController extends Controller
{
    public function __invoke(Request $request, int $node): RedirectResponse
    {
        $data = $request->validate([
            'billing_mode' => ['required', 'in:' . implode(',', BillingMode::ALL)],
        ]);

        $country = InfrastructureNode::query()
            ->where('id', $node)
            ->where('type', 'country')
            ->firstOrFail();

        $country->billing_mode = $data['billing_mode'];
        $country->save();

        $label = $data['billing_mode'] === BillingMode::SUBSCRIPTION
            ? textByLanguage('الاشتراك', 'Subscription')
            : textByLanguage('العمولة', 'Commission');

        return back()->with('status', textByLanguage('تم ضبط ', 'Set ') . $country->name . textByLanguage(' على وضع ', ' to ') . $label . '.');
    }
}
