@extends('panel.layouts.master')

@section('title', textByLanguage('المدن', 'Cities'))
@section('page-title', textByLanguage('المدن', 'Cities'))

@php $r = fn ($n) => "panel.admin.{$n}"; @endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('مدن الرحلات الثابتة', 'Fixed-trip cities')"
        :subtitle="textByLanguage('مدن هذه الدولة المستخدمة في مسارات السفر بين المدن', 'This country\'s cities used for intercity travel routes')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    <div class="p-card" style="margin-bottom:16px;">
        <form method="POST" action="{{ route($r('cities.store')) }}" style="display:grid; grid-template-columns:1fr 1fr auto; gap:12px; align-items:end;">
            @csrf
            <div><label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;">{{ textByLanguage('الاسم', 'Name') }}</label>
                <input name="name" required style="width:100%;padding:9px 11px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;">{{ textByLanguage('الاسم بالإنجليزية (خرائط جوجل)', 'Latin name (Google Maps)') }}</label>
                <input name="name_on_google_map" style="width:100%;padding:9px 11px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-plus-lg"></i> {{ textByLanguage('إضافة', 'Add') }}</button>
        </form>
    </div>

    <div class="p-card" style="margin-bottom:16px;">
        <h3 class="p-card__title" style="margin:0 0 4px;"><i class="bi bi-magic"></i> {{ textByLanguage('استيراد المحافظات دفعة واحدة', 'Bulk import provinces') }}</h3>
        <p style="margin:0 0 12px;font-size:.82rem;color:var(--p-text-muted);">{{ textByLanguage('عبّئ محافظات هذه الدولة تلقائياً من القائمة المدمجة، أو الصق قائمة — محافظة في كل سطر، ويمكن "العربية | English".', 'Fill this country\'s provinces from the built-in list, or paste your own — one per line, optionally "Arabic | English".') }}</p>
        <form method="POST" action="{{ route($r('cities.import')) }}" style="margin-bottom:12px;">
            @csrf
            <input type="hidden" name="use_bundled" value="1">
            <button type="submit" class="p-btn p-btn--soft"><i class="bi bi-stars"></i> {{ textByLanguage('تعبئة من القائمة المدمجة', 'Fill from built-in list') }}</button>
        </form>
        <form method="POST" action="{{ route($r('cities.import')) }}">
            @csrf
            <textarea name="provinces" rows="5" placeholder="{{ textByLanguage('دمشق | Damascus&#10;حلب | Aleppo', 'Riyadh&#10;Makkah | Makkah') }}" style="width:100%;padding:10px 12px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);font-family:inherit;"></textarea>
            <div style="margin-top:10px;"><button type="submit" class="p-btn p-btn--primary"><i class="bi bi-upload"></i> {{ textByLanguage('استيراد القائمة', 'Import list') }}</button></div>
        </form>
    </div>

    <div class="p-card">
        @if($cities->count())
            <x-panel.table :headers="['#', textByLanguage('الاسم', 'Name'), textByLanguage('الإنجليزية', 'Latin'), '']">
                @foreach($cities as $c)
                    <tr>
                        <td>{{ $c->id }}</td>
                        <td><strong>{{ $c->name }}</strong></td>
                        <td>{{ $c->name_on_google_map ?: '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route($r('cities.delete'), $c->id) }}" onsubmit="return confirm('{{ textByLanguage('حذف المدينة؟', 'Delete city?') }}');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-btn p-btn--soft" style="color:var(--p-danger);"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-geo-alt"></i> {{ textByLanguage('لا توجد مدن', 'No cities') }}</p>
        @endif
    </div>

@endsection
