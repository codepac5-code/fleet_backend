@extends('panel.layouts.master')

@php use App\Http\Services\Panel\Drivers\Logic\DocumentStatus; @endphp

@section('title', textByLanguage('وثائق المكتب', 'Office documents'))
@section('page-title', textByLanguage('وثائق المكتب', 'Office documents'))

@php
    $ar = app()->getLocale() === 'ar';
    $t = fn ($en, $arText) => $ar ? $arText : $en;
@endphp

@section('content')

    <x-panel.page-toolbar :title="textByLanguage('توثيق المكتب (KYC)', 'Office KYC') . ' — ' . $office->officeName" :subtitle="textByLanguage('رفع وتوثيق وثائق المكتب', 'Upload and verify office documents')">
        <x-slot:actions>
            <a href="{{ route('panel.admin.office.show', $office->id) }}" class="p-btn p-btn--ghost"><i class="bi bi-arrow-{{ $ar ? 'right' : 'left' }}"></i> {{ $t('Back', 'رجوع') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    <div class="p-card" style="margin-bottom:16px;">
        <form method="POST" action="{{ route('panel.admin.office.documents.store', $office->id) }}" enctype="multipart/form-data" style="display:grid; grid-template-columns:1fr 1fr auto auto; gap:12px; align-items:end;">
            @csrf
            <div><label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;">{{ $t('Document name', 'اسم المستند') }}</label>
                <input name="name" required placeholder="{{ $t('Trade license', 'رخصة تجارية') }}" style="width:100%;padding:9px 11px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;">{{ $t('File', 'الملف') }}</label>
                <input type="file" name="file" required style="width:100%;"></div>
            <div><label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;">{{ $t('Expires', 'الانتهاء') }}</label>
                <input type="date" name="expires_at" style="padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-upload"></i> {{ $t('Upload', 'رفع') }}</button>
        </form>
    </div>

    <div class="p-card">
        @if($documents->count())
            <x-panel.table :headers="[$t('Name', 'الاسم'), $t('Status', 'الحالة'), $t('Expires', 'الانتهاء'), $t('Note', 'ملاحظة'), '']">
                @foreach($documents as $doc)
                    <tr>
                        <td><a href="{{ asset('storage/' . $doc->file) }}" target="_blank" rel="noopener"><i class="bi bi-paperclip"></i> {{ $doc->name }}</a></td>
                        <td><x-panel.badge :tone="DocumentStatus::tone($doc->status)">{{ DocumentStatus::label($doc->status) }}</x-panel.badge></td>
                        <td>{{ $doc->expires_at ? \Illuminate\Support\Carbon::parse($doc->expires_at)->format('Y-m-d') : '—' }}</td>
                        <td style="max-width:220px; color:var(--p-text-muted); font-size:.82rem;">{{ $doc->note ?: '—' }}</td>
                        <td style="white-space:nowrap;">
                            <form method="POST" action="{{ route('panel.admin.office.documents.status', [$office->id, $doc->id]) }}" style="display:inline-flex; gap:6px; align-items:center;">
                                @csrf @method('PUT')
                                <input name="note" placeholder="{{ $t('note', 'ملاحظة') }}" value="{{ $doc->note }}" style="padding:6px 8px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);font-size:.8rem;width:120px;">
                                <select name="status" onchange="this.form.submit()" class="p-search__select">
                                    @foreach($statusOptions as $s)
                                        <option value="{{ $s }}" @selected($doc->status === $s)>{{ DocumentStatus::label($s) }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <form method="POST" action="{{ route('panel.admin.office.documents.destroy', [$office->id, $doc->id]) }}" style="display:inline;" onsubmit="return confirm('{{ $t('Delete document?', 'حذف المستند؟') }}');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-btn p-btn--soft" style="color:var(--p-danger);"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-file-earmark-text"></i> {{ $t('No documents yet', 'لا توجد وثائق بعد') }}</p>
        @endif
    </div>

@endsection
