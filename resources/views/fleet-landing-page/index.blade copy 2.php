<!DOCTYPE html>
<html class="no-js" lang="ar" dir="rtl">
  <head>
    <meta charset="utf-8" />

    <!--====== Title ======-->
    <title>

      fleet - taxi app</title>

    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!--====== Favicon Icon ======-->
    <!-- <link
      rel="shortcut icon"
      href="assets/images/favicon.png"
      type="image/png"
    /> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>


    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

      <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  {{-- <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet"> --}}
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">


    <!--====== CSS Files LinkUp ======-->
    <link rel="stylesheet" href="assets/css/animate.css" />
    <link rel="stylesheet" href="assets/css/glightbox.min.css" />
    <!-- <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" /> -->
    <link rel="stylesheet" href="assets/css/lineIcons.css" />
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/style2.css" />


    
  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">


  </head>

  <body>
    <!--[if IE]>
      <p class="browserupgrade">
        You are using an <strong>outdated</strong> browser. Please
        <a href="https://browsehappy.com/">upgrade your browser</a> to improve
        your experience and security.
      </p>
    <![endif]-->

    <!--====== PRELOADER PART START ======-->
    <div class="preloader">
      <div class="loader">
        <div class="spinner">
          <div class="spinner-container">
            <div class="spinner-rotator">
              <div class="spinner-left">
                <div class="spinner-circle"></div>
              </div>
              <div class="spinner-right">
                <div class="spinner-circle"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--====== PRELOADER PART ENDS ======-->

    <!--====== HEADER PART START ======-->
    <header class="header-area">
      <div class="navbar-area">
        <div class="container">
          <div class="row">
            <div class="col-lg-12">
              <nav class="navbar navbar-expand-lg">
                <a class="navbar-brand" href="index.html">
            <h1 style="
            font-size: 37px; 
            color: #ffcc00; 
            font-weight: bold; 
            font-family: 'Segoe UI', sans-serif; 
            text-shadow: 2px 2px 5px rgba(0,0,0,0.5);
            ">
              .fleet
            </h1>
                  <!-- <img src="assets/images/logo/logo.svg" alt="Logo" /> -->
                </a>
                <button
                  class="navbar-toggler"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#navbarSupportedContent"
                  aria-controls="navbarSupportedContent"
                  aria-expanded="false"
                  aria-label="Toggle navigation"
                >
                  <span class="toggler-icon"> </span>
                  <span class="toggler-icon"> </span>
                  <span class="toggler-icon"> </span>
                </button>

                <div
                class="collapse navbar-collapse sub-menu-bar"
                id="navbarSupportedContent"
              >
                <ul id="nav" class="navbar-nav ms-auto">
                  <li class="nav-item">
                    <a class="page-scroll active" href="#home">
                      {{-- Home --}}
                      الرئيسية
                    </a>
                  </li>
                  {{-- <li class="nav-item">
                    <a class="page-scroll" href="#why">Why</a>
                  </li> --}}
                  <li class="nav-item">
                    <a class="page-scroll" href="#features">
                      {{-- Features --}}
                      الميزات
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="page-scroll" href="#services">
                      {{-- Services --}}
                    الخدمات
                    </a>
                  </li>

                  <li class="nav-item">
                    <a class="page-scroll" href="#bbbbbb">
                      {{-- About --}}
                    حول التطبيق
                    </a>
                  </li>

                  <li class="nav-item">
                    <a class="page-scroll" href="#footer">
                      تواصل معنا
                      {{-- Contact --}}
                    </a>
                  </li>
                  
                  {{-- <li class="nav-item">
                    <a class="page-scroll" href="#payment">Payment Methods</a>
                  </li> --}}


                  {{-- <li class="nav-item">
                    <a href="javascript:void(0)">Team</a>
                  </li> --}}
                  {{-- <li class="nav-item">
                    <a href="javascript:void(0)">Blog</a>
                  </li> --}}
                </ul>
              </div>

              <!---lang-->
              <div class="lang-switcher">
                <button class="lang-btn" id="langToggle">
                  {{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}
                  <span class="arrow"></span>
                </button>
                <ul class="lang-menu">
                  <li><a href="{{ route('lang.switch', ['lang' => 'ar']) }}">العربية</a></li>
                  <li><a href="{{ route('lang.switch', ['lang' => 'en']) }}">English</a></li>
                </ul>
              </div>
              <script>
                document.addEventListener('DOMContentLoaded', function () {
                  const toggle = document.getElementById('langToggle');
                  const wrapper = toggle.closest('.lang-switcher');
              
                  toggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    wrapper.classList.toggle('open');
                  });
              
                  document.addEventListener('click', function () {
                    wrapper.classList.remove('open');
                  });
                });
              </script>
                <!-- navbar collapse -->

                <div class="navbar-btn d-none d-sm-inline-block">
                  <a
                    class="main-btn"
                    data-scroll-nav="0"
                    href="#download"
                    rel="nofollow"
                  >
                  حمل التطبيق الآن
                    <!-- Download Now -->
                  </a>
                </div>
              </nav>
              <!-- navbar -->
            </div>
          </div>
          <!-- row -->
        </div>
        <!-- container -->
      </div>
      <!-- navbar area -->

      <div
        id="home"
        class="header-hero bg_cover"
  style="background-image: url(assets/images/header/banner-bg.jpg); height: 100vh;"
      >
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-8">
              <div class="header-hero-content text-center">
                <h3
                class="header-sub-title wow fadeInUp"
                data-wow-duration="1.3s"
                data-wow-delay="0.2s"
                style="font-family: 'Poppins', sans-serif; font-size: 36px; font-weight: bold; color: white; text-align: center; line-height: 1.5; padding: 10px 0; margin: 0;">
                خدمة تاكسي ذكية تناسب نمط حياتك..
              </h3>
              
              <h4
                class="header-title wow fadeInUp"
                data-wow-duration="1.3s"
                data-wow-delay="0.5s"
                style="font-family: 'Poppins', sans-serif; font-size: 24px; font-weight: normal; color: white; text-align: center; line-height: 1.6; padding: 3px 0; margin: 0;">
                نقدم لك تجربة تنقّل ذكية بخيارات مرنة تناسب جميع احتياجاتك
                               </h4>

              <h4
              class="header-title wow fadeInUp"
              data-wow-duration="1.3s"
              data-wow-delay="0.5s"
              style="font-family: 'Poppins', sans-serif; font-size: 24px; font-weight: normal; color: white; text-align: center; line-height: 1.6; padding: 3px 0; margin: 0;">
             من الرحلات القصيرة إلى السفر الطويل
                              </h4>
              
              <!-- <p
                class="text wow fadeInUp"
                data-wow-duration="1.3s"
                data-wow-delay="0.8s"
                style="font-family: 'Poppins', sans-serif; font-size: 18px; color: white; text-align: center; line-height: 1.8; margin-bottom: 30px;">
                اختر نوع الخدمة، حدّد موقعك، وتابع سائقك مباشرة حتى وصوله إليك. Fleet يجعل التنقل بسيطًا، آمنًا وسريعًا.
              </p> -->
              
              <a
              class="main-btn wow fadeInUp"
              data-wow-duration="1.3s"
              data-wow-delay="1.1s"
              href="#why"           
               >

            ابدأ الآن
            </a>
            
              
              </div>
              <!-- header hero content -->
            </div>
          </div>
          <!-- row -->
          <div class="row">
            <div class="col-lg-12">
              <div
              class="header-hero-image text-right wow fadeIn"
              style="text-align: right; vertical-align: top; position: relative; top: 150;"
              data-wow-duration="1.3s"
              data-wow-delay="1.4s"
            >
              </div>
              <!-- header hero image -->
            </div>
          </div>
          <!-- row -->
        </div>
        <!-- container -->
        <div id="particles-1" class="particles"></div>
      </div>
      <!-- header hero -->
    </header>
    <!--====== HEADER PART ENDS ======-->

   <!--Why START-->
   <section id="why"  >
    <div id="booking" class="ride_section " >
      <div class="container">
        <div class="ride_main">
          <h1 class="ride_text wow zoomIn">لماذا <span style="color: #f4db31;">فلييت؟</span></h1>
      </div>
        </div>
    </div>
    <!--====== ABOUT PART START ======-->
    <div class="about-area" style="margin-top: 70px;">
      <div class="container">
        <div class="row">
          <div class="col-lg-6">
            <div
              class="about-content mt-50 wow fadeInLeftBig"
              data-wow-duration="0.5s"
              data-wow-delay="0.2s"
            >
              <div class="section-title">
                <div class="line"></div>
                <h3 class="title">مصمم <span>لتجربة نقل ذكية للمستخدم</span></h3>
              </div>
              <p class="text">
                اختر رحلتك بسهولة، سواء كانت قصيرة داخل المدينة أو طويلة بين المحافظات، مع إمكانية تحديد وجهات متعددة (Multi-Destination). 
                يمكنك تخصيص تجربتك، حفظ العناوين المعتادة، وتتبع حالة الرحلة بشكل لحظي. 
                الدفع سهل وآمن عبر محفظة فليت أو من خلال شركتي الاتصالات الرائدتين في سوريا.
                استمتع بكوبونات وحسومات دورية، ودعم مباشر على مدار الساعة لأي استفسار أو مشكلة.
              </p>
              <a href="javascript:void(0)" class="main-btn">ابدأ رحلتك الآن</a>
            </div>
            <!-- about content -->
          </div>
          <div class="col-lg-6">
            <div
              class="about-image text-center mt-50 wow fadeInRightBig"

            >
              <img src="assets/images/services/user1.jpg" alt="about" />
            </div>
            <!-- about image -->
          </div>
        </div>
        <!-- row -->
      </div>
      <!-- container -->
      <div class="about-shape-1">
        <img src="assets/images/about/about-shape-1.svg" alt="shape" />
      </div>
    </div>
    <!--====== ABOUT PART ENDS ======-->

    <!--====== ABOUT PART START ======-->
    <div class="about-area pt-70">
      <div class="about-shape-2">
        <img src="assets/images/about/about-shape-2.svg" alt="shape" />
      </div>
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 order-lg-last">
            <div
            class="about-image text-center mt-50 wow zoomIn"

            >
              <div class="section-title">
                <div class="line"></div>
                <h3 class="title">فرص ذهبية <span>لشركات ومكاتب التأجير</span></h3>
              </div>
              <p class="text">
                نوفر للمكاتب منصة رقمية متقدمة للوصول إلى قاعدة عملاء أوسع. 
                يمكنكم ربط عملكم بسائقين تابعين، وإدارة الطلبات والمدفوعات بكفاءة. 
                نظام العمولة لدينا مرن ومحفز، مع دعم فني وتقني دائم يضمن نجاح شراكتكم معنا.
              </p>
              <a href="javascript:void(0)" class="main-btn">انضم كـ مكتب</a>
            </div>
            <!-- about content -->
          </div>
          <div class="col-lg-6 order-lg-first">
            <div
            class="about-image text-center mt-50 wow zoomIn"
            style="width: 450px; text-align: left;"

          >
            <img src="assets/images/services/office22.png" alt="about" />
          </div>
            <!-- about image -->
          </div>
        </div>
        <!-- row -->
      </div>
      <!-- container -->
    </div>
    <!--====== ABOUT PART ENDS ======-->

    <!--====== ABOUT PART START ======-->
    <div class="about-area pt-70">
      <div class="container">
        <div class="row">
          <div class="col-lg-6">
            <div
              class="about-content mt-50 wow fadeInLeftBig"

            >
              <div class="section-title">
                <div class="line"></div>
                <h3 class="title">مزايا استثنائية <span>لشركائنا من السائقين</span></h3>
              </div>
              <p class="text">
                احصل على عمولات منافسة، ومرونة في اختيار الرحلات، ودعم مباشر على مدار الساعة.
                يمكنك إدارة أرباحك بسهولة من خلال محفظتك الرقمية، وتتبع الطلبات بواجهة استخدام بسيطة وآمنة.
                فليت تساعدك على بناء قاعدة زبائن وتوسيع دخلك بثقة.
              </p>
              <a href="javascript:void(0)" class="main-btn">انضم كـ سائق</a>
            </div>
            <!-- about content -->
          </div>
          <div class="col-lg-6">
            <div
            class="about-image text-center mt-50 wow zoomIn"
            data-wow-duration="1s"
            data-wow-delay="0.5s"
          >
          
            <img src="assets/images/services/driver5.png" alt="about" />

            </div>
            <!-- about image -->
          </div>
        </div>
        <!-- row -->
      </div>
      <!-- container -->
      <div class="about-shape-1">
        <img src="assets/images/about/about-shape-1.svg" alt="shape" />
      </div>
    </div>
    <!--====== WHY PART ENDS ======-->


    <!--====== SERVICES PART START ======-->
    <section id="features" class="services-area pt-120">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="section-title text-center pb-40">
              <div class="line m-auto"></div>
              <h3 class="title" >
                تصميم بسيط ونظيف,
                <span> يأتي مع كل ما تحتاجه لتبدأ رحلتك!</span>
              </h3>
            </div>
            <!-- section title -->
          </div>
        </div>
        <!-- row -->
        <div class="row justify-content-center">
          <div class="col-lg-4 col-md-7 col-sm-8">
            <div class="single-services text-center mt-30 wow fadeIn" data-wow-duration="1s" data-wow-delay="0.2s">
              <div class="services-icon">
                <img class="shape" src="assets/images/services/services-shape.svg" alt="shape" />
                <img class="shape-1" src="assets/images/services/services-shape-1.svg" alt="shape" />
                <i class="lni lni-baloon"> </i>
              </div>
              <div class="services-content mt-30">
                <h4 class="services-title">
                  <a href="javascript:void(0)">تصميم بسيط ونظيف</a>
                </h4>
                <p class="text">
                  تطبيق Fleet يأتي بتصميم بسيط وعصري يضمن لك تجربة مستخدم سريعة وسهلة. لا توجد تعقيدات، فقط كل ما تحتاجه في مكان واحد.
                </p>
                {{-- <a class="more" href="javascript:void(0)">
                  اكتشف المزيد <i class="lni lni-chevron-right"></i>
                </a> --}}
              </div>
            </div>
            <!-- single services -->
          </div>
          <div class="col-lg-4 col-md-7 col-sm-8">
            <div class="single-services text-center mt-30 wow fadeIn" data-wow-duration="1s" data-wow-delay="0.5s">
              <div class="services-icon">
                <img class="shape" src="assets/images/services/services-shape.svg" alt="shape" />
                <img class="shape-1" src="assets/images/services/services-shape-2.svg" alt="shape" />
                <i class="lni lni-cog"> </i>
              </div>
              <div class="services-content mt-30">
                <h4 class="services-title">
                  <a href="javascript:void(0)">قوي وموثوق</a>
                </h4>
                <p class="text">
                  مع Fleet، ستكون لديك تجربة تنقل قوية وموثوقة، حيث يقدم لك التطبيق أداءً ثابتًا وموارد مدمجة تساعدك في إدارة رحلاتك بكل سهولة.
                </p>
                {{-- <a class="more" href="javascript:void(0)">
                  اكتشف المزيد <i class="lni lni-chevron-right"></i>
                </a> --}}
              </div>
            </div>
            <!-- single services -->
          </div>
          <div class="col-lg-4 col-md-7 col-sm-8">
            <div class="single-services text-center mt-30 wow fadeIn" data-wow-duration="1s" data-wow-delay="0.8s">
              <div class="services-icon">
                <img class="shape" src="assets/images/services/services-shape.svg" alt="shape" />
                <img class="shape-1" src="assets/images/services/services-shape-3.svg" alt="shape" />
                <i class="lni lni-bolt-alt"> </i>
              </div>
              <div class="services-content mt-30">
                <h4 class="services-title">
                  <a href="javascript:void(0)">قوي وفعّال</a>
                </h4>
                <p class="text">
                  Fleet ليس مجرد تطبيق للنقل، بل هو تجربة قوية تجمع بين الأداء العالي وسرعة الاستجابة تضمن لك التنقل بفاعلية وأمان.
                </p>
                {{-- <a class="more" href="javascript:void(0)">
                  اكتشف المزيد <i class="lni lni-chevron-right"></i>
                </a> --}}
              </div>
            </div>
            <!-- single services -->
          </div>
        </div>
        <!-- row -->
      </div>
      <!-- container -->
    </section>
    
    <!--====== SERVICES PART ENDS ======-->

    <section id="services" class="taxis_section layout_padding" >
        <div class="container">
          <h1 class="our_text">Our <span style="color: #f4db31;">Services</span></h1>
          <div class="taxis_section_2">

                <div class="services-scroll center">

                @foreach ($services as $service)
                  <div class="taxi_main">
                    <div class="round_1">{{$loop->iteration}}</div>
                    <h2 class="carol_text">{{$service->title}}</h2>
                    <p class="reader_text">{{$service->description ?? 'no description'}}</p>
                    <br>
                    <div class="image-container">
                      <div class="images_2">
                        <a href="#"><img src="{{$service->image ?? ''}}" alt="SERVICE"></a>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
              </div>
        
                      
        </div>
 </section>



 <!-- About Section -->
<section id="bbbbbb" class="about section" >
<div class="container" data-aos="fade-up" data-aos-delay="100">
  <h1>...</h1>
  <span class="wow zoomIn" data-wow-delay=".2s">.</span>
  <div class="row justify-content-center" >
    <div class="col-lg-10">
      <div class="section-title text-center pb-40">
        <div class="line m-auto"></div>
        <h3 class="title">
          نظام آمن وموثوق لتنقلاتك اليومية,
          
          <span>لأن خدماتنا تحت إشراف كامل </span>
        </h3>
      </div>
      <!-- section title -->
    </div>
  </div>
  
  <div class="row align-items-xl-center gy-5 " >

    <div class="col-xl-5 content">
      <h3>عن فلييت</h3>
      <h2>نظام آمن وموثوق لتنقلاتك اليومية</h2>
      <p>نحن في فلييت نضع الأمان أولًا. نظامنا يوفر لك بيئة آمنة للتنقل من خلال سائقين معتمدين، سواء كانوا يعملون مع مكاتب أو بشكل مستقل. نقوم بالتحقق الكامل من أوراق السائقين لضمان راحة بالك أثناء كل رحلة. نحن نؤمن بتقديم خدمات تنقل ذكية، آمنة، وموثوقة لكل من السائقين والمستخدمين.</p>
      <a href="#" class="read-more"><span>اقرأ المزيد</span><i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="col-xl-7" style="margin-top: 50px; margin-bottom: 10px;">
      <div class="row gy-4 icon-boxes">

        <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
          <div class="icon-box">
            <i class="bi bi-shield-lock"></i>
            <h3>أمان مضمّن</h3>
            <p>كل سائق في فلييت يتم التحقق من أوراقه الشخصية، لضمان أن جميع من يقدمون الخدمة ملتزمين بالمعايير الأمنية العالية.</p>
          </div>
        </div> <!-- End Icon Box -->

        <div class="col-md-6" data-aos="fade-up" data-aos-delay="300">
          <div class="icon-box">
            <i class="bi bi-person-check"></i>
            <h3>تحقق دقيق من السائقين</h3>
            <p>نقوم بالتحقق الكامل من هوية السائقين ومستنداتهم، سواء كانوا يعملون مع مكاتب تأجير أو بشكل مستقل، لضمان أمان كل رحلة.</p>
          </div>
        </div> <!-- End Icon Box -->

        <div class="col-md-6" data-aos="fade-up" data-aos-delay="400">
          <div class="icon-box">
            <i class="bi bi-car-front"></i>
            <h3>سيارات موثوقة</h3>
            <p>نحن نضمن أن جميع السيارات التي يتم استخدامها في النظام هي سيارات موثوقة وتخضع لفحص دوري للتأكد من سلامتها.</p>
          </div>
        </div> <!-- End Icon Box -->

        <div class="col-md-6" data-aos="fade-up" data-aos-delay="500">
          <div class="icon-box">
            <i class="bi bi-clipboard-check"></i>
            <h3>مراقبة مستمرة</h3>
            <p>نظامنا يقدم لك المراقبة المستمرة أثناء كل رحلة، مع القدرة على تتبع السائق والتأكد من سير الرحلة بأمان ووفقًا للجدول الزمني.</p>
          </div>
        </div> <!-- End Icon Box -->

      </div>
    </div>

  </div>
</div>
</section><!-- /About Section -->


 
   

   




         <!--====== BRAND PART START ======-->
<section id="payment" class="brand-area">

          <div class="brand-area pt-10">
            <div class="container">
              <div class="section-title mt-40">
                <!-- <h2 class="wow fadeInUp" data-wow-delay=".4s">Payment Methods</h2> --><h2 class="wow fadeInUp" data-wow-delay=".4s">وسائل الدفع المتاحة في التطبيق</h2>
      
      <h3 class="wow fadeInUp" data-wow-delay=".1s">
        اختر وسيلة الدفع التي تناسبك من بين خيارات متعددة وآمنة حسب تفضيلاتك 
      </h3>
        
            </div>
            <div class="payment-scroll">
              @foreach ($payment_methods as $payment)
                        <!-- single logo -->
                    <div
                    class="single-logo mt-30 wow fadeIn"
                    data-wow-duration="1.5s"
                    data-wow-delay="0.2s"
                  >
                    <img src="{{$payment->image}}" alt="brand"
                    style="width: auto; height:  100px;
                    border-radius: 10px;
                    " 
                    />
                    <h3 style="margin-top: 25px">{{$payment->name}}</h3>
                  </div>
                  <!-- single logo -->
                    @endforeach
                
              </div>
              <!-- row -->
            </div>
            <!-- container -->
          </div>
          <!--====== BRAND PART ENDS ======-->
      
        <div style="margin-top: 100px;">
      
        </div>
          </section>
      
      
          
        <!-- Start Intro Video Area -->
        <section class="intro-video-area section">
          
          <div class="container" style="margin-bottom: 200px; margin-top: 150px;">
              <div class="row">
                  <div class="col-12">
                      <div class="inner-content-head">
                          <div class="inner-content">
                              <img class="shape1" src="assets/images/video/shape1.svg" alt="#" style="color: #ffcc00">
                              <img class="shape2" src="assets/images/video/shape2.svg" alt="#">
                              <div class="section-title">
                                  <span class="wow zoomIn" data-wow-delay=".2s">اجعل كل رحلة تجربة فريدة، خصص رحلتك واستمتع بكل لحظة</span>
                                  <h2 class="wow fadeInUp" data-wow-delay=".4s"> شاهد كيف يتم الطلب</h2>
                                  <p class="wow fadeInUp" data-wow-delay=".6s">سيوضح لك الفيديو التعريفي كل خطوة، ليُظهر لك مدى راحة استخدام تطبيقنا لخدمات التاكسي</p>
                              </div>
                              <div class="intro-video-play">
                                  <div class="play-thumb wow zoomIn" data-wow-delay=".2s">
                                      <a href="https://www.youtube.com/watch?v=yvefze898Jw"
                                          class="glightbox video"><i class="lni lni-play"></i></a>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </section>
      <!-- End Intro Video Area -->
      
      
      
      
      
      
          <!--====== DOWNLOAD APP PART START ======-->
      
          <section id="download" class="download_app_area pt-2 mb-1" style="margin-top: 200px;">
            <div class="container">
      
              
           
      
      
                <div class="inner-content">
                  <div class="row align-items-center">
        
      
                      <div class="col-lg-6 col-md-7 col-12">
                          <div class="text">
                              <h2  style="font-size: 35px;">تحميــل التــطبيق 
                                  <br> <h3>حمل التطبيق الآن و قم بإنشاء حسابك الخاص على فلييت.</h3>
                              </h2>
                          </div>
      
                          <div class="button" style="margin-top: 15px">
                              <a href="pricing.html" class="btn"><i class="lni lni-apple"></i> App Store
                              </a>
                              <a href="about-us.html" class="btn btn-alt"><i class="lni lni-play-store"></i> Google
                                  Play</a>
                          </div>
                      </div>
      
      
                      <div class="col-lg-6 col-md-12 text-center">
                        <div class="image wow fadeInRightBig" data-wow-duration="1.3s" data-wow-delay="0.5s">
                          <img src="assets/images/download.png" alt="download" style="max-width: 100%; height: auto;">
                        </div>
                      </div>
                      
                  </div>
                  
              </div>
        
            </div> <!-- container -->
          </section>
          
      
          <!--====== DOWNLOAD APP PART ENDS ======-->
          
      
        
      
      <!-- Testimonials Section -->
      <section id="testimonials" class="testimonials section dark-background" style="position: relative; overflow: hidden;">
        
      
        <img src="assets/images/footer/f1.jpg" alt="" style="
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          object-fit: cover;
          opacity: 0.3;
          z-index: 1;
        ">
        <div class="container" data-aos="fade-up" data-aos-delay="100">
      
          <div class="swiper" style="margin-top: 85px">
            <div class="swiper-wrapper">
      
              <div class="swiper-slide">
                <div class="testimonial-item">
                  <img src="assets/images/testimonials/user_no_photo.png" class="testimonial-img" alt="">
                  <h3>firstName LastName</h3>
                  {{-- <h4>CEO </h4> --}}
                  <div class="stars">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                  </div>
                  <p>
                    <i class="bi bi-quote quote-icon-left"></i>
                    <span style="color: rgb(203, 207, 216)">التطبيق سهل الاستخدام جدًا، وتمكنت من حجز رحلة بسرعة..كانت الرحلة سلسة والأجرة معقولة.</span>
                    <i class="bi bi-quote quote-icon-right"></i>
                  </p>
                </div>
              </div>
      
      
              <div class="swiper-slide">
                <div class="testimonial-item">
                  <img src="assets/images/testimonials/user_no_photo.png" class="testimonial-img" alt="">
                  <h3>firstName LastName</h3>
                  {{-- <h4> Founder</h4> --}}
                  <div class="stars">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                  </div>
                  <p>
                    <i class="bi bi-quote quote-icon-left"></i>
                    <span style="color: rgb(203, 207, 216)">كانت تجربتي رائعة مع خدمة التاكسي هذه! السائق كان في الموعد، والرحلة كانت مريحة، أوصي بها بشدة!</span>
                    <i class="bi bi-quote quote-icon-right"></i>
                  </p>
                </div>
              </div>
      
      
            </div>
      
            <!-- Pagination -->
            <div class="swiper-pagination"></div>
          </div>
      
        </div>
      </section>
      
      
      <!-- Swiper Init -->
      <script>
        new Swiper('.swiper', {
          loop: true,
          speed: 600,
          autoplay: {
            delay: 5000
          },
          slidesPerView: 'auto',
          pagination: {
            el: '.swiper-pagination',
            clickable: true
          }
        });
      </script>
      
    
  <!-- Start Faq Area -->
<section class="faq section" style="margin-top: 100px;">
  <div class="container" >
      <div class="row" tyle="margin-top: 400px;" >
          <div class="col-12">
              <div class="section-title"tyle="margin-top: 300px;">
                <h1>..</h1>
                <h1>...</h1>
                <h1>....</h1>

                  <h3 class="wow zoomIn" data-wow-delay=".2s">الأسئلة الشائعة</h3>
                  <h2 class="wow fadeInUp" data-wow-delay=".4s">كل ما تحتاج معرفته</h2>
                  <p class="wow fadeInUp" data-wow-delay=".5s">إجابات لأكثر الأسئلة التي تردنا حول التطبيق وخدماته.</p>
              </div>
          </div>
      </div>

      <div class="row">
          <!-- العمود الأول -->
          <div class="col-lg-6 col-md-12 col-12">
              <div class="accordion" id="accordionExample">
                  <div class="accordion-item">
                      <h2 class="accordion-header" id="heading1">
                          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                              data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
                              <span class="title">ما هي وسائل الدفع المتاحة في التطبيق؟</span><i class="lni lni-plus"></i>
                          </button>
                      </h2>
                      <div id="collapse1" class="accordion-collapse collapse" aria-labelledby="heading1"
                          data-bs-parent="#accordionExample">
                          <div class="accordion-body">
                              <p>يمكنك الدفع باستخدام المحفظة الإلكترونية (مثل فليت)، أو عبر شركات الاتصالات المحلية. جميع طرق الدفع مؤمنة وسهلة الاستخدام.</p>
                          </div>
                      </div>
                  </div>

                  <div class="accordion-item">
                    <h2 class="accordion-header" id="heading2">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                            <span class="title">ما هي متطلبات التسجيل في تطبيق السائق؟</span><i class="lni lni-plus"></i>
                        </button>
                    </h2>
                    <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="heading2"
                        data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <p>للتسجيل في تطبيق السائق، يجب أن يكون لديك رخصة قيادة سارية، بطاقة هوية، تأمين للمركبة، وأوراق المركبة الرسمية. بعد رفع المستندات المطلوبة، سيتم مراجعتها والموافقة عليها خلال فترة قصيرة.</p>
                        </div>
                    </div>
                </div>
                

                  <div class="accordion-item">
                      <h2 class="accordion-header" id="heading3">
                          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                              data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                              <span class="title">هل أستطيع تتبع طلبي أو رحلتي مباشرة؟</span><i class="lni lni-plus"></i>
                          </button>
                      </h2>
                      <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="heading3"
                          data-bs-parent="#accordionExample">
                          <div class="accordion-body">
                              <p>نعم، يوفر التطبيق ميزة تتبع مباشر لحالة الطلب أو موقع السيارة في الوقت الحقيقي، لتبقى دائمًا على اطلاع.</p>
                          </div>
                      </div>
                  </div>
              </div>
          </div>

          <!-- العمود الثاني -->
          <div class="col-lg-6 col-md-12 col-12 xs-margin">
              <div class="accordion" id="accordionExample2">

                  <div class="accordion-item">
                      <h2 class="accordion-header" id="heading11">
                          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                              data-bs-target="#collapse11" aria-expanded="false" aria-controls="collapse11">
                              <span class="title">هل يتوفر دعم فني على مدار الساعة؟</span><i class="lni lni-plus"></i>
                          </button>
                      </h2>
                      <div id="collapse11" class="accordion-collapse collapse" aria-labelledby="heading11"
                          data-bs-parent="#accordionExample2">
                          <div class="accordion-body">
                              <p>نعم، يتوفر دعم فني من خلال التطبيق على مدار 24 ساعة لمساعدتك في أي مشكلة أو استفسار.</p>
                          </div>
                      </div>
                  </div>

                  <div class="accordion-item">
                      <h2 class="accordion-header" id="heading22">
                          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                              data-bs-target="#collapse22" aria-expanded="false" aria-controls="collapse22">
                              <span class="title">هل يمكنني تعديل بياناتي الشخصية لاحقًا؟</span><i class="lni lni-plus"></i>
                          </button>
                      </h2>
                      <div id="collapse22" class="accordion-collapse collapse" aria-labelledby="heading22"
                          data-bs-parent="#accordionExample2">
                          <div class="accordion-body">
                              <p>بكل تأكيد. يمكنك تعديل بياناتك الشخصية مثل رقم الهاتف أو البريد الإلكتروني أو كلمة المرور من خلال صفحة "الملف الشخصي".</p>
                          </div>
                      </div>
                  </div>

                  <div class="accordion-item">
                      <h2 class="accordion-header" id="heading33">
                          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                              data-bs-target="#collapse33" aria-expanded="false" aria-controls="collapse33">
                              <span class="title">هل التطبيق متاح لجميع أنواع الأجهزة؟</span><i class="lni lni-plus"></i>
                          </button>
                      </h2>
                      <div id="collapse33" class="accordion-collapse collapse" aria-labelledby="heading33"
                          data-bs-parent="#accordionExample2">
                          <div class="accordion-body">
                              <p>نعم، التطبيق متوفر لأنظمة Android و iOS ويمكن تحميله من المتاجر الرسمية بسهولة.</p>
                          </div>
                      </div>
                  </div>

              </div>
          </div>
      </div>
  </div>
</section>

    <!--/ End Faq Area -->


    

       
    
    <!--====== VIDEO COUNTER PART ENDS ======-->

    <!--====== FOOTER PART START ======-->
    <footer id="footer" class="footer-area pt-300">
      <div class="container">
        <!-- subscribe area -->
        <div class="footer-widget pb-100">
          <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-8">
              <div
                class="footer-about mt-50 wow fadeIn"
                data-wow-duration="1s"
                data-wow-delay="0.2s"
              >
              <h1 style="
              font-size: 37px; 
              color: #ffcc00; 
              font-weight: bold; 
              font-family: 'Segoe UI', sans-serif; 
              text-shadow: 2px 2px 5px rgba(0,0,0,0.5);
              ">
                .fleet
              </h1>
                <p class="text">
                  استكشف مجموعة واسعة من المركبات المصانة بعناية، المصممة لتوفير الراحة والأمان والكفاءة في كل رحلة. سواء كنت تسافر للعمل أو للترفيه، لدينا السيارة المثالية لك
                </p>
                <ul class="social">
                  <li>
                    <a href="javascript:voi)">
                      <i class="lni lni-facebook-filled"> </i>
                    </a>
                  </li>
                  <li>
                    <a href="javascript:void)">
                      <i class="lni lni-twitter-filled"> </i>
                    </a>
                  </li>
                  <li>
                    <a href="javascript:">
                      <i class="lni lni-instagram-filled"> </i>
                    </a>
                  </li>
                  <li>
                    <a href="javascript:voi">
                      <i class="lni lni-linkedin-original"> </i>
                    </a>
                  </li>
                </ul>
              </div>
              <!-- footer about -->
            </div>
            <div class="col-lg-5 col-md-7 col-sm-12">
              <div class="footer-link d-flex mt-50 justify-content-sm-between">
                <div
                  class="link-wrapper wow fadeIn"
                  data-wow-duration="1s"
                  data-wow-delay="0.4s"
                >
                  <div class="footer-title">
                    {{-- <h4 class="title"> Quick Link </h4> --}}
                    <h4 class="title"> رابط سريع </h4>

                  </div>
                  <ul class="link">
                    <li><a href="javascript:void(0)">خريطة الطريق</a></li>
                    <li><a href="javascript:void(0)">سياسة الخصوصية</a></li>
                    <li><a href="javascript:void(0)">سياسة الاسترجاع</a></li>
                    <li><a href="javascript:void(0)">شروط الخدمة</a></li>
                    <li><a href="javascript:void(0)">التسعير</a></li>                    
                  </ul>
                </div>
                <!-- footer wrapper -->
                <div
                  class="link-wrapper wow fadeIn"
                  data-wow-duration="1s"
                  data-wow-delay="0.6s"
                >
                  <div class="footer-title">
                    <h4 class="title">موارد</h4>
                  </div>
                  <ul class="link">
                    <li><a href="home">الرئيسية</a></li>
                    <li><a href="services">خدماتنا</a></li>
                    <li><a href="about1">حول التطبيق</a></li>
                    <li><a href="payment">وسائل الدفع</a></li>
                    <li><a href="video">كيف ؟</a></li>
                  </ul>
                </div>
                <!-- footer wrapper -->
              </div>
              <!-- footer link -->
            </div>
            <div class="col-lg-3 col-md-5 col-sm-12">
              <div
                class="footer-contact mt-50 wow fadeIn"
                data-wow-duration="1s"
                data-wow-delay="0.8s"
              >
                <div class="footer-title">
                  {{-- <h4 class="title">Contact Us</h4> --}}
                  <h4 class="title">اتصل بنا</h4>

                </div>
                <ul class="contact">
                  <li>+963900000000</li>
                  <li>info@gmail.com</li>
                  <li>www.CodePac.com</li>
                  <li>
                   اتوستراد المزة - برج تالا<br/>
                   دمشق ، المزة 

                  </li>
                </ul>
              </div>
              <!-- footer contact -->
            </div>
          </div>
          <!-- row -->
        </div>
        <!-- footer widget -->
        <div class="footer-copyright">
          <div class="row">
            <div class="col-lg-12">
              <div class="copyright d-sm-flex justify-content-between">
                <div class="copyright-content">
                  <p class="text">
                    Designed and Developed by CodePac - 2025
                    <!-- <a href="" rel="nofollow"> codeP </a> -->
                  </p>
                </div>
                <!-- copyright content -->
              </div>
              <!-- copyright -->
            </div>
          </div>
          <!-- row -->
        </div>
        <!-- footer copyright -->
      </div>
      <!-- container -->
      <div id="particles-2"></div>
    </footer>
    <!--====== FOOTER PART ENDS ======-->

    <!--====== BACK TOP TOP PART START ======-->
    <a href="#" class="back-to-top"> <i class="lni lni-chevron-up"> </i> </a>
    <!--====== BACK TOP TOP PART ENDS ======-->

    <!--====== Javascript Files ======-->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/glightbox.min.js"></script>
    <script src="assets/js/count-up.min.js"></script>
    <script src="assets/js/particles.min.js"></script>
    <script src="assets/js/main.js"></script>


       <script src="assets/js/tiny-slider.js"></script>

       <script>
   
           //========= testimonial 
           tns({
               container: '.testimonial-slider',
               items: 3,
               slideBy: 'page',
               autoplay: false,
               mouseDrag: true,
               gutter: 0,
               nav: true,
               controls: false,
               responsive: {
                   0: {
                       items: 1,
                   },
                   540: {
                       items: 1,
                   },
                   768: {
                       items: 2,
                   },
                   992: {
                       items: 2,
                   },
                   1170: {
                       items: 3,
                   }
               }
           });
   
           //====== counter up 
           var cu = new counterUp({
               start: 0,
               duration: 2000,
               intvalues: true,
               interval: 100,
               append: " ",
           });
           cu.start();
   
           //========= glightbox
           GLightbox({
               'href': 'https://www.youtube.com/watch?v=r44RKWyfcFw&fbclid=IwAR21beSJORalzmzokxDRcGfkZA1AtRTE__l5N4r09HcGS5Y6vOluyouM9EM',
               'type': 'video',
               'source': 'youtube', //vimeo, youtube or local
               'width': 900,
               'autoplayVideos': true,
           });
   
       </script>

        <!-- Scroll Top -->
 <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  </body>
</html>
