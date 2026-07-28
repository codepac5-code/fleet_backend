@extends('panel.layouts.master')

@section('title', __('messages.users'))
@section('page-title', __('messages.users'))

@php $r = fn ($name) => "panel.{$entity}.{$name}"; @endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar :title="__('messages.users')"
        :subtitle="textByLanguage('إدارة عملاء التطبيق', 'Manage application customers')">
        <x-slot:actions>
            <a href="{{ route($r('user.create')) }}" class="p-btn p-btn--primary">
                <i class="bi bi-plus-lg"></i> {{ textByLanguage('إضافة مستخدم', 'Add user') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-card">
        <form method="GET" action="{{ route($r('user.index')) }}" class="p-search">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="{{ $search }}"
                placeholder="{{ textByLanguage('ابحث بالاسم أو الهاتف أو رمز الإحالة', 'Search by name, phone or referral') }}">
            @if($search)
                <a href="{{ route($r('user.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>
            @endif
            <button type="submit" class="p-btn p-btn--ghost">{{ textByLanguage('بحث', 'Search') }}</button>
        </form>

        @if($users->count())
            <x-panel.table :headers="[
                textByLanguage('المستخدم', 'User'),
                textByLanguage('الهاتف', 'Phone'),
                textByLanguage('المحفظة', 'Wallet'),
                textByLanguage('مسجّل', 'Registered'),
                textByLanguage('الحالة', 'Status'),
                '',
            ]">
                @foreach($users as $u)
                    <tr>
                        <td>
                            <div class="p-cell-main">
                                <span class="p-avatar">{{ mb_substr($u->firstName ?: '؟', 0, 1) }}</span>
                                <div>
                                    <strong>{{ trim($u->firstName.' '.$u->lastName) }}</strong>
                                    <span class="p-cell-sub">#{{ $u->id }} @if($u->referralCode)· {{ $u->referralCode }}@endif</span>
                                </div>
                            </div>
                        </td>
                        <td dir="ltr" style="text-align:start;">{{ trim(($u->dialCode ? '+'.ltrim($u->dialCode,'+').' ' : '').$u->phoneNumber) }}</td>
                        <td>{{ getPriceFormat($u->walletBalance ?? 0) }}</td>
                        <td>
                            <x-panel.badge :tone="$u->is_registered ? 'success' : 'gray'">
                                {{ $u->is_registered ? textByLanguage('نعم', 'Yes') : textByLanguage('لا', 'No') }}
                            </x-panel.badge>
                        </td>
                        <td>
                            <x-panel.badge :tone="$u->isActive ? 'success' : 'danger'">
                                {{ $u->isActive ? textByLanguage('مفعّل', 'Active') : textByLanguage('محظور', 'Blocked') }}
                            </x-panel.badge>
                            @if(! $u->isActive && $u->block_reason)
                                <div class="p-cell-sub" title="{{ $u->block_reason }}" style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <i class="bi bi-info-circle"></i> {{ $u->block_reason }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="p-row-actions">
                                <a href="{{ route($r('user.edit'), $u->id) }}" class="p-icon-btn" title="{{ textByLanguage('تعديل', 'Edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route($r('user.toggle'), $u->id) }}"
                                    @if($u->isActive) onsubmit="var r=prompt('{{ textByLanguage('سبب الحظر (اختياري):', 'Block reason (optional):') }}'); if(r===null)return false; this.reason.value=r;" @endif>
                                    @csrf
                                    <input type="hidden" name="reason" value="">
                                    <button type="submit" class="p-icon-btn" title="{{ $u->isActive ? textByLanguage('حظر', 'Block') : textByLanguage('إعادة تفعيل', 'Reinstate') }}">
                                        <i class="bi {{ $u->isActive ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route($r('user.destroy'), $u->id) }}"
                                    onsubmit="return confirm('{{ textByLanguage('حذف هذا المستخدم؟', 'Delete this user?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-icon-btn p-icon-btn--danger" title="{{ textByLanguage('حذف', 'Delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>

            @if($users->hasPages())
                <div class="p-pagination">
                    <a class="p-page {{ $users->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $users->previousPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></a>
                    <span class="p-page-info">{{ $users->currentPage() }} / {{ $users->lastPage() }}</span>
                    <a class="p-page {{ ! $users->hasMorePages() ? 'is-disabled' : '' }}" href="{{ $users->nextPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></a>
                </div>
            @endif
        @else
            <p class="p-empty">
                <i class="bi bi-people"></i>
                {{ $search ? textByLanguage('لا توجد نتائج مطابقة', 'No matching results') : textByLanguage('لا يوجد مستخدمون بعد', 'No users yet') }}
            </p>
        @endif
    </div>

@endsection
