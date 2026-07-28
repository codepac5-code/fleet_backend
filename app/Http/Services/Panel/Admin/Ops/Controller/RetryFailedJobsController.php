<?php

namespace App\Http\Services\Panel\Admin\Ops\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

class RetryFailedJobsController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $id = $request->input('id');

        try {
            if ($id === 'all' || $id === null) {
                Artisan::call('queue:retry', ['id' => ['all']]);
            } else {
                $uuid = DB::table('failed_jobs')->where('id', (int) $id)->value('uuid');
                if ($uuid) {
                    Artisan::call('queue:retry', ['id' => [$uuid]]);
                }
            }
        } catch (Throwable $e) {
            return back()->with('error', textByLanguage('تعذّر إعادة التشغيل', 'Retry failed'));
        }

        return back()->with('status', textByLanguage('تمت إعادة جدولة الوظائف الفاشلة', 'Failed jobs re-queued'));
    }
}
