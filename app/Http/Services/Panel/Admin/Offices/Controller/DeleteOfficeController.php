<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use Illuminate\Http\RedirectResponse;

class DeleteOfficeController extends Controller
{
    public function __invoke(int $office, OfficeRepository $offices): RedirectResponse
    {
        $offices->delete($offices->findOrFail($office));

        return redirect()
            ->route('panel.admin.office.index')
            ->with('status', textByLanguage('تم حذف المكتب', 'Office deleted'));
    }
}
