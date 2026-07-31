<?php

namespace App\Http\Services\Panel\Admin\RatingTags\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\RatingTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SaveRatingTagController extends Controller
{
    public function __invoke(Request $request, ?int $tag = null): RedirectResponse
    {
        $conn = TenantConnection::current();
        $uniqueTable = ($conn ? $conn . '.' : '') . 'rating_tags';

        $data = $request->validate([
            'code' => [
                'required', 'string', 'max:40', 'regex:/^[a-z0-9_]+$/',
                Rule::unique($uniqueTable, 'code')->ignore($tag),
            ],
            'label_en' => ['required', 'string', 'max:120'],
            'label_ar' => ['required', 'string', 'max:120'],
            'audience' => ['required', Rule::in(RatingTag::AUDIENCES)],
            'stars_min' => ['nullable', 'integer', 'min:1', 'max:5'],
            'stars_max' => ['nullable', 'integer', 'min:1', 'max:5'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:65000'],
        ], [
            'code.regex' => textByLanguage('الرمز: أحرف إنجليزية صغيرة وأرقام و_ فقط', 'Code: lowercase letters, digits and _ only'),
        ]);

        $min = (int) ($data['stars_min'] ?? 1);
        $max = (int) ($data['stars_max'] ?? 5);

        if ($min > $max) {
            return back()->withErrors([
                'stars_min' => textByLanguage('أقل عدد نجوم يجب ألا يتجاوز الأعلى', 'Minimum stars cannot exceed the maximum'),
            ])->withInput();
        }

        $model = $tag !== null
            ? RatingTag::on($conn)->findOrFail($tag)
            : (new RatingTag())->setConnection($conn);

        $model->code = $data['code'];
        $model->label_en = $data['label_en'];
        $model->label_ar = $data['label_ar'];
        $model->audience = $data['audience'];
        $model->stars_min = $min;
        $model->stars_max = $max;
        $model->sort = (int) ($data['sort'] ?? 0);

        if ($tag === null) {
            $model->is_active = true;
        }

        $model->save();

        return back()->with('status', $tag !== null
            ? textByLanguage('تم تحديث الوسم', 'Tag updated')
            : textByLanguage('تمت إضافة الوسم', 'Tag added'));
    }
}
