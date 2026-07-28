<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use App\Models\Service;
use App\Models\SubService;
use App\Models\TravelRoutes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class SubServicesPricingController extends Controller
{
public function page(): mixed
{
    $services = Service::forCurrentUser()->get();
    return view('services_pricing.my_services', compact('services'));
}


    public function index() {
        $services = Service::forCurrentUser()->with(['subServices.travelRoutes'])->get();
        $data = $services->map(fn($service)=>[
            'id' => $service->id,
            'title' => $service->title,
            'description' => $service->description,
            'image' => $service->image,
            'travel_service' => $service->travel_service,
            'sub_services' => $service->subServices->map(fn($sub)=>[
                'id' => $sub->id,
                'name' => $sub->name,
                'openPrice' => $sub->openPrice ?? 0,
                'kmPrice' => $sub->kmPrice ?? 0,
                'minutePrice' => $sub->minutePrice ?? 0,
                'routes' => $sub->travelRoutes->map(fn($r)=>[
                    'departure_city_id' => $r->departure_city_id,
                    'departure_country_id' => City::find($r->departure_city_id)?->countryId,
                    'arrival_city_id' => $r->arrival_city_id,
                    'arrival_country_id' => City::find($r->arrival_city_id)?->countryId,
                    'trip_price' => $r->trip_price
                ]),
            ])
        ]);

        return response()->json($data);
    }

    public function show($id){
        $sub = SubService::findOrFail($id);
        return response()->json([
            'id'=>$sub->id,
            'name'=>$sub->name,
            'openPrice'=>$sub->openPrice ?? 0,
            'kmPrice'=>$sub->kmPrice ?? 0,
            'minutePrice'=>$sub->minutePrice ?? 0
        ]);
    }

    public function update(Request $request,$id){
        $sub = SubService::findOrFail($id);
        $sub->update($request->only(['openPrice','kmPrice','minutePrice']));
        return response()->json(['success'=>true]);
    }

    public function getRoutes($id){
        $sub = SubService::findOrFail($id);
        return response()->json($sub->travelRoutes()->get());
    }

    public function updateRoutes(Request $request,$id){
        $sub = SubService::findOrFail($id);
        $sub->travelRoutes()->delete();
        foreach($request->routes as $route){
            TravelRoutes::create([
                'sub_service_id' => $id,
                'departure_city_id' => $route['departure_city_id'],
                'arrival_city_id' => $route['arrival_city_id'],
                'trip_price' => $route['trip_price']
            ]);
        }
        return response()->json(['success'=>true]);
    }

    public function cities($countryId){
        $cities = City::where('countryId', $countryId)->get();
        return response()->json($cities);
    }

    public function countries(){
        $countries = Country::all();
        return response()->json($countries);
    }
}
