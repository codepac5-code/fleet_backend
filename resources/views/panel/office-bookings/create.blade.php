@extends('panel.layouts.master')

@section('title', textByLanguage('حجز مكتبي', 'Manual booking'))
@section('page-title', textByLanguage('حجز مكتبي', 'Manual booking'))

@php
    $r = fn ($n) => "panel.{$entity}.{$n}";
    $ar = app()->getLocale() === 'ar';
    $tt = fn ($en, $arT) => $ar ? $arT : $en;
@endphp

@section('content')

    @if(session('error'))
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('حجز رحلة يدوياً', 'Book a ride manually')"
        :subtitle="textByLanguage('احجز نيابةً عن عميل خطوة بخطوة', 'Book on behalf of a customer, step by step')">
        <x-slot:actions>
            <a href="{{ route($r('office-bookings.index')) }}" class="p-btn p-btn--ghost"><i class="bi bi-list-ul"></i> {{ $tt('Office bookings', 'الحجوزات المكتبية') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    @if($isAdmin && !$ready)
        <x-panel.card :title="textByLanguage('اختر المكتب', 'Select office')">
            <form method="GET" action="{{ route($r('office-bookings.create')) }}" class="p-form-grid">
                <x-panel.field name="office" type="select" :label="textByLanguage('المكتب', 'Office')" :options="$offices" required />
                <div class="p-field" style="align-self:end;">
                    <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-arrow-right-circle"></i> {{ $tt('Continue', 'متابعة') }}</button>
                </div>
            </form>
        </x-panel.card>
    @elseif(!$ready)
        <x-panel.card><p class="p-empty"><i class="bi bi-shop"></i> {{ $tt('No office context.', 'لا يوجد مكتب.') }}</p></x-panel.card>
    @else
        @php $mapReady = !empty($googleMapsKey); @endphp

        <div class="ob">
            <form method="POST" action="{{ route($r('office-bookings.store')) }}" id="obkForm" class="ob__form">
                @csrf
                <input type="hidden" name="office_id" value="{{ $officeId }}">
                <input type="hidden" name="service" id="f_service">
                <input type="hidden" name="service_class" id="f_class">
                <input type="hidden" name="pickup_lat" id="f_plat">
                <input type="hidden" name="pickup_lng" id="f_plng">
                <input type="hidden" name="dropoff_lat" id="f_dlat">
                <input type="hidden" name="dropoff_lng" id="f_dlng">
                <input type="hidden" name="assign_mode" id="f_mode" value="driver">
                <input type="hidden" name="driver_id" id="f_driver">

                {{-- Progress steps --}}
                <div class="ob-steps">
                    <div class="ob-steps__bar"><span id="obBar"></span></div>
                    @foreach([['bi-person','Customer','العميل'],['bi-geo-alt','Trip','الرحلة'],['bi-cash-coin','Price','السعر'],['bi-person-badge','Driver','السائق']] as $i => $st)
                        <div class="ob-step is-{{ $i === 0 ? 'active' : 'todo' }}" data-dot="{{ $i }}">
                            <span class="ob-step__ic"><i class="bi {{ $st[0] }}"></i></span>
                            <span class="ob-step__n">{{ $tt($st[1], $st[2]) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="p-card ob__card">
                    {{-- STEP 1 — Customer --}}
                    <section class="ob-panel is-active" data-panel="0">
                        <h3 class="ob-h"><i class="bi bi-person-fill"></i> {{ $tt('Customer details', 'بيانات العميل') }}</h3>
                        <div class="ob-grid">
                            <div class="ob-field">
                                <label>{{ $tt('Phone number', 'رقم الهاتف') }} <span class="ob-req">*</span></label>
                                <div class="ob-inp"><i class="bi bi-telephone"></i><input type="tel" name="phone" id="f_phone" dir="ltr" placeholder="+974 5512 3456" required></div>
                                <div class="ob-err" data-err="phone"></div>
                            </div>
                            <div class="ob-field">
                                <label>{{ $tt('Name', 'الاسم') }}</label>
                                <div class="ob-inp"><i class="bi bi-person"></i><input type="text" name="name" placeholder="{{ $tt('Customer name', 'اسم العميل') }}"></div>
                            </div>
                        </div>
                        <div class="ob-note"><i class="bi bi-phone-vibrate"></i> {{ $tt('The customer receives a link to open/download the app and track the ride.', 'سيصل العميل رابط لفتح/تحميل التطبيق وتتبّع الرحلة.') }}</div>
                    </section>

                    {{-- STEP 2 — Trip --}}
                    <section class="ob-panel" data-panel="1">
                        <h3 class="ob-h"><i class="bi bi-map-fill"></i> {{ $tt('Trip details', 'تفاصيل الرحلة') }}</h3>

                        <label class="ob-lbl">{{ $tt('Service', 'الخدمة') }} <span class="ob-req">*</span></label>
                        <div class="ob-chips" id="obTariffs">
                            @foreach($tariffs as $i => $t)
                                <button type="button" class="ob-chip {{ $i === 0 ? 'is-on' : '' }}" data-tariff="{{ $t['service'] }}::{{ $t['service_class'] }}" data-currency="{{ $t['currency'] }}">
                                    <i class="bi bi-{{ $t['service'] === 'travel' ? 'airplane' : ($t['service'] === 'taxi' ? 'taxi-front' : 'car-front') }}"></i>
                                    {{ ucfirst($t['service']) }} · {{ ucfirst(str_replace('_',' ',$t['service_class'])) }}
                                </button>
                            @endforeach
                        </div>

                        <div class="ob-grid" style="margin-top:14px;">
                            <div class="ob-field">
                                <label>{{ $tt('Passengers', 'عدد الركّاب') }}</label>
                                <div class="ob-stepper">
                                    <button type="button" data-pax="-"><i class="bi bi-dash-lg"></i></button>
                                    <input type="number" name="passengers" id="f_pax" min="1" max="20" value="1" readonly>
                                    <button type="button" data-pax="+"><i class="bi bi-plus-lg"></i></button>
                                </div>
                            </div>
                        </div>

                        <label class="ob-lbl" style="margin-top:14px;">{{ $tt('Route', 'المسار') }} <span class="ob-req">*</span></label>
                        <div class="ob-loc">
                            <div class="ob-loc__row" data-loc="pickup">
                                <span class="ob-loc__pin" style="background:#16a34a;"><i class="bi bi-check-lg"></i></span>
                                <input type="text" name="pickup_title" id="f_ptitle" placeholder="{{ $tt('Pickup point', 'نقطة الانطلاق') }}" autocomplete="off">
                                <button type="button" class="ob-loc__set" data-set="pickup" title="{{ $tt('Set on map', 'حدّد على الخريطة') }}"><i class="bi bi-cursor-fill"></i></button>
                            </div>
                            <div class="ob-loc__row" data-loc="dropoff">
                                <span class="ob-loc__pin" style="background:#dc2626;"><i class="bi bi-flag-fill"></i></span>
                                <input type="text" name="dropoff_title" id="f_dtitle" placeholder="{{ $tt('Destination', 'الوجهة') }}" autocomplete="off">
                                <button type="button" class="ob-loc__set" data-set="dropoff" title="{{ $tt('Set on map', 'حدّد على الخريطة') }}"><i class="bi bi-cursor-fill"></i></button>
                            </div>
                        </div>
                        <div class="ob-err" data-err="trip"></div>

                        @if($mapReady)
                            <div id="obkMap" class="ob-map"></div>
                            <div class="ob-hint" data-map-hint><i class="bi bi-hand-index"></i> {{ $tt('Search an address, or click / drag the pins on the map.', 'ابحث عن عنوان، أو انقر / اسحب الدبابيس على الخريطة.') }}</div>
                        @else
                            <div class="ob-grid" style="margin-top:12px;">
                                <div class="ob-field"><label>{{ $tt('Pickup lat', 'انطلاق: خط العرض') }}</label><div class="ob-inp"><i class="bi bi-geo"></i><input type="number" step="any" data-manual="plat"></div></div>
                                <div class="ob-field"><label>{{ $tt('Pickup lng', 'انطلاق: خط الطول') }}</label><div class="ob-inp"><i class="bi bi-geo"></i><input type="number" step="any" data-manual="plng"></div></div>
                                <div class="ob-field"><label>{{ $tt('Dropoff lat', 'وجهة: خط العرض') }}</label><div class="ob-inp"><i class="bi bi-geo"></i><input type="number" step="any" data-manual="dlat"></div></div>
                                <div class="ob-field"><label>{{ $tt('Dropoff lng', 'وجهة: خط الطول') }}</label><div class="ob-inp"><i class="bi bi-geo"></i><input type="number" step="any" data-manual="dlng"></div></div>
                            </div>
                        @endif
                    </section>

                    {{-- STEP 3 — Price --}}
                    <section class="ob-panel" data-panel="2">
                        <h3 class="ob-h"><i class="bi bi-cash-stack"></i> {{ $tt('Ride pricing', 'تسعير الرحلة') }}</h3>

                        <div class="ob-fare" id="obkFare">
                            <div class="ob-fare__spin" data-fare-loading><span class="ob-spin"></span> {{ $tt('Calculating fare…', 'جارٍ حساب السعر…') }}</div>
                            <div class="ob-fare__box" data-fare-box style="display:none;">
                                <div class="ob-fare__ic"><i class="bi bi-cash-coin"></i></div>
                                <div>
                                    <span class="ob-fare__lbl">{{ $tt('Suggested fare', 'السعر المقترح') }}</span>
                                    <div class="ob-fare__amt"><b data-fare-amt>0.00</b> <small data-fare-cur></small></div>
                                </div>
                                <button type="button" class="ob-fare__use" data-fare-use>{{ $tt('Use', 'اعتمد') }}</button>
                            </div>
                            <div class="ob-fare__err" data-fare-err style="display:none;"></div>
                        </div>

                        <label class="ob-switch"><input type="checkbox" id="f_manualToggle"><span class="ob-switch__t"></span> {{ $tt('Set price manually', 'تعديل السعر يدوياً') }}</label>
                        <div class="ob-field" id="f_manualWrap" style="display:none;max-width:280px;margin-top:8px;">
                            <label>{{ $tt('Final price', 'السعر النهائي') }}</label>
                            <div class="ob-inp"><i class="bi bi-pencil"></i><input type="number" step="0.01" min="0" name="fare" id="f_fare" placeholder="0.00"></div>
                        </div>

                        <label class="ob-lbl" style="margin-top:18px;">{{ $tt('Payment method', 'طريقة الدفع') }}</label>
                        <div class="ob-opts">
                            <button type="button" class="ob-opt is-on" data-pay="cash">
                                <i class="bi bi-cash-coin"></i>
                                <b>{{ $tt('Cash', 'نقدي') }}</b>
                                <span>{{ $tt('Customer pays the driver', 'العميل يدفع للسائق') }}</span>
                            </button>
                            <button type="button" class="ob-opt" data-pay="office_wallet">
                                <i class="bi bi-wallet2"></i>
                                <b>{{ $tt('Office wallet', 'محفظة المكتب') }}</b>
                                <span>{{ $tt('Deducted from your balance', 'يُخصم من رصيدك') }}</span>
                            </button>
                        </div>
                        <input type="hidden" name="payment_method" id="f_payment" value="cash">
                    </section>

                    {{-- STEP 4 — Driver --}}
                    <section class="ob-panel" data-panel="3">
                        <h3 class="ob-h"><i class="bi bi-person-badge-fill"></i> {{ $tt('Assign driver', 'تعيين السائق') }}</h3>
                        <div class="ob-opts">
                            <button type="button" class="ob-opt is-on" data-assign="driver"><i class="bi bi-person-check-fill"></i><b>{{ $tt('Specific driver', 'سائق محدّد') }}</b><span>{{ $tt('Pick from your fleet', 'اختر من أسطولك') }}</span></button>
                            <button type="button" class="ob-opt" data-assign="broadcast"><i class="bi bi-broadcast"></i><b>{{ $tt('Broadcast', 'بثّ') }}</b><span>{{ $tt('Offer to nearby online drivers', 'عرض على القريبين المتّصلين') }}</span></button>
                        </div>

                        <div data-drivers>
                            <div class="ob-inp ob-search" style="margin:12px 0;"><i class="bi bi-search"></i><input type="text" id="obDriverSearch" placeholder="{{ $tt('Search driver by name or plate…', 'ابحث بالاسم أو اللوحة…') }}"></div>
                            <div class="ob-drivers">
                                @forelse($drivers as $d)
                                    <label class="ob-driver" data-name="{{ mb_strtolower($d['name']) }}" data-plate="{{ mb_strtolower($d['car']['plate'] ?? '') }}">
                                        <input type="radio" name="_driver_pick" value="{{ $d['id'] }}">
                                        <span class="ob-driver__av">@if($d['photo'])<img src="{{ asset('storage/' . $d['photo']) }}" alt="" onerror="this.parentNode.textContent='{{ mb_strtoupper(mb_substr($d['name'],0,1)) }}'">@else{{ mb_strtoupper(mb_substr($d['name'], 0, 1)) }}@endif</span>
                                        <span class="ob-driver__tx">
                                            <strong>{{ $d['name'] }}</strong>
                                            <span dir="ltr">{{ $d['phone'] }}@if($d['car']) · {{ $d['car']['brand'] }} {{ $d['car']['model'] }} · {{ $d['car']['plate'] }}@endif</span>
                                        </span>
                                        <i class="bi bi-check-circle-fill ob-driver__tick"></i>
                                    </label>
                                @empty
                                    <p class="p-empty"><i class="bi bi-person-slash"></i> {{ $tt('No active drivers', 'لا يوجد سائقون نشطون') }}</p>
                                @endforelse
                            </div>
                            <div class="ob-err" data-err="driver"></div>
                        </div>
                    </section>

                    <div class="ob-nav">
                        <button type="button" class="p-btn p-btn--ghost" data-prev style="visibility:hidden;"><i class="bi bi-arrow-{{ $ar ? 'right' : 'left' }}"></i> {{ $tt('Back', 'السابق') }}</button>
                        <button type="button" class="p-btn p-btn--primary" data-next>{{ $tt('Next', 'التالي') }} <i class="bi bi-arrow-{{ $ar ? 'left' : 'right' }}"></i></button>
                        <button type="submit" class="p-btn p-btn--primary" data-submit style="display:none;"><i class="bi bi-check2-circle"></i> {{ $tt('Confirm booking', 'تأكيد الحجز') }}</button>
                    </div>
                </div>
            </form>

            {{-- LIVE SUMMARY --}}
            <aside class="ob-sum">
                <div class="p-card ob-sum__card">
                    <h3 class="ob-h" style="margin-bottom:14px;"><i class="bi bi-clipboard-check"></i> {{ $tt('Booking summary', 'ملخّص الحجز') }}</h3>
                    <div class="ob-sum__row"><span class="ob-sum__ic" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-person"></i></span>
                        <div><div class="ob-sum__l">{{ $tt('Customer', 'العميل') }}</div><div class="ob-sum__v" data-sum="customer">—</div></div></div>
                    <div class="ob-sum__row"><span class="ob-sum__ic" style="background:#eef2ff;color:#4f46e5;"><i class="bi bi-signpost-split"></i></span>
                        <div><div class="ob-sum__l">{{ $tt('Route', 'المسار') }}</div><div class="ob-sum__v" data-sum="route">—</div></div></div>
                    <div class="ob-sum__row"><span class="ob-sum__ic" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-car-front"></i></span>
                        <div><div class="ob-sum__l">{{ $tt('Service', 'الخدمة') }}</div><div class="ob-sum__v" data-sum="service">—</div></div></div>
                    <div class="ob-sum__row"><span class="ob-sum__ic" style="background:#fffbeb;color:#d97706;"><i class="bi bi-cash"></i></span>
                        <div><div class="ob-sum__l">{{ $tt('Price', 'السعر') }}</div><div class="ob-sum__v" data-sum="price">—</div></div></div>
                    <div class="ob-sum__row"><span class="ob-sum__ic" style="background:#faf5ff;color:#9333ea;"><i class="bi bi-person-badge"></i></span>
                        <div><div class="ob-sum__l">{{ $tt('Driver', 'السائق') }}</div><div class="ob-sum__v" data-sum="driver">—</div></div></div>
                </div>
            </aside>
        </div>

        {{-- success overlay --}}
        <div id="obSuccess" class="ob-success"><div class="ob-success__box"><span class="ob-success__ic"><i class="bi bi-check-lg"></i></span><div>{{ $tt('Confirming booking…', 'جارٍ تأكيد الحجز…') }}</div></div></div>

        <script>
            window.OBK = {
                quoteUrl: @json(route($r('office-bookings.quote'))),
                officeId: {{ $officeId }},
                center: @json($mapCenter),
                mapReady: @json($mapReady),
                t: {
                    setPickup: @json($tt('Click the map to set pickup', 'انقر الخريطة لتحديد الانطلاق')),
                    setDrop: @json($tt('Click the map to set destination', 'انقر الخريطة لتحديد الوجهة')),
                    needTrip: @json($tt('Set pickup and destination', 'حدّد الانطلاق والوجهة')),
                    needPhone: @json($tt('Enter a valid phone number', 'أدخل رقم هاتف صحيح')),
                    needDriver: @json($tt('Choose a driver or switch to broadcast', 'اختر سائقاً أو حوّل للبثّ')),
                    broadcast: @json($tt('Broadcast to nearby drivers', 'بثّ للسائقين القريبين')),
                    payCash: @json($tt('Cash', 'نقدي')),
                    payWallet: @json($tt('Office wallet', 'محفظة المكتب')),
                    dash: '—'
                }
            };
        </script>
        <script src="{{ asset('panel/js/office-booking.js') }}"></script>
        @if($mapReady)
            <script async src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&libraries=places&callback=obkInitMap"></script>
        @endif
    @endif

@endsection

@push('styles')
<style>
    .ob { display:grid; grid-template-columns:1fr 320px; gap:16px; align-items:start; }
    @media (max-width:960px){ .ob { grid-template-columns:1fr; } .ob-sum { order:-1; } }

    /* progress steps */
    .ob-steps { display:flex; gap:6px; position:relative; margin-bottom:16px; }
    .ob-steps__bar { position:absolute; top:19px; inset-inline-start:6%; width:88%; height:3px; background:var(--p-border); border-radius:2px; z-index:0; }
    .ob-steps__bar span { display:block; height:100%; width:0; background:linear-gradient(90deg,#5b5bd6,#7c3aed); border-radius:2px; transition:width .4s; }
    .ob-step { flex:1; text-align:center; position:relative; z-index:1; cursor:default; }
    .ob-step__ic { width:40px; height:40px; border-radius:50%; margin:0 auto 6px; display:flex; align-items:center; justify-content:center; font-size:1.05rem;
        background:var(--p-surface,#fff); color:var(--p-text-muted); border:2px solid var(--p-border); transition:.3s; }
    .ob-step__n { font-size:.76rem; color:var(--p-text-muted); font-weight:600; }
    .ob-step.is-active .ob-step__ic { border-color:#5b5bd6; color:#5b5bd6; box-shadow:0 0 0 4px rgba(91,91,214,.14); transform:scale(1.08); }
    .ob-step.is-active .ob-step__n { color:var(--p-text); }
    .ob-step.is-done .ob-step__ic { background:#16a34a; border-color:#16a34a; color:#fff; }
    .ob-step.is-done .ob-step__ic i::before { content:"\F26E"; } /* check */

    .ob-panel { display:none; animation:obFade .35s both; }
    .ob-panel.is-active { display:block; }
    @keyframes obFade { from { opacity:0; transform:translateX(10px); } to { opacity:1; transform:none; } }
    @keyframes obShake { 0%,100%{transform:translateX(0)} 20%,60%{transform:translateX(-6px)} 40%,80%{transform:translateX(6px)} }

    .ob-h { display:flex; align-items:center; gap:9px; font-size:1.02rem; font-weight:800; margin:0 0 16px; }
    .ob-h i { color:#5b5bd6; }
    .ob-lbl { display:block; font-size:.8rem; font-weight:700; margin-bottom:8px; }
    .ob-req { color:#dc2626; }
    .ob-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; } @media (max-width:600px){ .ob-grid{grid-template-columns:1fr;} }
    .ob-field label { display:block; font-size:.8rem; font-weight:600; margin-bottom:6px; }

    .ob-inp { display:flex; align-items:center; gap:8px; padding:0 12px; border:1.6px solid var(--p-border); border-radius:11px; transition:.15s; background:var(--p-surface,#fff); }
    .ob-inp:focus-within { border-color:#5b5bd6; box-shadow:0 0 0 3px rgba(91,91,214,.12); }
    .ob-inp i { color:var(--p-text-muted); font-size:.95rem; }
    .ob-inp input { flex:1; border:0; outline:0; padding:11px 0; background:transparent; font-size:.92rem; width:100%; }
    .ob-inp.is-bad { border-color:#dc2626; animation:obShake .4s; }
    .ob-err { color:#dc2626; font-size:.78rem; margin-top:5px; min-height:0; display:none; }
    .ob-err.show { display:block; }
    .ob-note { display:flex; align-items:center; gap:8px; margin-top:14px; padding:11px 13px; border-radius:11px; background:#eff6ff; color:#1d4ed8; font-size:.82rem; }

    /* service chips */
    .ob-chips { display:flex; flex-wrap:wrap; gap:9px; }
    .ob-chip { display:inline-flex; align-items:center; gap:7px; padding:9px 15px; border-radius:11px; border:1.6px solid var(--p-border); background:var(--p-surface,#fff); font-weight:600; font-size:.86rem; cursor:pointer; transition:.15s; }
    .ob-chip:hover { border-color:#a5b4fc; }
    .ob-chip.is-on { border-color:#5b5bd6; background:#eef2ff; color:#4338ca; box-shadow:0 2px 8px rgba(91,91,214,.15); }
    .ob-chip i { font-size:1rem; }

    .ob-stepper { display:inline-flex; align-items:center; border:1.6px solid var(--p-border); border-radius:11px; overflow:hidden; }
    .ob-stepper button { border:0; background:var(--p-bg-soft,#f4f5f9); width:40px; height:42px; cursor:pointer; color:var(--p-text); }
    .ob-stepper button:hover { background:#eef2ff; color:#5b5bd6; }
    .ob-stepper input { width:52px; text-align:center; border:0; font-weight:800; font-size:1rem; }

    /* route inputs */
    .ob-loc { display:flex; flex-direction:column; gap:10px; }
    .ob-loc__row { display:flex; align-items:center; gap:10px; padding:0 6px 0 0; border:1.6px solid var(--p-border); border-radius:11px; background:var(--p-surface,#fff); transition:.15s; }
    .ob-loc__row:focus-within { border-color:#5b5bd6; box-shadow:0 0 0 3px rgba(91,91,214,.12); }
    .ob-loc__pin { width:34px; height:42px; border-radius:11px 0 0 11px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:.85rem; flex:none; }
    .ob-loc__row input { flex:1; border:0; outline:0; padding:11px 4px; background:transparent; font-size:.92rem; }
    .ob-loc__set { border:0; background:var(--p-bg-soft,#f4f5f9); width:38px; height:38px; border-radius:9px; cursor:pointer; color:#5b5bd6; flex:none; }
    .ob-loc__set.is-on { background:#5b5bd6; color:#fff; }
    .ob-map { width:100%; height:280px; border-radius:12px; margin-top:12px; background:linear-gradient(135deg,#eef1f6,#e2e8f0); }
    .ob-hint { display:flex; align-items:center; gap:6px; font-size:.78rem; color:var(--p-text-muted); margin-top:8px; }

    /* fare */
    .ob-fare { min-height:74px; }
    .ob-fare__spin { display:flex; align-items:center; gap:9px; padding:20px; color:var(--p-text-muted); font-size:.88rem; }
    .ob-spin { width:18px; height:18px; border:2.5px solid var(--p-border); border-top-color:#5b5bd6; border-radius:50%; animation:obSpin .7s linear infinite; }
    @keyframes obSpin { to { transform:rotate(360deg); } }
    .ob-fare__box { display:flex; align-items:center; gap:14px; padding:16px 18px; border-radius:13px; background:linear-gradient(120deg,#f0fdf4,#ecfdf5); border:1px solid #bbf7d0; }
    .ob-fare__ic { width:44px; height:44px; border-radius:12px; background:#16a34a; color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.3rem; }
    .ob-fare__lbl { font-size:.76rem; color:#15803d; font-weight:600; }
    .ob-fare__amt b { font-size:1.6rem; font-weight:800; color:#166534; } .ob-fare__amt small { color:#15803d; }
    .ob-fare__use { margin-inline-start:auto; border:0; background:#16a34a; color:#fff; padding:8px 16px; border-radius:9px; font-weight:700; cursor:pointer; }
    .ob-fare__err { padding:14px 16px; border-radius:11px; background:#fef2f2; color:#b91c1c; font-size:.85rem; }

    .ob-switch { display:inline-flex; align-items:center; gap:9px; margin-top:14px; font-size:.85rem; font-weight:600; cursor:pointer; }
    .ob-switch input { display:none; }
    .ob-switch__t { width:38px; height:22px; border-radius:20px; background:var(--p-border); position:relative; transition:.2s; flex:none; }
    .ob-switch__t::after { content:''; position:absolute; top:2px; inset-inline-start:2px; width:18px; height:18px; border-radius:50%; background:#fff; transition:.2s; }
    .ob-switch input:checked + .ob-switch__t { background:#5b5bd6; }
    .ob-switch input:checked + .ob-switch__t::after { inset-inline-start:18px; }

    /* option cards (payment / assign) */
    .ob-opts { display:grid; grid-template-columns:1fr 1fr; gap:12px; } @media (max-width:600px){ .ob-opts{grid-template-columns:1fr;} }
    .ob-opt { text-align:start; padding:14px 16px; border-radius:13px; border:1.6px solid var(--p-border); background:var(--p-surface,#fff); cursor:pointer; transition:.15s; }
    .ob-opt:hover { border-color:#a5b4fc; transform:translateY(-2px); }
    .ob-opt i { font-size:1.35rem; color:var(--p-text-muted); }
    .ob-opt b { display:block; margin:7px 0 2px; font-size:.92rem; }
    .ob-opt span { font-size:.76rem; color:var(--p-text-muted); }
    .ob-opt.is-on { border-color:#5b5bd6; background:#eef2ff; box-shadow:0 4px 14px rgba(91,91,214,.15); }
    .ob-opt.is-on i { color:#5b5bd6; }

    /* drivers */
    .ob-search { margin-bottom:10px; }
    .ob-drivers { display:flex; flex-direction:column; gap:9px; max-height:340px; overflow:auto; }
    .ob-driver { display:flex; align-items:center; gap:12px; padding:11px 13px; border-radius:12px; border:1.6px solid var(--p-border); cursor:pointer; transition:.15s; }
    .ob-driver:hover { border-color:#a5b4fc; }
    .ob-driver input { display:none; }
    .ob-driver__av { width:42px; height:42px; border-radius:50%; background:linear-gradient(135deg,#8b5cf6,#6366f1); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; overflow:hidden; flex:none; }
    .ob-driver__av img { width:100%; height:100%; object-fit:cover; }
    .ob-driver__tx strong { display:block; font-size:.9rem; } .ob-driver__tx span { font-size:.76rem; color:var(--p-text-muted); }
    .ob-driver__tick { margin-inline-start:auto; color:#16a34a; opacity:0; font-size:1.15rem; transition:.15s; }
    .ob-driver.is-picked { border-color:#16a34a; background:#f0fdf4; }
    .ob-driver.is-picked .ob-driver__tick { opacity:1; }

    .ob-nav { display:flex; align-items:center; gap:10px; margin-top:22px; padding-top:16px; border-top:1px solid var(--p-border); }
    .ob-nav [data-next], .ob-nav [data-submit] { margin-inline-start:auto; }

    /* live summary */
    .ob-sum__card { position:sticky; top:16px; }
    .ob-sum__row { display:flex; gap:11px; padding:10px 0; align-items:center; border-bottom:1px dashed var(--p-border); }
    .ob-sum__row:last-child { border-bottom:0; }
    .ob-sum__ic { width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.95rem; flex:none; }
    .ob-sum__l { font-size:.7rem; color:var(--p-text-muted); }
    .ob-sum__v { font-weight:700; font-size:.86rem; word-break:break-word; }
    .ob-sum__v.set { color:var(--p-text); } .ob-sum__v:not(.set) { color:var(--p-text-muted); }

    /* success overlay */
    .ob-success { display:none; position:fixed; inset:0; background:rgba(15,23,42,.5); z-index:9999; align-items:center; justify-content:center; }
    .ob-success.show { display:flex; }
    .ob-success__box { background:#fff; border-radius:16px; padding:28px 34px; text-align:center; font-weight:700; }
    .ob-success__ic { width:56px; height:56px; border-radius:50%; background:#16a34a; color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.7rem; margin:0 auto 12px; animation:obShake .5s; }
</style>
@endpush
