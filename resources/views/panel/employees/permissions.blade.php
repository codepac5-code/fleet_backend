@extends('panel.layouts.master')

@section('title', textByLanguage('صلاحيات الموظف', 'Employee permissions'))
@section('page-title', textByLanguage('صلاحيات الموظف', 'Employee permissions'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    // The employee lives on a specific country shard. When this page was opened
    // for a shard (aggregate "All countries" view passes ?country=<node>), the
    // save MUST target the SAME shard — otherwise ConfigureCountryDatabase falls
    // back to the session shard and writes the permissions to the wrong DB (every
    // shard has an employee id=1), so the change "doesn't stick".
    $shardCountry = request('country') ?: session('active_shard_id');
@endphp

@section('content')

    @php
        $grantedSet = collect($granted)->flip();
        $presetSet = collect($preset ?? [])->flip();
        $totalCount = collect($groups)->sum(fn ($g) => count($g['permissions']));
        $grantedCount = collect($groups)->sum(fn ($g) => collect($g['permissions'])->filter(fn ($p) => $grantedSet->has($p['name']))->count());
    @endphp

    <x-panel.page-toolbar
        :title="trim($employee->firstName.' '.$employee->lastName)"
        :subtitle="textByLanguage('تحديد ما يمكن لهذا الموظف الوصول إليه', 'Control what this employee can access')">
        <x-slot:actions>
            @if(($preset ?? []) !== [])
                <form method="POST" action="{{ route($r('employee.permissions.reset'), $employee->id) }}" style="display:inline;"
                      onsubmit="return confirm('{{ textByLanguage('إعادة الصلاحيات إلى إعدادات الدور؟ سيُلغى أي تخصيص يدوي.', 'Reset permissions to the role defaults? Any manual tweaks are discarded.') }}');">
                    @csrf
                    @if($shardCountry)<input type="hidden" name="country" value="{{ $shardCountry }}">@endif
                    <button type="submit" class="p-btn p-btn--ghost"><i class="bi bi-arrow-counterclockwise"></i> {{ textByLanguage('إعادة لإعدادات الدور', 'Reset to role') }}</button>
                </form>
            @endif
            <a href="{{ route($r('employee.index')) }}" class="p-btn p-btn--ghost">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-card" style="margin-bottom:14px;display:flex;gap:14px;align-items:flex-start;">
        <span class="p-badge p-badge--primary" style="flex:0 0 auto;"><i class="bi bi-person-badge"></i> {{ $roleLabel }}</span>
        <div style="flex:1;">
            <p style="margin:0;font-size:.85rem;">{{ $roleDescription }}</p>
            <p style="margin:6px 0 0;font-size:.8rem;color:var(--p-text-muted);">
                @if($customised)
                    <i class="bi bi-pencil"></i> {{ textByLanguage('الصلاحيات الحالية مخصّصة يدوياً وتختلف عن إعدادات الدور.', 'These permissions have been tuned by hand and differ from the role defaults.') }}
                @else
                    <i class="bi bi-check2-circle"></i> {{ textByLanguage('الصلاحيات مطابقة لإعدادات الدور.', 'Permissions match the role defaults.') }}
                @endif
                {{ textByLanguage('الصلاحيات المعلّمة بنجمة هي جزء من الدور.', 'Starred permissions are part of the role.') }}
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route($r('employee.permissions.update'), $employee->id) }}" id="permForm">
        @csrf
        @method('PUT')
        @if($shardCountry)<input type="hidden" name="country" value="{{ $shardCountry }}">@endif

        <div class="p-card perm-bar">
            <div class="perm-bar__info">
                <i class="bi bi-shield-check"></i>
                <span>{{ textByLanguage('الصلاحيات الممنوحة', 'Granted permissions') }}:
                    <strong id="permCount">{{ $grantedCount }}</strong> / {{ $totalCount }}</span>
            </div>
            <div class="perm-bar__actions">
                <button type="button" class="p-btn p-btn--ghost" data-bulk="all">{{ textByLanguage('تحديد الكل', 'Select all') }}</button>
                <button type="button" class="p-btn p-btn--ghost" data-bulk="none">{{ textByLanguage('إلغاء الكل', 'Clear all') }}</button>
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ textByLanguage('حفظ', 'Save') }}</button>
            </div>
        </div>

        <div class="p-grid p-grid--2" style="align-items: stretch;">
            @foreach($groups as $group)
                <div class="p-card perm-group" data-group>
                    <div class="perm-group__head">
                        <h3 class="p-card__title" style="margin:0;">{{ $group['label'] }}</h3>
                        <label class="perm-group__toggle">
                            <input type="checkbox" data-group-toggle>
                            <span>{{ textByLanguage('الكل', 'All') }}</span>
                        </label>
                    </div>
                    <div class="perm-list">
                        @foreach($group['permissions'] as $perm)
                            <label class="perm-item">
                                <input type="checkbox" name="permissions[]" value="{{ $perm['name'] }}"
                                    data-perm @checked($grantedSet->has($perm['name']))>
                                <span class="perm-item__box"><i class="bi bi-check"></i></span>
                                <span class="perm-item__label">
                                    {{ $perm['label'] }}
                                    @if($presetSet->has($perm['name']))<i class="bi bi-star-fill" style="font-size:.6rem;color:var(--p-accent);" title="{{ textByLanguage('من إعدادات الدور', 'From the role defaults') }}"></i>@endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p-form-actions" style="margin-top:18px;">
            <a href="{{ route($r('employee.index')) }}" class="p-btn p-btn--ghost">{{ textByLanguage('إلغاء', 'Cancel') }}</a>
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ textByLanguage('حفظ الصلاحيات', 'Save permissions') }}</button>
        </div>
    </form>

@endsection

@push('scripts')
<script>
    (function () {
        var form = document.getElementById('permForm');
        if (!form) return;
        var perms = form.querySelectorAll('[data-perm]');
        var countEl = document.getElementById('permCount');

        function refreshCount() {
            var n = 0;
            perms.forEach(function (p) { if (p.checked) n++; });
            if (countEl) countEl.textContent = n;
        }

        function syncGroupToggle(group) {
            var toggle = group.querySelector('[data-group-toggle]');
            var items = group.querySelectorAll('[data-perm]');
            var all = items.length > 0;
            items.forEach(function (i) { if (!i.checked) all = false; });
            if (toggle) toggle.checked = all;
        }

        form.querySelectorAll('[data-group]').forEach(function (group) {
            var toggle = group.querySelector('[data-group-toggle]');
            var items = group.querySelectorAll('[data-perm]');
            if (toggle) {
                toggle.addEventListener('change', function () {
                    items.forEach(function (i) { i.checked = toggle.checked; });
                    refreshCount();
                });
            }
            items.forEach(function (i) {
                i.addEventListener('change', function () { syncGroupToggle(group); refreshCount(); });
            });
            syncGroupToggle(group);
        });

        form.querySelectorAll('[data-bulk]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var on = btn.getAttribute('data-bulk') === 'all';
                perms.forEach(function (p) { p.checked = on; });
                form.querySelectorAll('[data-group-toggle]').forEach(function (t) { t.checked = on; });
                refreshCount();
            });
        });
    })();
</script>
@endpush
