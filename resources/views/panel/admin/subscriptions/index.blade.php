@extends('panel.layouts.master')

@section('title', textByLanguage('اشتراكات المكاتب', 'Office subscriptions'))
@section('page-title', textByLanguage('اشتراكات المكاتب', 'Office subscriptions'))

@php
    $r = fn ($name) => "panel.admin.{$name}";
    $statuses = ['trialing', 'active', 'past_due', 'canceled', 'ended'];
    $tone = [
        'trialing' => 'primary', 'active' => 'success', 'past_due' => 'danger',
        'canceled' => 'gray', 'ended' => 'gray',
    ];
    $cur = $currency ?? '';
    $m = fn ($minor) => number_format(((int) $minor) / 100, 2) . ' ' . $cur;
    $att = $attention ?? ['endingSoon' => [], 'pastDue' => [], 'unsubscribed' => []];
    $isSubMode = ($mode ?? 'subscription') === 'subscription';
    $hasAttention = count($att['endingSoon']) || count($att['pastDue']) || count($att['unsubscribed']);
    $officeLink = function ($id) {
        return \Illuminate\Support\Facades\Route::has('panel.admin.office.subscription.show')
            ? route('panel.admin.office.subscription.show', $id)
            : null;
    };
@endphp

@push('styles')
<style>
    .sub-act { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; margin-bottom: 18px; }
    .sub-act__card { border: 1px solid var(--p-border); border-radius: 14px; padding: 14px 16px; background: var(--p-surface, #fff); }
    .sub-act__card.is-danger { border-color: rgba(220,38,38,.32); background: rgba(220,38,38,.05); }
    .sub-act__head { display: flex; align-items: center; gap: 8px; font-size: .9rem; font-weight: 700; margin-bottom: 10px; }
    .sub-act__head .n { margin-inline-start: auto; font-size: .78rem; font-weight: 700; opacity: .7; }
    .sub-act__row { display: flex; align-items: center; gap: 8px; padding: 7px 0; border-bottom: 1px solid var(--p-border); font-size: .85rem; }
    .sub-act__row:last-child { border-bottom: 0; }
    .sub-act__row a { text-decoration: none; color: inherit; font-weight: 600; }
    .sub-act__row a:hover { text-decoration: underline; }
    .sub-act__when { margin-inline-start: auto; font-size: .76rem; font-weight: 700; }
    .sub-act__more { font-size: .76rem; opacity: .7; padding-top: 8px; }
</style>
@endpush

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('اشتراكات المكاتب', 'Office subscriptions')"
        :subtitle="textByLanguage('متابعة التجارب والاشتراكات النشطة وحالات فشل الدفع في الدولة الحالية', 'Track trials, active subscriptions and payment failures for the current country')">
        <x-slot:actions>
            @if(\Illuminate\Support\Facades\Route::has($r('subscriptions.sync')))
                {{-- When an office says "I paid and it is not showing", nobody
                     wants to be told to wait for the hourly job. --}}
                <form method="POST" action="{{ route($r('subscriptions.sync')) }}">
                    @csrf
                    <button type="submit" class="p-btn p-btn--soft"><i class="bi bi-arrow-repeat"></i> {{ textByLanguage('مزامنة مع Stripe', 'Sync with Stripe') }}</button>
                </form>
            @endif
            <a href="{{ route($r('overage-invoices.index')) }}" class="p-btn p-btn--ghost"><i class="bi bi-receipt"></i> {{ textByLanguage('فواتير التجاوز', 'Overage invoices') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    @unless($isSubMode)
        {{-- An empty table in a commission country reads as a broken screen
             rather than as "there is nothing to bill here". --}}
        <div class="p-flash" style="background:rgba(49,40,115,.06);color:var(--p-primary);border:1px solid var(--p-border);">
            <i class="bi bi-info-circle"></i>
            {{ textByLanguage('هذه الدولة تعمل بنظام العمولة — لا تُحصَّل اشتراكات فيها، والدخل عمولة على كل رحلة مكتملة. ما يظهر أدناه سجلات قديمة أو مُسندة يدوياً.', 'This country runs on commission — no subscriptions are billed here; income is a commission on each completed ride. Anything below is historical or manually assigned.') }}
        </div>
    @endunless

    <div class="p-grid p-grid--4" style="margin-bottom:16px;">
        <x-panel.stat :label="textByLanguage('الإيراد الشهري المتكرّر', 'Monthly recurring')" :value="$m($money['mrrMinor'])" icon="bi-graph-up-arrow" />
        <x-panel.stat :label="textByLanguage('تحت الخطر (فشل الدفع)', 'At risk (past due)')" :value="$m($money['atRiskMinor'])" icon="bi-exclamation-octagon" :variant="$money['atRiskMinor'] > 0 ? 'danger' : null" />
        <x-panel.stat :label="textByLanguage('قيمة التجارب الجارية', 'Trials in play')" :value="$m($money['trialMinor'])" icon="bi-hourglass-split" />
        <x-panel.stat :label="textByLanguage('تجاوز مجمّع غير مفوتر', 'Accrued overage')" :value="$m($money['overageMinor'])" icon="bi-receipt" :variant="$money['overageMinor'] > 0 ? 'warning' : null" />
    </div>

    @if($hasAttention)
        <div class="sub-act">
            @if(count($att['pastDue']))
                <div class="sub-act__card is-danger">
                    <div class="sub-act__head"><i class="bi bi-exclamation-octagon"></i> {{ textByLanguage('فشل الدفع', 'Payment failed') }} <span class="n">{{ count($att['pastDue']) }}</span></div>
                    @foreach(array_slice($att['pastDue'], 0, 6) as $row)
                        <div class="sub-act__row">
                            @if($officeLink($row['office_id']))
                                <a href="{{ $officeLink($row['office_id']) }}">{{ $row['office'] }}</a>
                            @else
                                <span>{{ $row['office'] }}</span>
                            @endif
                            <span class="sub-act__when">{{ ucfirst($row['plan']) }}</span>
                        </div>
                    @endforeach
                    @if(count($att['pastDue']) > 6)<div class="sub-act__more">+{{ count($att['pastDue']) - 6 }}</div>@endif
                </div>
            @endif

            @if(count($att['endingSoon']))
                <div class="sub-act__card">
                    <div class="sub-act__head"><i class="bi bi-hourglass-split"></i> {{ textByLanguage('تجارب تنتهي خلال أسبوع', 'Trials ending this week') }} <span class="n">{{ count($att['endingSoon']) }}</span></div>
                    @foreach(array_slice($att['endingSoon'], 0, 6) as $row)
                        <div class="sub-act__row">
                            @if($officeLink($row['office_id']))
                                <a href="{{ $officeLink($row['office_id']) }}">{{ $row['office'] }}</a>
                            @else
                                <span>{{ $row['office'] }}</span>
                            @endif
                            <span class="sub-act__when" style="color:{{ $row['days'] <= 2 ? 'var(--p-danger)' : 'var(--p-primary)' }};">
                                {{ $row['days'] }} {{ textByLanguage('يوم', 'd') }}
                            </span>
                        </div>
                    @endforeach
                    @if(count($att['endingSoon']) > 6)<div class="sub-act__more">+{{ count($att['endingSoon']) - 6 }}</div>@endif
                </div>
            @endif

            @if(count($att['unsubscribed']))
                <div class="sub-act__card is-danger">
                    {{-- Offices trading with no subscription in a country that
                         bills for one: nothing on this screen used to show them. --}}
                    <div class="sub-act__head"><i class="bi bi-building-exclamation"></i> {{ textByLanguage('مكاتب بلا اشتراك', 'Offices with no subscription') }} <span class="n">{{ count($att['unsubscribed']) }}</span></div>
                    @foreach(array_slice($att['unsubscribed'], 0, 6) as $row)
                        <div class="sub-act__row">
                            @if($officeLink($row['office_id']))
                                <a href="{{ $officeLink($row['office_id']) }}">{{ $row['office'] ?: '#' . $row['office_id'] }}</a>
                            @else
                                <span>{{ $row['office'] ?: '#' . $row['office_id'] }}</span>
                            @endif
                            <span class="sub-act__when">#{{ $row['office_id'] }}</span>
                        </div>
                    @endforeach
                    @if(count($att['unsubscribed']) > 6)<div class="sub-act__more">+{{ count($att['unsubscribed']) - 6 }}</div>@endif
                </div>
            @endif
        </div>
    @endif

    <div class="p-faq-stats" style="grid-template-columns:repeat(4,1fr);">
        <x-panel.stat :label="textByLanguage('تجربة', 'Trialing')" :value="$counts['trialing']" icon="bi-hourglass-split" />
        <x-panel.stat :label="textByLanguage('نشط', 'Active')" :value="$counts['active']" icon="bi-check2-circle" />
        <x-panel.stat :label="textByLanguage('فشل الدفع', 'Past due')" :value="$counts['past_due']" icon="bi-exclamation-octagon" :variant="$counts['past_due'] ? 'danger' : null" />
        <x-panel.stat :label="textByLanguage('الإجمالي', 'Total')" :value="$counts['total']" icon="bi-collection" />
    </div>

    <div class="p-card">
        <form method="GET" action="{{ route($r('subscriptions.index')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الحالات', 'All statuses') }}</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected($statusFilter === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
            @if($statusFilter)<a href="{{ route($r('subscriptions.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
        </form>

        @if($subscriptions->count())
            <x-panel.table :headers="[
                textByLanguage('المكتب', 'Office'),
                textByLanguage('الخطة', 'Plan'),
                textByLanguage('الحالة', 'Status'),
                textByLanguage('السعر', 'Price'),
                textByLanguage('تجاوز مجمّع', 'Accrued overage'),
                textByLanguage('التجربة / التجديد', 'Trial / renews'),
                textByLanguage('المزوّد', 'Provider'),
                '',
            ]">
                @foreach($subscriptions as $sub)
                    <tr @if($sub->status === 'past_due') style="background:rgba(220,38,38,.06);" @endif>
                        <td>
                            <div class="p-cell-main">
                                <div>
                                    <strong>{{ $officeNames[$sub->office_id] ?? (textByLanguage('مكتب', 'Office') . ' #' . $sub->office_id) }}</strong>
                                    <span class="p-cell-sub">#{{ $sub->office_id }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ ucfirst($sub->plan_key) }}</td>
                        <td><x-panel.badge :tone="$tone[$sub->status] ?? 'gray'">{{ ucfirst(str_replace('_', ' ', $sub->status)) }}</x-panel.badge></td>
                        <td>{{ number_format(((int) $sub->price_minor) / 100, 2) }} {{ $sub->currency_code }}</td>
                        <td>
                            @php $ov = $overageByOffice[$sub->office_id] ?? 0; @endphp
                            @if($ov > 0)
                                <strong style="color:#dc2626;">{{ number_format($ov / 100, 2) }} {{ $sub->currency_code }}</strong>
                            @else
                                <span class="p-cell-sub">—</span>
                            @endif
                        </td>
                        <td>
                            @if($sub->status === 'trialing' && $sub->trial_ends_at)
                                <i class="bi bi-hourglass-split" style="color:var(--p-primary);"></i> {{ \Illuminate\Support\Carbon::parse($sub->trial_ends_at)->isoFormat('D MMM YYYY') }}
                            @elseif($sub->current_period_end)
                                <i class="bi bi-arrow-repeat"></i> {{ \Illuminate\Support\Carbon::parse($sub->current_period_end)->isoFormat('D MMM YYYY') }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($sub->provider_subscription_id)
                                <span class="p-badge p-badge--gray" title="{{ $sub->provider_subscription_id }}"><i class="bi bi-stripe"></i> Stripe</span>
                            @else
                                <span class="p-cell-sub">{{ textByLanguage('يدوي', 'Manual') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($officeLink($sub->office_id))
                                <a href="{{ $officeLink($sub->office_id) }}" class="p-icon-btn" title="{{ textByLanguage('تفاصيل الاشتراك', 'Subscription details') }}"><i class="bi bi-box-arrow-up-right"></i></a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-collection"></i> {{ textByLanguage('لا توجد اشتراكات', 'No subscriptions') }}</p>
        @endif
    </div>

@endsection
