<?php

namespace App\Http\Services\Panel\Admin\RatingTags\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\RatingTag;
use Illuminate\View\View;

class RatingTagsPageController extends Controller
{
    public function __invoke(): View
    {
        // Per-country catalog — read on the active shard.
        return view('panel.rating-tags.index', [
            'tags' => RatingTag::on(TenantConnection::current())
                ->orderBy('audience')->orderBy('sort')->orderBy('id')->get(),
        ]);
    }
}
