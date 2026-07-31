@extends('panel.layouts.master')

@section('title', textByLanguage('الاشتراك', 'Subscription'))
@section('page-title', textByLanguage('الاشتراك', 'Subscription'))

@php
    $isSubMode = ($mode ?? 'commission') === 'subscription';
    $status = $subscription['status'] ?? null;
    $money = fn ($m, $c) => number_format(((int) $m) / 100, 2) . ' ' . ($c ?: '');
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif
    @if(request('checkout') === 'success' && ($reconciled ?? null) !== 'pending')
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ textByLanguage('تمّ الدفع وفُعِّل اشتراكك.', 'Payment received — your plan is active.') }}</div>
    @elseif(request('checkout') === 'success')
        {{-- Payment taken but Stripe has not called the session complete yet
             (or we could not read it back). Saying "activated" here is how an
             office ends up believing it paid for nothing. --}}
        <div class="p-flash p-flash--warn">
            <i class="bi bi-hourglass-split"></i>
            {{ textByLanguage('تمّ استلام الدفع ولم يُفعَّل الاشتراك بعد. حدِّث الصفحة بعد قليل، وإن استمرّ الأمر راسل الدعم ومعك رقم عملية الدفع.', 'Payment received but the plan is not active yet. Refresh in a moment; if it persists, contact support with your payment reference.') }}
        </div>
    @elseif(request('checkout') === 'cancel')
        <div class="p-flash p-flash--err"><i class="bi bi-x-circle"></i> {{ textByLanguage('أُلغيت عملية الدفع.', 'Checkout was cancelled.') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('اشتراك مكتبي', 'My subscription')"
        :subtitle="textByLanguage('خطتك الحالية ونِسب العمولة', 'Your current plan and commission rates')" />

    @if(!empty($usage))
        @php
            $ar = app()->getLocale() === 'ar';
            $line = function ($used, $limit, $over, $extra, $cur) use ($ar) {
                $limTxt = $limit === null ? ($ar ? 'بلا حد' : 'Unlimited') : $limit;
                $out = $used . ' / ' . $limTxt;
                if ($over > 0) {
                    $out .= ' — ' . ($ar ? 'تجاوز' : 'over') . ' ' . $over;
                    if ($extra) { $out .= ' (' . ($ar ? 'رسوم' : 'fee') . ' ' . number_format($extra / 100, 2) . ' ' . $cur . '/' . ($ar ? 'وحدة' : 'unit') . ')'; }
                }
                return $out;
            };
            $anyOver = $usage['drivers_over'] > 0 || $usage['rides_over'] > 0;
        @endphp
        <div class="p-card" style="margin-bottom:16px; @if($anyOver) border-color:#dc2626; @endif">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <strong>{{ textByLanguage('استهلاك الخطة', 'Plan usage') }} — {{ $usage['plan_name'] }}</strong>
                @if($anyOver)<span class="p-badge p-badge--danger">{{ textByLanguage('تجاوز الخطة', 'Over plan') }}</span>@endif
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div>
                    <div style="font-size:.8rem; color:var(--p-text-muted);">{{ textByLanguage('السائقون', 'Drivers') }}</div>
                    <div style="font-weight:700; @if($usage['drivers_over'] > 0) color:#dc2626; @endif">{{ $line($usage['drivers_used'], $usage['driver_limit'], $usage['drivers_over'], $usage['extra_driver_minor'], $usage['currency']) }}</div>
                </div>
                <div>
                    <div style="font-size:.8rem; color:var(--p-text-muted);">{{ textByLanguage('رحلات هذا الشهر', 'Rides this month') }}</div>
                    <div style="font-weight:700; @if($usage['rides_over'] > 0) color:#dc2626; @endif">{{ $line($usage['rides_used'], $usage['ride_limit'], $usage['rides_over'], $usage['extra_ride_minor'], $usage['currency']) }}</div>
                </div>
            </div>
            @if(!empty($overagePending) && $overagePending > 0)
                <div style="margin-top:12px; padding-top:12px; border-top:1px solid var(--p-border); display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:.85rem; color:var(--p-text-muted);">{{ textByLanguage('رسوم تجاوز مجمّعة لهذا الشهر (تُضاف للفاتورة)', 'Overage accrued this month (billed on your invoice)') }}</span>
                    <strong style="color:#dc2626;">{{ number_format($overagePending / 100, 2) }} {{ $usage['currency'] }}</strong>
                </div>
            @endif
        </div>
    @endif

    @if($status === 'trialing' && !empty($subscription['trial_ends_at']))
        @php
            $daysLeft = $trialDaysLeft ?? max(0, (int) ceil(now()->floatDiffInDays($subscription['trial_ends_at'], false)));
            $checkoutRoute = 'panel.' . $entity . '.subscription.checkout';
        @endphp
        <div class="p-bill-banner p-bill-banner--trial">
            <i class="bi bi-hourglass-split"></i>
            <div>
                <strong>{{ textByLanguage('تجربة مجانية سارية', 'Free trial active') }}</strong>
                <span>{{ textByLanguage('تنتهي خلال', 'Ends in') }} {{ $daysLeft }} {{ textByLanguage('يوم', 'days') }} ({{ \Illuminate\Support\Carbon::parse($subscription['trial_ends_at'])->isoFormat('D MMM YYYY') }})</span>
            </div>
            {{-- An office that wants to start paying before the trial runs out had
                 no way to do it: its own plan was the one button on the page that
                 was disabled. --}}
            @if(\Illuminate\Support\Facades\Route::has($checkoutRoute))
                <div class="p-bill-banner__actions">
                    <form method="POST" action="{{ route($checkoutRoute) }}">
                        @csrf
                        <input type="hidden" name="plan_key" value="{{ $subscription['plan_key'] }}">
                        <input type="hidden" name="billing_starts" value="after_trial">
                        <button type="submit" class="p-btn p-btn--soft"><i class="bi bi-credit-card"></i> {{ textByLanguage('أضف بطاقتي الآن', 'Add my card now') }}</button>
                    </form>
                    <form method="POST" action="{{ route($checkoutRoute) }}">
                        @csrf
                        <input type="hidden" name="plan_key" value="{{ $subscription['plan_key'] }}">
                        <input type="hidden" name="billing_starts" value="now">
                        <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-lightning-charge"></i> {{ textByLanguage('ادفع الآن وابدأ الاشتراك', 'Pay now and start') }}</button>
                    </form>
                </div>
            @endif
        </div>
        <p class="p-plan-note" style="margin:-6px 0 16px;">
            <i class="bi bi-info-circle"></i>
            {{ textByLanguage(
                'أضف بطاقتك الآن لتبدأ الفوترة يوم انتهاء التجربة دون انقطاع — أيامك المتبقية محفوظة ولن تُحتسب مرتين. أو ادفع الآن لتفعيل الاشتراك فوراً والتنازل عمّا تبقّى من التجربة.',
                'Add your card now and billing starts the day the trial ends, with no interruption — your remaining days are kept and never charged twice. Or pay now to activate immediately and give up the rest of the trial.'
            ) }}
        </p>
    @elseif($status === 'past_due')
        <div class="p-bill-banner p-bill-banner--danger">
            <i class="bi bi-exclamation-octagon"></i>
            <div>
                <strong>{{ textByLanguage('فشل تجديد الدفع', 'Payment failed') }}</strong>
                <span>{{ textByLanguage('منصّتك لا تزال فعّالة — يُرجى تحديث بيانات الدفع لتجنّب الانقطاع.', 'Your platform is still active — please update billing to avoid interruption.') }}</span>
            </div>
        </div>
    @elseif($status === 'active' && !empty($subscription['current_period_end']))
        <div class="p-bill-banner p-bill-banner--ok">
            <i class="bi bi-check-circle"></i>
            <div>
                <strong>{{ textByLanguage('اشتراك نشط', 'Subscription active') }}</strong>
                <span>{{ $subscription['cancel_at_period_end'] ? textByLanguage('ينتهي في', 'Ends on') : textByLanguage('يتجدّد في', 'Renews on') }} {{ \Illuminate\Support\Carbon::parse($subscription['current_period_end'])->isoFormat('D MMM YYYY') }}</span>
            </div>
        </div>
    @endif

    @if($subscription)
        @php
            $fleetRate = (float) $subscription['fleet_commission_rate'];
            $officeRate = (float) $subscription['office_commission_rate'];
            $rateSum = max(0.01, $fleetRate + $officeRate);
            $statusTone = match ($status) {
                'active' => 'success', 'trialing' => 'primary',
                'past_due' => 'danger', 'canceled', 'ended' => 'gray', default => 'gray',
            };
        @endphp

        <div class="p-plan-wrap">
            <div class="p-plan-card">
                <div class="p-plan-card__top">
                    <span class="p-plan-card__badge"><i class="bi bi-award-fill"></i> {{ textByLanguage('الخطة الحالية', 'Current plan') }}</span>
                    <span class="p-plan-card__status is-{{ $statusTone }}">{{ ucfirst($status) }}</span>
                </div>
                <div class="p-plan-card__key">{{ ucfirst($subscription['plan_key']) }}</div>
                <div class="p-plan-card__price">
                    <b>{{ number_format($subscription['price_minor'] / 100, 2) }}</b>
                    <span>{{ $subscription['currency_code'] }} / {{ textByLanguage('شهر', 'mo') }}</span>
                </div>
                <div class="p-plan-card__meta">
                    <span><i class="bi bi-building"></i> {{ textByLanguage('عمولة الأسطول', 'Fleet') }} {{ rtrim(rtrim(number_format($fleetRate, 2), '0'), '.') }}%</span>
                    <span><i class="bi bi-shop"></i> {{ textByLanguage('حصّة المكتب', 'Office') }} {{ rtrim(rtrim(number_format($officeRate, 2), '0'), '.') }}%</span>
                </div>
            </div>

            <x-panel.card :title="textByLanguage('توزيع العمولة', 'Commission split')" class="p-plan-side">
                <div class="p-split" style="margin-bottom:18px;">
                    <div class="p-split__bar" style="height:16px;">
                        <span style="width:{{ round($fleetRate / $rateSum * 100, 1) }}%;"></span>
                        <span style="width:{{ round($officeRate / $rateSum * 100, 1) }}%;"></span>
                    </div>
                </div>
                <div class="p-rep-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="p-rep-line p-rep-line--fleet">
                        <span class="p-rep-line__dot"></span>
                        <div class="p-rep-line__tx">
                            <span>{{ textByLanguage('عمولة الأسطول', 'Fleet commission') }}</span>
                            <b>{{ rtrim(rtrim(number_format($fleetRate, 2), '0'), '.') }}%</b>
                        </div>
                    </div>
                    <div class="p-rep-line p-rep-line--office">
                        <span class="p-rep-line__dot"></span>
                        <div class="p-rep-line__tx">
                            <span>{{ textByLanguage('حصّة المكتب', 'Office share') }}</span>
                            <b>{{ rtrim(rtrim(number_format($officeRate, 2), '0'), '.') }}%</b>
                        </div>
                    </div>
                </div>
                <p class="p-plan-note"><i class="bi bi-info-circle"></i> {{ textByLanguage('تُطبَّق هذه النِسب على كل رحلة مكتملة عبر التسوية.', 'These rates apply to every completed ride at settlement.') }}</p>
            </x-panel.card>
        </div>
    @elseif(!$isSubMode)
        <x-panel.card>
            <p class="p-empty"><i class="bi bi-cash-coin"></i> {{ textByLanguage('منطقتك تعمل بنظام العمولة — لا حاجة لاشتراك شهري.', 'Your region runs on commission — no monthly subscription needed.') }}</p>
        </x-panel.card>
    @endif

    @if($isSubMode && count($plans))
        <x-panel.card :title="$subscription ? textByLanguage('ترقية / تغيير الخطة', 'Upgrade / change plan') : textByLanguage('اختر خطة للاشتراك', 'Choose a plan')" style="margin-top:18px;">
            <div class="p-lead-grid">
                @foreach($plans as $plan)
                    @php
                        $isCurrent = $subscription && $subscription['plan_key'] === $plan['key'];
                        $isPicked = ($preselected ?? null) === $plan['key'];
                    @endphp
                    <div id="plan-{{ $plan['key'] }}" class="p-price @if($plan['is_popular']) is-popular @endif @if($isCurrent) is-current @endif @if($isPicked) is-preselected @endif">
                        @if($isPicked)<span class="p-price__ribbon" style="background:var(--p-success,#1a7f37);">{{ textByLanguage('خطتك المختارة', 'Your pick') }}</span>@endif
                        @if($plan['is_popular'] && !$isPicked)<span class="p-price__ribbon">{{ textByLanguage('الأكثر شيوعاً', 'Popular') }}</span>@endif
                        <h3 class="p-price__name">{{ $plan['name'] }}</h3>
                        <div class="p-price__amt"><b>{{ number_format($plan['price_minor'] / 100, 0) }}</b> <span>{{ $plan['currency_code'] }} / {{ textByLanguage('شهر', 'mo') }}</span></div>
                        <ul class="p-price__feats">
                            <li><i class="bi bi-check2"></i> {{ textByLanguage('عمولة الأسطول', 'Fleet commission') }} {{ rtrim(rtrim(number_format($plan['fleet_commission_rate'], 2), '0'), '.') }}%</li>
                            <li><i class="bi bi-check2"></i> {{ $plan['trial_days'] }} {{ textByLanguage('يوم تجربة مجانية', 'day free trial') }}</li>
                            @foreach(array_slice($plan['features'], 0, 4) as $feat)
                                <li><i class="bi bi-check2"></i> {{ is_array($feat) ? ($feat[app()->getLocale()] ?? reset($feat)) : $feat }}</li>
                            @endforeach
                        </ul>
                        @php
                            $checkoutRoute = 'panel.' . $entity . '.subscription.checkout';
                            $trialRoute = 'panel.' . $entity . '.subscription.trial';
                            // The trial is only genuinely on offer before any
                            // subscription exists and only once per office; the
                            // button used to say "start trial" and go to Stripe.
                            $canTrial = ! $subscription && ! ($trialUsed ?? false) && \Illuminate\Support\Facades\Route::has($trialRoute);
                        @endphp
                        @if($isCurrent && $status === 'trialing' && \Illuminate\Support\Facades\Route::has($checkoutRoute))
                            <form method="POST" action="{{ route($checkoutRoute) }}">
                                @csrf
                                <input type="hidden" name="plan_key" value="{{ $plan['key'] }}">
                                <input type="hidden" name="billing_starts" value="now">
                                <button type="submit" class="p-btn p-btn--primary" style="width:100%;">
                                    <i class="bi bi-lightning-charge"></i> {{ textByLanguage('ادفع الآن وابدأ الاشتراك', 'Pay now and start') }}
                                </button>
                            </form>
                        @elseif($isCurrent)
                            <button type="button" class="p-btn p-btn--ghost" disabled style="width:100%;opacity:.7;"><i class="bi bi-check-lg"></i> {{ textByLanguage('خطتك الحالية', 'Current plan') }}</button>
                        @elseif(\Illuminate\Support\Facades\Route::has($checkoutRoute))
                            @if($canTrial)
                                <form method="POST" action="{{ route($trialRoute) }}" style="margin-bottom:8px;">
                                    @csrf
                                    <input type="hidden" name="plan_key" value="{{ $plan['key'] }}">
                                    <button type="submit" class="p-btn p-btn--primary" style="width:100%;">
                                        <i class="bi bi-gift"></i> {{ textByLanguage('ابدأ التجربة المجانية', 'Start the free trial') }}
                                        ({{ $plan['trial_days'] }} {{ textByLanguage('يوم', 'days') }})
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route($checkoutRoute) }}">
                                @csrf
                                <input type="hidden" name="plan_key" value="{{ $plan['key'] }}">
                                <button type="submit" class="p-btn {{ $canTrial ? 'p-btn--soft' : 'p-btn--primary' }}" style="width:100%;">
                                    <i class="bi bi-credit-card"></i> {{ $subscription ? textByLanguage('التبديل لهذه الخطة', 'Switch to this plan') : textByLanguage('الاشتراك الآن', 'Subscribe now') }}
                                </button>
                            </form>
                        @else
                            {{-- Admin (or any non-office guard) viewing the plans: subscribing is
                                 the office's own action, so there is no self-checkout here. --}}
                            <button type="button" class="p-btn p-btn--ghost" disabled style="width:100%;opacity:.7;">
                                <i class="bi bi-building"></i> {{ textByLanguage('يُدار من حساب المكتب', 'Managed from the office account') }}
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
            <p class="p-plan-note" style="margin-top:14px;"><i class="bi bi-shield-lock"></i> {{ textByLanguage('الدفع الآمن عبر Stripe. تبدأ الفوترة بعد انتهاء التجربة، ويمكنك الإلغاء في أي وقت.', 'Secure payment via Stripe. Billing starts after the trial ends; cancel anytime.') }}</p>
        </x-panel.card>
    @endif

    @if(!empty($preselected))
        <style>
            .p-price.is-preselected {
                outline: 2px solid var(--p-success, #1a7f37);
                box-shadow: 0 0 0 4px rgba(26,127,55,.14);
                animation: planPulse 1.4s ease-in-out 2;
            }
            @keyframes planPulse { 50% { box-shadow: 0 0 0 9px rgba(26,127,55,.05); } }
        </style>
        <script>
            (function () {
                var el = document.getElementById('plan-{{ $preselected }}');
                if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
            })();
        </script>
    @endif

@endsection
