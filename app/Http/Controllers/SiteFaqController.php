<?php

namespace App\Http\Controllers;

use App\Models\SiteFaq;
use Illuminate\Http\Request;

class SiteFaqController extends Controller
{
    public function index()
    {
        $faqs = SiteFaq::query()->orderBy('sort')->orderBy('id')->get();

        return view('panel.admin.site-faqs', compact('faqs'));
    }

    public function store(Request $request)
    {
        SiteFaq::query()->create($this->validated($request));

        return back()->with('status', 'created');
    }

    public function update(Request $request, $id)
    {
        SiteFaq::query()->findOrFail($id)->update($this->validated($request));

        return back()->with('status', 'updated');
    }

    public function destroy($id)
    {
        SiteFaq::query()->findOrFail($id)->delete();

        return back()->with('status', 'deleted');
    }

    public function toggle($id)
    {
        $faq = SiteFaq::query()->findOrFail($id);
        $faq->is_active = !$faq->is_active;
        $faq->save();

        return back()->with('status', 'toggled');
    }

    private function validated(Request $request): array
    {
        $v = $request->validate([
            'question_en' => 'required|string|max:255',
            'question_ar' => 'required|string|max:255',
            'answer_en' => 'required|string|max:2000',
            'answer_ar' => 'required|string|max:2000',
            'sort' => 'nullable|integer|min:0',
        ]);

        return [
            'question_en' => $v['question_en'], 'question_ar' => $v['question_ar'],
            'answer_en' => $v['answer_en'], 'answer_ar' => $v['answer_ar'],
            'sort' => (int) ($v['sort'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
