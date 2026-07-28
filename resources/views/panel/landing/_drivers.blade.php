<section id="page-drivers" class="page">
    <div class="phero">
        <div class="wrap in">
            <span class="eyebrow on-dark"><i class="fa-solid fa-car"></i> {{ $t('For drivers', 'للسائقين') }}</span>
            <h1>{{ $t('Drive with FleetOS', 'قُد مع FleetOS') }}</h1>
            <p>{{ $t('Get ride offers from your office, earn to a secure wallet, and cash out instantly. Register your details and documents to get started.', 'استقبل عروض الرحلات من مكتبك، اكسب إلى محفظة آمنة، واسحب فوراً. سجّل بياناتك ووثائقك لتبدأ.') }}</p>
        </div>
    </div>

    <div class="pad">
        <div class="wrap">
            <div class="grid-3 reveal" style="margin-bottom:3rem">
                <div class="card"><div class="ic o"><i class="fa-solid fa-bolt"></i></div><h3>{{ $t('Instant offers', 'عروض فوريّة') }}</h3><p>{{ $t('Accept nearby rides with one tap — first to accept wins.', 'اقبل الرحلات القريبة بلمسة — أوّل من يقبل يفوز.') }}</p></div>
                <div class="card"><div class="ic g"><i class="fa-solid fa-wallet"></i></div><h3>{{ $t('Fast payouts', 'سحوبات سريعة') }}</h3><p>{{ $t('Earnings settle to your wallet; request a payout anytime.', 'تُسوّى أرباحك في محفظتك؛ اطلب السحب في أيّ وقت.') }}</p></div>
                <div class="card"><div class="ic p"><i class="fa-solid fa-shield-halved"></i></div><h3>{{ $t('Backed by your office', 'بدعم مكتبك') }}</h3><p>{{ $t('Work under a trusted local brand with real support.', 'اعمل تحت علامة محلّيّة موثوقة بدعم حقيقيّ.') }}</p></div>
            </div>

            <div style="max-width:860px; margin:0 auto" class="reveal">
                <div class="form-shell">
                    <div class="form-head">
                        <span class="fi" style="background:linear-gradient(135deg,var(--ink),var(--ink-3))"><i class="fa-solid fa-id-card"></i></span>
                        <div><div style="font-weight:800; color:var(--ink)">{{ $t('Driver application', 'طلب سائق') }}</div><div style="font-size:.82rem; color:var(--muted)">{{ $t('Your data is stored securely', 'بياناتك محفوظة بأمان') }}</div></div>
                    </div>
                    <div class="form-body">
                        <form id="driverForm" novalidate>
                            <span class="fstep-tag">{{ $t('1 · Personal & account', '١ · شخصيّ وحساب') }}</span>
                            <div class="fgrid">
                                <div class="field"><label>{{ $t('Full name', 'الاسم الكامل') }} <span class="req">*</span></label><input name="name" required><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Phone number', 'رقم الهاتف') }} <span class="req">*</span></label><input name="phone" required><div class="msg"></div></div>
                                <div class="field col-2"><label>{{ $t('Password (for the driver app)', 'كلمة المرور (لتطبيق السائق)') }} <span class="req">*</span></label><input type="password" name="password" required><div class="msg"></div></div>
                            </div>

                            <span class="fstep-tag">{{ $t('2 · Vehicle', '٢ · المركبة') }}</span>
                            <div class="fgrid">
                                <div class="field"><label>{{ $t('Brand', 'الماركة') }} <span class="req">*</span></label><input name="brand" required><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Model', 'الطراز') }} <span class="req">*</span></label><input name="model" required><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Year', 'السنة') }} <span class="req">*</span></label><input name="year" required><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Color', 'اللون') }} <span class="req">*</span></label><input name="color" required><div class="msg"></div></div>
                                <div class="field col-2"><label>{{ $t('Plate number', 'رقم اللوحة') }} <span class="req">*</span></label><input name="plateNumber" required><div class="msg"></div></div>
                            </div>

                            <span class="fstep-tag">{{ $t('3 · Documents', '٣ · الوثائق') }}</span>
                            <div class="up-grid">
                                @php
                                    $docs = [
                                        ['profileImage', $t('Profile photo', 'صورة شخصيّة'), 'fa-user'],
                                        ['idFrontImage', $t('ID — front', 'الهويّة — أمام'), 'fa-id-card'],
                                        ['idBackImage', $t('ID — back', 'الهويّة — خلف'), 'fa-id-card'],
                                        ['licenseFrontImage', $t('License — front', 'الرخصة — أمام'), 'fa-address-card'],
                                        ['licenseBackImage', $t('License — back', 'الرخصة — خلف'), 'fa-address-card'],
                                        ['mechanicalImage', $t('Mechanical check', 'الفحص الميكانيكيّ'), 'fa-gears'],
                                    ];
                                @endphp
                                @foreach ($docs as $d)
                                    <label class="up"><span class="ok"><i class="fa-solid fa-check"></i></span><input type="file" name="{{ $d[0] }}" accept="image/*" required><span class="ph2"><i class="fa-solid {{ $d[2] }}"></i><span>{{ $d[1] }}</span></span><img class="prev" alt=""></label>
                                @endforeach
                            </div>

                            <span class="fstep-tag">{{ $t('4 · Vehicle photos', '٤ · صور المركبة') }}</span>
                            <div class="up-grid">
                                @php
                                    $photos = [
                                        ['frontCarImage', $t('Front', 'أمام')], ['backCarImage', $t('Back', 'خلف')],
                                        ['rightCarImage', $t('Right', 'يمين')], ['leftCarImage', $t('Left', 'يسار')],
                                        ['insideCarImage', $t('Interior', 'داخل')], ['frontSeatsImage', $t('Front seats', 'المقاعد الأماميّة')],
                                        ['backSeatsImage', $t('Back seats', 'المقاعد الخلفيّة')],
                                    ];
                                @endphp
                                @foreach ($photos as $p)
                                    <label class="up"><span class="ok"><i class="fa-solid fa-check"></i></span><input type="file" name="{{ $p[0] }}" accept="image/*" required><span class="ph2"><i class="fa-solid fa-camera"></i><span>{{ $p[1] }}</span></span><img class="prev" alt=""></label>
                                @endforeach
                            </div>

                            <button type="submit" class="btn btn-dark btn-block" style="margin-top:1.8rem; padding:1.05rem">
                                <span class="lbl">{{ $t('Submit driver application', 'إرسال طلب السائق') }}</span>
                                <i class="fa-solid fa-spinner fa-spin spin" style="display:none"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
