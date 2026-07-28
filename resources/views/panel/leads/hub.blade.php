@extends('panel.layouts.master')

@section('title', textByLanguage('طلبات الموقع', 'Website leads'))
@section('page-title', textByLanguage('طلبات الموقع', 'Website leads'))

@php
    $cards = [
        [
            'title' => textByLanguage('طلبات السائقين', 'Driver applications'),
            'desc' => textByLanguage('سائقون يتقدّمون للانضمام عبر نموذج الموقع.', 'Drivers applying to join through the website form.'),
            'icon' => 'bi-person-badge', 'accent' => 'indigo',
            'pending' => $pending['drivers'], 'total' => $totals['drivers'],
            'route' => route('panel.admin.leads.drivers'),
        ],
        [
            'title' => textByLanguage('طلبات المكاتب', 'Office requests'),
            'desc' => textByLanguage('أنشطة تطلب إطلاق مكتب أجرة على المنصّة.', 'Businesses requesting to launch a taxi office.'),
            'icon' => 'bi-building-add', 'accent' => 'gold',
            'pending' => $pending['offices'], 'total' => $totals['offices'],
            'route' => route('panel.admin.leads.offices'),
        ],
        [
            'title' => textByLanguage('رسائل التواصل', 'Contact messages'),
            'desc' => textByLanguage('عملاء محتملون من نموذج التواصل في الموقع.', 'Leads from the website contact form.'),
            'icon' => 'bi-headset', 'accent' => 'teal',
            'pending' => $pending['contacts'], 'total' => $totals['contacts'],
            'route' => route('panel.admin.leads.contacts'),
        ],
    ];
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('طلبات الموقع', 'Website submissions')"
        :subtitle="textByLanguage('كل ما يُرسَل من نماذج الموقع، مصنّفاً حسب النوع', 'Everything submitted from the website forms, grouped by type')" />

    <div class="p-lead-grid">
        @foreach($cards as $c)
            <a class="p-lead-card p-lead-card--{{ $c['accent'] }}" href="{{ $c['route'] }}">
                <div class="p-lead-card__top">
                    <span class="p-lead-card__ic"><i class="bi {{ $c['icon'] }}"></i></span>
                    @if($c['pending'] > 0)
                        <span class="p-lead-card__new">{{ $c['pending'] }} {{ textByLanguage('جديد', 'new') }}</span>
                    @endif
                </div>
                <h3 class="p-lead-card__title">{{ $c['title'] }}</h3>
                <p class="p-lead-card__desc">{{ $c['desc'] }}</p>
                <div class="p-lead-card__foot">
                    <span class="p-lead-card__total"><b>{{ $c['total'] }}</b> {{ textByLanguage('الإجمالي', 'total') }}</span>
                    <span class="p-lead-card__go">{{ textByLanguage('استعراض', 'Review') }} <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></span>
                </div>
            </a>
        @endforeach
    </div>

@endsection
