@extends('panel.layouts.master')

@section('title', textByLanguage('قوالب الإشعارات', 'Notification templates'))
@section('page-title', textByLanguage('قوالب الإشعارات', 'Notification templates'))

@php $r = fn ($name) => "panel.admin.{$name}"; @endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('قوالب الإشعارات والرسائل', 'Notification & message templates')"
        :subtitle="textByLanguage('نصوص الإشعارات (ثنائية اللغة) — القيمة المحفوظة تتجاوز النص المدمج', 'Bilingual notification text — a saved version overrides the built-in default')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif

    <div class="p-card">
        <x-panel.table :headers="[
            textByLanguage('المفتاح', 'Key'),
            textByLanguage('العنوان', 'Subject'),
            textByLanguage('القنوات', 'Channels'),
            textByLanguage('الحالة', 'State'),
            '',
        ]">
            @foreach($templates as $t)
                <tr>
                    <td><code>{{ $t['key'] }}</code></td>
                    <td>{{ $t['subject'] }}</td>
                    <td>@foreach($t['channels'] as $ch)<span class="p-badge p-badge--gray">{{ $ch }}</span> @endforeach</td>
                    <td>
                        @if($t['overridden'])
                            <x-panel.badge :tone="$t['is_active'] ? 'success' : 'gray'">{{ $t['is_active'] ? textByLanguage('مُخصّص مفعّل', 'Custom (active)') : textByLanguage('مُخصّص موقوف', 'Custom (off)') }}</x-panel.badge>
                        @else
                            <x-panel.badge tone="primary">{{ textByLanguage('افتراضي', 'Default') }}</x-panel.badge>
                        @endif
                    </td>
                    <td><a href="{{ route($r('notification-templates.edit'), $t['key']) }}" class="p-btn p-btn--soft"><i class="bi bi-pencil"></i> {{ textByLanguage('تعديل', 'Edit') }}</a></td>
                </tr>
            @endforeach
        </x-panel.table>
    </div>

@endsection
