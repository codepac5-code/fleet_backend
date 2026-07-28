<?php
use App\Http\Core\GoogleService;
use App\Models\InfrastructureNode;

class GeoResolver
{
    public function resolve($lat, $lng)
    {
        $location = app(GoogleService::class )
            ->resolveLocation($lat, $lng);

        if (!$location) {
            return null;
        }

        return InfrastructureNode::query()
            ->where('country_code', $location['country_code'])
            ->where('city', $location['city'])
            ->where('is_active', true)
            ->first();
    }
}
