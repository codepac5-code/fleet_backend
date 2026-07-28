@php
    $ar = app()->getLocale() === 'ar';
    $t = fn($en, $arText) => $ar ? $arText : $en;
    $money = fn($m, $c) => $m === null ? $t('Custom', 'مخصّص') : (($c ?: 'USD') . ' ' . number_format(((int) $m) / 100, 2));
@endphp
<x-master-layout>
<div class="dash">

    <div class="head">
        <div>
            <h1>{{ $t('Subscription plans', 'خطط الاشتراك') }}</h1>
            <p>{{ $t('Create, edit and manage the plans shown on the website and offered to offices.', 'أنشئ وعدّل وأدِر الخطط الظاهرة في الموقع والمعروضة على المكاتب.') }}</p>
        </div>
        <form method="POST" action="{{ route('admin.plans.seed') }}">@csrf
            <button type="submit" class="btn-soft"><i class="fa-solid fa-wand-magic-sparkles"></i> {{ $t('Seed defaults', 'تعبئة الافتراضيّة') }}</button>
        </form>
    </div>

    @if(session('status'))
        <div class="flash ok"><i class="fa-solid fa-circle-check"></i> {{ $t('Saved successfully.', 'تمّ الحفظ بنجاح.') }}</div>
    @endif
    @if(session('error'))
        <div class="flash bad"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="flash bad"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</div>
    @endif

    <div class="grid">
        <!-- FORM -->
        <div class="panel-card">
            <h3 id="formTitle">{{ $t('Add a plan', 'إضافة خطّة') }}</h3>
            <form method="POST" action="{{ route('admin.plans.store') }}" id="planForm">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <div class="f2">
                    <div class="fld"><label>{{ $t('Key', 'المفتاح') }} <span>*</span></label><input name="key" id="f_key" required placeholder="business"></div>
                    <div class="fld"><label>{{ $t('Name', 'الاسم') }} <span>*</span></label><input name="name" id="f_name" required placeholder="Business"></div>
                    <div class="fld"><label>{{ $t('Monthly price', 'السعر الشهريّ') }}</label><input name="price" id="f_price" type="number" step="0.01" min="0" placeholder="35"></div>
                    <div class="fld"><label>{{ $t('Currency', 'العملة') }}</label><input name="currency_code" id="f_cur" placeholder="USD" value="USD"></div>
                    <div class="fld"><label>{{ $t('Commission %', 'العمولة %') }}</label><input name="fleet_commission_rate" id="f_rate" type="number" step="0.01" min="0" max="100" placeholder="12"></div>
                    <div class="fld"><label>{{ $t('Driver limit', 'حدّ السائقين') }}</label><input name="driver_limit" id="f_limit" type="number" min="0" placeholder="50"></div>
                    <div class="fld"><label>{{ $t('Trial days', 'أيام التجربة') }}</label><input name="trial_days" id="f_trial" type="number" min="0" max="365" placeholder="14"></div>
                    <div class="fld"><label>{{ $t('Sort order', 'الترتيب') }}</label><input name="sort" id="f_sort" type="number" min="0" value="0"></div>
                    <div class="fld chk">
                        <label class="cb"><input type="checkbox" name="is_active" id="f_active" value="1" checked> {{ $t('Active', 'مُفعّلة') }}</label>
                        <label class="cb"><input type="checkbox" name="is_popular" id="f_pop" value="1"> {{ $t('Popular', 'الأكثر رواجاً') }}</label>
                    </div>
                </div>
                <div class="actions">
                    <button type="submit" class="btn-main" id="submitBtn">{{ $t('Add plan', 'إضافة الخطّة') }}</button>
                    <button type="button" class="btn-soft" id="cancelEdit" style="display:none" onclick="resetForm()">{{ $t('Cancel', 'إلغاء') }}</button>
                </div>
            </form>
            <p class="hint">{{ $t('Leave price / commission / limit empty for a custom (Enterprise) plan.', 'اترك السعر / العمولة / الحدّ فارغاً لخطّة مخصّصة (Enterprise).') }}</p>
        </div>

        <!-- LIST -->
        <div class="panel-card list">
            <h3>{{ $t('Current plans', 'الخطط الحاليّة') }} <span class="count">{{ $plans->count() }}</span></h3>
            @forelse($plans as $plan)
                <div class="row {{ $plan->is_active ? '' : 'off' }}">
                    <div class="rmain">
                        <div class="rname">
                            {{ $plan->name }}
                            <code>{{ $plan->key }}</code>
                            @if($plan->is_popular)<span class="pill pop">{{ $t('Popular', 'رائجة') }}</span>@endif
                            @if(!$plan->is_active)<span class="pill dim">{{ $t('Inactive', 'معطّلة') }}</span>@endif
                        </div>
                        <div class="rmeta">
                            <span><i class="fa-solid fa-tag"></i> {{ $money($plan->price_minor, $plan->currency_code) }}</span>
                            <span><i class="fa-solid fa-percent"></i> {{ $plan->fleet_commission_rate !== null ? rtrim(rtrim(number_format($plan->fleet_commission_rate, 2), '0'), '.') . '%' : $t('Custom', 'مخصّص') }}</span>
                            <span><i class="fa-solid fa-user"></i> {{ $plan->driver_limit ?? '∞' }}</span>
                        </div>
                    </div>
                    <div class="rbtns">
                        <button class="ic-btn edit" title="{{ $t('Edit', 'تعديل') }}"
                            data-id="{{ $plan->id }}" data-key="{{ $plan->key }}" data-name="{{ $plan->name }}"
                            data-price="{{ $plan->price_minor !== null ? $plan->price_minor / 100 : '' }}" data-cur="{{ $plan->currency_code }}"
                            data-rate="{{ $plan->fleet_commission_rate }}" data-limit="{{ $plan->driver_limit }}" data-trial="{{ $plan->trial_days }}" data-sort="{{ $plan->sort }}"
                            data-active="{{ $plan->is_active ? 1 : 0 }}" data-pop="{{ $plan->is_popular ? 1 : 0 }}"
                            onclick="editPlan(this)"><i class="fa-solid fa-pen"></i></button>
                        <form method="POST" action="{{ route('admin.plans.toggle', $plan->id) }}">@csrf
                            <button class="ic-btn" title="{{ $t('Toggle', 'تبديل') }}"><i class="fa-solid fa-power-off"></i></button>
                        </form>
                        <form method="POST" action="{{ route('admin.plans.destroy', $plan->id) }}" onsubmit="return confirm('{{ $t('Delete this plan?', 'حذف هذه الخطّة؟') }}')">@csrf @method('DELETE')
                            <button class="ic-btn del" title="{{ $t('Delete', 'حذف') }}"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="muted">{{ $t('No plans yet. Add one or seed the defaults.', 'لا خطط بعد. أضف واحدة أو عبّئ الافتراضيّة.') }}</p>
            @endforelse
        </div>
    </div>
</div>

<script>
    const BASE = "{{ url('/admin/plans') }}";
    function editPlan(b) {
        const d = b.dataset;
        document.getElementById('f_key').value = d.key;
        document.getElementById('f_name').value = d.name;
        document.getElementById('f_price').value = d.price;
        document.getElementById('f_cur').value = d.cur;
        document.getElementById('f_rate').value = d.rate;
        document.getElementById('f_limit').value = d.limit;
        document.getElementById('f_trial').value = d.trial;
        document.getElementById('f_sort').value = d.sort;
        document.getElementById('f_active').checked = d.active === '1';
        document.getElementById('f_pop').checked = d.pop === '1';
        document.getElementById('planForm').action = BASE + '/' + d.id;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('formTitle').textContent = "{{ $t('Edit plan', 'تعديل الخطّة') }}";
        document.getElementById('submitBtn').textContent = "{{ $t('Save changes', 'حفظ التعديلات') }}";
        document.getElementById('cancelEdit').style.display = '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    function resetForm() {
        const f = document.getElementById('planForm'); f.reset();
        f.action = "{{ route('admin.plans.store') }}"; document.getElementById('formMethod').value = 'POST';
        document.getElementById('formTitle').textContent = "{{ $t('Add a plan', 'إضافة خطّة') }}";
        document.getElementById('submitBtn').textContent = "{{ $t('Add plan', 'إضافة الخطّة') }}";
        document.getElementById('cancelEdit').style.display = 'none';
    }
</script>

<style>
    .dash { max-width: 1100px; margin: auto; padding: 36px 20px; font-family: 'Plus Jakarta Sans', 'Cairo', sans-serif; }
    .head { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.4rem; }
    .head h1 { font-size: 1.9rem; font-weight: 800; color: #312873; }
    .head p { color: #6b7280; font-size: .92rem; max-width: 520px; margin-top: .3rem; }
    .flash { display: flex; align-items: center; gap: .6rem; padding: .85rem 1.1rem; border-radius: 12px; font-weight: 700; margin-bottom: 1rem; }
    .flash.ok { background: #ecfdf5; color: #047857; } .flash.bad { background: #fef2f2; color: #b91c1c; }
    .grid { display: grid; grid-template-columns: 380px 1fr; gap: 1.2rem; align-items: start; }
    .panel-card { background: #fff; border: 1px solid #eceefb; border-radius: 18px; padding: 1.4rem; box-shadow: 0 10px 30px rgba(49,40,115,.05); }
    .panel-card h3 { font-size: 1.1rem; font-weight: 800; color: #312873; margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem; }
    .count { background: #F29C0B; color: #fff; font-size: .72rem; padding: 2px 9px; border-radius: 999px; }
    .f2 { display: grid; grid-template-columns: 1fr 1fr; gap: .8rem; }
    .fld { display: flex; flex-direction: column; gap: .3rem; } .fld.chk { grid-column: 1/-1; flex-direction: row; gap: 1.2rem; margin-top: .2rem; }
    .fld label { font-size: .76rem; font-weight: 700; color: #312873; } .fld label span { color: #F29C0B; }
    .fld input { border: 1.5px solid #eceefb; border-radius: 10px; padding: .62rem .7rem; font-size: .9rem; background: #fbfcff; }
    .fld input:focus { outline: none; border-color: #F29C0B; background: #fff; box-shadow: 0 0 0 3px rgba(242,156,11,.12); }
    .cb { display: inline-flex; align-items: center; gap: .4rem; font-size: .85rem; font-weight: 600; color: #312873; cursor: pointer; }
    .actions { display: flex; gap: .6rem; margin-top: 1.1rem; }
    .btn-main { background: linear-gradient(135deg,#F29C0B,#FFB43B); color: #fff; border: none; padding: .75rem 1.3rem; border-radius: 10px; font-weight: 800; cursor: pointer; }
    .btn-soft { background: #312873; color: #fff; border: none; padding: .7rem 1.1rem; border-radius: 10px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: .5rem; }
    .hint { color: #9aa1bd; font-size: .76rem; margin-top: .8rem; }
    .row { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: .95rem; border: 1px solid #eceefb; border-radius: 14px; margin-bottom: .7rem; transition: .2s; }
    .row:hover { box-shadow: 0 10px 24px rgba(49,40,115,.07); } .row.off { opacity: .6; }
    .rname { font-weight: 800; color: #312873; display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
    .rname code { background: #f1f0fb; color: #6b7280; padding: 1px 7px; border-radius: 6px; font-size: .72rem; }
    .pill { font-size: .64rem; font-weight: 800; padding: 2px 8px; border-radius: 999px; }
    .pill.pop { background: #fef3c7; color: #92400e; } .pill.dim { background: #f3f4f6; color: #6b7280; }
    .rmeta { display: flex; gap: 1rem; margin-top: .4rem; color: #6b7280; font-size: .8rem; flex-wrap: wrap; }
    .rmeta i { color: #F29C0B; margin-inline-end: .2rem; }
    .rbtns { display: flex; gap: .4rem; }
    .ic-btn { width: 36px; height: 36px; border-radius: 10px; border: 1px solid #eceefb; background: #fff; color: #312873; cursor: pointer; transition: .2s; }
    .ic-btn:hover { background: #312873; color: #fff; } .ic-btn.del:hover { background: #ef4444; border-color: #ef4444; } .ic-btn.edit:hover { background: #F29C0B; border-color: #F29C0B; }
    .rbtns form { margin: 0; }
    .muted { color: #9aa1bd; padding: 1rem 0; }
    @media (max-width: 820px) { .grid { grid-template-columns: 1fr; } }
</style>
</x-master-layout>
