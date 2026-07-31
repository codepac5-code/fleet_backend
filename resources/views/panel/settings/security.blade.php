@extends('panel.layouts.master')

@section('title', textByLanguage('سياسة الأمان', 'Security policy'))
@section('page-title', textByLanguage('سياسة الأمان', 'Security policy'))

@php
    $r = fn ($n) => "panel.admin.{$n}";
    $options = [
        '' => textByLanguage('اختياري لكل حساب', 'Optional — each account decides'),
        'admin' => textByLanguage('إلزامي على المدراء', 'Mandatory for admins'),
        'all' => textByLanguage('إلزامي على الجميع (مدراء ومكاتب وموظفين)', 'Mandatory for everyone (admins, offices, employees)'),
    ];
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('التحقق بخطوتين للموظفين', 'Staff two-factor authentication')"
        :subtitle="textByLanguage('من يجب عليه تفعيل التحقق بخطوتين، ومن فعّله فعلاً', 'Who must turn two-factor on, and who already has')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif

    <div class="p-card" style="margin-bottom:16px;">
        <form method="POST" action="{{ route($r('settings.security.save')) }}" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
            @csrf
            <div style="flex:1;min-width:280px;">
                <label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('إلزام التحقق بخطوتين', 'Require two-factor') }}</label>
                <select name="requirement" style="width:100%;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);">
                    @foreach($options as $value => $label)<option value="{{ $value }}" @selected($requirement === $value)>{{ $label }}</option>@endforeach
                </select>
            </div>
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ textByLanguage('حفظ', 'Save') }}</button>
        </form>
        <p style="font-size:.82rem;color:var(--p-text-muted);margin:12px 0 0;">
            {{ textByLanguage('الحسابات الملزَمة وغير المفعّلة تُوجَّه إلى صفحة الأمان بعد تسجيل الدخول ولا يمكنها إيقاف التحقق.', 'Accounts that are required but not enrolled are sent to their security page after signing in, and cannot turn it off.') }}
        </p>
    </div>

    <div class="p-card">
        @if($records->count())
            <x-panel.table :headers="[textByLanguage('النوع', 'Guard'), textByLanguage('المعرّف', 'Staff id'), textByLanguage('الدولة', 'Country'), textByLanguage('الحالة', 'Status'), textByLanguage('آخر استخدام', 'Last used'), '']">
                @foreach($records as $record)
                    <tr>
                        <td>{{ $record->guard }}</td>
                        <td class="p-row-id">#{{ $record->staff_id }}</td>
                        <td>{{ $record->country_code ? strtoupper($record->country_code) : '—' }}</td>
                        <td>
                            <x-panel.badge :tone="$record->confirmed_at ? 'success' : 'warning'">
                                {{ $record->confirmed_at ? textByLanguage('مفعّل', 'Enrolled') : textByLanguage('قيد التفعيل', 'Pending') }}
                            </x-panel.badge>
                        </td>
                        <td>{{ $record->last_used_at ? $record->last_used_at->diffForHumans() : '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route($r('settings.security.reset'), $record->id) }}"
                                onsubmit="return confirm('{{ textByLanguage('إعادة تعيين التحقق بخطوتين لهذا الحساب؟', 'Reset two-factor for this account?') }}');">
                                @csrf
                                <button type="submit" class="p-btn p-btn--soft"><i class="bi bi-arrow-counterclockwise"></i> {{ textByLanguage('إعادة تعيين', 'Reset') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-shield-lock"></i> {{ textByLanguage('لا يوجد موظفون فعّلوا التحقق بخطوتين بعد', 'No staff have enrolled yet') }}</p>
        @endif
    </div>

@endsection
