@extends('panel.layouts.master')

@section('title', textByLanguage('حوافز السائقين', 'Driver incentives'))
@section('page-title', textByLanguage('حوافز السائقين', 'Driver incentives'))

@php
    $r = fn ($n) => "panel.admin.{$n}";
    $inp = 'width:100%;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);';
    $lbl = 'display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;';
    $money = fn ($minor) => number_format(((int) $minor) / 100, 2) . ' ' . $currency;
    $windowLabel = [
        'day' => textByLanguage('يومي', 'Daily'),
        'week' => textByLanguage('أسبوعي', 'Weekly'),
        'month' => textByLanguage('شهري', 'Monthly'),
    ];
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('برامج الحوافز', 'Incentive programmes')"
        :subtitle="textByLanguage('أكمل عدد رحلات خلال المدة → تُضاف المكافأة إلى محفظة السائق فوراً', 'Complete N rides within the window → the reward lands in the driver wallet immediately')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    <div class="p-card" style="margin-bottom:16px;">
        <form id="ruleForm" method="POST" action="{{ route($r('incentives.store')) }}" style="display:grid;grid-template-columns:1fr 1fr auto auto auto auto;gap:10px;align-items:end;">
            @csrf
            <input type="hidden" name="_method" id="ruleMethod" value="POST">
            <div><label style="{{ $lbl }}">{{ textByLanguage('الاسم بالعربية', 'Arabic name') }}</label>
                <input name="name_ar" id="ruleAr" required style="{{ $inp }}" placeholder="حافز نهاية الأسبوع"></div>
            <div><label style="{{ $lbl }}">{{ textByLanguage('الاسم بالإنجليزية', 'English name') }}</label>
                <input name="name_en" id="ruleEn" required style="{{ $inp }}" placeholder="Weekend push"></div>
            <div><label style="{{ $lbl }}">{{ textByLanguage('المدة', 'Window') }}</label>
                <select name="window" id="ruleWindow" style="{{ $inp }}">
                    @foreach($windows as $w)<option value="{{ $w }}">{{ $windowLabel[$w] ?? $w }}</option>@endforeach
                </select></div>
            <div><label style="{{ $lbl }}">{{ textByLanguage('عدد الرحلات', 'Rides') }}</label>
                <input name="target_rides" id="ruleTarget" type="number" min="1" max="1000" required style="width:90px;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="{{ $lbl }}">{{ textByLanguage('المكافأة', 'Reward') }} ({{ $currency }})</label>
                <input name="reward" id="ruleReward" type="number" step="0.01" min="0" required style="width:110px;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div style="display:flex;gap:6px;">
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> <span id="ruleSubmitLabel">{{ textByLanguage('إضافة', 'Add') }}</span></button>
                <button type="button" id="ruleReset" class="p-btn p-btn--soft" style="display:none;">{{ textByLanguage('إلغاء', 'Cancel') }}</button>
            </div>
        </form>
    </div>

    <div class="p-grid p-grid--2" style="margin-bottom:18px;">
        <x-panel.stat :label="textByLanguage('برامج مفعّلة', 'Active programmes')" :value="$rules->where('is_active', true)->count()" icon="bi-trophy" />
        <x-panel.stat :label="textByLanguage('إجمالي ما دُفع', 'Total paid out')" :value="$money($paidTotalMinor)" icon="bi-cash-stack" />
    </div>

    <div class="p-card">
        @if($rules->count())
            <x-panel.table :headers="[textByLanguage('الحافز', 'Incentive'), textByLanguage('المدة', 'Window'), textByLanguage('الهدف', 'Target'), textByLanguage('المكافأة', 'Reward'), textByLanguage('الدورة الحالية', 'Current period'), textByLanguage('الحالة', 'Status'), '']">
                @foreach($rules as $rule)
                    @php $now = $current[$rule->id] ?? ['drivers' => 0, 'rewarded' => 0, 'paidMinor' => 0, 'period' => '—']; @endphp
                    <tr>
                        <td>
                            <div class="p-cell-main"><div>
                                <strong>{{ $rule->name_ar }}</strong>
                                <span class="p-cell-sub">{{ $rule->name_en }}</span>
                            </div></div>
                        </td>
                        <td>{{ $windowLabel[$rule->window] ?? $rule->window }}</td>
                        <td>{{ $rule->target_rides }} {{ textByLanguage('رحلة', 'rides') }}</td>
                        <td>{{ $money($rule->reward_minor) }}</td>
                        <td>
                            <span class="p-cell-sub" dir="ltr">{{ $now['period'] }}</span><br>
                            {{ $now['rewarded'] }}/{{ $now['drivers'] }} {{ textByLanguage('سائق نال المكافأة', 'drivers rewarded') }}
                            @if($now['paidMinor'] > 0) · {{ $money($now['paidMinor']) }}@endif
                        </td>
                        <td><x-panel.badge :tone="$rule->is_active ? 'success' : 'gray'">{{ $rule->is_active ? textByLanguage('يعمل', 'Running') : textByLanguage('متوقّف', 'Paused') }}</x-panel.badge></td>
                        <td>
                            <div class="p-row-actions">
                                <button type="button" class="p-icon-btn" title="{{ textByLanguage('تعديل', 'Edit') }}"
                                    data-rule-edit data-id="{{ $rule->id }}" data-ar="{{ $rule->name_ar }}" data-en="{{ $rule->name_en }}"
                                    data-window="{{ $rule->window }}" data-target="{{ $rule->target_rides }}"
                                    data-reward="{{ number_format($rule->reward_minor / 100, 2, '.', '') }}"><i class="bi bi-pencil"></i></button>
                                <form method="POST" action="{{ route($r('incentives.toggle'), $rule->id) }}">
                                    @csrf
                                    <button type="submit" class="p-icon-btn" title="{{ $rule->is_active ? textByLanguage('إيقاف', 'Pause') : textByLanguage('تشغيل', 'Resume') }}">
                                        <i class="bi {{ $rule->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-trophy"></i> {{ textByLanguage('لا توجد حوافز', 'No incentives') }}</p>
        @endif
    </div>

@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('ruleForm');
    if (!form) return;
    var storeAction = form.getAttribute('action');
    var updateBase = "{{ url('panel/admin/incentives') }}";

    function reset() {
        form.setAttribute('action', storeAction);
        document.getElementById('ruleMethod').value = 'POST';
        ['ruleAr', 'ruleEn', 'ruleTarget', 'ruleReward'].forEach(function (id) { document.getElementById(id).value = ''; });
        document.getElementById('ruleWindow').value = 'week';
        document.getElementById('ruleSubmitLabel').textContent = @json(textByLanguage('إضافة', 'Add'));
        document.getElementById('ruleReset').style.display = 'none';
    }

    document.querySelectorAll('[data-rule-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.setAttribute('action', updateBase + '/' + btn.dataset.id);
            document.getElementById('ruleMethod').value = 'PUT';
            document.getElementById('ruleAr').value = btn.dataset.ar || '';
            document.getElementById('ruleEn').value = btn.dataset.en || '';
            document.getElementById('ruleWindow').value = btn.dataset.window || 'week';
            document.getElementById('ruleTarget').value = btn.dataset.target || '';
            document.getElementById('ruleReward').value = btn.dataset.reward || '';
            document.getElementById('ruleSubmitLabel').textContent = @json(textByLanguage('حفظ', 'Save'));
            document.getElementById('ruleReset').style.display = '';
        });
    });

    document.getElementById('ruleReset').addEventListener('click', reset);
})();
</script>
@endpush
