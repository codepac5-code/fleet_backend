<?php

namespace App\Http\Services\Panel\Admin\Settings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\GeoServices\ShardContext;
use App\Http\Services\Panel\Admin\Settings\Logic\SettingsRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\Currency;
use App\Models\Document;
use App\Models\InfrastructureNode;
use App\Models\Service;
use App\Models\SiteFaq;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\View\View;

class SettingsPageController extends Controller
{
    public function __invoke(EntityScope $scope, SettingsRepository $settings): View
    {
        $node = ShardContext::current();

        return view('panel.settings.index', [
            'entity'      => $scope->guard(),
            'user'        => $scope->user(),
            'commissions' => $settings->commissions(),
            'system'      => $settings->system(),
            'countryName' => $node?->name,
            'currencies'  => Currency::where('is_active', true)->orderBy('code')->pluck('code', 'code')->all(),
            'counts'      => [
                'countries'     => $this->count(fn () => InfrastructureNode::where('type', 'country')->count()),
                'currencies'    => $this->count(fn () => Currency::count()),
                'plans'         => $this->count(fn () => SubscriptionPlan::count()),
                'services'      => $this->count(fn () => Service::count()),
                'documents'     => $this->count(fn () => Document::count()),
                'faqs'          => $this->count(fn () => SiteFaq::count()),
            ],
        ]);
    }

    private function count(callable $callback): ?int
    {
        try {
            return (int) $callback();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
