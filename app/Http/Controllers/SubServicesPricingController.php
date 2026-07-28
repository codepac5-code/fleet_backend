<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use App\Models\Service;
use App\Models\SubService;
use App\Models\TravelRoutes;
use App\Models\OfficeSubServicePrice;
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
    $services = Service::forCurrentUser()->with(['subServices.travelRoutes', 'subServices.officePrices'])->get();

    $data = $services->map(function($service) {
        return [
            'id' => $service->id,
            'title' => $service->title,
            'description' => $service->description,
            'image' => $service->image,
            'travel_service' => $service->travel_service,
            'sub_services' => $service->subServices->map(function($sub) {

                $openPrice = $sub->openPrice ?? 0;
                $kmPrice   = $sub->kmPrice ?? 0;
                $minutePrice = $sub->minutePrice ?? 0;

                if(Auth::guard('office')->check()) {
                    $office = Auth::guard('office')->user();
                    $price = $sub->officePrices()->where('office_id', $office->id)->first();
                    if($price) {
                        $openPrice = $price->openPrice;
                        $kmPrice = $price->kmPrice;
                        $minutePrice = $price->minutePrice;
                    }
                }

                if(Auth::guard('employee')->check()) {
                    $employee = Auth::guard('employee')->user();
                    if($employee->officeId) {
                        $price = $sub->officePrices()->where('office_id', $employee->officeId)->first();
                        if($price) {
                            $openPrice = $price->openPrice;
                            $kmPrice = $price->kmPrice;
                            $minutePrice = $price->minutePrice;
                        }
                    }
                }

                return [
                    'id' => $sub->id,
                    'name' => $sub->name,
                    'openPrice' => $openPrice,
                    'kmPrice' => $kmPrice,
                    'minutePrice' => $minutePrice,

                    'routes' => $sub->travelRoutes->map(function($r) {
                    $depCity = City::find($r->departure_city_id);
                    $arrCity = City::find($r->arrival_city_id);
                    $depCountry = $depCity ? Country::find($depCity->countryId) : null;
                    $arrCountry = $arrCity ? Country::find($arrCity->countryId) : null;

                    return [
                        'departure_city_id' => $r->departure_city_id,
                        'departure_city_name' => $depCity?->name,
                        'departure_country_id' => $depCity?->countryId,
                        'departure_country_name' => $depCountry?->name,
                        'arrival_city_id' => $r->arrival_city_id,
                        'arrival_city_name' => $arrCity?->name,
                        'arrival_country_id' => $arrCity?->countryId,
                        'arrival_country_name' => $arrCountry?->name,
                        'trip_price' => $r->trip_price
                    ];
                    }),
                ];
            }),
        ];
    });

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

    public function update(Request $request, $id){
        $sub = SubService::findOrFail($id);

        if(Auth::guard('admin')->check()){
            $sub->update($request->only(['openPrice','kmPrice','minutePrice']));

        } elseif(Auth::guard('office')->check()){
            $office = Auth::guard('office')->user();
            OfficeSubServicePrice::updateOrCreate(
                [
                    'office_id' => $office->id,
                    'sub_service_id' => $id
                ],
                $request->only(['openPrice','kmPrice','minutePrice'])
            );

        } elseif(Auth::guard('employee')->check()){
            $employee = Auth::guard('employee')->user();
            if($employee->officeId){
                OfficeSubServicePrice::updateOrCreate(
                    [
                        'office_id' => $employee->officeId,
                        'sub_service_id' => $id
                    ],
                    $request->only(['openPrice','kmPrice','minutePrice'])
                );
            } else {
                $sub->update($request->only(['openPrice','kmPrice','minutePrice']));
            }
        }

        return response()->json(['success'=>true]);
    }

    public function getRoutes($id){
        $sub = SubService::findOrFail($id);

        $routes = $sub->travelRoutes->map(function($r){
            $depCity = City::find($r->departure_city_id);
            $arrCity = City::find($r->arrival_city_id);
            $depCountry = $depCity ? Country::find($depCity->countryId) : null;
            $arrCountry = $arrCity ? Country::find($arrCity->countryId) : null;

            return [
                'departure_city_id' => $r->departure_city_id,
                'departure_city_name' => $depCity?->name,
                'departure_country_id' => $depCity?->countryId,
                'departure_country_name' => $depCountry?->name,
                'arrival_city_id' => $r->arrival_city_id,
                'arrival_city_name' => $arrCity?->name,
                'arrival_country_id' => $arrCity?->countryId,
                'arrival_country_name' => $arrCountry?->name,
                'trip_price' => $r->trip_price
            ];
        });

        return response()->json($routes);
    }


    public function updateRoutes(Request $request, $id){
        $sub = SubService::findOrFail($id);

        $officeId = null;
        if(Auth::guard('office')->check()){
            $officeId = Auth::guard('office')->user()->id;
        } elseif(Auth::guard('employee')->check()){
            $employee = Auth::guard('employee')->user();
            if($employee->officeId){
                $officeId = $employee->officeId;
            }
        }

        $query = $sub->travelRoutes();
        if($officeId){
            $query->where('officeId', $officeId);
        }
        $query->delete();

        foreach($request->routes as $route){
            $data = [
                'sub_service_id' => $id,
                'departure_city_id' => $route['departure_city_id'],
                'arrival_city_id' => $route['arrival_city_id'],
                'trip_price' => $route['trip_price'],
            ];
            if($officeId){
                $data['officeId'] = $officeId;
            }
            TravelRoutes::create($data);
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
