<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Drivers\Logic\DocumentStatus;
use App\Http\Services\Panel\Drivers\Logic\DriverDocumentRepository;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Drivers\Request\StoreDriverDocumentRequest;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class StoreDriverDocumentController extends Controller
{
    public function __invoke(StoreDriverDocumentRequest $request, int $driver, EntityScope $scope, DriverRepository $drivers, DriverDocumentRepository $documents): RedirectResponse
    {
        $model = $drivers->findOrFail($driver);

        $data = $request->validated();
        $data['driverId'] = $model->id;
        $data['file'] = $request->file('file')->store('driver-documents', 'public');
        $data['status'] = DocumentStatus::PENDING;

        $documents->create($data);

        return redirect()
            ->route("panel.{$scope->guard()}.driver.show", $model->id)
            ->with('status', textByLanguage('تم رفع المستند', 'Document uploaded'));
    }
}
