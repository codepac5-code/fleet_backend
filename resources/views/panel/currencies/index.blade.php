@extends('panel.layouts.master')

@section('title', textByLanguage('العملات', 'Currencies'))
@section('page-title', textByLanguage('العملات', 'Currencies'))

@push('styles')
<style>
    .cur-table { width: 100%; border-collapse: collapse; }
    .cur-table th, .cur-table td { padding: 12px 14px; text-align: start; border-bottom: 1px solid var(--p-border); font-size: .92rem; }
    .cur-table th { color: var(--p-text-muted); font-weight: 600; }
    .cur-form { display: grid; grid-template-columns: repeat(5, 1fr) auto; gap: 12px; align-items: end; }
    .cur-form .field { margin: 0; }
    .cur-form label { display: block; font-size: .8rem; font-weight: 600; margin-bottom: 5px; color: var(--p-text); }
    .cur-form input { width: 100%; padding: 10px 12px; border: 1.5px solid var(--p-border); border-radius: var(--p-radius-sm); font-family: inherit; }
    .cur-check { display: flex; align-items: center; gap: 6px; font-size: .85rem; color: var(--p-text-muted); }
    .cur-add-btn { padding: 10px 20px; border: none; background: var(--p-accent); color: #fff; font-weight: 700; border-radius: var(--p-radius-sm); cursor: pointer; }
    .pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: .78rem; font-weight: 700; }
    .pill-on { background: rgba(26,127,55,.12); color: var(--p-success); }
    .pill-off { background: rgba(220,53,69,.12); color: var(--p-danger); }
    .pill-default { background: rgba(49,40,115,.1); color: var(--p-primary); }
    .toggle-btn { border: 1.5px solid var(--p-border); background: #fff; border-radius: var(--p-radius-sm); padding: 6px 14px; font-family: inherit; font-weight: 600; cursor: pointer; font-size: .82rem; }
    .cur-edit-row { display: none; }
    .cur-edit-row.open { display: table-row; }
    .cur-edit-row td { background: var(--p-bg-soft, #f7f7fb); }
    @media (max-width: 992px) { .cur-form { grid-template-columns: 1fr 1fr; } }
</style>
@endpush

@section('content')

    @if ($errors->any())
        <div class="auth-errors" style="background:#fdecec;border:1px solid #f5c2c2;color:#842029;border-radius:9px;padding:10px 14px;margin-bottom:16px;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="p-card" style="margin-bottom: 18px;">
        <h3 class="p-card__title">{{ textByLanguage('إضافة عملة', 'Add currency') }}</h3>
        <form method="POST" action="{{ route('panel.admin.currencies.store') }}">
            @csrf
            <div class="cur-form">
                <div class="field">
                    <label>{{ textByLanguage('الرمز', 'Code') }}</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="SAR" required>
                </div>
                <div class="field">
                    <label>{{ textByLanguage('الاسم', 'Name') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Saudi Riyal" required>
                </div>
                <div class="field">
                    <label>{{ textByLanguage('العلامة', 'Symbol') }}</label>
                    <input type="text" name="symbol" value="{{ old('symbol') }}" placeholder="ر.س">
                </div>
                <div class="field">
                    <label>{{ textByLanguage('الخانات العشرية', 'Decimals') }}</label>
                    <input type="number" name="decimals" value="{{ old('decimals', 2) }}" min="0" max="4">
                </div>
                <div class="field">
                    <label>{{ textByLanguage('سعر الصرف', 'Exchange rate') }}</label>
                    <input type="number" step="0.000001" name="exchange_rate" value="{{ old('exchange_rate', 1) }}" min="0">
                </div>
                <button type="submit" class="cur-add-btn">{{ textByLanguage('إضافة', 'Add') }}</button>
            </div>
            <label class="cur-check" style="margin-top:14px;">
                <input type="checkbox" name="is_default" value="1" @checked(old('is_default'))>
                {{ textByLanguage('تعيينها كعملة افتراضية', 'Set as default currency') }}
            </label>
        </form>
    </div>

    <div class="p-card">
        <h3 class="p-card__title">{{ textByLanguage('العملات المدعومة', 'Supported currencies') }}</h3>
        <table class="cur-table">
            <thead>
                <tr>
                    <th>{{ textByLanguage('الرمز', 'Code') }}</th>
                    <th>{{ textByLanguage('الاسم', 'Name') }}</th>
                    <th>{{ textByLanguage('العلامة', 'Symbol') }}</th>
                    <th>{{ textByLanguage('سعر الصرف', 'Rate') }}</th>
                    <th>{{ textByLanguage('الحالة', 'Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($currencies as $currency)
                    <tr>
                        <td>
                            <strong>{{ $currency->code }}</strong>
                            @if($currency->is_default)
                                <span class="pill pill-default">{{ textByLanguage('افتراضية', 'Default') }}</span>
                            @endif
                        </td>
                        <td>{{ $currency->name }}</td>
                        <td>{{ $currency->symbol }}</td>
                        <td>{{ rtrim(rtrim(number_format($currency->exchange_rate, 6), '0'), '.') }}</td>
                        <td>
                            <span class="pill {{ $currency->is_active ? 'pill-on' : 'pill-off' }}">
                                {{ $currency->is_active ? textByLanguage('مفعّلة', 'Active') : textByLanguage('معطّلة', 'Inactive') }}
                            </span>
                        </td>
                        <td style="display:flex; gap:8px;">
                            <button type="button" class="toggle-btn" onclick="document.getElementById('cur-edit-{{ $currency->id }}').classList.toggle('open')">
                                {{ textByLanguage('تعديل', 'Edit') }}
                            </button>
                            <form method="POST" action="{{ route('panel.admin.currencies.toggle', $currency->id) }}">
                                @csrf
                                <button type="submit" class="toggle-btn">
                                    {{ $currency->is_active ? textByLanguage('تعطيل', 'Disable') : textByLanguage('تفعيل', 'Enable') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    <tr id="cur-edit-{{ $currency->id }}" class="cur-edit-row">
                        <td colspan="6">
                            <form method="POST" action="{{ route('panel.admin.currencies.update', $currency->id) }}" class="cur-form">
                                @csrf
                                @method('PUT')
                                <div class="field">
                                    <label>{{ textByLanguage('الاسم', 'Name') }}</label>
                                    <input type="text" name="name" value="{{ $currency->name }}" required>
                                </div>
                                <div class="field">
                                    <label>{{ textByLanguage('العلامة', 'Symbol') }}</label>
                                    <input type="text" name="symbol" value="{{ $currency->symbol }}">
                                </div>
                                <div class="field">
                                    <label>{{ textByLanguage('الخانات العشرية', 'Decimals') }}</label>
                                    <input type="number" name="decimals" value="{{ $currency->decimals }}" min="0" max="4">
                                </div>
                                <div class="field">
                                    <label>{{ textByLanguage('سعر الصرف', 'Exchange rate') }}</label>
                                    <input type="number" step="0.000001" name="exchange_rate" value="{{ rtrim(rtrim(number_format($currency->exchange_rate, 6, '.', ''), '0'), '.') }}" min="0" {{ $currency->is_default ? 'readonly' : '' }}>
                                </div>
                                <div class="field cur-check" style="align-self:center;">
                                    <input type="checkbox" name="is_default" value="1" @checked($currency->is_default) {{ $currency->is_default ? 'disabled' : '' }}>
                                    <span>{{ textByLanguage('افتراضية', 'Default') }}</span>
                                </div>
                                <button type="submit" class="cur-add-btn">{{ textByLanguage('حفظ', 'Save') }}</button>
                            </form>
                            @if($currency->is_default)
                                <p style="margin:8px 0 0;color:var(--p-text-muted);font-size:.8rem;">{{ textByLanguage('العملة الافتراضية سعر صرفها ثابت = 1', 'The default currency rate is fixed at 1') }}</p>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="color:var(--p-text-muted);">{{ textByLanguage('لا توجد عملات بعد', 'No currencies yet') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
