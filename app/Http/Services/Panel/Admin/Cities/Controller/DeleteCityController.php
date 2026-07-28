<?php

namespace App\Http\Services\Panel\Admin\Cities\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\City;
use Illuminate\Http\RedirectResponse;

class DeleteCityController extends Controller
{
    public function __invoke(int $city): RedirectResponse
    {
        $model = City::on(TenantConnection::current())->find($city);

        if ($model !== null) {
            $model->delete();
        }

        return back()->with('status', textByLanguage('تم حذف المدينة', 'City deleted'));
    }
}
