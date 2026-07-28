<?php

namespace App\Http\Services\Panel\Admin\Cities\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreCityController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'name_on_google_map' => ['nullable', 'string', 'max:120'],
        ]);

        // Written to the active country shard (single-shard guarded).
        $city = new City([
            'name' => $data['name'],
            'name_on_google_map' => $data['name_on_google_map'] ?? null,
        ]);
        $city->setConnection(TenantConnection::current());
        $city->save();

        return redirect()
            ->route('panel.admin.cities.index')
            ->with('status', textByLanguage('تمت إضافة المدينة', 'City added'));
    }
}
