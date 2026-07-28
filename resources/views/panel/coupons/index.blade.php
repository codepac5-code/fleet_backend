@extends('panel.layouts.master')

@section('title', textByLanguage('الكوبونات', 'Coupons'))
@section('page-title', textByLanguage('الكوبونات', 'Coupons'))

@php $r = fn ($name) => "panel.admin.{$name}"; @endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('كوبونات الخصم', 'Discount coupons')"
        :subtitle="textByLanguage('كوبونات هذه الدولة فقط — لا تُشارَك مع دول أخرى', 'Coupons for this country only — not shared across countries')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    <div class="p-card" style="margin-bottom:16px;">
        <form method="POST" action="{{ route($r('coupons.store')) }}" style="display:grid; grid-template-columns:repeat(5,1fr) auto; gap:12px; align-items:end;">
            @csrf
            <div><label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;">{{ textByLanguage('الكود', 'Code') }}</label>
                <input name="code" required placeholder="SUMMER20" style="width:100%;padding:9px 11px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;">{{ textByLanguage('قيمة الخصم', 'Discount') }}</label>
                <input name="discount" type="number" step="0.01" min="0" required style="width:100%;padding:9px 11px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;">{{ textByLanguage('النوع', 'Type') }}</label>
                <select name="is_percentage" style="width:100%;padding:9px 11px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);">
                    <option value="1">{{ textByLanguage('نسبة %', 'Percentage %') }}</option>
                    <option value="0">{{ textByLanguage('مبلغ ثابت', 'Fixed amount') }}</option>
                </select></div>
            <div><label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;">{{ textByLanguage('حد الاستخدام', 'Usage limit') }}</label>
                <input name="limit" type="number" min="0" value="0" style="width:100%;padding:9px 11px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);">
                <small style="color:var(--p-text-muted);">{{ textByLanguage('0 = بلا حد', '0 = unlimited') }}</small></div>
            <div><label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;">{{ textByLanguage('تاريخ الانتهاء', 'Expires') }}</label>
                <input name="expire_date" type="date" style="width:100%;padding:9px 11px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-plus-lg"></i> {{ textByLanguage('إضافة', 'Add') }}</button>
        </form>
    </div>

    <div class="p-card">
        @if($coupons->count())
            <x-panel.table :headers="[
                textByLanguage('الكود', 'Code'),
                textByLanguage('الخصم', 'Discount'),
                textByLanguage('الاستخدام', 'Limit'),
                textByLanguage('الانتهاء', 'Expires'),
                textByLanguage('الحالة', 'Status'),
                '',
            ]">
                @foreach($coupons as $c)
                    <tr @if($c->trashed()) style="opacity:.5;" @endif>
                        <td><strong>{{ $c->code }}</strong></td>
                        <td>{{ $c->isPercentage ? $c->discount . '%' : $c->discount }}</td>
                        <td>{{ (int) $c->limit === 0 ? textByLanguage('بلا حد', 'Unlimited') : $c->limit }}</td>
                        <td>{{ $c->expireDate ?: '—' }}</td>
                        <td>
                            @if($c->trashed())
                                <x-panel.badge tone="danger">{{ textByLanguage('محذوف', 'Deleted') }}</x-panel.badge>
                            @else
                                <x-panel.badge :tone="$c->isActive ? 'success' : 'gray'">{{ $c->isActive ? textByLanguage('مفعّل', 'Active') : textByLanguage('موقوف', 'Inactive') }}</x-panel.badge>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            @unless($c->trashed())
                                <form method="POST" action="{{ route($r('coupons.toggle'), $c->id) }}" style="display:inline;">@csrf
                                    <button class="p-btn p-btn--soft" type="submit">{{ $c->isActive ? textByLanguage('إيقاف', 'Disable') : textByLanguage('تفعيل', 'Enable') }}</button>
                                </form>
                                <form method="POST" action="{{ route($r('coupons.delete'), $c->id) }}" style="display:inline;" onsubmit="return confirm('{{ textByLanguage('حذف الكوبون؟', 'Delete coupon?') }}');">@csrf @method('DELETE')
                                    <button class="p-btn p-btn--soft" type="submit" style="color:var(--p-danger);"><i class="bi bi-trash"></i></button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-ticket-perforated"></i> {{ textByLanguage('لا توجد كوبونات', 'No coupons') }}</p>
        @endif
    </div>

@endsection
