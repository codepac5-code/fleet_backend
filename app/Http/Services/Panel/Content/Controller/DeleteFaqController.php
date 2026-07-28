<?php

namespace App\Http\Services\Panel\Content\Controller;

use App\Http\Controllers\Controller;
use App\Models\SiteFaq;
use Illuminate\Http\RedirectResponse;

class DeleteFaqController extends Controller
{
    public function __invoke(int $faq): RedirectResponse
    {
        SiteFaq::query()->whereKey($faq)->delete();

        return back()->with('status', textByLanguage('تم حذف السؤال.', 'FAQ deleted.'));
    }
}
