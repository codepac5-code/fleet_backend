@extends('panel.layouts.master')

@section('title', textByLanguage('العمولات', 'Commissions'))
@section('page-title', textByLanguage('العمولات', 'Commissions'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $pct = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.') . '%';
    $driverShare = max(0, $ceiling - $officeRate);
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('كيف تُقسَّم أجرة الرحلة', 'How a fare is divided')"
        :subtitle="textByLanguage('حصّة فليت تُقتطع أولاً، والباقي بينك وبين سائقك', 'FleetOS takes its cut first; the rest is between you and your driver')" />

    <style>
        .cm-bar { display:flex; height:38px; border-radius:10px; overflow:hidden; margin:6px 0 4px; border:1px solid var(--p-border); }
        .cm-bar span { display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:600; color:#fff; white-space:nowrap; }
        .cm-bar .cm-fleet { background:#312873; }
        .cm-bar .cm-office { background:#F8A609; color:#3a2c00; }
        .cm-bar .cm-driver { background:#2f7d4f; }
        .cm-legend { display:flex; gap:18px; flex-wrap:wrap; font-size:13px; }
        .cm-legend i { font-size:10px; margin-inline-end:6px; }
    </style>

    <x-panel.card>
        <div class="cm-bar">
            <span class="cm-fleet" style="width:{{ max($fleetRate, 4) }}%">{{ $pct($fleetRate) }}</span>
            <span class="cm-office" style="width:{{ max($officeRate, $officeRate > 0 ? 4 : 0) }}%">@if($officeRate > 0){{ $pct($officeRate) }}@endif</span>
            <span class="cm-driver" style="width:{{ max($driverShare, 4) }}%">{{ $pct($driverShare) }}</span>
        </div>
        <div class="cm-legend">
            <span><i class="bi bi-circle-fill" style="color:#312873;"></i>{{ textByLanguage('فليت', 'FleetOS') }} — {{ $pct($fleetRate) }}</span>
            <span><i class="bi bi-circle-fill" style="color:#F8A609;"></i>{{ textByLanguage('مكتبك', 'Your office') }} — {{ $pct($officeRate) }}</span>
            <span><i class="bi bi-circle-fill" style="color:#2f7d4f;"></i>{{ textByLanguage('السائق', 'Driver') }} — {{ $pct($driverShare) }}</span>
        </div>
        <p class="p-muted" style="margin-top:12px;">
            {{ textByLanguage(
                'حصّة فليت تحدّدها إدارة المنصّة ولا يمكن تعديلها من هنا.',
                "FleetOS's cut is set by the platform and cannot be changed here."
            ) }}
        </p>
    </x-panel.card>

    <x-panel.card :title="textByLanguage('عمولتك من السائق', 'Your cut from a driver')" style="margin-top:18px;">
        <form method="POST" action="{{ route($r('commission.update')) }}">
            @csrf
            @method('PUT')
            <div class="p-form-grid">
                <div class="p-field">
                    <label for="dcr">{{ textByLanguage('النسبة', 'Rate') }}</label>
                    <div class="p-pct">
                        <input type="number" step="0.01" min="0" max="{{ $ceiling }}" id="dcr" name="driver_commission_rate"
                            value="{{ old('driver_commission_rate', $configured) }}" placeholder="0">
                        <span>%</span>
                    </div>
                    @error('driver_commission_rate')<small class="p-field__error">{{ $message }}</small>@enderror
                    <small class="p-muted">
                        {{ textByLanguage(
                            'من أصل ' . $pct($ceiling) . ' المتبقّية لك ولسائقك. اتركها فارغة لتأخذ لا شيء ويحتفظ السائق بكل الباقي.',
                            'Out of the ' . $pct($ceiling) . ' left to you and your driver. Leave it empty to take nothing and let the driver keep the rest.'
                        ) }}
                    </small>
                </div>
            </div>
            <div class="p-form-actions" style="margin-top:14px;">
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ textByLanguage('حفظ', 'Save') }}</button>
            </div>
        </form>
    </x-panel.card>

    <x-panel.card :title="textByLanguage('سائقوك', 'Your drivers')" style="margin-top:18px;">
        @if(count($drivers))
            <x-panel.table :headers="[
                textByLanguage('السائق', 'Driver'),
                textByLanguage('النسبة المطبَّقة', 'Applied rate'),
                textByLanguage('المصدر', 'Source'),
                '',
            ]">
                @foreach($drivers as $d)
                    <tr>
                        <td><strong>{{ $d['name'] }}</strong><br><small class="p-muted">{{ $d['phone'] }}</small></td>
                        <td>{{ $pct($d['effective']) }}</td>
                        <td>
                            @if($d['override'] !== null)
                                <span class="p-badge">{{ textByLanguage('اتفاق خاص', 'Negotiated') }}</span>
                            @else
                                <span class="p-muted">{{ textByLanguage('عمولة المكتب', 'Office rate') }}</span>
                            @endif
                        </td>
                        <td style="text-align:end;">
                            @if(\Illuminate\Support\Facades\Route::has($r('driver.show')))
                                <a href="{{ route($r('driver.show'), $d['id']) }}" class="p-btn p-btn--ghost p-btn--sm">
                                    <i class="bi bi-pencil"></i> {{ textByLanguage('تعديل', 'Edit') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-person-badge"></i> {{ textByLanguage('لا سائقين بعد', 'No drivers yet') }}</p>
        @endif
    </x-panel.card>

@endsection
