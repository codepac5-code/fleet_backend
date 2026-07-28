@php
    $ar = app()->getLocale() === 'ar';
    $t = fn($en, $arText) => $ar ? $arText : $en;
@endphp
<x-master-layout>
<div class="dash">
    <div class="head">
        <div>
            <h1>{{ $t('FAQ', 'الأسئلة الشائعة') }}</h1>
            <p>{{ $t('Add and manage the questions shown on the website (bilingual).', 'أضف وأدِر الأسئلة الظاهرة في الموقع (بلغتين).') }}</p>
        </div>
    </div>

    @if(session('status'))<div class="flash ok"><i class="fa-solid fa-circle-check"></i> {{ $t('Saved successfully.', 'تمّ الحفظ بنجاح.') }}</div>@endif
    @if($errors->any())<div class="flash bad"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</div>@endif

    <div class="grid">
        <div class="panel-card">
            <h3 id="formTitle">{{ $t('Add a question', 'إضافة سؤال') }}</h3>
            <form method="POST" action="{{ route('admin.site-faqs.store') }}" id="faqForm">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <div class="fld"><label>{{ $t('Question (EN)', 'السؤال (إنجليزي)') }} <span>*</span></label><input name="question_en" id="f_qen" required></div>
                <div class="fld"><label>{{ $t('Question (AR)', 'السؤال (عربي)') }} <span>*</span></label><input name="question_ar" id="f_qar" dir="rtl" required></div>
                <div class="fld"><label>{{ $t('Answer (EN)', 'الجواب (إنجليزي)') }} <span>*</span></label><textarea name="answer_en" id="f_aen" required></textarea></div>
                <div class="fld"><label>{{ $t('Answer (AR)', 'الجواب (عربي)') }} <span>*</span></label><textarea name="answer_ar" id="f_aar" dir="rtl" required></textarea></div>
                <div class="fld"><label>{{ $t('Sort order', 'الترتيب') }}</label><input name="sort" id="f_sort" type="number" min="0" value="0"></div>
                <label class="cb"><input type="checkbox" name="is_active" id="f_active" value="1" checked> {{ $t('Active', 'مُفعّل') }}</label>
                <div class="actions">
                    <button type="submit" class="btn-main" id="submitBtn">{{ $t('Add question', 'إضافة السؤال') }}</button>
                    <button type="button" class="btn-soft" id="cancelEdit" style="display:none" onclick="resetForm()">{{ $t('Cancel', 'إلغاء') }}</button>
                </div>
            </form>
        </div>

        <div class="panel-card">
            <h3>{{ $t('Questions', 'الأسئلة') }} <span class="count">{{ $faqs->count() }}</span></h3>
            @forelse($faqs as $faq)
                <div class="row {{ $faq->is_active ? '' : 'off' }}">
                    <div class="rmain">
                        <div class="rq">{{ $ar ? $faq->question_ar : $faq->question_en }}</div>
                        <div class="ra">{{ \Illuminate\Support\Str::limit($ar ? $faq->answer_ar : $faq->answer_en, 90) }}</div>
                        @if(!$faq->is_active)<span class="pill dim">{{ $t('Inactive', 'معطّل') }}</span>@endif
                    </div>
                    <div class="rbtns">
                        <button class="ic-btn edit" onclick='editFaq(@json($faq))'><i class="fa-solid fa-pen"></i></button>
                        <form method="POST" action="{{ route('admin.site-faqs.toggle', $faq->id) }}">@csrf<button class="ic-btn"><i class="fa-solid fa-power-off"></i></button></form>
                        <form method="POST" action="{{ route('admin.site-faqs.destroy', $faq->id) }}" onsubmit="return confirm('{{ $t('Delete?', 'حذف؟') }}')">@csrf @method('DELETE')<button class="ic-btn del"><i class="fa-solid fa-trash"></i></button></form>
                    </div>
                </div>
            @empty
                <p class="muted">{{ $t('No questions yet.', 'لا أسئلة بعد.') }}</p>
            @endforelse
        </div>
    </div>
</div>

<script>
    const BASE = "{{ url('/admin/site-faqs') }}";
    function editFaq(f) {
        document.getElementById('f_qen').value = f.question_en;
        document.getElementById('f_qar').value = f.question_ar;
        document.getElementById('f_aen').value = f.answer_en;
        document.getElementById('f_aar').value = f.answer_ar;
        document.getElementById('f_sort').value = f.sort;
        document.getElementById('f_active').checked = !!f.is_active;
        document.getElementById('faqForm').action = BASE + '/' + f.id;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('formTitle').textContent = "{{ $t('Edit question', 'تعديل السؤال') }}";
        document.getElementById('submitBtn').textContent = "{{ $t('Save changes', 'حفظ') }}";
        document.getElementById('cancelEdit').style.display = '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    function resetForm() {
        const f = document.getElementById('faqForm'); f.reset();
        f.action = "{{ route('admin.site-faqs.store') }}"; document.getElementById('formMethod').value = 'POST';
        document.getElementById('formTitle').textContent = "{{ $t('Add a question', 'إضافة سؤال') }}";
        document.getElementById('submitBtn').textContent = "{{ $t('Add question', 'إضافة السؤال') }}";
        document.getElementById('cancelEdit').style.display = 'none';
    }
</script>

<style>
    .dash { max-width: 1050px; margin: auto; padding: 40px 20px; font-family: 'Plus Jakarta Sans','Cairo',sans-serif; }
    .head h1 { font-size: 1.9rem; font-weight: 800; color: #312873; } .head p { color: #6b7280; margin-top: .3rem; }
    .flash { display: flex; align-items: center; gap: .6rem; padding: .85rem 1.1rem; border-radius: 12px; font-weight: 700; margin: 1rem 0; }
    .flash.ok { background: #ecfdf5; color: #047857; } .flash.bad { background: #fef2f2; color: #b91c1c; }
    .grid { display: grid; grid-template-columns: 400px 1fr; gap: 1.2rem; align-items: start; margin-top: 1.2rem; }
    .panel-card { background: #fff; border: 1px solid #eceefb; border-radius: 18px; padding: 1.4rem; box-shadow: 0 10px 30px rgba(49,40,115,.05); }
    .panel-card h3 { font-size: 1.05rem; font-weight: 800; color: #312873; margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem; }
    .count { background: #F29C0B; color: #fff; font-size: .72rem; padding: 2px 9px; border-radius: 999px; }
    .fld { display: flex; flex-direction: column; gap: .3rem; margin-bottom: .8rem; }
    .fld label { font-size: .78rem; font-weight: 700; color: #312873; } .fld label span { color: #F29C0B; }
    .fld input, .fld textarea { border: 1.5px solid #eceefb; border-radius: 10px; padding: .65rem .75rem; font-size: .9rem; background: #fbfcff; font-family: inherit; }
    .fld textarea { min-height: 70px; resize: vertical; }
    .fld input:focus, .fld textarea:focus { outline: none; border-color: #F29C0B; background: #fff; box-shadow: 0 0 0 3px rgba(242,156,11,.12); }
    .cb { display: inline-flex; align-items: center; gap: .4rem; font-size: .85rem; font-weight: 600; color: #312873; cursor: pointer; }
    .actions { display: flex; gap: .6rem; margin-top: 1rem; }
    .btn-main { background: linear-gradient(135deg,#F29C0B,#FFB43B); color: #fff; border: none; padding: .75rem 1.3rem; border-radius: 10px; font-weight: 800; cursor: pointer; }
    .btn-soft { background: #ece9f6; color: #312873; border: none; padding: .7rem 1.1rem; border-radius: 10px; font-weight: 700; cursor: pointer; }
    .row { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: .9rem; border: 1px solid #eceefb; border-radius: 12px; margin-bottom: .6rem; }
    .row.off { opacity: .55; }
    .rq { font-weight: 800; color: #312873; font-size: .92rem; } .ra { color: #6b7280; font-size: .8rem; margin-top: .2rem; }
    .pill.dim { background: #f3f4f6; color: #6b7280; font-size: .64rem; font-weight: 800; padding: 2px 8px; border-radius: 999px; margin-top: .3rem; display: inline-block; }
    .rbtns { display: flex; gap: .4rem; } .rbtns form { margin: 0; }
    .ic-btn { width: 34px; height: 34px; border-radius: 9px; border: 1px solid #eceefb; background: #fff; color: #312873; cursor: pointer; }
    .ic-btn:hover { background: #312873; color: #fff; } .ic-btn.del:hover { background: #ef4444; border-color: #ef4444; } .ic-btn.edit:hover { background: #F29C0B; border-color: #F29C0B; }
    .muted { color: #9aa1bd; padding: 1rem 0; }
    @media (max-width: 820px) { .grid { grid-template-columns: 1fr; } }
</style>
</x-master-layout>
