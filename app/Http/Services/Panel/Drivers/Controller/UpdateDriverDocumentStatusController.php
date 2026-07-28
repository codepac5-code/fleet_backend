<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Drivers\Logic\DriverDocumentRepository;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Drivers\Request\UpdateDriverDocumentStatusRequest;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class UpdateDriverDocumentStatusController extends Controller
{
    public function __invoke(UpdateDriverDocumentStatusRequest $request, int $driver, int $document, EntityScope $scope, DriverRepository $drivers, DriverDocumentRepository $documents): RedirectResponse
    {
        $model = $drivers->findOrFail($driver);
        $doc = $documents->findForDriver($model->id, $document);

        $documents->updateStatus($doc, $request->validated()['status'], $request->input('note'));

        return redirect()
            ->route("panel.{$scope->guard()}.driver.show", $model->id)
            ->with('status', textByLanguage('تم تحديث حالة المستند', 'Document status updated'));
    }
}
