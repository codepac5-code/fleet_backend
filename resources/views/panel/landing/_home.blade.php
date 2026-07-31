@php
    $arLoc = app()->getLocale() === 'ar';
    $heroTitle = \App\Models\SiteSetting::val($arLoc ? 'hero_title_ar' : 'hero_title_en');
    $heroSub = \App\Models\SiteSetting::val($arLoc ? 'hero_sub_ar' : 'hero_sub_en');
@endphp
<section id="page-home" class="page active">

    <!-- HERO -->
    <div class="hero">
        <div class="hero-bg"></div>
        <div class="wrap hero-grid">
            <div class="reveal">
                <span class="eyebrow"><i class="fa-solid fa-bolt"></i> {{ $t('The mobility marketplace', 'سوق التنقّل') }}</span>
                <h1>@if($heroTitle){{ $heroTitle }}@else{{ $t('Launch your own', 'أطلق مكتب') }} <span class="hl">{{ $t('taxi office', 'أجرة') }}</span> {{ $t('in the cloud', 'خاصّاً بك في السحابة') }}@endif</h1>
                <p class="lead">{{ $heroSub ?: $t('FleetOS gives you a branded office, rider & driver apps, live dispatch, wallets and analytics — riders choose you in a shared marketplace by rating and price. Go live in minutes, not months.', 'يمنحك FleetOS مكتباً باسمك، وتطبيقَي راكب وسائق، وإسناداً حيّاً، ومحافظ وتحليلات — والركّاب يختارونك في سوق مشترك بالتقييم والسعر. انطلق خلال دقائق لا شهور.') }}</p>
                <div class="hero-cta">
                    <button class="btn btn-primary" onclick="showPage('offices')"><i class="fa-solid fa-rocket"></i> {{ $t('Launch an office', 'أطلق مكتباً') }}</button>
                    <button class="btn btn-ghost" onclick="showPage('drivers')"><i class="fa-solid fa-id-card"></i> {{ $t('Apply as a driver', 'تقدّم كسائق') }}</button>
                </div>
                <div class="trust">
                    <span><i class="fa-solid fa-check"></i>{{ $t('No code, no servers', 'بلا برمجة أو خوادم') }}</span>
                    <span><i class="fa-solid fa-check"></i>{{ $t('Shared rider demand', 'طلب ركّاب مشترك') }}</span>
                    <span><i class="fa-solid fa-check"></i>{{ $t('Your brand, your pricing', 'علامتك وأسعارك') }}</span>
                </div>
            </div>

            <div class="reveal phone-wrap">
                <div class="blob"></div>
                <div class="float-badge fb-1"><i class="fa-solid fa-star"></i> {{ $t('4.9 rider rating', 'تقييم ٤٫٩') }}</div>
                <div class="float-badge fb-2"><i class="fa-solid fa-bolt"></i> {{ $t('Live dispatch', 'إسناد حيّ') }}</div>
                <div class="phone">
                    <div class="screen">
                        <div class="ph-top">
                            <small>{{ $t('Choose your office', 'اختر مكتبك') }}</small>
                            <h5>{{ $t('Nearby offices', 'مكاتب قريبة') }}</h5>
                        </div>
                        <div class="ph-body">
                            <div class="ph-off"><div><div class="nm">{{ $t('Local Fleet', 'أسطول محلّي') }}</div><div class="st">★★★★☆ 4.7</div></div><div class="pr">$12.5</div></div>
                            <div class="ph-off"><div><div class="nm">{{ $t('City Cabs', 'كابات المدينة') }}</div><div class="st">★★★★☆ 4.6</div></div><div class="pr">$14.0</div></div>
                            <div class="ph-off"><div><div class="nm">{{ $t('Premium Ride', 'رحلة مميّزة') }}</div><div class="st">★★★★★ 5.0</div></div><div class="pr">$18.0</div></div>
                            <div class="ph-cta">{{ $t('Request a ride', 'اطلب رحلة') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- STRIP -->
    <div class="wrap" style="margin-top:1rem">
        <div class="strip reveal">
            <div class="s"><b>$<span data-count="25">0</span></b><span>{{ $t('Start / month', 'ابدأ / شهر') }}</span></div>
            <div class="s"><b><span data-count="11">0</span>–<span data-count="18">0</span>%</b><span>{{ $t('Commission by plan', 'عمولة حسب الخطّة') }}</span></div>
            <div class="s"><b><span data-count="3">0</span></b><span>{{ $t('Apps: rider·office·driver', 'تطبيقات: راكب·مكتب·سائق') }}</span></div>
            <div class="s"><b>&lt;<span data-count="10">0</span>{{ $t(' min', ' د') }}</b><span>{{ $t('To go live', 'حتى الانطلاق') }}</span></div>
        </div>
    </div>

    <!-- HOW -->
    <div class="pad">
        <div class="wrap">
            <div class="center reveal" style="margin-bottom:2.6rem">
                <span class="eyebrow">{{ $t('How it works', 'كيف يعمل') }}</span>
                <h2 class="h-sec" style="margin:.8rem 0">{{ $t('From application to live in five steps', 'من الطلب إلى الانطلاق بخمس خطوات') }}</h2>
            </div>
            <div class="steps reveal">
                @php
                    $steps = [
                        ['icones-01.png', $t('Apply', 'تقدّم'), $t('Submit your office or driver application.', 'أرسل طلب مكتبك أو طلبك كسائق.')],
                        ['icones-02.png', $t('Get approved', 'اعتماد'), $t('We review your documents and activate you.', 'نراجع وثائقك ونفعّل حسابك.')],
                        ['icones-03.png', $t('Set up', 'تهيئة'), $t('Add drivers, pricing and coverage.', 'أضف السائقين والأسعار والتغطية.')],
                        ['icones-04-1.png', $t('Go live', 'انطلاق'), $t('Appear in the rider marketplace.', 'اظهر في سوق الركّاب.')],
                        ['icones-05.png', $t('Grow', 'نموّ'), $t('Earn, settle and scale with analytics.', 'اربح، سوِّ، ونمِّ مع التحليلات.')],
                    ];
                @endphp
                @foreach ($steps as $i => $s)
                    <div class="step"><span class="n">{{ $i + 1 }}</span><img src="{{ $img($s[0]) }}" alt=""><h4>{{ $s[1] }}</h4><p>{{ $s[2] }}</p></div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- WHY -->
    <div class="pad" style="background:#fff">
        <div class="wrap">
            <div class="reveal" style="margin-bottom:2.6rem">
                <span class="eyebrow">{{ $t('Why FleetOS', 'لماذا FleetOS') }}</span>
                <h2 class="h-sec" style="margin:.8rem 0">{{ $t('Own the brand. We run the tech.', 'امتلك العلامة. نحن ندير التقنية.') }}</h2>
            </div>
            <div class="grid-4 reveal">
                <div class="card"><div class="ic o"><i class="fa-solid fa-layer-group"></i></div><h3>{{ $t('Shared demand', 'طلب مشترك') }}</h3><p>{{ $t('Tap into a marketplace of riders instead of building demand alone.', 'استفد من سوق ركّاب بدل بناء الطلب وحدك.') }}</p></div>
                <div class="card"><div class="ic b"><i class="fa-solid fa-sliders"></i></div><h3>{{ $t('Full control', 'تحكّم كامل') }}</h3><p>{{ $t('Set your pricing, coverage and driver roster from one dashboard.', 'حدّد أسعارك وتغطيتك وسائقيك من لوحة واحدة.') }}</p></div>
                <div class="card"><div class="ic p"><i class="fa-solid fa-shield-halved"></i></div><h3>{{ $t('Trust & safety', 'الثقة والأمان') }}</h3><p>{{ $t('Verified drivers, dual ratings and secure escrow payments.', 'سائقون موثّقون، تقييم ثنائيّ، ومدفوعات ضمان آمنة.') }}</p></div>
                <div class="card"><div class="ic g"><i class="fa-solid fa-chart-line"></i></div><h3>{{ $t('Grow with data', 'انمُ بالبيانات') }}</h3><p>{{ $t('Live reports on revenue, commission and driver earnings.', 'تقارير حيّة عن الإيراد والعمولة وأرباح السائقين.') }}</p></div>
            </div>
        </div>
    </div>

    <!-- APP SHOWCASE -->
    <div class="pad showcase">
        <div class="showcase-bg"></div>
        <div class="wrap" style="position:relative; z-index:1">
            <div class="center reveal" style="margin-bottom:1.4rem">
                <span class="eyebrow"><i class="fa-solid fa-mobile-screen-button"></i> {{ $t('The rider app', 'تطبيق الراكب') }}</span>
                <h2 class="h-sec" style="margin:.8rem 0">{{ $t('A rider experience your brand can be proud of', 'تجربة راكب تفخر بها علامتك') }}</h2>
                <p class="sub-sec">{{ $t('Real screens from the Fleet Ride app — pick an office, set your route, track live, and rate the trip.', 'شاشات حقيقية من تطبيق Fleet Ride — اختر مكتباً، حدّد مسارك، تتبّع الرحلة حيّاً، وقيّمها.') }}</p>
            </div>
            <div class="shots reveal">
                @php
                    $shots = [
                        ['04-home', $t('Marketplace', 'السوق')],
                        ['07-office', $t('Pick an office', 'اختر مكتباً')],
                        ['05-route', $t('Set your route', 'حدّد مسارك')],
                        ['11-arriving', $t('Live tracking', 'تتبّع حيّ')],
                        ['16-rating', $t('Rate the trip', 'قيّم الرحلة')],
                    ];
                    $suffix = $arLoc ? '-ar' : '';
                @endphp
                @foreach($shots as $i => $sh)
                    <div class="device {{ in_array($i, [1, 3]) ? 'raise' : '' }}">
                        <img src="{{ asset('assets/img/app-shots/' . $sh[0] . $suffix . '.png') }}" alt="{{ $sh[1] }}" loading="lazy">
                        <span class="cap">{{ $sh[1] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="center" style="margin-top:3.2rem">
                <button class="btn btn-primary" onclick="showPage('offices')"><i class="fa-solid fa-rocket"></i> {{ $t('Launch your office', 'أطلق مكتبك') }}</button>
            </div>
        </div>
    </div>

    <!-- TESTIMONIALS -->
    <div class="pad" style="background:#fff">
        <div class="wrap">
            <div class="center reveal" style="margin-bottom:2.6rem">
                <span class="eyebrow">{{ $t('Loved by operators', 'محبوب من المشغّلين') }}</span>
                <h2 class="h-sec" style="margin:.8rem 0">{{ $t('Offices growing with FleetOS', 'مكاتب تنمو مع FleetOS') }}</h2>
            </div>
            <div class="grid-3 reveal">
                @php
                    $quotes = [
                        [$t('We launched in a weekend and had rides the same week. The dashboard runs the whole operation.', 'أطلقنا خلال عطلة نهاية أسبوع ووصلتنا رحلات في الأسبوع نفسه. اللوحة تدير العمليّة كلّها.'), 'R', $t('Rami · Local Fleet', 'رامي · أسطول محلّي')],
                        [$t('Lower commission as we grew, instant payouts for drivers, and a marketplace that brings riders to us.', 'عمولة أقلّ كلّما نمونا، وسحوبات فوريّة للسائقين، وسوق يجلب الركّاب إلينا.'), 'S', $t('Sara · City Cabs', 'سارة · كابات المدينة')],
                        [$t('The safety and rating system built real trust with our riders. We kept our brand, they run the tech.', 'نظام الأمان والتقييم بنى ثقةً حقيقيّة مع ركّابنا. احتفظنا بعلامتنا وهم يديرون التقنية.'), 'K', $t('Khaled · Premium Ride', 'خالد · رحلة مميّزة')],
                    ];
                @endphp
                @foreach ($quotes as $q)
                    <div class="quote">
                        <div class="stars">★★★★★</div>
                        <p>“{{ $q[0] }}”</p>
                        <div class="who"><div class="av">{{ $q[1] }}</div><div><b>{{ $q[2] }}</b><span>{{ $t('FleetOS partner', 'شريك FleetOS') }}</span></div></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- CTA BAND -->
    <div class="pad">
        <div class="wrap reveal">
            <div class="band">
                <span class="eyebrow on-dark">{{ $t('Ready when you are', 'جاهزون حين تكون') }}</span>
                <h2 style="margin-top:1rem">{{ $t('Put your brand on the road today', 'ضع علامتك على الطريق اليوم') }}</h2>
                <p>{{ $t('Join the marketplace built for mobility offices and their drivers.', 'انضمّ إلى السوق المبنيّ لمكاتب التنقّل وسائقيها.') }}</p>
                <div style="display:flex; gap:.9rem; justify-content:center; flex-wrap:wrap; margin-top:1.6rem">
                    <button class="btn btn-primary" onclick="showPage('offices')"><i class="fa-solid fa-building"></i> {{ $t('Launch an office', 'أطلق مكتباً') }}</button>
                    <button class="btn btn-light" onclick="showPage('drivers')"><i class="fa-solid fa-car"></i> {{ $t('Become a driver', 'كن سائقاً') }}</button>
                </div>
            </div>
        </div>
    </div>
</section>
