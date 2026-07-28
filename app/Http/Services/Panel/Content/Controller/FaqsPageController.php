<?php

namespace App\Http\Services\Panel\Content\Controller;

use App\Http\Controllers\Controller;
use App\Models\SiteFaq;
use Illuminate\Contracts\View\View;

class FaqsPageController extends Controller
{
    public function __invoke(): View
    {
        return view('panel.faqs.index', [
            'entity' => 'admin',
            'faqs' => SiteFaq::query()->orderBy('sort')->orderBy('id')->get(),
        ]);
    }
}
