<?php

namespace App\Http\Services\Panel\Admin\RatingTags\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\RatingTag;
use Illuminate\Http\RedirectResponse;

class ToggleRatingTagController extends Controller
{
    public function __invoke(int $tag): RedirectResponse
    {
        $model = RatingTag::on(TenantConnection::current())->find($tag);

        if ($model !== null) {
            $model->is_active = ! $model->is_active;
            $model->save();
        }

        return back()->with('status', textByLanguage('تم تحديث حالة الوسم', 'Tag status updated'));
    }
}
