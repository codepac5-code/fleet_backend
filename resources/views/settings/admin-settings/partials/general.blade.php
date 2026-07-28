<form id="regionForm" class="region-card" method="POST" action="{{ route('settings.generals.update') }}">
    @csrf

    <div class="region-section">
        <div class="section-header">
            <h3>{{ __('messages.country_and_language') }}</h3>
            <p>{{ __('messages.choose_default_country_language') }}</p>
        </div>
        <div class="section-body">

            <div class="form-group">
                <label>{{ __('messages.country') }}</label>
                <div class="country-list scroll-x">
            @foreach($countries as $country)
                    <input type="radio"
                        name="countryId"
                        id="country-{{ $country['id'] }}"
                        value="{{ $country['id'] }}"
                        class="country-radio"
                        {{ $settings['country'] == $country['id'] ? 'checked' : '' }}>
                    <label for="country-{{ $country['id'] }}" class="country-item">
                        <img src="{{ asset($country['flag']) }}" class="mini-flag">
                        <span class="country-name">{{ $country['name'] }}</span>
                        <span class="checkmark">✓</span>
                    </label>
                @endforeach
                </div>
            </div>

            @php $locale = app()->getLocale(); @endphp
            <div class="form-group" style="margin-top:20px;">
                <label>{{ __('messages.language') }}</label>
                <select name="language" class="stg-input">
                    @foreach($languages as $lang)
                        <option value="{{ $lang['code'] }}"
                            {{ $settings['language'] == $lang['code'] ? 'selected' : '' }}>
                            {{ $locale == 'ar' ? $lang['ar_name'] : $lang['en_name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>


    <div class="region-section">
        <div class="section-header">
            <h3>{{ __('messages.currency_and_timezone') }}</h3>
            <p>{{ __('messages.choose_default_currency_timezone') }}</p>
        </div>
        <div class="section-body">
            <div class="form-flex">
                <div class="form-group">
                    <label>{{ __('messages.currency') }}</label>
                    <select name="currency" class="stg-input">
                        @foreach($countries as $country)
                            <option value="{{ $country['currency_code'] }}"
                                {{ $settings['currency'] == $country['currency_code'] ? 'selected' : '' }}>
                                {{ $country['currency_code'] }} ({{ $country['currency_symbol'] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('messages.timezone') }}</label>
                    <select name="timezone" class="stg-input">
                        @foreach($timezones as $tz)
                            <option value="{{ $tz }}"
                                {{ $settings['timezone'] == $tz ? 'selected' : '' }}>
                                {{ $tz }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="region-footer">
        <button type="submit" class="save-btn">{{ __('messages.save_settings') }}</button>
    </div>
</form>

<style>
/* Wrapper */
.region-wrapper{
    width:100%;
    max-width:1200px;
    margin:auto;
    padding:clamp(20px,4vw,50px);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Sections */
.region-section{
    background:#fff;
    border-radius:18px;
    padding:20px 25px;
    margin-bottom:30px;
    box-shadow:0 12px 35px rgba(0,0,0,0.08);
    transition:.3s;
}
.region-section:hover{
    box-shadow:0 18px 45px rgba(0,0,0,0.12);
}
.section-header h3{
    color:#312873;
    font-size:16px;
    font-weight:700;
    margin-bottom:6px;
}
.section-header p{
    font-size:13px;
    color:#555;
    margin-bottom:15px;
}
.section-body .form-flex{
    display:flex;
    flex-wrap:wrap;
    gap:20px;
}

/* Form groups */
.form-group{
    flex:1 1 280px;
    display:flex;
    flex-direction:column;
}
.form-group label{
    margin-bottom:8px;
    font-weight:600;
    font-size:14px;
    color:#312873;
}
.stg-input{
    width:100%;
    padding:12px 16px;
    border-radius:12px;
    border:1px solid #e0e0e5;
    background:#f8f9ff;
    font-size:14px;
    transition:0.3s;
}
.stg-input:focus{
    border-color:#312873;
    background:#fff;
    outline:none;
    box-shadow:0 0 0 4px rgba(49,40,115,.15);
}

/* Country list */
.country-list{
    display:flex;
    gap:10px;
    overflow-x:auto;
    padding-bottom:5px;
    scroll-behavior: smooth;
}
.country-list::-webkit-scrollbar{
    height:6px;
}
.country-list::-webkit-scrollbar-thumb{
    background:#312873;
    border-radius:3px;
}
.country-list::-webkit-scrollbar-track{
    background:#f0f0f0;
    border-radius:3px;
}

/* Country button */
.country-item{
    position: relative;
    display:flex;
    align-items:center;
    gap:8px;
    padding:10px 14px;
    border-radius:12px;
    cursor:pointer;
    transition: all 0.3s ease;
    border:1px solid #ddd;
    background:#fefefe;
    color:#312873;
    flex-shrink:0;
    min-width:100px;
    justify-content:center;
    outline:none;
}

.country-item:focus{
    outline:none;
}

.country-item:hover{
    background:#fcb90234;
    color:#312873;
    border-color:#fcb902bd;
}

/* Selected */
.country-item.selected{
    background:#FCB902 !important;
    color:#fff !important;
    border-color:#312873 !important;
}

.country-item .checkmark{
    position: absolute;
    top:5px;
    right:5px;
    color:#fff;
    font-size:16px;
    font-weight:700;
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s ease;
}

.country-item.selected .checkmark{
    opacity: 1;
    transform: scale(1.2);
}

/* Mini flag and name */
.mini-flag{
    width:26px;
    height:18px;
    object-fit:cover;
    border-radius:3px;
}
.country-name{
    font-size:14px;
    font-weight:500;
    text-align:center;
}

/* Save button */
.region-footer{
    display:flex;
    justify-content:flex-end;
    margin-top:20px;
}
.save-btn{
    padding:14px 36px;
    border-radius:12px;
    border:none;
    background:linear-gradient(135deg,#312873,#FCB902);
    color:#fff;
    font-weight:600;
    cursor:pointer;
    font-size:15px;
    transition:.3s;
}
.save-btn:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 25px rgba(49,40,115,.25);
}

/* Responsive */
@media(max-width:768px){
    .country-item{
        min-width:80px;
        padding:8px 10px;
    }
    .country-name{
        font-size:12px;
    }
}


.country-radio {
    display: none;
}

.country-item .checkmark{
    color: #312873;
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s ease;
}

.country-radio:checked + .country-item .checkmark{
    opacity: 1;
    transform: scale(1.2);
}
.country-item {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid #ddd;
    background: #fefefe;
    color: #312873;
    flex-shrink: 0;
    min-width: 120px;
    justify-content: center;
}

.country-item .checkmark {
    position: absolute;
    top: 8px;
    right: 8px;
    color: rgb(35, 107, 4);
    font-size: 16px;
    font-weight: 700;
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s ease;
}

.country-radio:checked + .country-item .checkmark {
    opacity: 1;
    transform: scale(1.2);
}

.region-footer {
    display: flex;
    justify-content: flex-end;
}

html[dir="rtl"] .region-footer {
    justify-content: flex-start;
}

</style>

