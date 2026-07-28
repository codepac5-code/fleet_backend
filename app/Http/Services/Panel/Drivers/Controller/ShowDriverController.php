<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Documents\Logic\DocumentRepository;
use App\Http\Services\Panel\Drivers\Logic\DocumentStatus;
use App\Http\Services\Panel\Drivers\Logic\DriverDocumentRepository;
use App\Http\Services\Panel\Drivers\Logic\DriverProfile;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Office;
use Illuminate\Contracts\View\View;

class ShowDriverController extends Controller
{
    public function __invoke(int $driver, EntityScope $scope, DriverRepository $drivers, DriverProfile $profile, DriverDocumentRepository $documents, DocumentRepository $documentTypes): View
    {
        $model = $drivers->findOrFail($driver);

        $office = $model->officeId ? Office::on(TenantConnection::current())->find($model->officeId) : null;

        return view('panel.drivers.show', [
            'entity'        => $scope->guard(),
            'user'          => $scope->user(),
            'isAdmin'       => $scope->isAdmin(),
            'driver'        => $model,
            'office'        => $office,
            'overview'      => $profile->overview($model),
            'vehicle'       => $profile->vehicle($model),
            'rating'        => $profile->ratingSummary($model),
            'documents'     => $documents->forDriver($model->id),
            'documentTypes' => $documentTypes->activeOptions(),
            'statusOptions' => DocumentStatus::options(),
        ]);
    }
}
