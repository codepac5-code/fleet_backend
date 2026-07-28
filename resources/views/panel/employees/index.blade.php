@extends('panel.layouts.master')

@section('title', __('messages.employees'))
@section('page-title', __('messages.employees'))

@php $r = fn ($name) => "panel.{$entity}.{$name}"; @endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar :title="__('messages.employees')"
        :subtitle="textByLanguage('إدارة فريق العمل والصلاحيات', 'Manage team members and permissions')">
        <x-slot:actions>
            @if(shardIsAll())
                <span class="p-btn p-btn--ghost"><i class="bi bi-plus-lg"></i> {{ textByLanguage('إضافة موظف في', 'Add employee in') }}</span>
                @foreach(($panelCountries ?? []) as $pc)
                    <a href="{{ route($r('employee.create')) }}?country={{ $pc->id }}" class="p-btn p-btn--primary" style="margin-inline-start:6px;">{{ $pc->country_code ?: $pc->name }}</a>
                @endforeach
            @else
                <a href="{{ route($r('employee.create')) }}" class="p-btn p-btn--primary">
                    <i class="bi bi-plus-lg"></i> {{ textByLanguage('إضافة موظف', 'Add employee') }}
                </a>
            @endif
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-card">
        <form method="GET" action="{{ route($r('employee.index')) }}" class="p-search">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="{{ $search }}"
                placeholder="{{ textByLanguage('ابحث بالاسم أو البريد أو الهاتف', 'Search by name, email or phone') }}">
            @if($isAdmin && !empty($officeOptions))
                <select name="office" onchange="this.form.submit()" class="p-search__select">
                    <option value="">{{ textByLanguage('كل المكاتب', 'All offices') }}</option>
                    @foreach($officeOptions as $id => $name)
                        <option value="{{ $id }}" @selected($officeFilter == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            @endif
            @if($search || $officeFilter)
                <a href="{{ route($r('employee.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>
            @endif
            <button type="submit" class="p-btn p-btn--ghost">{{ textByLanguage('بحث', 'Search') }}</button>
        </form>

        @if($employees->count())
            <x-panel.table :headers="array_filter([
                shardIsAll() ? textByLanguage('الدولة', 'Country') : null,
                textByLanguage('الموظف', 'Employee'),
                textByLanguage('التواصل', 'Contact'),
                textByLanguage('المسمى', 'Job'),
                $isAdmin ? textByLanguage('المكتب', 'Office') : null,
                textByLanguage('الحالة', 'Status'),
                '',
            ], fn($h) => $h !== null)">
                @foreach($employees as $emp)
                    <tr>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($emp) ?: '—' }}</x-panel.badge></td>@endif
                        <td>
                            <div class="p-cell-main">
                                <span class="p-avatar">{{ mb_substr($emp->firstName ?: '؟', 0, 1) }}</span>
                                <div>
                                    <strong>{{ trim($emp->firstName.' '.$emp->lastName) }}</strong>
                                    <span class="p-cell-sub">#{{ $emp->id }} · {{ $emp->role }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $emp->email }}</div>
                            <span class="p-cell-sub">{{ $emp->phoneNumber ?: '—' }}</span>
                        </td>
                        <td>{{ app()->getLocale() === 'ar' ? ($emp->employeeJobName_ar ?: $emp->employeeJobName_en) : ($emp->employeeJobName_en ?: $emp->employeeJobName_ar) }}</td>
                        @if($isAdmin)
                            <td>{{ $officeOptions[$emp->officeId] ?? '—' }}</td>
                        @endif
                        <td>
                            <x-panel.badge :tone="$emp->isActive ? 'success' : 'danger'">
                                {{ $emp->isActive ? textByLanguage('مفعّل', 'Active') : textByLanguage('معطّل', 'Inactive') }}
                            </x-panel.badge>
                        </td>
                        <td>
                            <div class="p-row-actions">
                                <a href="{{ shardLink($r('employee.permissions.edit'), $emp->id, $emp) }}" class="p-icon-btn" title="{{ textByLanguage('الصلاحيات', 'Permissions') }}">
                                    <i class="bi bi-shield-lock"></i>
                                </a>
                                <a href="{{ shardLink($r('employee.edit'), $emp->id, $emp) }}" class="p-icon-btn" title="{{ textByLanguage('تعديل', 'Edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route($r('employee.toggle'), $emp->id) }}">
                                    @csrf
                                    @if(shardOf($emp))<input type="hidden" name="country" value="{{ shardOf($emp) }}">@endif
                                    <button type="submit" class="p-icon-btn" title="{{ $emp->isActive ? textByLanguage('تعطيل', 'Disable') : textByLanguage('تفعيل', 'Enable') }}">
                                        <i class="bi {{ $emp->isActive ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route($r('employee.destroy'), $emp->id) }}"
                                    onsubmit="return confirm('{{ textByLanguage('حذف هذا الموظف؟', 'Delete this employee?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    @if(shardOf($emp))<input type="hidden" name="country" value="{{ shardOf($emp) }}">@endif
                                    <button type="submit" class="p-icon-btn p-icon-btn--danger" title="{{ textByLanguage('حذف', 'Delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>

            @if($employees->hasPages())
                <div class="p-pagination">
                    <a class="p-page {{ $employees->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $employees->previousPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></a>
                    <span class="p-page-info">{{ $employees->currentPage() }} / {{ $employees->lastPage() }}</span>
                    <a class="p-page {{ ! $employees->hasMorePages() ? 'is-disabled' : '' }}" href="{{ $employees->nextPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></a>
                </div>
            @endif
        @else
            <p class="p-empty">
                <i class="bi bi-people"></i>
                {{ ($search || $officeFilter) ? textByLanguage('لا توجد نتائج مطابقة', 'No matching results') : textByLanguage('لا يوجد موظفون بعد', 'No employees yet') }}
            </p>
        @endif
    </div>

@endsection
