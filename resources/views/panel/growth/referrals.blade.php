@extends('panel.layouts.master')

@section('title', textByLanguage('برنامج الإحالة', 'Referral programme'))
@section('page-title', textByLanguage('برنامج الإحالة', 'Referral programme'))

@php
    $r = fn ($n) => "panel.admin.{$n}";
    $inp = 'width:100%;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);';
    $lbl = 'display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;';
    $money = fn ($minor) => number_format(((int) $minor) / 100, 2) . ' ' . $currency;
    $who = fn ($id) => ($names[$id] ?? null) ? trim(($names[$id]->firstName ?? '') . ' ' . ($names[$id]->lastName ?? '')) : '#' . $id;
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('دعوة صديق', 'Refer a friend')"
        :subtitle="textByLanguage('المكافأة تُدفع بعد أن يُكمل المدعوّ رحلاته الأولى فعلياً — لا عند التسجيل', 'The reward is paid once the invitee actually completes their first rides — not at signup')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    <div class="p-card" style="margin-bottom:16px;">
        <form method="POST" action="{{ route($r('referrals.save')) }}" style="display:grid;grid-template-columns:auto 1fr 1fr 1fr auto;gap:12px;align-items:end;">
            @csrf
            <label style="display:flex;gap:8px;align-items:center;font-weight:700;font-size:.85rem;padding-bottom:8px;">
                <input type="checkbox" name="is_active" value="1" @checked($settings->is_active)>
                {{ textByLanguage('مُفعّل', 'Active') }}
            </label>
            <div><label style="{{ $lbl }}">{{ textByLanguage('مكافأة الداعي', 'Referrer reward') }} ({{ $currency }})</label>
                <input name="referrer_reward" type="number" step="0.01" min="0" style="{{ $inp }}" value="{{ number_format(($settings->referrer_reward_minor ?? 0) / 100, 2, '.', '') }}"></div>
            <div><label style="{{ $lbl }}">{{ textByLanguage('مكافأة المدعوّ', 'Invitee reward') }} ({{ $currency }})</label>
                <input name="invitee_reward" type="number" step="0.01" min="0" style="{{ $inp }}" value="{{ number_format(($settings->invitee_reward_minor ?? 0) / 100, 2, '.', '') }}"></div>
            <div><label style="{{ $lbl }}">{{ textByLanguage('رحلات مطلوبة', 'Qualifying rides') }}</label>
                <input name="qualifying_rides" type="number" min="1" max="100" style="{{ $inp }}" value="{{ $settings->qualifying_rides ?: 1 }}"></div>
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ textByLanguage('حفظ', 'Save') }}</button>
        </form>
    </div>

    <div class="p-grid p-grid--3" style="margin-bottom:18px;">
        <x-panel.stat :label="textByLanguage('بانتظار أول رحلة', 'Waiting on first ride')" :value="$counts['pending']" icon="bi-hourglass-split" />
        <x-panel.stat :label="textByLanguage('مكافآت مدفوعة', 'Rewarded')" :value="$counts['rewarded']" icon="bi-gift" />
        <x-panel.stat :label="textByLanguage('إجمالي المدفوع', 'Total paid')" :value="$money($counts['paidMinor'])" icon="bi-cash-stack" />
    </div>

    <div class="p-card">
        @if($referrals->count())
            <x-panel.table :headers="['#', textByLanguage('الداعي', 'Referrer'), textByLanguage('المدعوّ', 'Invitee'), textByLanguage('الرمز', 'Code'), textByLanguage('الحالة', 'Status'), textByLanguage('المكافأة', 'Reward'), textByLanguage('التاريخ', 'When')]">
                @foreach($referrals as $referral)
                    <tr>
                        <td class="p-row-id">#{{ $referral->id }}</td>
                        <td>{{ $who($referral->referrer_user_id) }}</td>
                        <td>{{ $who($referral->invitee_user_id) }}</td>
                        <td><code>{{ $referral->code }}</code></td>
                        <td>
                            <x-panel.badge :tone="$referral->status === 'rewarded' ? 'success' : 'warning'">
                                {{ $referral->status === 'rewarded' ? textByLanguage('مكافأة مدفوعة', 'Rewarded') : textByLanguage('قيد الانتظار', 'Pending') }}
                            </x-panel.badge>
                        </td>
                        <td>{{ $referral->status === 'rewarded' ? $money($referral->referrer_reward_minor + $referral->invitee_reward_minor) : '—' }}</td>
                        <td>{{ $referral->created_at ? $referral->created_at->diffForHumans() : '—' }}</td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-people"></i> {{ textByLanguage('لا توجد إحالات بعد', 'No referrals yet') }}</p>
        @endif
    </div>

@endsection
