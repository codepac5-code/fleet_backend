<section id="page-pricing" class="page">
    <div class="phero">
        <div class="wrap in">
            <span class="eyebrow on-dark">{{ $t('Pricing', 'الأسعار') }}</span>
            <h1>{{ $t('Plans that scale with your office', 'خطط تنمو مع مكتبك') }}</h1>
            <p>{{ $t('Lower platform commission as you grow. Start free, upgrade anytime, cancel anytime.', 'عمولة منصّة أقلّ كلّما نموت. ابدأ مجّاناً، رقِّ في أيّ وقت، وألغِ في أيّ وقت.') }}</p>
        </div>
    </div>

    <div class="pad">
        <div class="wrap">
            @php
                $cur = fn($minor, $code) => $minor === null ? $t('Custom', 'مخصّص') : ($code === 'USD' || !$code ? '$' : $code . ' ') . number_format(((int) $minor) / 100, ((int) $minor) % 100 === 0 ? 0 : 2);
                if (isset($plans) && count($plans)) {
                    $rows = collect($plans)->map(fn($p) => [
                        'name' => $p->name,
                        'price' => $cur($p->price_minor, $p->currency_code),
                        'rate' => $p->fleet_commission_rate !== null ? rtrim(rtrim(number_format((float) $p->fleet_commission_rate, 2), '0'), '.') . '%' : $t('Custom', 'مخصّص'),
                        'limit' => $p->driver_limit !== null ? $p->driver_limit . ' ' . $t('drivers', 'سائقاً') : $t('Unlimited', 'غير محدود'),
                        'pop' => (bool) ($p->is_popular ?? false),
                        'features' => is_array($p->features ?? null) ? $p->features : null,
                    ])->all();
                } else {
                    $rows = [
                        ['name' => $t('Free', 'مجّاني'), 'price' => '$0', 'rate' => '18%', 'limit' => $t('5 drivers', '٥ سائقين'), 'pop' => false, 'features' => null],
                        ['name' => $t('Starter', 'المبتدئ'), 'price' => '$20', 'rate' => '13%', 'limit' => $t('25 drivers', '٢٥ سائقاً'), 'pop' => false, 'features' => null],
                        ['name' => $t('Business', 'الأعمال'), 'price' => '$35', 'rate' => '12%', 'limit' => $t('50 drivers', '٥٠ سائقاً'), 'pop' => true, 'features' => null],
                        ['name' => $t('Scale', 'التوسّع'), 'price' => '$50', 'rate' => '11%', 'limit' => $t('150 drivers', '١٥٠ سائقاً'), 'pop' => false, 'features' => null],
                        ['name' => $t('Enterprise', 'المؤسّسات'), 'price' => $t('Custom', 'مخصّص'), 'rate' => $t('Custom', 'مخصّص'), 'limit' => $t('Unlimited', 'غير محدود'), 'pop' => false, 'features' => null],
                    ];
                }
            @endphp
            <div class="price-grid reveal">
                @foreach ($rows as $p)
                    <div class="plan {{ $p['pop'] ? 'pop' : '' }}">
                        <div class="pn">{{ $p['name'] }}</div>
                        <div class="pc">{{ $p['price'] }}@if($p['price'] !== $t('Custom', 'مخصّص'))<small>/{{ $t('mo', 'شهر') }}</small>@endif</div>
                        <span class="rate">{{ $p['rate'] }} {{ $t('commission', 'عمولة') }}</span>
                        <ul>
                            <li><i class="fa-solid fa-check"></i>{{ $p['limit'] }}</li>
                            @if($p['features'])
                                @foreach($p['features'] as $f)<li><i class="fa-solid fa-check"></i>{{ $f }}</li>@endforeach
                            @else
                                <li><i class="fa-solid fa-check"></i>{{ $t('Office dashboard', 'لوحة المكتب') }}</li>
                                <li><i class="fa-solid fa-check"></i>{{ $t('Driver & rider apps', 'تطبيقا السائق والراكب') }}</li>
                                <li><i class="fa-solid fa-check"></i>{{ $t('Wallets & payouts', 'محافظ وسحوبات') }}</li>
                            @endif
                        </ul>
                        <button class="btn {{ $p['pop'] ? 'btn-primary' : 'btn-ghost' }} btn-block" onclick="showPage('offices')">{{ $t('Choose', 'اختر') }}</button>
                    </div>
                @endforeach
            </div>

            <div class="reveal" style="margin-top:2.5rem">
                <div class="band">
                    <span class="eyebrow on-dark"><i class="fa-solid fa-gift"></i> {{ $t('Launch offer', 'عرض الإطلاق') }}</span>
                    <h2 style="margin-top:1rem; font-size:clamp(1.5rem,3vw,2.2rem)">{{ $t('Founding offices save on their first months', 'المكاتب المؤسِّسة توفّر في أشهرها الأولى') }}</h2>
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; max-width:640px; margin:1.6rem auto 0">
                        <div style="background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:14px; padding:1.1rem"><b style="display:block; color:var(--primary-2); font-size:1.4rem; font-weight:800">{{ $t('1 month free', 'شهر مجّاني') }}</b><span style="opacity:.85; font-size:.85rem">{{ $t('Any paid plan', 'أيّ خطّة مدفوعة') }}</span></div>
                        <div style="background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:14px; padding:1.1rem"><b style="display:block; color:var(--primary-2); font-size:1.4rem; font-weight:800">-3%</b><span style="opacity:.85; font-size:.85rem">{{ $t('Commission · 90 days', 'عمولة · ٩٠ يوماً') }}</span></div>
                        <div style="background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:14px; padding:1.1rem"><b style="display:block; color:var(--primary-2); font-size:1.4rem; font-weight:800">{{ $t('Priority', 'أولويّة') }}</b><span style="opacity:.85; font-size:.85rem">{{ $t('Marketplace spot', 'ظهور في السوق') }}</span></div>
                    </div>
                    <button class="btn btn-primary" style="margin-top:1.6rem" onclick="showPage('offices')">{{ $t('Claim the offer', 'احصل على العرض') }}</button>
                </div>
            </div>
        </div>
    </div>
</section>
