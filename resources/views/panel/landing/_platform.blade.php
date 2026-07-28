<section id="page-platform" class="page">
    <div class="phero">
        <div class="wrap in">
            <span class="eyebrow on-dark">{{ $t('One platform · three apps', 'منصّة واحدة · ثلاثة تطبيقات') }}</span>
            <h1>{{ $t('Everything the marketplace needs', 'كلّ ما يحتاجه السوق') }}</h1>
            <p>{{ $t('A rider app to book, an office dashboard to run everything, and a driver app to earn — all connected in real time.', 'تطبيق راكب للحجز، لوحة مكتب لإدارة كلّ شيء، وتطبيق سائق للكسب — كلّها متّصلة لحظيّاً.') }}</p>
        </div>
    </div>

    <div class="pad">
        <div class="wrap">
            <div class="grid-3 reveal">
                <div class="card">
                    <div class="ic o"><i class="fa-solid fa-mobile-screen-button"></i></div>
                    <span class="tag">{{ $t('Rider app', 'تطبيق الراكب') }}</span>
                    <h3 style="margin-top:.4rem">Fleet Ride</h3>
                    <ul>
                        <li><i class="fa-solid fa-check"></i>{{ $t('Choose an office by rating & price', 'اختيار المكتب بالتقييم والسعر') }}</li>
                        <li><i class="fa-solid fa-check"></i>{{ $t('Live tracking & secure wallet', 'تتبّع حيّ ومحفظة آمنة') }}</li>
                        <li><i class="fa-solid fa-check"></i>{{ $t('Rate every ride', 'تقييم كلّ رحلة') }}</li>
                    </ul>
                </div>
                <div class="card">
                    <div class="ic p"><i class="fa-solid fa-gauge-high"></i></div>
                    <span class="tag">{{ $t('Office dashboard', 'لوحة المكتب') }}</span>
                    <h3 style="margin-top:.4rem">Fleet Panel</h3>
                    <ul>
                        <li><i class="fa-solid fa-check"></i>{{ $t('Live dispatch board & map', 'لوحة إسناد حيّة وخريطة') }}</li>
                        <li><i class="fa-solid fa-check"></i>{{ $t('Drivers, pricing & subscriptions', 'السائقون والأسعار والاشتراكات') }}</li>
                        <li><i class="fa-solid fa-check"></i>{{ $t('Wallets, payouts & reports', 'المحافظ والسحوبات والتقارير') }}</li>
                    </ul>
                </div>
                <div class="card">
                    <div class="ic g"><i class="fa-solid fa-car"></i></div>
                    <span class="tag">{{ $t('Driver app', 'تطبيق السائق') }}</span>
                    <h3 style="margin-top:.4rem">DriverX</h3>
                    <ul>
                        <li><i class="fa-solid fa-check"></i>{{ $t('Instant ride offers', 'عروض رحلات فوريّة') }}</li>
                        <li><i class="fa-solid fa-check"></i>{{ $t('Earnings & instant payouts', 'أرباح وسحوبات فوريّة') }}</li>
                        <li><i class="fa-solid fa-check"></i>{{ $t('Navigation & presence', 'ملاحة وحضور') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="pad" style="background:#fff">
        <div class="wrap">
            <div class="center reveal" style="margin-bottom:2.4rem">
                <span class="eyebrow">{{ $t('Built for trust', 'مبنيّ على الثقة') }}</span>
                <h2 class="h-sec" style="margin:.8rem 0">{{ $t('Safe on both sides of every ride', 'آمن لطرفَي كلّ رحلة') }}</h2>
            </div>
            <div class="grid-4 reveal">
                <div class="card"><div class="ic p"><i class="fa-solid fa-user-check"></i></div><h3>{{ $t('Verified drivers', 'سائقون موثّقون') }}</h3><p>{{ $t('Every driver is reviewed with ID, license and vehicle documents.', 'كلّ سائق يُراجَع بالهويّة والرخصة ووثائق المركبة.') }}</p></div>
                <div class="card"><div class="ic o"><i class="fa-solid fa-star-half-stroke"></i></div><h3>{{ $t('Dual ratings', 'تقييم ثنائيّ') }}</h3><p>{{ $t('Riders and drivers rate each other after every trip.', 'الركّاب والسائقون يقيّم كلٌّ منهما الآخر بعد كلّ رحلة.') }}</p></div>
                <div class="card"><div class="ic g"><i class="fa-solid fa-lock"></i></div><h3>{{ $t('Escrow payments', 'مدفوعات ضمان') }}</h3><p>{{ $t('Fares are held securely and settled only when the ride completes.', 'تُحجز الأجرة بأمان وتُسوّى فقط عند اكتمال الرحلة.') }}</p></div>
                <div class="card"><div class="ic b"><i class="fa-solid fa-scale-balanced"></i></div><h3>{{ $t('Fair governance', 'حَوكمة عادلة') }}</h3><p>{{ $t('Transparent commissions, audit logs and marketplace rules.', 'عمولات شفّافة وسجلّات تدقيق وقواعد سوق واضحة.') }}</p></div>
            </div>
            <div class="center reveal" style="margin-top:2.4rem">
                <button class="btn btn-primary" onclick="showPage('pricing')"><i class="fa-solid fa-tags"></i> {{ $t('See pricing', 'شاهد الأسعار') }}</button>
            </div>
        </div>
    </div>
</section>
