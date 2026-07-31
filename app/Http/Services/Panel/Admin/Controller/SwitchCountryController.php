<?php

namespace App\Http\Services\Panel\Admin\Controller;

use App\Http\Controllers\Controller;
use App\Models\InfrastructureNode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SwitchCountryController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country_id' => ['required'],
        ]);

        if ($validated['country_id'] === \App\Http\Core\GeoServices\ShardAggregator::SCOPE) {
            session(['active_shard_id' => \App\Http\Core\GeoServices\ShardAggregator::SCOPE]);

            return redirect()->route('panel.admin.home');
        }

        $node = InfrastructureNode::query()
            ->where('id', $validated['country_id'])
            ->where('type', 'country')
            ->where('is_active', true)
            ->first();

        if (! $node) {
            return back()->withErrors(['country_id' => __('auth.unsupported_region')]);
        }

        // Never switch into a country whose shard isn't provisioned yet — its
        // database doesn't exist, so every screen would crash. Send the admin to
        // provision it first.
        if ($node->provisioned_at === null) {
            return back()->withErrors(['country_id' => textByLanguage(
                'جهّز قاعدة بيانات هذه الدولة أولاً من شاشة الدول',
                'Provision this country\'s database first (Countries screen)'
            )]);
        }

        session(['active_shard_id' => $node->id]);

        return redirect()->route('panel.admin.home');
    }
}
