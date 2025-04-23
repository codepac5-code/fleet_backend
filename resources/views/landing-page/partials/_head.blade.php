<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>{{env('APP_NAME')}} Service - On-Demand Home Service Flutter App with Complete Solution</title>
<link rel="shortcut icon" class="favicon_preview" href="{{ getDashboardLogo() }}" />
<link rel="stylesheet" href="https://innoquad.in/extra-disk/innoquad_extra_disk/themes/handyman_frontend_html/html/dist/assets/vendor/swiperSlider/swiper-bundle.min.css"/>
<link rel="stylesheet" href="{{ asset('css/landing-page.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/landing-page-rtl.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/landing-page.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/landing-page-custom.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/@fortawesome/fontawesome-free/css/all.min.css')}}">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- <meta name="assert_url" content="{{ URL::to('') }}" /> --}}

<meta name="baseUrl" content="{{env('APP_URL')}}" />

<!-- Required Facebook Open Graph Tags -->
<meta property="og:type" content="website" />
<meta property="og:image" content="https://appyourserbisyo.ph/storage/7101/Untitled-design-(7).png" />
<meta property="og:url" content="https://appyourserbisyo.ph" />
<meta property="og:title" content="Appyourserbisyo Service - On-Demand Home Service" />
<meta property="og:description" content="On-Demand Home Service With Complete Solution" />

<!-- Facebook App ID -->
<meta property="fb:app_id" content="1035518844893652" />

<!-- App Links Meta Tags -->
<meta property="al:ios:url" content="https://appyourserbisyo.ph" />
<meta property="al:ios:app_store_id" content="6469496340" />
<meta property="al:ios:app_name" content="AYS (Instant Services)" />

<meta property="al:android:url" content="https://appyourserbisyo.ph" />
<meta property="al:android:package" content="ays.instant.service" />
<meta property="al:android:app_name" content="AYS (Instant Service)" />

<meta property="al:web:url" content="https://appyourserbisyo.ph" />
{{-- @php
        $currentLang = app()->getLocale();
        $langFolderPath = resource_path("lang/$currentLang");
        $filePaths = \File::files($langFolderPath);
    @endphp

    @foreach ($filePaths as $filePath)
        @php
            $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        @endphp
        <script>
            window.localMessagesUpdate = {
                ...window.localMessagesUpdate,
                "{{ $fileName }}": @json(require($filePath))
            };
        </script>
    @endforeach --}}





