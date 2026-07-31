<section id="page-offices" class="page">
    <div class="phero">
        <div class="wrap in">
            <span class="eyebrow on-dark"><i class="fa-solid fa-building"></i> {{ $t('For offices', 'للمكاتب') }}</span>
            <h1>{{ $t('Launch your office', 'أطلق مكتبك') }}</h1>
            <p>{{ $t('Bring your brand to a ready marketplace — no code, no servers. Tell us about your business and our team gets you live fast.', 'انقل علامتك إلى سوق جاهز — بلا برمجة أو خوادم. حدّثنا عن نشاطك ويجعلك فريقنا فعّالاً بسرعة.') }}</p>
        </div>
    </div>

    <div class="pad">
        <div class="wrap">
            <div class="grid-3 reveal" style="margin-bottom:3rem">
                <div class="card"><div class="ic o"><i class="fa-solid fa-rocket"></i></div><h3>{{ $t('Go live in minutes', 'انطلق خلال دقائق') }}</h3><p>{{ $t('Get approved, set pricing and coverage, and appear to riders.', 'اعتمد، اضبط الأسعار والتغطية، واظهر للركّاب.') }}</p></div>
                <div class="card"><div class="ic b"><i class="fa-solid fa-users-gear"></i></div><h3>{{ $t('Manage everything', 'أدِر كلّ شيء') }}</h3><p>{{ $t('Drivers, dispatch, wallets, subscriptions and reports in one place.', 'السائقون، الإسناد، المحافظ، الاشتراكات والتقارير في مكان واحد.') }}</p></div>
                <div class="card"><div class="ic g"><i class="fa-solid fa-arrow-trend-up"></i></div><h3>{{ $t('Grow revenue', 'نمِّ الإيراد') }}</h3><p>{{ $t('Lower commission as you scale, with live financial reports.', 'عمولة أقلّ كلّما توسّعت، مع تقارير ماليّة حيّة.') }}</p></div>
            </div>

            <div style="max-width:760px; margin:0 auto" class="reveal">
                <div class="form-shell">
                    <div class="form-head">
                        <span class="fi" style="background:linear-gradient(135deg,var(--primary),var(--primary-2))"><i class="fa-solid fa-building"></i></span>
                        <div><div style="font-weight:800; color:var(--ink)">{{ $t('Office application', 'طلب مكتب') }}</div><div style="font-size:.82rem; color:var(--muted)">{{ $t('Takes about 2 minutes', 'يستغرق نحو دقيقتين') }}</div></div>
                    </div>
                    <div class="form-body">
                        <form id="officeForm" novalidate>
                            <span class="fstep-tag">{{ $t('1 · Office info', '١ · معلومات المكتب') }}</span>
                            <div class="fgrid">
                                <div class="field"><label>{{ $t('Office name', 'اسم المكتب') }} <span class="req">*</span></label><input name="office_name" required><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Contact person', 'الشخص المسؤول') }} <span class="req">*</span></label><input name="contact_name" required><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Email', 'البريد') }} <span class="req">*</span></label><input type="email" name="email" required><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Phone', 'الهاتف') }} <span class="req">*</span></label><input name="phone" required><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Country', 'الدولة') }} <span class="req">*</span></label>
                                    <select name="country" id="of_country" required>
                                        <option value="">{{ $t('Select', 'اختر') }}</option>
                                        @foreach(($countries ?? []) as $c)
                                            <option value="{{ $c->name }}" data-id="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('City', 'المدينة') }} <span class="req">*</span></label>
                                    <select name="city" id="of_city" required disabled data-ph="{{ $t('Select a country first', 'اختر الدولة أولاً') }}">
                                        <option value="">{{ $t('Select a country first', 'اختر الدولة أولاً') }}</option>
                                    </select><div class="msg"></div></div>
                                <div class="field col-2"><label>{{ $t('Website (optional)', 'الموقع (اختياري)') }}</label><input type="url" name="website" placeholder="https://"><div class="msg"></div></div>
                            </div>

                            <span class="fstep-tag">{{ $t('2 · Business', '٢ · النشاط') }}</span>
                            <div class="fgrid">
                                <div class="field"><label>{{ $t('Business type', 'نوع النشاط') }} <span class="req">*</span></label>
                                    <select name="business_category" required><option value="">{{ $t('Select', 'اختر') }}</option><option value="New">{{ $t('New business', 'نشاط جديد') }}</option><option value="Existing">{{ $t('Existing fleet', 'أسطول قائم') }}</option><option value="Corporate">{{ $t('Corporate', 'شركة') }}</option></select><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Fleet size', 'حجم الأسطول') }} <span class="req">*</span></label><input type="number" name="fleet_size" min="1" required><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Service', 'الخدمة') }} <span class="req">*</span></label>
                                    <select name="service_type" id="of_service" required disabled>
                                        <option value="">{{ $t('Select a country first', 'اختر الدولة أولاً') }}</option>
                                    </select><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Current tools', 'الأدوات الحاليّة') }}</label><input name="current_tools"><div class="msg"></div></div>
                                <div class="field col-2"><label>{{ $t('Coverage area', 'منطقة التغطية') }}</label><input name="coverage"><div class="msg"></div></div>
                            </div>

                            <span class="fstep-tag">{{ $t('3 · Details', '٣ · تفاصيل') }}</span>
                            <div class="fgrid">
                                <div class="field"><label>{{ $t('Licensed?', 'مرخّص؟') }} <span class="req">*</span></label>
                                    <select name="license_status" required><option value="">{{ $t('Select', 'اختر') }}</option><option value="Yes">{{ $t('Yes', 'نعم') }}</option><option value="No">{{ $t('No', 'لا') }}</option><option value="Not sure">{{ $t('Not sure', 'غير متأكّد') }}</option></select><div class="msg"></div></div>
                                <div class="field"><label>{{ $t('Timeline', 'الإطار الزمنيّ') }} <span class="req">*</span></label>
                                    <select name="timeline" required><option value="">{{ $t('Select', 'اختر') }}</option><option value="Immediate">{{ $t('Immediate', 'فوري') }}</option><option value="30 days">{{ $t('30 days', '٣٠ يوماً') }}</option><option value="60-90 days">{{ $t('60–90 days', '٦٠–٩٠ يوماً') }}</option><option value="Exploring">{{ $t('Just exploring', 'مجرّد استكشاف') }}</option></select><div class="msg"></div></div>
                                <div class="field col-2"><label>{{ $t('Notes', 'ملاحظات') }}</label><textarea name="notes"></textarea><div class="msg"></div></div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block" style="margin-top:1.6rem; padding:1.05rem">
                                <span class="lbl">{{ $t('Submit application', 'إرسال الطلب') }}</span>
                                <i class="fa-solid fa-spinner fa-spin spin" style="display:none"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var country = document.getElementById('of_country');
        var city = document.getElementById('of_city');
        var service = document.getElementById('of_service');
        if (!country || !city || !service) return;

        var URL = "{{ route('public.office-form') }}";
        var C = {
            city: "{{ $t('Select a city', 'اختر مدينة') }}",
            service: "{{ $t('Select a service', 'اختر خدمة') }}",
            loading: "{{ $t('Loading…', 'جارٍ التحميل…') }}",
            errCity: "{{ $t('Could not load cities', 'تعذّر تحميل المدن') }}",
            errSvc: "{{ $t('Could not load services', 'تعذّر تحميل الخدمات') }}"
        };

        function fill(sel, items, placeholder) {
            sel.innerHTML = '';
            var ph = new Option(placeholder, '');
            sel.appendChild(ph);
            (items || []).forEach(function (it) { sel.appendChild(new Option(it.name, it.name)); });
            sel.disabled = !(items && items.length);
        }

        country.addEventListener('change', function () {
            var opt = country.options[country.selectedIndex];
            var id = opt ? opt.getAttribute('data-id') : '';
            city.disabled = true; service.disabled = true;
            city.innerHTML = '<option value="">' + C.loading + '</option>';
            service.innerHTML = '<option value="">' + C.loading + '</option>';
            if (!id) { city.innerHTML = '<option value=""></option>'; service.innerHTML = '<option value=""></option>'; return; }

            fetch(URL + '?country=' + encodeURIComponent(id), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    fill(city, d.cities, C.city);
                    fill(service, d.services, C.service);
                })
                .catch(function () {
                    city.innerHTML = '<option value="">' + C.errCity + '</option>';
                    service.innerHTML = '<option value="">' + C.errSvc + '</option>';
                });
        });
    })();
    </script>
</section>
