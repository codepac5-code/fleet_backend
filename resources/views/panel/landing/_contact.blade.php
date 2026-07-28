<section id="page-contact" class="page">
    <div class="phero">
        <div class="wrap in">
            <span class="eyebrow on-dark"><i class="fa-solid fa-headset"></i> {{ $t('Talk to us', 'تواصل معنا') }}</span>
            <h1>{{ $t('Book a demo or ask us anything', 'احجز عرضاً توضيحيّاً أو اسأل') }}</h1>
            <p>{{ $t('Whether you run an existing fleet, are starting fresh, or just exploring — our team will walk you through FleetOS.', 'سواء تدير أسطولاً قائماً، أو تبدأ من الصفر، أو تستكشف فقط — سيشرح لك فريقنا FleetOS.') }}</p>
        </div>
    </div>

    <div class="pad">
        <div class="wrap">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:2.5rem; align-items:start" class="reveal contact-grid">
                <div>
                    <h2 class="h-sec" style="font-size:1.8rem">{{ $t('We reply fast', 'نردّ بسرعة') }}</h2>
                    <p class="sub-sec" style="margin-top:.6rem">{{ $t('Pick a reason, leave your details, and we will get back to you within 24 hours.', 'اختر سبباً، اترك بياناتك، وسنعاود التواصل خلال ٢٤ ساعة.') }}</p>
                    @php
                        $cEmail = \App\Models\SiteSetting::val('contact_email', 'hello@fleetos.app');
                        $cPhone = \App\Models\SiteSetting::val('contact_phone');
                        $cAddr = \App\Models\SiteSetting::val('contact_address');
                    @endphp
                    <div style="display:grid; gap:.9rem; margin-top:1.6rem">
                        <a href="mailto:{{ $cEmail }}" style="display:flex; align-items:center; gap:.8rem"><span class="ic o" style="width:44px;height:44px;margin:0;border-radius:12px"><i class="fa-solid fa-envelope"></i></span><span style="font-weight:600;color:var(--ink)">{{ $cEmail }}</span></a>
                        @if($cPhone)<a href="tel:{{ $cPhone }}" style="display:flex; align-items:center; gap:.8rem"><span class="ic p" style="width:44px;height:44px;margin:0;border-radius:12px"><i class="fa-solid fa-phone"></i></span><span style="font-weight:600;color:var(--ink)" dir="ltr">{{ $cPhone }}</span></a>@endif
                        @if($cAddr)<div style="display:flex; align-items:center; gap:.8rem"><span class="ic b" style="width:44px;height:44px;margin:0;border-radius:12px"><i class="fa-solid fa-location-dot"></i></span><span style="font-weight:600;color:var(--ink)">{{ $cAddr }}</span></div>@endif
                        <div style="display:flex; align-items:center; gap:.8rem"><span class="ic g" style="width:44px;height:44px;margin:0;border-radius:12px"><i class="fa-solid fa-clock"></i></span><span style="font-weight:600;color:var(--ink)">{{ $t('We reply within 24 hours', 'نردّ خلال ٢٤ ساعة') }}</span></div>
                    </div>
                </div>

                <div class="form-shell">
                    <div class="form-body">
                        <form id="contactForm" novalidate>
                            <div class="fgrid">
                                <div class="field col-2"><label>{{ $t('I want to…', 'أريد أن…') }} <span class="req">*</span></label>
                                    <select name="intent" required>
                                        <option value="demo">{{ $t('Book a demo', 'أحجز عرضاً توضيحيّاً') }}</option>
                                        <option value="sales">{{ $t('Talk to sales', 'أتحدّث للمبيعات') }}</option>
                                        <option value="support">{{ $t('Get support', 'أحصل على دعم') }}</option>
                                        <option value="waitlist">{{ $t('Join the waitlist', 'أنضمّ لقائمة الانتظار') }}</option>
                                    </select><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Name', 'الاسم') }} <span class="req">*</span></label><input name="name" required><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Email', 'البريد') }} <span class="req">*</span></label><input type="email" name="email" required><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Phone', 'الهاتف') }}</label><input name="phone"><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Company', 'الشركة') }}</label><input name="company"><div class="msg"></div></div>
                                <div class="field col-2"><label>{{ $t('Message', 'الرسالة') }}</label><textarea name="message"></textarea><div class="msg"></div></div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" style="margin-top:1.4rem; padding:1.05rem">
                                <span class="lbl">{{ $t('Send message', 'إرسال الرسالة') }}</span>
                                <i class="fa-solid fa-spinner fa-spin spin" style="display:none"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pad" style="background:#fff">
        <div class="wrap">
            <div class="center reveal" style="margin-bottom:2.4rem">
                <span class="eyebrow">{{ $t('FAQ', 'الأسئلة الشائعة') }}</span>
                <h2 class="h-sec" style="margin:.8rem 0">{{ $t('Answers before you start', 'إجابات قبل أن تبدأ') }}</h2>
            </div>
            <div class="faq reveal">
                @php
                    $arLoc = app()->getLocale() === 'ar';
                    $dbFaqs = \App\Models\SiteFaq::active();
                    if ($dbFaqs->count()) {
                        $faqs = $dbFaqs->map(fn($f) => [$arLoc ? $f->question_ar : $f->question_en, $arLoc ? $f->answer_ar : $f->answer_en])->all();
                    } else {
                        $faqs = [
                            [$t('What exactly is FleetOS?', 'ما هو FleetOS بالضبط؟'), $t('A multi-tenant mobility marketplace. You launch a branded taxi office in the cloud; riders pick their office by rating and price, and you manage everything from one dashboard.', 'سوق تنقّل متعدّد المستأجرين. تُطلق مكتب أجرة باسمك في السحابة؛ يختار الركّاب مكتبهم بالتقييم والسعر، وتدير كلّ شيء من لوحة واحدة.')],
                            [$t('How much does it cost?', 'كم التكلفة؟'), $t('Plans start free (18% commission) and scale to Business at $35/mo with 12% commission. Enterprise pricing is custom, and you can change plans anytime.', 'تبدأ الخطط مجّاناً (عمولة ١٨٪) وتتوسّع إلى الأعمال بـ٣٥$ شهريّاً وعمولة ١٢٪. أسعار المؤسّسات مخصّصة، ويمكنك تغيير الخطّة في أيّ وقت.')],
                            [$t('How do drivers get paid?', 'كيف يُدفع للسائقين؟'), $t('Earnings settle to a secure in-app wallet after each ride. Drivers request payouts to their bank; offices withdraw revenue the same way.', 'تُسوّى الأرباح إلى محفظة آمنة داخل التطبيق بعد كلّ رحلة. يطلب السائقون السحب إلى بنوكهم، وتسحب المكاتب إيرادها بالطريقة نفسها.')],
                            [$t('What documents do drivers need?', 'ما الوثائق التي يحتاجها السائقون؟'), $t('A profile photo, ID (front & back), driving license (front & back), a mechanical check, and photos of the vehicle — all uploaded in the driver application.', 'صورة شخصيّة، الهويّة (أمام وخلف)، رخصة القيادة (أمام وخلف)، فحص ميكانيكيّ، وصور للمركبة — كلّها تُرفع في طلب السائق.')],
                            [$t('How long until I go live?', 'كم حتى الانطلاق؟'), $t('Most offices are reviewed within a day or two. Once approved, setup takes minutes and you appear in the rider marketplace immediately.', 'تُراجَع معظم المكاتب خلال يوم أو يومين. بعد الاعتماد، تستغرق التهيئة دقائق وتظهر في سوق الركّاب فوراً.')],
                        ];
                    }
                @endphp
                @foreach ($faqs as $f)
                    <div class="qa"><button onclick="toggleQa(this)">{{ $f[0] }} <i class="fa-solid fa-chevron-down"></i></button><div class="ans"><p>{{ $f[1] }}</p></div></div>
                @endforeach
            </div>
        </div>
    </div>
</section>
