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

        session(['active_shard_id' => $node->id]);

        return redirect()->route('panel.admin.home');
    }
}
