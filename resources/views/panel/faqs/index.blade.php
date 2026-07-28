@extends('panel.layouts.master')

@section('title', textByLanguage('الأسئلة الشائعة', 'FAQs'))
@section('page-title', textByLanguage('الأسئلة الشائعة', 'FAQs'))

@php
    $total = $faqs->count();
    $active = $faqs->where('is_active', true)->count();
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('الأسئلة الشائعة', 'Frequently asked questions')"
        :subtitle="textByLanguage('تظهر في الموقع والتطبيقات', 'Shown on the site and apps')" />

    <div class="p-faq-stats">
        <x-panel.stat :label="textByLanguage('إجمالي الأسئلة', 'Total FAQs')" :value="$total" icon="bi-question-circle" />
        <x-panel.stat :label="textByLanguage('مفعّلة', 'Active')" :value="$active" icon="bi-check-circle" />
        <x-panel.stat :label="textByLanguage('معطّلة', 'Hidden')" :value="$total - $active" icon="bi-eye-slash" />
    </div>

    <x-panel.card :title="textByLanguage('إضافة / تعديل سؤال', 'Add / edit FAQ')" style="margin-bottom:1.1rem;">
        <x-slot:actions>
            <button type="button" class="p-btn p-btn--ghost" data-faq-reset style="display:none;"><i class="bi bi-plus-lg"></i> {{ textByLanguage('سؤال جديد', 'New') }}</button>
        </x-slot:actions>
        <form method="POST" action="{{ route('panel.admin.faqs.save') }}" data-faq-form>
            @csrf
            <input type="hidden" name="id" value="0" data-faq-id>
            <div class="p-form-grid">
                <x-panel.field name="question_ar" :label="textByLanguage('السؤال (AR)', 'Question (AR)')" full required />
                <x-panel.field name="question_en" :label="textByLanguage('السؤال (EN)', 'Question (EN)')" full required />
                <x-panel.field name="answer_ar" type="textarea" :label="textByLanguage('الجواب (AR)', 'Answer (AR)')" full required />
                <x-panel.field name="answer_en" type="textarea" :label="textByLanguage('الجواب (EN)', 'Answer (EN)')" full required />
                <x-panel.field name="sort" type="number" :label="textByLanguage('الترتيب', 'Sort')" value="0" />
                <div class="p-field">
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" checked data-faq-active> {{ textByLanguage('مفعّل', 'Active') }}
                    </label>
                </div>
            </div>
            <div class="p-form-actions">
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> <span data-faq-submit>{{ textByLanguage('حفظ', 'Save') }}</span></button>
            </div>
        </form>
    </x-panel.card>

    @if($total)
        <div class="p-faq-list">
            @foreach($faqs as $faq)
                <div class="p-faq-item @if(! $faq->is_active) is-off @endif"
                     data-faq
                     data-id="{{ $faq->id }}"
                     data-qar="{{ $faq->question_ar }}" data-qen="{{ $faq->question_en }}"
                     data-aar="{{ $faq->answer_ar }}" data-aen="{{ $faq->answer_en }}"
                     data-sort="{{ $faq->sort }}" data-active="{{ $faq->is_active ? 1 : 0 }}">
                    <div class="p-faq-item__head" data-faq-toggle>
                        <span class="p-faq-item__sort">{{ $faq->sort }}</span>
                        <div class="p-faq-item__q">
                            <strong>{{ $faq->question_ar }}</strong>
                            <span>{{ $faq->question_en }}</span>
                        </div>
                        <x-panel.badge :tone="$faq->is_active ? 'success' : 'danger'">{{ $faq->is_active ? textByLanguage('مفعّل', 'Active') : textByLanguage('معطّل', 'Off') }}</x-panel.badge>
                        <i class="bi bi-chevron-down p-faq-item__chev"></i>
                    </div>
                    <div class="p-faq-item__body">
                        <div class="p-faq-item__ans">
                            <p><span>{{ textByLanguage('الجواب (AR)', 'Answer AR') }}</span>{{ $faq->answer_ar }}</p>
                            <p><span>{{ textByLanguage('الجواب (EN)', 'Answer EN') }}</span>{{ $faq->answer_en }}</p>
                        </div>
                        <div class="p-faq-item__acts">
                            <button type="button" class="p-btn p-btn--ghost" data-faq-edit><i class="bi bi-pencil"></i> {{ textByLanguage('تعديل', 'Edit') }}</button>
                            <form method="POST" action="{{ route('panel.admin.faqs.delete', $faq->id) }}"
                                  onsubmit="return confirm('{{ textByLanguage('حذف هذا السؤال؟', 'Delete this FAQ?') }}');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-icon-btn p-icon-btn--danger" title="{{ textByLanguage('حذف', 'Delete') }}"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="p-empty"><i class="bi bi-question-circle"></i> {{ textByLanguage('لا توجد أسئلة', 'No FAQs') }}</p>
    @endif

    <script>
        (function () {
            var form = document.querySelector('[data-faq-form]');
            if (!form) return;
            var idIn = form.querySelector('[data-faq-id]');
            var submitTx = form.querySelector('[data-faq-submit]');
            var resetBtn = document.querySelector('[data-faq-reset]');
            var editLabel = @json(textByLanguage('تحديث السؤال', 'Update FAQ'));
            var newLabel = @json(textByLanguage('حفظ', 'Save'));

            function fill(el) {
                idIn.value = el.getAttribute('data-id');
                form.question_ar.value = el.getAttribute('data-qar');
                form.question_en.value = el.getAttribute('data-qen');
                form.answer_ar.value = el.getAttribute('data-aar');
                form.answer_en.value = el.getAttribute('data-aen');
                form.sort.value = el.getAttribute('data-sort');
                form.querySelector('[data-faq-active]').checked = el.getAttribute('data-active') === '1';
                submitTx.textContent = editLabel;
                if (resetBtn) resetBtn.style.display = '';
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                form.question_ar.focus();
            }

            document.querySelectorAll('[data-faq-toggle]').forEach(function (h) {
                h.addEventListener('click', function () { h.closest('[data-faq]').classList.toggle('is-open'); });
            });
            document.querySelectorAll('[data-faq-edit]').forEach(function (b) {
                b.addEventListener('click', function (e) { e.stopPropagation(); fill(b.closest('[data-faq]')); });
            });
            if (resetBtn) {
                resetBtn.addEventListener('click', function () {
                    form.reset();
                    idIn.value = '0';
                    submitTx.textContent = newLabel;
                    resetBtn.style.display = 'none';
                });
            }
        })();
    </script>

@endsection
