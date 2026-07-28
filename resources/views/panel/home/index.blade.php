@extends('panel.layouts.master')

@section('title', __('messages.dashboard'))
@section('page-title', __('messages.dashboard'))

@push('styles')
<style>
    @keyframes pFlashVal { 0% { background: transparent; } 18% { background: color-mix(in srgb, var(--p-accent) 32%, transparent); } 100% { background: transparent; } }
    .p-flash-val { animation: pFlashVal 1.1s ease; border-radius: 7px; }
    .p-hero__stat, .p-kpi, .stat-tile { transition: transform .16s ease, box-shadow .16s ease; }
    .p-hero__stat:hover, .stat-tile:hover { transform: translateY(-3px); }
    .p-kpi { cursor: default; }
    .p-kpi:hover { transform: translateY(-3px); box-shadow: 0 12px 26px -16px rgba(0,0,0,.4); }
    .kpi-auto { display: inline-flex; align-items: center; gap: 6px; font-size: .74rem; font-weight: 800; color: var(--p-text-muted); cursor: pointer; user-select: none; padding: 4px 10px; border-radius: 999px; border: 1px solid var(--p-border); background: var(--p-surface, #fff); }
    .kpi-auto__dot { width: 8px; height: 8px; border-radius: 50%; background: #1a7f37; }
    .kpi-auto.is-on .kpi-auto__dot { animation: pPulse 1.6s infinite; }
    .kpi-auto.is-off { color: var(--p-text-muted); opacity: .7; }
    .kpi-auto.is-off .kpi-auto__dot { background: var(--p-text-muted); }
    @keyframes pPulse { 0% { box-shadow: 0 0 0 0 rgba(26,127,55,.5); } 70% { box-shadow: 0 0 0 7px rgba(26,127,55,0); } 100% { box-shadow: 0 0 0 0 rgba(26,127,55,0); } }
    .kpi-bar__refresh.is-spin i { animation: pSpin .8s linear infinite; }
    @keyframes pSpin { to { transform: rotate(360deg); } }
</style>
@endpush

@section('content')

    @php
        $hour = (int) now()->format('H');
        $greeting = $hour < 12
            ? textByLanguage('صباح الخير', 'Good morning')
            : ($hour < 18 ? textByLanguage('مساء الخير', 'Good afternoon') : textByLanguage('مساء الخير', 'Good evening'));
    @endphp
    <div class="p-hero p-hero--brand">
        <div class="p-hero__top">
            <div class="p-hero__text">
                <span class="p-hero__greeting">{{ $greeting }}@if(!empty($userName)), {{ $userName }}@endif</span>
                <h1 class="p-hero__title">{{ __('messages.dashboard') }}</h1>
                <p class="p-hero__date"><i class="bi bi-calendar3"></i> {{ now()->translatedFormat('l، j F Y') }}</p>
            </div>
            <div class="p-hero__meta">
                @if($countryName)
                    <span class="p-hero__chip"><i class="bi bi-geo-alt"></i> {{ $countryName }}</span>
                @endif
                <span class="p-hero__chip p-hero__chip--role">
                    <i class="bi {{ $isAdmin ? 'bi-shield-lock' : ($entity === 'office' ? 'bi-building' : 'bi-person-badge') }}"></i>
                    {{ $isAdmin ? textByLanguage('مدير النظام', 'Administrator') : ($entity === 'office' ? textByLanguage('مكتب', 'Office') : textByLanguage('موظف', 'Employee')) }}
                </span>
            </div>
        </div>
        @if(!empty($counters))
            <div class="p-hero__stats">
                @foreach($counters as $c)
                    <div class="p-hero__stat">
                        <span class="p-hero__stat-ic"><i class="bi {{ $c['icon'] }}"></i></span>
                        <div class="p-hero__stat-tx">
                            <b id="{{ $c['id'] }}">{{ number_format((int) $c['value']) }}</b>
                            <span>{{ $c['label'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if(!empty($liveKpis))
        <div class="kpi-wrap">
            <div class="kpi-bar">
                <span class="kpi-bar__title"><i class="bi bi-activity"></i> {{ textByLanguage('المؤشّرات الحيّة', 'Live indicators') }}</span>
                <div class="kpi-bar__right">
                    <span class="kpi-auto is-on" id="liveAuto" title="{{ textByLanguage('التحديث التلقائي', 'Auto refresh') }}">
                        <span class="kpi-auto__dot"></span>
                        <span id="liveAutoLbl">{{ textByLanguage('تلقائي', 'Auto') }}</span>
                    </span>
                    <span class="kpi-bar__time"><i class="bi bi-clock-history"></i> {{ textByLanguage('آخر تحديث', 'Updated') }}: <b id="liveTime">--:--:--</b></span>
                    <button type="button" class="kpi-bar__refresh" id="liveRefresh" title="{{ textByLanguage('تحديث', 'Refresh') }}"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
            </div>
            <div class="p-kpis">
                @foreach($liveKpis as $k)
                    <div class="p-kpi p-kpi--{{ $k['tone'] }}">
                        <span class="p-kpi__ic"><i class="bi {{ $k['icon'] }}"></i></span>
                        <div class="p-kpi__tx">
                            <b id="{{ $k['id'] }}">{{ $k['value'] }}</b>
                            <span class="p-kpi__lbl">{{ $k['label'] }}@if(!empty($k['live']))<i class="p-kpi__live" title="{{ textByLanguage('حيّ', 'live') }}"></i>@endif</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="p-card p-mapcard">
        <div class="p-mapcard__head">
            <div class="p-mapcard__title">
                <span class="p-mapcard__radar"><span></span><span></span><i class="bi bi-broadcast"></i></span>
                <div class="p-mapcard__titletx">
                    <h3>{{ textByLanguage('خريطة العمليّات الحيّة', 'Live operations map') }}</h3>
                    <p><i class="bi bi-geo-alt"></i> {{ textByLanguage('مراقبة وتتبّع مباشر للأسطول', 'Real-time fleet monitoring') }}</p>
                </div>
            </div>
            <span class="p-mapcard__rec"><span class="p-mapcard__recdot"></span> LIVE</span>
        </div>
        <div class="p-mapcard__frame">
            <div id="panelMap" class="p-mapcard__map"></div>
            <span class="p-mapcard__scan"></span>
            <span class="p-mapcard__corner p-mapcard__corner--tl"></span>
            <span class="p-mapcard__corner p-mapcard__corner--tr"></span>
            <span class="p-mapcard__corner p-mapcard__corner--bl"></span>
            <span class="p-mapcard__corner p-mapcard__corner--br"></span>
        </div>
        <div class="p-mapcard__foot">
            <span class="p-mapcard__foot-l"><i class="bi bi-broadcast-pin"></i> {{ textByLanguage('تتبّع حيّ لمواقع السائقين', 'Live driver tracking') }}</span>
            <span class="p-mapcard__foot-r">{{ textByLanguage('آخر تحديث', 'Updated') }}: <b id="mapLastUpdate">--</b></span>
        </div>
    </div>

    @php
        $statPeriods = [
            'today' => textByLanguage('اليوم', 'Today'),
            'week'  => textByLanguage('هذا الأسبوع', 'This week'),
            'month' => textByLanguage('هذا الشهر', 'This month'),
        ];
        $tileTones = ['indigo', 'gold', 'teal', 'violet'];
    @endphp
    <div class="p-card p-stats" style="margin-bottom: 18px;">
        <div class="stats-head">
            <h3 class="p-card__title" style="margin:0;"><i class="bi bi-graph-up-arrow"></i> {{ textByLanguage('الإحصاءات', 'Statistics') }}</h3>
            <div class="stats-filter">
                <div class="stats-presets" id="statsTabs">
                    @foreach($statPeriods as $key => $label)
                        <button type="button" class="stats-tab {{ $key === 'today' ? 'is-active' : '' }}" data-period="{{ $key }}">{{ $label }}</button>
                    @endforeach
                    <button type="button" class="stats-tab" data-period="custom">{{ textByLanguage('مخصّص', 'Custom') }}</button>
                </div>
                <div class="stats-range">
                    <input type="date" id="statFrom" aria-label="from">
                    <span class="stats-range__sep"><i class="bi bi-arrow-left-right"></i></span>
                    <input type="date" id="statTo" aria-label="to">
                    <button type="button" class="p-btn p-btn--primary p-btn--sm" id="statApply"><i class="bi bi-funnel"></i> {{ textByLanguage('تطبيق', 'Apply') }}</button>
                </div>
            </div>
        </div>

        @foreach($periodStats as $period => $metrics)
            <div class="stats-grid {{ $period === 'today' ? '' : 'is-hidden' }}" data-period-panel="{{ $period }}">
                @foreach($metrics as $i => $m)
                    <div class="stat-tile stat-tile--{{ $tileTones[$i % count($tileTones)] }}">
                        <span class="stat-tile__ic"><i class="bi {{ $m['icon'] }}"></i></span>
                        <div class="stat-tile__tx">
                            <b>{{ $m['money'] ? getPriceFormat($m['value']) : number_format((int) $m['value']) }}</b>
                            <span>{{ $m['label'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
        <div class="stats-grid is-hidden" data-period-panel="custom" id="statsCustom"></div>

        <div class="stats-status" id="statsStatus">
            <div class="stats-status__head"><i class="bi bi-pie-chart-fill"></i> {{ textByLanguage('توزّع حالات الطلبات', 'Order status breakdown') }}</div>
            <div class="dsplit" id="statsSplit"></div>
        </div>
    </div>

    @php $multi = count($currencyBalances ?? []) > 1; @endphp
    <div class="p-grid p-grid--fin" style="margin-bottom: 18px; align-items: stretch;">
        <div class="p-card fin-panel">
            @if(!empty($currencyBalances))
                <div class="fin-left">
                    <div class="fin-card {{ $walletRevealed ? '' : 'is-locked' }}">
                        <span class="fin-card__sheen"></span>
                        <div class="fin-card__main">
                            <div class="fin-card__top">
                                <span class="fin-card__brand">fleet<i>.</i></span>
                                @if($walletRevealed)
                                    <form method="POST" action="{{ route('panel.'.$entity.'.wallet.hide') }}" class="fin-card__hide">@csrf
                                        <button type="submit" class="fin-card__eye" title="{{ textByLanguage('إخفاء', 'Hide') }}"><i class="bi bi-eye-slash"></i></button>
                                    </form>
                                @else
                                    <span class="fin-card__tag"><i class="bi bi-stars"></i> {{ textByLanguage('محميّة', 'Secured') }}</span>
                                @endif
                            </div>
                            <div class="fin-card__chiprow">
                                <span class="fin-card__chip"></span>
                                <i class="bi bi-reception-4 fin-card__wifi"></i>
                            </div>
                            <div class="fin-card__balance">
                                <span class="fin-card__label">{{ textByLanguage('الرصيد المتاح', 'Available balance') }}</span>
                                @if($walletRevealed)
                                    <span class="fin-card__amt" id="walAmt">{{ $currencyBalances[0]['symbol'] }} {{ number_format($currencyBalances[0]['balance'], 2) }}</span>
                                @else
                                    <span class="fin-card__amt fin-card__amt--stars">★★★★★★</span>
                                @endif
                            </div>
                            <div class="fin-card__foot">
                                <div class="fin-card__holder">
                                    <small>{{ textByLanguage('صاحب الحساب', 'Account holder') }}</small>
                                    <span>{{ $userName ?: textByLanguage('الحساب', 'Account') }}</span>
                                </div>
                                @if($walletRevealed)
                                    <span class="fin-card__code" id="walCode">{{ $currencyBalances[0]['code'] }}</span>
                                @else
                                    <span class="fin-card__code">{{ count($currencyBalances) }} {{ $multi ? textByLanguage('عملات', 'currencies') : textByLanguage('عملة', 'currency') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="fin-card__aside">
                            <div class="fin-card__row">
                                <span class="fin-card__k"><i class="bi bi-taxi-front"></i> {{ __('messages.unpaid_driver_dues') }}</span>
                                <b>{{ getPriceFormat($wallet['driverDues']) }}</b>
                            </div>
                            <div class="fin-card__row">
                                <span class="fin-card__k"><i class="bi bi-building"></i> {{ __('messages.unpaid_office_dues') }}</span>
                                <b>{{ getPriceFormat($wallet['officeDues']) }}</b>
                            </div>
                            <div class="fin-card__row">
                                <span class="fin-card__k"><i class="bi bi-hourglass-split"></i> {{ __('messages.pending_amount') }}</span>
                                <b>{{ getPriceFormat($wallet['pending']) }}</b>
                            </div>
                        </div>
                    </div>

                    @if($walletRevealed && $multi)
                        <div class="fin-slider" id="walSwitch">
                            <button type="button" class="fin-slider__nav" data-dir="-1" aria-label="prev"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></button>
                            <div class="fin-slider__cur"><i class="bi bi-coin"></i> <b id="walSlCode">{{ $currencyBalances[0]['code'] }}</b></div>
                            <button type="button" class="fin-slider__nav" data-dir="1" aria-label="next"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></button>
                        </div>
                        <div class="fin-slider__dots" id="walDots">
                            @foreach($currencyBalances as $i => $b)<span class="{{ $i === 0 ? 'is-active' : '' }}"></span>@endforeach
                        </div>
                    @elseif(!$walletRevealed)
                        <button type="button" class="fin-card__view" onclick="document.getElementById('walletModal').classList.add('open')">
                            <span class="fin-card__view-in"><i class="bi bi-unlock-fill"></i> {{ textByLanguage('كشف الرصيد', 'Reveal balance') }}</span>
                        </button>
                    @endif
                </div>
            @endif

        </div>

        <div class="p-card rev-card">
            <div class="rev-head">
                <h3 class="p-card__title" style="margin:0;"><i class="bi bi-graph-up"></i> {{ __('messages.monthly_revenue') }}</h3>
                @if(!empty($currencyBalances))<span class="rev-cur" id="revCur">{{ $currencyBalances[0]['code'] }}</span>@endif
            </div>
            <canvas id="revenueChart" height="150"></canvas>
        </div>
    </div>

    <div class="p-grid p-grid--2" style="align-items: start;">
        <div class="p-card p-ranks p-ranks--podium">
            <div class="p-ranks__head">
                <h3 class="p-card__title" style="margin:0;"><i class="bi bi-trophy-fill"></i> {{ textByLanguage('لوحة المتصدّرين', 'Leaderboard') }}</h3>
                <div class="p-ranks__tabs" id="rankTabs">
                    @foreach($rankings as $key => $r)
                        <button type="button" class="p-ranks__tab {{ $loop->first ? 'is-active' : '' }}" data-rank="{{ $key }}"><i class="bi {{ $r['icon'] }}"></i> <span>{{ $r['label'] }}</span></button>
                    @endforeach
                </div>
            </div>
            @foreach($rankings as $key => $r)
                <div class="p-ranks__panel {{ $loop->first ? '' : 'is-hidden' }}" data-rank-panel="{{ $key }}">
                    @if(!empty($r['rows']))
                        @php
                            $rows = $r['rows']; $top = array_slice($rows, 0, 3); $rest = array_slice($rows, 3); $order = count($top) >= 3 ? [1, 0, 2] : array_keys($top);
                            $defAv = $key === 'offices' ? asset('panel/img/building.svg') : asset('panel/img/avatar.svg');
                        @endphp
                        <div class="podium">
                            @foreach($order as $pos)
                                @php $row = $top[$pos] ?? null; @endphp
                                @if($row)
                                    <div class="podium__item podium__item--{{ $pos + 1 }}">
                                        @if($pos === 0)<i class="bi bi-crown-fill podium__crown"></i>@endif
                                        <span class="podium__av"><img src="{{ !empty($row['photo']) ? asset('storage/'.$row['photo']) : $defAv }}" alt="" loading="lazy" onerror="this.src='{{ $defAv }}'"></span>
                                        <strong class="podium__name">{{ $row['name'] }}</strong>
                                        <b class="podium__metric">{{ $row['metric'] }}<small>{{ $row['unit'] }}</small></b>
                                        @isset($row['sub'])<span class="podium__sub">{{ $row['sub'] }}</span>@endisset
                                        <span class="podium__base">{{ $pos + 1 }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @if(!empty($rest))
                            <ol class="rank-list rank-list--rest">
                                @foreach($rest as $i => $row)
                                    <li class="rank-row">
                                        <span class="rank-row__pos">{{ $i + 4 }}</span>
                                        <span class="rank-row__av"><img src="{{ !empty($row['photo']) ? asset('storage/'.$row['photo']) : $defAv }}" alt="" loading="lazy" onerror="this.src='{{ $defAv }}'"></span>
                                        <strong class="rank-row__name">{{ $row['name'] }}</strong>
                                        <b class="rank-row__metric">{{ $row['metric'] }} <small>{{ $row['unit'] }}</small></b>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    @else
                        <p class="p-empty"><i class="bi bi-bar-chart"></i> {{ textByLanguage('لا توجد بيانات', 'No data') }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="p-card">
            <h3 class="p-card__title">{{ textByLanguage('أحدث الطلبات', 'Recent orders') }}</h3>
            @if(!empty($recentOrders))
                <x-panel.table :headers="['#', textByLanguage('العميل', 'Customer'), textByLanguage('السائق', 'Driver'), textByLanguage('المبلغ', 'Amount'), textByLanguage('الحالة', 'Status')]">
                    @foreach($recentOrders as $o)
                        <tr>
                            <td>#{{ $o['id'] }}</td>
                            <td>{{ $o['customer'] }}</td>
                            <td>{{ $o['driver'] }}</td>
                            <td>{{ getPriceFormat($o['amount']) }}</td>
                            <td><x-panel.badge :status="$o['status']" /></td>
                        </tr>
                    @endforeach
                </x-panel.table>
            @else
                <p class="p-empty"><i class="bi bi-inbox"></i> {{ textByLanguage('لا توجد طلبات بعد', 'No orders yet') }}</p>
            @endif
        </div>
    </div>

    @if(!empty($currencyBalances))
        <div class="wallet-modal {{ ($errors->has('wallet') || $errors->has('password')) ? 'open' : '' }}" id="walletModal">
            <div class="wm-box">
                <button type="button" class="wm-x" onclick="document.getElementById('walletModal').classList.remove('open')"><i class="bi bi-x-lg"></i></button>
                <div class="wm-lock" id="wmLock"><span class="wm-lock__ring"></span><i class="bi bi-lock-fill"></i></div>
                <h4 class="wm-title">{{ textByLanguage('عرض الرصيد', 'Reveal your balance') }}</h4>
                <p class="wm-sub">{{ textByLanguage('أدخل كلمة المرور للكشف عن رصيدك', 'Enter your password to unlock the balance') }}</p>
                <form method="POST" action="{{ route('panel.'.$entity.'.wallet.reveal') }}">
                    @csrf
                    <div class="wm-field">
                        <i class="bi bi-key-fill wm-field__ic"></i>
                        <input type="password" id="wmPw" name="password" placeholder="{{ textByLanguage('كلمة المرور', 'Password') }}" autocomplete="current-password" required autofocus
                            onfocus="document.getElementById('wmLock').classList.add('is-open')" onblur="if(!this.value)document.getElementById('wmLock').classList.remove('is-open')">
                        <button type="button" class="wm-field__toggle" onclick="(function(i){var p=document.getElementById('wmPw');var on=p.type==='password';p.type=on?'text':'password';i.className='bi '+(on?'bi-eye-slash':'bi-eye');})(this.querySelector('i'))"><i class="bi bi-eye"></i></button>
                    </div>
                    @if($errors->has('wallet') || $errors->has('password'))
                        <div class="wm-err"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('wallet') ?: $errors->first('password') }}</div>
                    @endif
                    <div class="wm-actions">
                        <button type="submit" class="wm-primary"><i class="bi bi-unlock-fill"></i> {{ textByLanguage('كشف الرصيد', 'Reveal balance') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
    // Interactive number animation + value-change flash (shared helpers)
    (function () {
        function ease(t) { return 1 - Math.pow(1 - t, 3); }
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Parse "prefix number suffix" preserving grouping/decimals.
        function parse(text) {
            var m = String(text == null ? '' : text).match(/^(\D*?)([\d.,]+)(.*)$/s);
            if (!m) return null;
            var raw = m[2];
            var decimals = (raw.split('.')[1] || '').length;
            var value = parseFloat(raw.replace(/,/g, ''));
            if (isNaN(value)) return null;
            return { pre: m[1], suf: m[3], value: value, decimals: decimals };
        }
        function fmt(v, decimals) {
            return v.toLocaleString(undefined, { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
        }

        window.panelCountUp = function (el, toText, opts) {
            if (!el) return;
            opts = opts || {};
            var target = parse(toText != null ? toText : el.textContent);
            if (!target) { if (toText != null) el.textContent = toText; return; }
            if (reduce) { el.textContent = target.pre + fmt(target.value, target.decimals) + target.suf; return; }
            var fromText = opts.from != null ? opts.from : el.textContent;
            var start = parse(fromText);
            var s = start ? start.value : 0;
            var e = target.value, dur = opts.duration || 850, t0 = null;
            function step(ts) {
                if (t0 === null) t0 = ts;
                var p = Math.min(1, (ts - t0) / dur), cur = s + (e - s) * ease(p);
                el.textContent = target.pre + fmt(cur, target.decimals) + target.suf;
                if (p < 1) requestAnimationFrame(step);
                else el.textContent = target.pre + fmt(e, target.decimals) + target.suf;
            }
            requestAnimationFrame(step);
        };

        window.panelFlash = function (el) {
            if (!el || reduce) return;
            el.classList.remove('p-flash-val'); void el.offsetWidth; el.classList.add('p-flash-val');
        };

        // Animate all dashboard numbers once on load.
        function initCountUp() {
            document.querySelectorAll('.p-hero__stat-tx b, .p-kpi__tx b, .stat-tile__tx b').forEach(function (el) {
                var final = el.textContent;
                window.panelCountUp(el, final, { from: '0' });
            });
        }
        if (document.readyState !== 'loading') initCountUp();
        else document.addEventListener('DOMContentLoaded', initCountUp);
    })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            var ctx = document.getElementById('revenueChart');
            if (!ctx) return;
            var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: @json(__('messages.monthly_revenue')),
                        data: @json($monthlyRevenue),
                        borderColor: '#F8A609',
                        backgroundColor: 'rgba(248,166,9,.15)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3,
                        pointRadius: 3,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        })();
    </script>
    <script>
        (function () {
            var sw = document.getElementById('walSwitch');
            if (!sw) return;
            @php
                $walletCurr = [];
                if ($walletRevealed && !empty($currencyBalances)) {
                    foreach ($currencyBalances as $b) {
                        $walletCurr[] = ['amt' => $b['symbol'] . ' ' . number_format($b['balance'], 2), 'code' => $b['code']];
                    }
                }
            @endphp
            var CURR = @json($walletCurr);
            if (CURR.length < 2) return;
            var amt = document.getElementById('walAmt'), code = document.getElementById('walCode'),
                slCode = document.getElementById('walSlCode'), revCur = document.getElementById('revCur'),
                dots = document.querySelectorAll('#walDots span');
            var idx = 0;
            function go(dir) {
                idx = (idx + dir + CURR.length) % CURR.length;
                var c = CURR[idx];
                if (slCode) slCode.textContent = c.code;
                if (revCur) revCur.textContent = c.code;
                dots.forEach(function (d, i) { d.classList.toggle('is-active', i === idx); });
                if (!amt) return;
                amt.classList.add('is-flip');
                setTimeout(function () {
                    amt.textContent = c.amt;
                    if (code) code.textContent = c.code;
                    amt.classList.remove('is-flip');
                }, 200);
            }
            sw.querySelectorAll('.fin-slider__nav').forEach(function (b) {
                b.addEventListener('click', function () { go(parseInt(b.getAttribute('data-dir'), 10)); });
            });
        })();
    </script>

    <script>
        (function () {
            var box = document.getElementById('statsSplit');
            if (!box) return;
            var PSTATUS = @json($periodStatus ?? []);
            window.__PSTATUS = PSTATUS;
            window.__renderSplit = function (parts) {
                parts = parts || [];
                var total = parts.reduce(function (a, p) { return a + (p.value || 0); }, 0) || 1;
                if (!parts.length) { box.innerHTML = '<div class="dsplit__empty">—</div>'; return; }
                var bar = '<div class="dsplit__bar">' + parts.map(function (p) {
                    return '<span style="width:' + (p.value / total * 100) + '%;background:' + p.color + '" title="' + p.label + '"></span>';
                }).join('') + '</div>';
                var legend = '<div class="dsplit__legend">' + parts.map(function (p) {
                    return '<span class="dsplit__item"><i style="background:' + p.color + '"></i>' + p.label + ' <b>' + p.value + '</b></span>';
                }).join('') + '</div>';
                box.innerHTML = bar + legend;
            };
            window.__renderSplit(PSTATUS['today'] || []);
        })();
    </script>

    <script>
        (function () {
            var btn = document.getElementById('liveRefresh');
            var timeEl = document.getElementById('liveTime');
            var auto = document.getElementById('liveAuto');
            var url = @json(route('panel.'.$entity.'.home.live'));
            var INTERVAL = 15000;
            var timer = null, autoOn = true;

            function stamp(t) { if (timeEl) timeEl.textContent = t || new Date().toLocaleTimeString(); }
            function refresh() {
                if (btn) btn.classList.add('is-spin');
                return fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        Object.keys(j.values || {}).forEach(function (id) {
                            var el = document.getElementById(id);
                            if (!el) return;
                            var next = String(j.values[id]);
                            if (el.textContent.trim() !== next.trim()) {
                                if (window.panelCountUp) window.panelCountUp(el, next); else el.textContent = next;
                                if (window.panelFlash) window.panelFlash(el.closest('.p-kpi, .p-hero__stat, .stat-tile') || el);
                            }
                        });
                        stamp(j.time);
                    })
                    .catch(function () {})
                    .finally(function () { if (btn) setTimeout(function () { btn.classList.remove('is-spin'); }, 400); });
            }
            function schedule() { clearInterval(timer); if (autoOn) timer = setInterval(refresh, INTERVAL); }
            function setAuto(on) {
                autoOn = on;
                if (auto) { auto.classList.toggle('is-on', on); auto.classList.toggle('is-off', !on); }
                schedule();
            }

            stamp();
            if (btn) btn.addEventListener('click', refresh);
            if (auto) auto.addEventListener('click', function () { setAuto(!autoOn); });
            // Pause polling when the tab is hidden to save resources.
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) clearInterval(timer);
                else { schedule(); if (autoOn) refresh(); }
            });
            schedule();
        })();
    </script>
    @if($walletRevealed && !empty($walletRevealSeconds))
    <script>
        setTimeout(function () { window.location.reload(); }, {{ (int) $walletRevealSeconds }} * 1000);
    </script>
    @endif

    <script>
        (function () {
            var tabs = document.querySelectorAll('#statsTabs .stats-tab');
            var panels = document.querySelectorAll('[data-period-panel]');
            var custom = document.getElementById('statsCustom');
            var range = document.querySelector('.stats-range');
            var fromI = document.getElementById('statFrom'), toI = document.getElementById('statTo');
            var apply = document.getElementById('statApply');
            var statsUrl = @json(route('panel.'.$entity.'.home.stats'));
            var TONES = ['indigo', 'gold', 'teal', 'violet'];

            function esc(s) { return (s == null ? '' : String(s)).replace(/[&<>"']/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]; }); }
            function show(p) {
                panels.forEach(function (x) { x.classList.toggle('is-hidden', x.getAttribute('data-period-panel') !== p); });
                var active = document.querySelector('[data-period-panel="' + p + '"]:not(.is-hidden)');
                if (active && window.panelCountUp) active.querySelectorAll('.stat-tile__tx b').forEach(function (el) { window.panelCountUp(el, el.textContent, { from: '0', duration: 650 }); });
            }
            function setActive(btn) { tabs.forEach(function (t) { t.classList.remove('is-active'); }); btn.classList.add('is-active'); }

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var p = tab.getAttribute('data-period');
                    if (range) range.classList.toggle('is-open', p === 'custom');
                    if (p === 'custom') { setActive(tab); if (fromI) fromI.focus(); return; }
                    setActive(tab); show(p);
                    if (window.__renderSplit) window.__renderSplit((window.__PSTATUS || {})[p] || []);
                });
            });

            function tile(m, i) {
                return '<div class="stat-tile stat-tile--' + TONES[i % TONES.length] + '"><span class="stat-tile__ic"><i class="bi ' + esc(m.icon) + '"></i></span>'
                    + '<div class="stat-tile__tx"><b>' + esc(m.value) + '</b><span>' + esc(m.label) + '</span></div></div>';
            }

            function loadCustom() {
                if (!fromI.value || !toI.value) { fromI.classList.toggle('is-bad', !fromI.value); toI.classList.toggle('is-bad', !toI.value); return; }
                fromI.classList.remove('is-bad'); toI.classList.remove('is-bad');
                var ct = document.querySelector('#statsTabs .stats-tab[data-period="custom"]');
                setActive(ct); show('custom');
                custom.innerHTML = '<div class="stats-loading"><span class="trip-spin"></span></div>';
                fetch(statsUrl + '?from=' + encodeURIComponent(fromI.value) + '&to=' + encodeURIComponent(toI.value), { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        custom.innerHTML = (j.stats || []).map(tile).join('') || '<div class="stats-loading">—</div>';
                        if (window.__renderSplit) window.__renderSplit(j.status || []);
                    })
                    .catch(function () { custom.innerHTML = '<div class="stats-loading">—</div>'; });
            }

            if (apply) apply.addEventListener('click', loadCustom);
        })();
    </script>

    <script>
        (function () {
            var tabs = document.querySelectorAll('#rankTabs .p-ranks__tab');
            var panels = document.querySelectorAll('[data-rank-panel]');
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) { t.classList.remove('is-active'); });
                    tab.classList.add('is-active');
                    var k = tab.getAttribute('data-rank');
                    panels.forEach(function (p) { p.classList.toggle('is-hidden', p.getAttribute('data-rank-panel') !== k); });
                });
            });
        })();
    </script>

    <script src="{{ asset('js/socket.io.min.js') }}"></script>
    <script>
        (function () {
            if (typeof io === 'undefined') return;
            var socket = io(@json($realtimeUrl), { transports: ['websocket', 'polling'] });
            var channel = @json($realtimeChannel);
            var event = @json($realtimeEvent);

            socket.on('connect', function () { socket.emit('subscribe', channel); });

            socket.on(event, function (data) {
                if (!data || !data.name) return;
                var el = document.getElementById(data.name);
                if (!el) return;
                var next = String(data.value);
                if (el.textContent.trim() === next.trim()) return;
                if (window.panelCountUp) window.panelCountUp(el, next); else el.textContent = next;
                if (window.panelFlash) window.panelFlash(el.closest('.p-kpi, .p-hero__stat, .stat-tile') || el);
            });
        })();
    </script>

    <script>
        (function () {
            var center = @json($mapCenter);
            var driversUrl = @json(route('panel.'.$entity.'.map.drivers'));
            var markers = {};
            var map = null;

            window.initPanelMap = function () {
                var el = document.getElementById('panelMap');
                if (!el) return;
                var LIGHT_MAP = [
                    { elementType: 'geometry', stylers: [{ color: '#e6e8f3' }] },
                    { elementType: 'labels.text.stroke', stylers: [{ color: '#f4f5fb' }] },
                    { elementType: 'labels.text.fill', stylers: [{ color: '#6b7099' }] },
                    { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#ffffff' }] },
                    { featureType: 'road', elementType: 'geometry.stroke', stylers: [{ color: '#d7dbee' }] },
                    { featureType: 'road.highway', elementType: 'geometry', stylers: [{ color: '#f3e9d6' }] },
                    { featureType: 'road', elementType: 'labels.text.fill', stylers: [{ color: '#5a608a' }] },
                    { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#bcd0ec' }] },
                    { featureType: 'poi', elementType: 'geometry', stylers: [{ color: '#dde1f0' }] },
                    { featureType: 'poi.park', elementType: 'geometry', stylers: [{ color: '#cfe6d4' }] },
                    { featureType: 'transit', stylers: [{ visibility: 'off' }] },
                    { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#b9bce0' }] },
                ];
                var DARK_MAP = [
                    { elementType: 'geometry', stylers: [{ color: '#1b2036' }] },
                    { elementType: 'labels.text.stroke', stylers: [{ color: '#1b2036' }] },
                    { elementType: 'labels.text.fill', stylers: [{ color: '#8a90b8' }] },
                    { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#2a2f4a' }] },
                    { featureType: 'road', elementType: 'geometry.stroke', stylers: [{ color: '#222740' }] },
                    { featureType: 'road', elementType: 'labels.text.fill', stylers: [{ color: '#9aa0c4' }] },
                    { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#11152a' }] },
                    { featureType: 'poi', elementType: 'geometry', stylers: [{ color: '#242a45' }] },
                    { featureType: 'poi.park', elementType: 'geometry', stylers: [{ color: '#16301f' }] },
                    { featureType: 'transit', stylers: [{ visibility: 'off' }] },
                    { featureType: 'administrative', elementType: 'geometry', stylers: [{ color: '#3a2f86' }] },
                ];
                function panelMapStyles() { return document.documentElement.getAttribute('data-theme') === 'dark' ? DARK_MAP : []; }
                map = new google.maps.Map(el, {
                    center: center, zoom: 11, styles: panelMapStyles(),
                    disableDefaultUI: false, zoomControl: true, mapTypeControl: false, streetViewControl: false, fullscreenControl: false,
                });
                new MutationObserver(function () { if (map) map.setOptions({ styles: panelMapStyles() }); })
                    .observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
                fetchDrivers();
                setInterval(fetchDrivers, 30000);
            };

            function fetchDrivers() {
                if (!map) return;
                fetch(driversUrl, { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (list) {
                        var seen = {};
                        list.forEach(function (d) {
                            var id = d.driver_id;
                            seen[id] = true;
                            var pos = { lat: parseFloat(d.latitude), lng: parseFloat(d.longitude) };
                            if (markers[id]) {
                                markers[id].setPosition(pos);
                            } else {
                                markers[id] = new google.maps.Marker({
                                    position: pos, map: map,
                                    title: (d.name || '') + (d.carBrand ? ' · ' + d.carBrand : '')
                                });
                            }
                        });
                        Object.keys(markers).forEach(function (id) {
                            if (!seen[id]) { markers[id].setMap(null); delete markers[id]; }
                        });
                        var lu = document.getElementById('mapLastUpdate');
                        if (lu) lu.textContent = new Date().toLocaleString();
                    })
                    .catch(function () {});
            }
        })();
    </script>
    @if(!empty($googleMapsKey))
        <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&callback=initPanelMap"></script>
    @endif
@endpush
