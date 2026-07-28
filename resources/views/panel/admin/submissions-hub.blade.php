@php
    $ar = app()->getLocale() === 'ar';
    $t = fn($en, $arText) => $ar ? $arText : $en;
    $cards = [
        [
            'title' => $t('Driver applications', 'طلبات السائقين'),
            'desc' => $t('Drivers applying to join, with documents & vehicle photos.', 'سائقون يتقدّمون للانضمام، مع الوثائق وصور المركبة.'),
            'icon' => 'fa-id-card', 'grad' => 'linear-gradient(135deg,#312873,#4c3bb3)',
            'pending' => $pending['drivers'], 'total' => $totals['drivers'], 'route' => route('admin.submissions.drivers'),
        ],
        [
            'title' => $t('Office requests', 'طلبات المكاتب'),
            'desc' => $t('Businesses requesting to launch a taxi office.', 'أنشطة تطلب إطلاق مكتب أجرة.'),
            'icon' => 'fa-building', 'grad' => 'linear-gradient(135deg,#F29C0B,#FFB43B)',
            'pending' => $pending['offices'], 'total' => $totals['offices'], 'route' => route('office.requests.index'),
        ],
        [
            'title' => $t('Contact & demo requests', 'طلبات التواصل والعروض'),
            'desc' => $t('Leads from the website contact form.', 'عملاء محتملون من نموذج التواصل في الموقع.'),
            'icon' => 'fa-headset', 'grad' => 'linear-gradient(135deg,#0ea5e9,#38bdf8)',
            'pending' => $pending['contacts'], 'total' => $totals['contacts'], 'route' => route('contact.messages.index'),
        ],
    ];
@endphp
<x-master-layout>
<div class="dash">
    <div class="head">
        <h1>{{ $t('Website submissions', 'طلبات الموقع') }}</h1>
        <p>{{ $t('Everything submitted from the website forms, grouped by type.', 'كلّ ما يُرسَل من نماذج الموقع، مصنّفاً حسب النوع.') }}</p>
    </div>

    <div class="cards">
        @foreach($cards as $c)
            <a class="scard" href="{{ $c['route'] }}">
                <div class="top">
                    <span class="icon" style="background:{{ $c['grad'] }}"><i class="fa-solid {{ $c['icon'] }}"></i></span>
                    @if($c['pending'] > 0)<span class="badge">{{ $c['pending'] }} {{ $t('new', 'جديد') }}</span>@endif
                </div>
                <h3>{{ $c['title'] }}</h3>
                <p>{{ $c['desc'] }}</p>
                <div class="foot">
                    <span class="tot"><b>{{ $c['total'] }}</b> {{ $t('total', 'الإجماليّ') }}</span>
                    <span class="go">{{ $t('Review', 'استعراض') }} <i class="fa-solid {{ $ar ? 'fa-arrow-left' : 'fa-arrow-right' }}"></i></span>
                </div>
            </a>
        @endforeach
    </div>
</div>

<style>
    .dash { max-width: 1100px; margin: auto; padding: 40px 20px; font-family: 'Plus Jakarta Sans','Cairo',sans-serif; }
    .head h1 { font-size: 1.9rem; font-weight: 800; color: #312873; }
    .head p { color: #6b7280; margin-top: .3rem; }
    .cards { display: grid; grid-template-columns: repeat(3,1fr); gap: 1.2rem; margin-top: 1.8rem; }
    .scard { background: #fff; border: 1px solid #eceefb; border-radius: 18px; padding: 1.6rem; box-shadow: 0 10px 30px rgba(49,40,115,.05); transition: .25s; display: block; color: inherit; }
    .scard:hover { transform: translateY(-6px); box-shadow: 0 24px 50px rgba(49,40,115,.12); }
    .top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
    .icon { width: 54px; height: 54px; border-radius: 15px; display: grid; place-items: center; color: #fff; font-size: 1.35rem; box-shadow: 0 10px 22px rgba(49,40,115,.2); }
    .badge { background: #ef4444; color: #fff; font-size: .72rem; font-weight: 800; padding: 4px 10px; border-radius: 999px; }
    .scard h3 { font-size: 1.15rem; font-weight: 800; color: #312873; margin-bottom: .35rem; }
    .scard p { color: #6b7280; font-size: .9rem; line-height: 1.6; min-height: 44px; }
    .foot { display: flex; justify-content: space-between; align-items: center; margin-top: 1.2rem; padding-top: 1rem; border-top: 1px solid #f1f0fb; }
    .tot { color: #6b7280; font-size: .85rem; } .tot b { color: #312873; font-size: 1.1rem; }
    .go { color: #F29C0B; font-weight: 800; font-size: .88rem; display: inline-flex; align-items: center; gap: .4rem; }
    @media (max-width: 820px) { .cards { grid-template-columns: 1fr; } }
</style>
</x-master-layout>
