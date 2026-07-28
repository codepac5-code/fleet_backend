<?php

namespace App\Http\Services\Panel\Admin\Countries\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\InfrastructureNode;
use Illuminate\Contracts\View\View;

class CountriesPageController extends Controller
{
    public function __invoke(EntityScope $scope): View
    {
        $countries = InfrastructureNode::query()
            ->where('type', 'country')
            ->orderBy('name')
            ->get()
            ->map(fn (InfrastructureNode $node) => [
                'id'              => $node->id,
                'name'            => $node->name,
                'country_code'    => $node->country_code,
                'city'            => $node->city,
                'lat'             => $node->lat,
                'lng'             => $node->lng,
                'currency_code'   => $node->currency_code,
                'currency_symbol' => $node->currency_symbol,
                'db_host'         => $node->db_host,
                'db_port'         => $node->db_port,
                'db_name'         => $node->db_name,
                'db_user'         => $node->db_user,
                'redis_host'      => $node->redis_host,
                'redis_db'        => $node->redis_db,
                'redis_prefix'    => $node->redis_prefix,
                'is_active'       => (bool) $node->is_active,
                'has_db'          => (bool) ($node->db_host && $node->db_name),
            ])
            ->all();

        return view('panel.countries.index', [
            'entity'      => $scope->guard(),
            'countries'   => $countries,
            'activeShard' => session('active_shard_id'),
        ]);
    }
}
