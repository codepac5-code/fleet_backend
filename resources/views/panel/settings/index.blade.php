@extends('panel.layouts.master')

@section('title', textByLanguage('الإعدادات', 'Settings'))
@section('page-title', textByLanguage('الإعدادات', 'Settings'))

@php
    use Illuminate\Support\Facades\Route as R;
    $has = fn ($n) => R::has('panel.admin.' . $n);

    // Inline tabs — content rendered in-place, switched client-side.
    // To add a tab: add an entry here + a matching <div class="st-panel" data-panel="KEY"> below.
    $tabs = [
        ['key' => 'commissions', 'icon' => 'bi-percent',  'label' => textByLanguage('العمولات', 'Commissions')],
        ['key' => 'system',      'icon' => 'bi-sliders',   'label' => textByLanguage('النظام', 'System')],
    ];

    // Management links — navigate to their own screens (grouped).
    $links = [
        ['group' => textByLanguage('الكتالوج والتسعير', 'Catalog & pricing'), 'items' => [
            ['route' => 'plans.index',         'icon' => 'bi-collection', 'label' => textByLanguage('خطط الاشتراك', 'Subscription plans'), 'count' => null],
            ['route' => 'subscriptions.index', 'icon' => 'bi-list-check',  'label' => textByLanguage('اشتراكات المكاتب', 'Office subscriptions'), 'count' => null],
            ['route' => 'currencies.index',    'icon' => 'bi-coin',       'label' => textByLanguage('العملات', 'Currencies'),           'count' => $counts['currencies']],
            ['route' => 'regions.billing',     'icon' => 'bi-diagram-3',  'label' => textByLanguage('وضع فوترة الدول', 'Region billing'), 'count' => null],
            ['route' => 'settings.payments',   'icon' => 'bi-credit-card','label' => textByLanguage('إعدادات الدفع (Stripe)', 'Payment settings (Stripe)'), 'count' => null],
            ['route' => 'settings.whatsapp',   'icon' => 'bi-whatsapp',   'label' => textByLanguage('واتساب / إرسال OTP', 'WhatsApp / OTP'), 'count' => null],
            ['route' => 'coupons.index',       'icon' => 'bi-ticket-perforated', 'label' => textByLanguage('الكوبونات', 'Coupons'), 'count' => null],
            ['route' => 'vehicle-brands.index','icon' => 'bi-car-front',   'label' => textByLanguage('ماركات المركبات', 'Vehicle brands'), 'count' => null],
            ['route' => 'cancellation-reasons.index','icon' => 'bi-x-circle', 'label' => textByLanguage('أسباب الإلغاء', 'Cancellation reasons'), 'count' => null],
            ['route' => 'rating-tags.index',   'icon' => 'bi-tags',       'label' => textByLanguage('وسوم التقييم', 'Rating tags'), 'count' => null],
        ]],
        ['group' => textByLanguage('الدول والبنية', 'Countries & infra'), 'items' => [
            ['route' => 'countries.index',     'icon' => 'bi-hdd-network','label' => textByLanguage('الدول وقواعد البيانات', 'Countries & databases'), 'count' => $counts['countries']],
            ['route' => 'cities.index',        'icon' => 'bi-geo-alt',    'label' => textByLanguage('المدن', 'Cities'), 'count' => null],
        ]],
        ['group' => textByLanguage('المحتوى والموقع', 'Content & site'), 'items' => [
            ['route' => 'settings.site',       'icon' => 'bi-window',     'label' => textByLanguage('الموقع والمحتوى', 'Site & content'), 'count' => null],
            ['route' => 'legal.index',         'icon' => 'bi-file-earmark-text', 'label' => textByLanguage('الشروط والخصوصية', 'Terms & privacy'), 'count' => null],
            ['route' => 'faqs.index',          'icon' => 'bi-question-circle', 'label' => textByLanguage('الأسئلة الشائعة', 'FAQs'),     'count' => $counts['faqs']],
            ['route' => 'document.index',      'icon' => 'bi-file-earmark-text', 'label' => textByLanguage('أنواع المستندات', 'Document types'), 'count' => $counts['documents']],
        ]],
        ['group' => textByLanguage('الطلبات الواردة', 'Inbound'), 'items' => [
            ['route' => 'leads.hub',           'icon' => 'bi-inboxes',    'label' => textByLanguage('طلبات الموقع', 'Website leads'), 'count' => null],
        ]],
        ['group' => textByLanguage('الرقابة', 'Oversight'), 'items' => [
            ['route' => 'audit-log.index',     'icon' => 'bi-clipboard-check', 'label' => textByLanguage('سجل التدقيق', 'Audit log'), 'count' => null],
            ['route' => 'notification-templates.index', 'icon' => 'bi-chat-square-text', 'label' => textByLanguage('قوالب الإشعارات', 'Notification templates'), 'count' => null],
            ['route' => 'ops.index',           'icon' => 'bi-activity',   'label' => textByLanguage('صحة التشغيل', 'Ops health'), 'count' => null],
            ['route' => 'app-status.index',    'icon' => 'bi-phone',      'label' => textByLanguage('حالة التطبيق والإصدارات', 'App status & versions'), 'count' => null],
        ]],
    ];
@endphp

@push('styles')
<style>
    .st-shell { display: grid; grid-template-columns: 268px 1fr; gap: 18px; align-items: start; }
    .st-rail { background: var(--p-surface, #fff); border: 1px solid var(--p-border); border-radius: 16px; padding: 10px; position: sticky; top: 16px; }
    .st-rail__group { font-size: .7rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: var(--p-text-muted); padding: 14px 12px 6px; }
    .st-rail__item { display: flex; align-items: center; gap: 11px; width: 100%; text-align: start; border: none; background: none; color: var(--p-text); font-family: inherit; font-size: .9rem; font-weight: 600; padding: 10px 12px; border-radius: 11px; cursor: pointer; text-decoration: none; transition: background .14s, color .14s; position: relative; }
    .st-rail__item i.lead { font-size: 1.1rem; width: 22px; text-align: center; color: var(--p-text-muted); transition: color .14s; }
    .st-rail__item:hover { background: color-mix(in srgb, var(--p-primary) 8%, transparent); }
    .st-rail__item:hover i.lead { color: var(--p-primary); }
    .st-rail__item.is-active { background: var(--p-primary); color: #fff; box-shadow: 0 8px 18px -10px var(--p-primary); }
    .st-rail__item.is-active i.lead { color: #fff; }
    .st-rail__item .st-arrow { margin-inline-start: auto; font-size: .85rem; color: var(--p-text-muted); }
    [dir=rtl] .st-rail__item .st-arrow { transform: scaleX(-1); }
    .st-rail__count { margin-inline-start: auto; font-size: .74rem; font-weight: 800; padding: 1px 8px; border-radius: 999px; background: color-mix(in srgb, var(--p-primary) 12%, transparent); color: var(--p-primary); }
    .st-rail__item.is-active .st-rail__count { background: rgba(255,255,255,.25); color: #fff; }

    .st-content { min-width: 0; }
    .st-panel { display: none; animation: stfade .22s ease; }
    .st-panel.is-active { display: block; }
    @keyframes stfade { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    .st-panel__head { display: flex; align-items: center; gap: 12px; margin-bottom: 6px; }
    .st-panel__head .st-ic { width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; font-size: 1.2rem; background: color-mix(in srgb, var(--p-primary) 12%, transparent); color: var(--p-primary); }
    .st-panel__head h3 { font-size: 1.15rem; font-weight: 800; }
    .st-panel__head p { font-size: .82rem; color: var(--p-text-muted); margin-top: 2px; }

    .st-embed-head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
    .st-embed-head h3 { font-size: 1.15rem; font-weight: 800; }
    .st-embed-framewrap { position: relative; border: 1px solid var(--p-border); border-radius: 16px; overflow: hidden; background: var(--p-bg, #f6f7fb); min-height: 420px; }
    .st-embed-framewrap iframe { display: block; width: 100%; min-height: 420px; border: 0; background: transparent; }
    .st-embed-loading { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; gap: 10px; color: var(--p-text-muted); font-weight: 700; background: var(--p-surface, #fff); z-index: 2; transition: opacity .2s; }
    .st-embed-loading.is-hidden { opacity: 0; pointer-events: none; }
    .st-spin { width: 18px; height: 18px; border: 2px solid var(--p-primary); border-inline-start-color: transparent; border-radius: 50%; animation: stspin .6s linear infinite; }
    @keyframes stspin { to { transform: rotate(360deg); } }

    .st-railtoggle { display: none; }
    @media (max-width: 860px) {
        .st-shell { grid-template-columns: 1fr; }
        .st-rail { position: static; }
    }
</style>
@endpush

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('مركز الإعدادات', 'Settings hub')"
        :subtitle="textByLanguage('كل إعدادات المنصّة وشاشات الإدارة في مكان واحد.', 'Every platform setting and management screen in one place.')" />

    <div class="st-shell">

        {{-- Sub-sidebar --}}
        <aside class="st-rail">
            <div class="st-rail__group">{{ textByLanguage('الإعدادات', 'Settings') }}</div>
            @foreach($tabs as $tab)
                <button type="button" class="st-rail__item st-tab {{ $loop->first ? 'is-active' : '' }}" data-tab="{{ $tab['key'] }}">
                    <i class="bi {{ $tab['icon'] }} lead"></i>
                    <span>{{ $tab['label'] }}</span>
                </button>
            @endforeach

            @foreach($links as $section)
                @php $vis = collect($section['items'])->filter(fn ($i) => $has($i['route'])); @endphp
                @if($vis->count())
                    <div class="st-rail__group">{{ $section['group'] }}</div>
                    @foreach($vis as $item)
                        <button type="button" class="st-rail__item st-embed"
                                data-key="{{ $item['route'] }}"
                                data-url="{{ route('panel.admin.' . $item['route']) }}?embed=1"
                                data-title="{{ $item['label'] }}">
                            <i class="bi {{ $item['icon'] }} lead"></i>
                            <span>{{ $item['label'] }}</span>
                            @if(!is_null($item['count']))
                                <span class="st-rail__count">{{ $item['count'] }}</span>
                            @else
                                <i class="bi bi-chevron-right st-arrow"></i>
                            @endif
                        </button>
                    @endforeach
                @endif
            @endforeach
        </aside>

        {{-- Content --}}
        <section class="st-content">

            {{-- Embedded management screen --}}
            <div class="st-panel" data-panel="__embed">
                <div class="st-embed-head">
                    <span class="st-ic"><i class="bi bi-box-arrow-in-down-right" id="stEmbedIcon"></i></span>
                    <h3 id="stEmbedTitle"></h3>
                    <a href="#" id="stEmbedOpen" target="_blank" class="p-btn p-btn--ghost" style="margin-inline-start:auto;"><i class="bi bi-box-arrow-up-right"></i> {{ textByLanguage('فتح في صفحة', 'Open full page') }}</a>
                </div>
                <div class="st-embed-framewrap">
                    <div class="st-embed-loading" id="stEmbedLoading"><span class="st-spin"></span> {{ textByLanguage('جارٍ التحميل…', 'Loading…') }}</div>
                    <iframe id="stEmbedFrame" title="settings-embed" src="about:blank" loading="lazy"></iframe>
                </div>
            </div>


            {{-- Commissions --}}
            <div class="st-panel is-active" data-panel="commissions">
                <div class="p-card p-card--accent">
                    <div class="st-panel__head">
                        <span class="st-ic"><i class="bi bi-percent"></i></span>
                        <div>
                            <h3>{{ textByLanguage('العمولات الافتراضية', 'Default commissions') }}</h3>
                            <p>{{ textByLanguage('تُطبَّق على البلد النشِط وتُستخدم كافتراضات للمكاتب والسائقين الجدد.', 'Applied to the active country; defaults for new offices and drivers.') }}</p>
                        </div>
                        @if($countryName)<x-panel.badge tone="primary" style="margin-inline-start:auto;"><i class="bi bi-geo-alt"></i> {{ $countryName }}</x-panel.badge>@endif
                    </div>
                    <form method="POST" action="{{ route('panel.admin.settings.commissions.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="p-form-grid">
                            <div class="p-field">
                                <label for="fcd">{{ textByLanguage('عمولة المنصّة (سيارة سائق)', 'Platform commission (driver car)') }}</label>
                                <div class="p-pct"><input type="number" step="0.01" min="0" max="100" id="fcd" name="fleet_commission_value_with_driver" value="{{ old('fleet_commission_value_with_driver', $commissions['fleet_commission_value_with_driver']) }}" required><span>%</span></div>
                                @error('fleet_commission_value_with_driver')<small class="p-field__error">{{ $message }}</small>@enderror
                            </div>
                            <div class="p-field">
                                <label for="fco">{{ textByLanguage('عمولة المنصّة (سيارة مكتب)', 'Platform commission (office car)') }}</label>
                                <div class="p-pct"><input type="number" step="0.01" min="0" max="100" id="fco" name="fleet_commission_value_with_office" value="{{ old('fleet_commission_value_with_office', $commissions['fleet_commission_value_with_office']) }}" required><span>%</span></div>
                                @error('fleet_commission_value_with_office')<small class="p-field__error">{{ $message }}</small>@enderror
                            </div>
                            <div class="p-field">
                                <label for="ocv">{{ textByLanguage('عمولة المكتب الافتراضية', 'Default office commission') }}</label>
                                <div class="p-pct"><input type="number" step="0.01" min="0" max="100" id="ocv" name="office_commission_value" value="{{ old('office_commission_value', $commissions['office_commission_value']) }}" required><span>%</span></div>
                                @error('office_commission_value')<small class="p-field__error">{{ $message }}</small>@enderror
                            </div>
                            <div class="p-field">
                                <label for="dcv">{{ textByLanguage('عمولة السائق الافتراضية', 'Default driver commission') }}</label>
                                <div class="p-pct"><input type="number" step="0.01" min="0" max="100" id="dcv" name="driver_commission_value" value="{{ old('driver_commission_value', $commissions['driver_commission_value']) }}" required><span>%</span></div>
                                @error('driver_commission_value')<small class="p-field__error">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="p-form-actions" style="margin-top:16px;">
                            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ textByLanguage('حفظ العمولات', 'Save commissions') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- System --}}
            <div class="st-panel" data-panel="system">
                <div class="p-card p-card--accent">
                    <div class="st-panel__head">
                        <span class="st-ic"><i class="bi bi-sliders"></i></span>
                        <div>
                            <h3>{{ textByLanguage('إعدادات النظام', 'System settings') }}</h3>
                            <p>{{ textByLanguage('إعدادات عامة على مستوى المنصّة (افتراضات).', 'Platform-wide default settings.') }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('panel.admin.settings.system.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="p-form-grid">
                            <x-panel.field name="language" type="select" :label="textByLanguage('اللغة الافتراضية', 'Default language')" :value="$system['language'] ?: 'ar'"
                                :options="['ar' => 'العربية', 'en' => 'English']" required />
                            <x-panel.field name="currency" type="select" :label="textByLanguage('العملة الافتراضية', 'Default currency')" :value="$system['currency']"
                                :options="$currencies" required />
                            <x-panel.field name="timezone" :label="textByLanguage('المنطقة الزمنية', 'Timezone')" :value="$system['timezone'] ?: 'UTC'" placeholder="Asia/Riyadh" required full />
                        </div>
                        <div class="p-form-actions" style="margin-top:16px;">
                            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ textByLanguage('حفظ الإعدادات', 'Save settings') }}</button>
                        </div>
                    </form>
                </div>
            </div>

        </section>
    </div>

@endsection

@push('scripts')
<script>
(function () {
    var tabs = document.querySelectorAll('.st-tab');
    var embeds = document.querySelectorAll('.st-embed');
    var panels = document.querySelectorAll('.st-panel');
    var frame = document.getElementById('stEmbedFrame');
    var loading = document.getElementById('stEmbedLoading');
    var titleEl = document.getElementById('stEmbedTitle');
    var iconEl = document.getElementById('stEmbedIcon');
    var openEl = document.getElementById('stEmbedOpen');
    var railItems = document.querySelectorAll('.st-rail__item');
    var loadedKey = null;

    function showPanel(panelKey) {
        panels.forEach(function (p) { p.classList.toggle('is-active', p.dataset.panel === panelKey); });
    }
    function markActive(el) {
        railItems.forEach(function (i) { i.classList.remove('is-active'); });
        if (el) el.classList.add('is-active');
    }

    function activateTab(key) {
        var t = document.querySelector('.st-tab[data-tab="' + (window.CSS && CSS.escape ? CSS.escape(key) : key) + '"]');
        if (!t) return false;
        markActive(t);
        showPanel(key);
        try { history.replaceState(null, '', '#' + key); } catch (e) {}
        return true;
    }

    function activateEmbed(btn) {
        var key = btn.dataset.key, url = btn.dataset.url;
        markActive(btn);
        titleEl.textContent = btn.dataset.title || '';
        var lead = btn.querySelector('i.lead');
        iconEl.className = lead ? lead.className.replace(' lead', '') : 'bi bi-window';
        try { var u = new URL(url, location.href); u.searchParams.delete('embed'); openEl.href = u.pathname + u.search; } catch (e) { openEl.href = url; }
        showPanel('__embed');
        if (loadedKey !== key) {
            loading.classList.remove('is-hidden');
            frame.style.height = '420px';
            frame.src = url;
            loadedKey = key;
        }
        try { history.replaceState(null, '', '#s:' + key); } catch (e) {}
    }

    tabs.forEach(function (t) { t.addEventListener('click', function () { activateTab(t.dataset.tab); }); });
    embeds.forEach(function (b) { b.addEventListener('click', function () { activateEmbed(b); }); });

    frame.addEventListener('load', function () {
        if (frame.src && frame.src !== 'about:blank') loading.classList.add('is-hidden');
    });
    window.addEventListener('message', function (e) {
        if (e.data && typeof e.data.__panelEmbedHeight === 'number') {
            frame.style.height = Math.max(420, e.data.__panelEmbedHeight + 4) + 'px';
        }
    });

    // Deep-link: #s:route → embed, #tab → inline tab
    var hash = (location.hash || '').replace('#', '');
    if (hash.indexOf('s:') === 0) {
        var target = document.querySelector('.st-embed[data-key="' + hash.slice(2) + '"]');
        if (target) activateEmbed(target);
    } else if (hash) {
        activateTab(hash);
    }
})();
</script>
@endpush
