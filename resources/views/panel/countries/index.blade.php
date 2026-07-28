@extends('panel.layouts.master')

@section('title', textByLanguage('الدول وقواعد البيانات', 'Countries & databases'))
@section('page-title', textByLanguage('الدول وقواعد البيانات', 'Countries & databases'))

@php
    $flag = function ($code) {
        $code = strtoupper(trim((string) $code));
        if (strlen($code) !== 2 || ! ctype_alpha($code)) return '🌐';
        return mb_chr(127397 + ord($code[0]), 'UTF-8') . mb_chr(127397 + ord($code[1]), 'UTF-8');
    };
    $activeCount = collect($countries)->where('is_active', true)->count();
    $dbCount = collect($countries)->where('has_db', true)->count();
@endphp

@push('styles')
<style>
    .cx-kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 20px; }
    .cx-kpi { background: var(--p-surface, #fff); border: 1px solid var(--p-border); border-radius: var(--p-radius, 14px); padding: 16px 18px; display: flex; align-items: center; gap: 14px; }
    .cx-kpi__ic { width: 46px; height: 46px; border-radius: 12px; display: grid; place-items: center; font-size: 1.3rem; background: rgba(49,40,115,.1); color: var(--p-primary); }
    .cx-kpi__ic.ok { background: rgba(26,127,55,.12); color: var(--p-success); }
    .cx-kpi__ic.db { background: rgba(13,110,253,.12); color: #0d6efd; }
    .cx-kpi__n { font-size: 1.6rem; font-weight: 800; line-height: 1; }
    .cx-kpi__l { font-size: .82rem; color: var(--p-text-muted); margin-top: 4px; }

    .cx-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
    .cx-card { position: relative; background: var(--p-surface, #fff); border: 1px solid var(--p-border); border-radius: 16px; padding: 18px; transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease; overflow: hidden; }
    .cx-card:hover { transform: translateY(-3px); box-shadow: 0 14px 34px -18px rgba(0,0,0,.35); border-color: var(--p-accent); }
    .cx-card::before { content: ''; position: absolute; inset-block-start: 0; inset-inline-start: 0; width: 100%; height: 4px; background: linear-gradient(90deg, var(--p-primary), var(--p-accent)); opacity: .0; transition: opacity .2s; }
    .cx-card:hover::before { opacity: 1; }
    .cx-card.is-off { opacity: .62; }
    .cx-card.is-current { border-color: var(--p-accent); box-shadow: 0 0 0 2px rgba(49,40,115,.14); }

    .cx-head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
    .cx-flag { font-size: 2.2rem; line-height: 1; filter: drop-shadow(0 2px 4px rgba(0,0,0,.18)); }
    .cx-name { font-weight: 800; font-size: 1.06rem; }
    .cx-sub { font-size: .8rem; color: var(--p-text-muted); margin-top: 2px; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .cx-badges { margin-inline-start: auto; display: flex; flex-direction: column; gap: 6px; align-items: flex-end; }
    .cx-tag { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 999px; font-size: .72rem; font-weight: 700; white-space: nowrap; }
    .cx-tag.on { background: rgba(26,127,55,.12); color: var(--p-success); }
    .cx-tag.off { background: rgba(220,53,69,.12); color: var(--p-danger); }
    .cx-tag.cur { background: rgba(255,159,0,.14); color: #b26a00; }
    .cx-tag.live { background: rgba(49,40,115,.12); color: var(--p-primary); }

    .cx-db { background: var(--p-bg, #f6f7fb); border: 1px dashed var(--p-border); border-radius: 11px; padding: 11px 12px; font-size: .8rem; margin-bottom: 14px; }
    .cx-db__row { display: flex; justify-content: space-between; gap: 10px; padding: 3px 0; }
    .cx-db__row span:first-child { color: var(--p-text-muted); }
    .cx-db__row code { font-family: ui-monospace, Menlo, Consolas, monospace; direction: ltr; font-size: .78rem; }
    .cx-db.empty { text-align: center; color: var(--p-text-muted); border-color: var(--p-danger); }

    .cx-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .cx-btn { flex: 1 1 auto; min-width: 0; border: 1.5px solid var(--p-border); background: var(--p-surface, #fff); color: var(--p-text); border-radius: 10px; padding: 8px 10px; font-family: inherit; font-weight: 700; font-size: .8rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 5px; transition: all .15s; }
    .cx-btn:hover { border-color: var(--p-accent); color: var(--p-accent); }
    .cx-btn.p { background: var(--p-primary); border-color: var(--p-primary); color: #fff; }
    .cx-btn.p:hover { filter: brightness(1.08); color: #fff; }
    .cx-btn.warn:hover { border-color: var(--p-danger); color: var(--p-danger); }

    .cx-fab { display: inline-flex; align-items: center; gap: 8px; background: var(--p-primary); color: #fff; border: none; border-radius: 12px; padding: 11px 20px; font-family: inherit; font-weight: 800; font-size: .92rem; cursor: pointer; box-shadow: 0 10px 24px -12px var(--p-primary); }
    .cx-fab:hover { filter: brightness(1.08); }

    /* Wizard modal */
    .cx-modal { position: fixed; inset: 0; z-index: 1000; display: none; align-items: center; justify-content: center; padding: 20px; }
    .cx-modal.open { display: flex; }
    .cx-modal__bg { position: absolute; inset: 0; background: rgba(15,17,34,.55); backdrop-filter: blur(3px); }
    .cx-modal__box { position: relative; width: min(680px, 100%); max-height: 92vh; overflow-y: auto; background: var(--p-surface, #fff); border-radius: 20px; box-shadow: 0 30px 80px -20px rgba(0,0,0,.5); animation: cxpop .22s cubic-bezier(.2,.9,.3,1.2); }
    @keyframes cxpop { from { transform: translateY(14px) scale(.97); opacity: 0; } to { transform: none; opacity: 1; } }
    .cx-modal__head { padding: 20px 24px; border-bottom: 1px solid var(--p-border); display: flex; align-items: center; gap: 12px; }
    .cx-modal__head h3 { font-weight: 800; font-size: 1.15rem; margin: 0; }
    .cx-modal__x { margin-inline-start: auto; background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--p-text-muted); line-height: 1; }

    .cx-steps { display: flex; gap: 6px; padding: 16px 24px 0; }
    .cx-step { flex: 1; text-align: center; font-size: .76rem; font-weight: 700; color: var(--p-text-muted); position: relative; padding-bottom: 12px; }
    .cx-step::after { content: ''; position: absolute; inset-inline: 0; bottom: 0; height: 3px; border-radius: 3px; background: var(--p-border); transition: background .2s; }
    .cx-step.active { color: var(--p-primary); }
    .cx-step.active::after { background: var(--p-primary); }
    .cx-step.done { color: var(--p-success); }
    .cx-step.done::after { background: var(--p-success); }
    .cx-step__n { display: inline-grid; place-items: center; width: 22px; height: 22px; border-radius: 999px; background: var(--p-border); color: #fff; font-size: .74rem; margin-inline-end: 5px; }
    .cx-step.active .cx-step__n { background: var(--p-primary); }
    .cx-step.done .cx-step__n { background: var(--p-success); }

    .cx-pane { padding: 18px 24px; display: none; }
    .cx-pane.active { display: block; animation: cxfade .2s; }
    @keyframes cxfade { from { opacity: 0; transform: translateX(8px); } to { opacity: 1; transform: none; } }
    .cx-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .cx-fields .full { grid-column: 1 / -1; }
    .cx-f label { display: block; font-size: .8rem; font-weight: 700; margin-bottom: 6px; }
    .cx-f label .req { color: var(--p-danger); }
    .cx-f input { width: 100%; padding: 10px 12px; border: 1.5px solid var(--p-border); border-radius: 10px; font-family: inherit; background: var(--p-bg, #fff); color: var(--p-text); }
    .cx-f input:focus { outline: none; border-color: var(--p-accent); box-shadow: 0 0 0 3px rgba(49,40,115,.12); }
    .cx-f input[dir=ltr] { direction: ltr; text-align: start; }
    .cx-err { color: var(--p-danger); font-size: .74rem; margin-top: 4px; display: none; }
    .cx-f.has-err input { border-color: var(--p-danger); }
    .cx-f.has-err .cx-err { display: block; }
    .cx-switch { display: flex; align-items: center; gap: 10px; font-size: .86rem; font-weight: 700; }

    .cx-probe { margin-top: 16px; border-radius: 12px; padding: 12px 14px; font-size: .82rem; font-weight: 700; display: none; align-items: center; gap: 10px; }
    .cx-probe.show { display: flex; }
    .cx-probe.testing { background: rgba(49,40,115,.08); color: var(--p-primary); }
    .cx-probe.ok { background: rgba(26,127,55,.12); color: var(--p-success); }
    .cx-probe.fail { background: rgba(220,53,69,.1); color: var(--p-danger); }
    .cx-probe small { font-weight: 500; opacity: .85; direction: ltr; }
    .cx-spin { width: 16px; height: 16px; border: 2px solid currentColor; border-inline-start-color: transparent; border-radius: 50%; animation: cxspin .6s linear infinite; }
    @keyframes cxspin { to { transform: rotate(360deg); } }

    .cx-modal__foot { padding: 16px 24px; border-top: 1px solid var(--p-border); display: flex; gap: 10px; align-items: center; }
    .cx-ghost { background: none; border: 1.5px solid var(--p-border); border-radius: 10px; padding: 10px 18px; font-family: inherit; font-weight: 700; cursor: pointer; color: var(--p-text); }
    .cx-next { margin-inline-start: auto; }

    .cx-toast { position: fixed; inset-block-end: 22px; inset-inline-end: 22px; z-index: 1100; background: #1a7f37; color: #fff; padding: 13px 20px; border-radius: 12px; font-weight: 700; box-shadow: 0 14px 30px -12px rgba(0,0,0,.5); transform: translateY(20px); opacity: 0; transition: all .25s; }
    .cx-toast.show { transform: none; opacity: 1; }
    .cx-toast.err { background: #b02a37; }

    @media (max-width: 640px) { .cx-kpis { grid-template-columns: 1fr; } .cx-fields { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('العزل على مستوى الدول', 'Country-level isolation')"
        :subtitle="textByLanguage('كل دولة لها قاعدة بيانات مستقلة. هذا هو المكان الرئيسي لإدخال معلومات كل دولة وبيانات الاتصال بقاعدتها.', 'Each country has its own database. This is the central place to enter every country and its database credentials.')">
        <x-slot:actions>
            <button type="button" class="cx-fab" onclick="cxOpen()">
                <i class="bi bi-plus-lg"></i> {{ textByLanguage('دولة جديدة', 'New country') }}
            </button>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="cx-kpis">
        <div class="cx-kpi">
            <div class="cx-kpi__ic"><i class="bi bi-globe-americas"></i></div>
            <div><div class="cx-kpi__n">{{ count($countries) }}</div><div class="cx-kpi__l">{{ textByLanguage('إجمالي الدول', 'Total countries') }}</div></div>
        </div>
        <div class="cx-kpi">
            <div class="cx-kpi__ic ok"><i class="bi bi-check-circle"></i></div>
            <div><div class="cx-kpi__n">{{ $activeCount }}</div><div class="cx-kpi__l">{{ textByLanguage('دول مفعّلة', 'Active') }}</div></div>
        </div>
        <div class="cx-kpi">
            <div class="cx-kpi__ic db"><i class="bi bi-hdd-stack"></i></div>
            <div><div class="cx-kpi__n">{{ $dbCount }}</div><div class="cx-kpi__l">{{ textByLanguage('مرتبطة بقاعدة بيانات', 'With database') }}</div></div>
        </div>
    </div>

    @if(count($countries))
        <div class="cx-grid">
            @foreach($countries as $c)
                <div class="cx-card {{ $c['is_active'] ? '' : 'is-off' }} {{ (string) $activeShard === (string) $c['id'] ? 'is-current' : '' }}"
                     data-country='@json($c)'>
                    <div class="cx-head">
                        <span class="cx-flag">{{ $flag($c['country_code']) }}</span>
                        <div>
                            <div class="cx-name">{{ $c['name'] }}</div>
                            <div class="cx-sub">
                                <span dir="ltr">{{ $c['country_code'] ?: '—' }}</span>
                                @if($c['city'])<span>· {{ $c['city'] }}</span>@endif
                            </div>
                        </div>
                        <div class="cx-badges">
                            <span class="cx-tag {{ $c['is_active'] ? 'on' : 'off' }}">
                                <i class="bi {{ $c['is_active'] ? 'bi-broadcast' : 'bi-slash-circle' }}"></i>
                                {{ $c['is_active'] ? textByLanguage('مفعّلة', 'Active') : textByLanguage('معطّلة', 'Off') }}
                            </span>
                            @if($c['currency_code'])
                                <span class="cx-tag cur">{{ $c['currency_symbol'] ?: '' }} {{ $c['currency_code'] }}</span>
                            @endif
                            @if((string) $activeShard === (string) $c['id'])
                                <span class="cx-tag live"><i class="bi bi-cursor-fill"></i> {{ textByLanguage('نشطة الآن', 'Current') }}</span>
                            @endif
                        </div>
                    </div>

                    @if($c['has_db'])
                        <div class="cx-db">
                            <div class="cx-db__row"><span>{{ textByLanguage('المضيف', 'Host') }}</span><code>{{ $c['db_host'] }}:{{ $c['db_port'] ?: 3306 }}</code></div>
                            <div class="cx-db__row"><span>{{ textByLanguage('القاعدة', 'Database') }}</span><code>{{ $c['db_name'] }}</code></div>
                            <div class="cx-db__row"><span>{{ textByLanguage('المستخدم', 'User') }}</span><code>{{ $c['db_user'] }}</code></div>
                            @if($c['redis_host'])
                                <div class="cx-db__row"><span>Redis</span><code>{{ $c['redis_host'] }} / db{{ $c['redis_db'] ?? 0 }}</code></div>
                            @endif
                        </div>
                    @else
                        <div class="cx-db empty"><i class="bi bi-exclamation-triangle"></i> {{ textByLanguage('لم تُدخل بيانات قاعدة البيانات بعد', 'No database credentials yet') }}</div>
                    @endif

                    <div class="cx-actions">
                        <button type="button" class="cx-btn p" onclick="cxEdit(this)"><i class="bi bi-pencil"></i> {{ textByLanguage('تعديل', 'Edit') }}</button>
                        <button type="button" class="cx-btn" onclick="cxProbeCard(this)"><i class="bi bi-plug"></i> {{ textByLanguage('اختبار', 'Test') }}</button>
                        <button type="button" class="cx-btn" onclick="cxProvision(this)"><i class="bi bi-database-gear"></i> {{ textByLanguage('تجهيز', 'Provision') }}</button>
                        <button type="button" class="cx-btn warn" onclick="cxToggle(this)"><i class="bi bi-power"></i> {{ $c['is_active'] ? textByLanguage('تعطيل', 'Disable') : textByLanguage('تفعيل', 'Enable') }}</button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-card" style="text-align:center; padding:48px 20px;">
            <div style="font-size:3rem;">🌍</div>
            <h3 style="margin:12px 0 6px;">{{ textByLanguage('لا توجد دول مُعرّفة بعد', 'No countries defined yet') }}</h3>
            <p style="color:var(--p-text-muted); margin-bottom:18px;">{{ textByLanguage('أنشئ أول دولة وأدخل معلومات قاعدة بياناتها لتفعيل العزل.', 'Create your first country and enter its database credentials to enable isolation.') }}</p>
            <button type="button" class="cx-fab" onclick="cxOpen()"><i class="bi bi-plus-lg"></i> {{ textByLanguage('دولة جديدة', 'New country') }}</button>
        </div>
    @endif

    {{-- Wizard modal --}}
    <div class="cx-modal" id="cxModal" aria-hidden="true">
        <div class="cx-modal__bg" onclick="cxClose()"></div>
        <div class="cx-modal__box" role="dialog" aria-modal="true">
            <div class="cx-modal__head">
                <i class="bi bi-hdd-network" style="font-size:1.5rem;color:var(--p-primary);"></i>
                <h3 id="cxTitle">{{ textByLanguage('دولة جديدة', 'New country') }}</h3>
                <button type="button" class="cx-modal__x" onclick="cxClose()">&times;</button>
            </div>

            <div class="cx-steps">
                <div class="cx-step active" data-step="1"><span class="cx-step__n">1</span>{{ textByLanguage('الهوية', 'Identity') }}</div>
                <div class="cx-step" data-step="2"><span class="cx-step__n">2</span>{{ textByLanguage('قاعدة البيانات', 'Database') }}</div>
                <div class="cx-step" data-step="3"><span class="cx-step__n">3</span>{{ textByLanguage('Redis والتفعيل', 'Redis & activation') }}</div>
            </div>

            <form id="cxForm" onsubmit="return false;">
                {{-- Step 1 --}}
                <div class="cx-pane active" data-pane="1">
                    <div class="cx-fields">
                        <div class="cx-f full">
                            <label>{{ textByLanguage('اسم الدولة', 'Country name') }}<span class="req">*</span></label>
                            <input name="name" placeholder="{{ textByLanguage('قطر', 'Qatar') }}">
                            <div class="cx-err" data-err="name"></div>
                        </div>
                        <div class="cx-f">
                            <label>{{ textByLanguage('رمز الدولة (ISO2)', 'Country code (ISO2)') }}<span class="req">*</span></label>
                            <input name="country_code" dir="ltr" maxlength="2" placeholder="QA" style="text-transform:uppercase;">
                            <div class="cx-err" data-err="country_code"></div>
                        </div>
                        <div class="cx-f">
                            <label>{{ textByLanguage('المدينة', 'City') }}</label>
                            <input name="city" placeholder="{{ textByLanguage('الدوحة', 'Doha') }}">
                            <div class="cx-err" data-err="city"></div>
                        </div>
                        <div class="cx-f">
                            <label>{{ textByLanguage('خط العرض', 'Latitude') }}</label>
                            <input name="lat" dir="ltr" placeholder="25.2854">
                            <div class="cx-err" data-err="lat"></div>
                        </div>
                        <div class="cx-f">
                            <label>{{ textByLanguage('خط الطول', 'Longitude') }}</label>
                            <input name="lng" dir="ltr" placeholder="51.5310">
                            <div class="cx-err" data-err="lng"></div>
                        </div>
                        <div class="cx-f">
                            <label>{{ textByLanguage('رمز العملة', 'Currency code') }}</label>
                            <input name="currency_code" dir="ltr" maxlength="3" placeholder="QAR" style="text-transform:uppercase;">
                            <div class="cx-err" data-err="currency_code"></div>
                        </div>
                        <div class="cx-f">
                            <label>{{ textByLanguage('علامة العملة', 'Currency symbol') }}</label>
                            <input name="currency_symbol" placeholder="ر.ق">
                            <div class="cx-err" data-err="currency_symbol"></div>
                        </div>
                    </div>
                </div>

                {{-- Step 2 --}}
                <div class="cx-pane" data-pane="2">
                    <div class="cx-fields">
                        <div class="cx-f">
                            <label>{{ textByLanguage('مضيف القاعدة', 'DB host') }}<span class="req">*</span></label>
                            <input name="db_host" dir="ltr" placeholder="127.0.0.1">
                            <div class="cx-err" data-err="db_host"></div>
                        </div>
                        <div class="cx-f">
                            <label>{{ textByLanguage('المنفذ', 'Port') }}<span class="req">*</span></label>
                            <input name="db_port" dir="ltr" value="3306" placeholder="3306">
                            <div class="cx-err" data-err="db_port"></div>
                        </div>
                        <div class="cx-f full">
                            <label>{{ textByLanguage('اسم القاعدة', 'Database name') }}<span class="req">*</span></label>
                            <input name="db_name" dir="ltr" placeholder="fleet_qa">
                            <div class="cx-err" data-err="db_name"></div>
                        </div>
                        <div class="cx-f">
                            <label>{{ textByLanguage('المستخدم', 'Username') }}<span class="req">*</span></label>
                            <input name="db_user" dir="ltr" placeholder="root">
                            <div class="cx-err" data-err="db_user"></div>
                        </div>
                        <div class="cx-f">
                            <label>{{ textByLanguage('كلمة المرور', 'Password') }}</label>
                            <input name="db_pass" type="password" dir="ltr" placeholder="••••••••">
                            <div class="cx-err" data-err="db_pass"></div>
                        </div>
                    </div>

                    <div style="margin-top:16px;">
                        <button type="button" class="cx-btn p" style="flex:none;" onclick="cxProbeForm()"><i class="bi bi-plug"></i> {{ textByLanguage('اختبار الاتصال المباشر', 'Test live connection') }}</button>
                    </div>
                    <div class="cx-probe" id="cxProbe"></div>
                </div>

                {{-- Step 3 --}}
                <div class="cx-pane" data-pane="3">
                    <div class="cx-fields">
                        <div class="cx-f">
                            <label>{{ textByLanguage('مضيف Redis', 'Redis host') }}</label>
                            <input name="redis_host" dir="ltr" placeholder="127.0.0.1">
                            <div class="cx-err" data-err="redis_host"></div>
                        </div>
                        <div class="cx-f">
                            <label>{{ textByLanguage('قاعدة Redis', 'Redis DB') }}</label>
                            <input name="redis_db" dir="ltr" placeholder="0">
                            <div class="cx-err" data-err="redis_db"></div>
                        </div>
                        <div class="cx-f full">
                            <label>{{ textByLanguage('بادئة Redis', 'Redis prefix') }}</label>
                            <input name="redis_prefix" dir="ltr" placeholder="fleet_qa:">
                            <div class="cx-err" data-err="redis_prefix"></div>
                        </div>
                        <div class="cx-f full">
                            <label class="cx-switch">
                                <input type="checkbox" name="is_active" value="1" checked style="width:auto;">
                                {{ textByLanguage('تفعيل الدولة فور الإنشاء', 'Activate country immediately') }}
                            </label>
                        </div>
                    </div>
                    <p class="p-plan-note" style="margin-top:14px;">
                        <i class="bi bi-info-circle"></i>
                        {{ textByLanguage('بعد الحفظ، استخدم زر «تجهيز» لتشغيل ترحيلات المخطط على قاعدة هذه الدولة.', 'After saving, use the “Provision” button to run the schema migrations on this country’s database.') }}
                    </p>
                </div>
            </form>

            <div class="cx-modal__foot">
                <button type="button" class="cx-ghost" id="cxBack" onclick="cxStep(-1)" style="display:none;"><i class="bi bi-arrow-right"></i> {{ textByLanguage('السابق', 'Back') }}</button>
                <button type="button" class="cx-fab cx-next" id="cxNext" onclick="cxStep(1)">{{ textByLanguage('التالي', 'Next') }} <i class="bi bi-arrow-left"></i></button>
                <button type="button" class="cx-fab cx-next" id="cxSave" onclick="cxSave()" style="display:none;"><i class="bi bi-check-lg"></i> {{ textByLanguage('حفظ الدولة', 'Save country') }}</button>
            </div>
        </div>
    </div>

    <div class="cx-toast" id="cxToast"></div>

@endsection

@push('scripts')
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const T = {
        testWorking: @json(textByLanguage('جارٍ اختبار الاتصال…', 'Testing connection…')),
        provisioning: @json(textByLanguage('جارٍ تجهيز المخطط… قد يستغرق دقائق', 'Provisioning schema… this may take minutes')),
        confirmToggle: @json(textByLanguage('هل أنت متأكد؟', 'Are you sure?')),
        newTitle: @json(textByLanguage('دولة جديدة', 'New country')),
        editTitle: @json(textByLanguage('تعديل الدولة', 'Edit country')),
    };
    const FIELDS = ['name','country_code','city','lat','lng','currency_code','currency_symbol','db_host','db_port','db_name','db_user','db_pass','redis_host','redis_db','redis_prefix'];

    let step = 1, editId = null;
    const modal = document.getElementById('cxModal');
    const form = document.getElementById('cxForm');

    function q(sel, root) { return (root || document).querySelector(sel); }
    function qa(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }

    async function api(url, method, body) {
        const res = await fetch(url, {
            method,
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: body ? JSON.stringify(body) : undefined,
        });
        let json = {};
        try { json = await res.json(); } catch (e) {}
        return { status: res.status, json };
    }

    function toast(msg, err) {
        const el = document.getElementById('cxToast');
        el.textContent = msg;
        el.classList.toggle('err', !!err);
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), 3200);
    }

    function clearErrors() {
        qa('.cx-f', form).forEach(f => f.classList.remove('has-err'));
    }
    function showErrors(errors) {
        clearErrors();
        let firstStep = null;
        Object.keys(errors || {}).forEach(key => {
            const box = q('[data-err="' + key + '"]', form);
            if (!box) return;
            box.textContent = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
            const field = box.closest('.cx-f');
            field.classList.add('has-err');
            const pane = field.closest('.cx-pane');
            if (firstStep === null && pane) firstStep = parseInt(pane.dataset.pane, 10);
        });
        if (firstStep) goStep(firstStep);
    }

    function collect() {
        const data = {};
        FIELDS.forEach(f => { const el = form.elements[f]; if (el) data[f] = el.value; });
        data.is_active = form.elements['is_active'].checked ? 1 : 0;
        return data;
    }

    function goStep(n) {
        step = n;
        qa('.cx-pane', form).forEach(p => p.classList.toggle('active', parseInt(p.dataset.pane, 10) === n));
        qa('.cx-step').forEach(s => {
            const sn = parseInt(s.dataset.step, 10);
            s.classList.toggle('active', sn === n);
            s.classList.toggle('done', sn < n);
        });
        q('#cxBack').style.display = n > 1 ? 'inline-flex' : 'none';
        q('#cxNext').style.display = n < 3 ? 'inline-flex' : 'none';
        q('#cxSave').style.display = n === 3 ? 'inline-flex' : 'none';
    }

    window.cxStep = function (dir) { goStep(Math.min(3, Math.max(1, step + dir))); };

    window.cxOpen = function (data) {
        clearErrors();
        form.reset();
        editId = null;
        q('#cxTitle').textContent = T.newTitle;
        q('#cxProbe').className = 'cx-probe';
        if (data) {
            editId = data.id;
            q('#cxTitle').textContent = T.editTitle + ' — ' + data.name;
            FIELDS.forEach(f => { if (form.elements[f] && data[f] != null) form.elements[f].value = data[f]; });
            form.elements['db_pass'].value = '';
            form.elements['is_active'].checked = !!data.is_active;
        }
        goStep(1);
        modal.classList.add('open');
    };
    window.cxClose = function () { modal.classList.remove('open'); };

    window.cxEdit = function (btn) {
        const data = JSON.parse(btn.closest('.cx-card').dataset.country);
        cxOpen(data);
    };

    async function probe(payload, target) {
        target.className = 'cx-probe show testing';
        target.innerHTML = '<span class="cx-spin"></span> ' + T.testWorking;
        const { json } = await api('{{ route('panel.admin.countries.test') }}', 'POST', payload);
        if (json.ok) {
            target.className = 'cx-probe show ok';
            target.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + json.message +
                (json.data ? ' <small>· ' + (json.data.server || '') + ' · ' + json.data.tables + ' tables</small>' : '');
        } else {
            target.className = 'cx-probe show fail';
            target.innerHTML = '<i class="bi bi-x-circle-fill"></i> ' + json.message +
                (json.error ? ' <small>· ' + escapeHtml(json.error).slice(0, 120) + '</small>' : '');
        }
    }
    function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

    window.cxProbeForm = function () {
        const d = collect();
        probe({ db_host: d.db_host, db_port: d.db_port, db_name: d.db_name, db_user: d.db_user, db_pass: d.db_pass, node: editId }, q('#cxProbe'));
    };

    window.cxProbeCard = async function (btn) {
        const d = JSON.parse(btn.closest('.cx-card').dataset.country);
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="cx-spin"></span>';
        const { json } = await api('{{ route('panel.admin.countries.test') }}', 'POST',
            { db_host: d.db_host, db_port: d.db_port, db_name: d.db_name, db_user: d.db_user, node: d.id });
        btn.disabled = false;
        btn.innerHTML = original;
        toast((json.ok ? '✅ ' : '❌ ') + (json.message || '') + (json.data && json.data.server ? ' · ' + json.data.server : ''), !json.ok);
    };

    window.cxSave = async function () {
        const data = collect();
        const url = editId ? '{{ url('panel/admin/countries') }}/' + editId : '{{ route('panel.admin.countries.store') }}';
        const method = editId ? 'PUT' : 'POST';
        const { status, json } = await api(url, method, data);
        if (status === 200 || status === 201) {
            toast(json.message || 'OK');
            cxClose();
            setTimeout(() => location.reload(), 700);
        } else if (status === 422) {
            showErrors(json.data || json.errors);
        } else {
            toast(json.message || 'Error', true);
        }
    };

    window.cxToggle = async function (btn) {
        if (!confirm(T.confirmToggle)) return;
        const d = JSON.parse(btn.closest('.cx-card').dataset.country);
        const { json } = await api('{{ url('panel/admin/countries') }}/' + d.id + '/toggle', 'POST');
        toast(json.message || 'OK');
        setTimeout(() => location.reload(), 600);
    };

    window.cxProvision = async function (btn) {
        const d = JSON.parse(btn.closest('.cx-card').dataset.country);
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="cx-spin"></span>';
        toast(T.provisioning);
        const { json } = await api('{{ url('panel/admin/countries') }}/' + d.id + '/provision', 'POST');
        btn.disabled = false;
        btn.innerHTML = original;
        toast((json.ok ? '✅ ' : '❌ ') + (json.message || ''), !json.ok);
    };

    document.addEventListener('keydown', e => { if (e.key === 'Escape') cxClose(); });
})();
</script>
@endpush
