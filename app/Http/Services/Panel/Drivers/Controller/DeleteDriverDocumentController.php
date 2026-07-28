<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Drivers\Logic\DriverDocumentRepository;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class DeleteDriverDocumentController extends Controller
{
    public function __invoke(int $driver, int $document, EntityScope $scope, DriverRepository $drivers, DriverDocumentRepository $documents): RedirectResponse
    {
        $model = $drivers->findOrFail($driver);

        $documents->delete($documents->findForDriver($model->id, $document));

        return redirect()
            ->route("panel.{$scope->guard()}.driver.show", $model->id)
            ->with('status', textByLanguage('تم حذف المستند', 'Document deleted'));
    }
}
