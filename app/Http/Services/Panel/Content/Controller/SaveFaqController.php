<?php

namespace App\Http\Services\Panel\Content\Controller;

use App\Http\Controllers\Controller;
use App\Models\SiteFaq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaveFaqController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question_ar' => ['required', 'string', 'max:255'],
            'question_en' => ['required', 'string', 'max:255'],
            'answer_ar' => ['required', 'string'],
            'answer_en' => ['required', 'string'],
            'sort' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['sort'] = (int) ($data['sort'] ?? 0);
        $data['is_active'] = $request->boolean('is_active', true);

        $id = (int) $request->input('id');

        if ($id > 0) {
            SiteFaq::query()->whereKey($id)->update($data);
        } else {
            SiteFaq::query()->create($data);
        }

        return back()->with('status', textByLanguage('تم حفظ السؤال.', 'FAQ saved.'));
    }
}
