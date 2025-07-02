<!DOCTYPE html>
<html class="no-js" lang="ar" dir="rtl">
  <head>
    <meta charset="utf-8" />

    <!--====== Title ======-->
    <title>

      fleet- support </title>

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

  <style>
form {
  background-color: #fff;
  padding: 30px 25px;
  border-radius: 10px;
  box-shadow: 0 0 15px rgba(0,0,0,0.1);
  max-width: 500px;
  margin: 50px auto 70px auto;
  font-family: 'Poppins', sans-serif;
}

label {
  display: block;
  margin-top: 20px;
  margin-bottom: 8px;

}

input, textarea, select {
  width: 100%;
  padding: 12px 15px;
  border-radius: 6px;
  border: 1.5px solid #ccc;
  box-sizing: border-box;
  transition: border-color 0.3s ease;
}

input:focus, textarea:focus, select:focus {
  outline: none;
  border-color: #ffcc00;
  box-shadow: 0 0 5px rgba(255,204,0,0.5);
}

textarea {
  resize: vertical;
}

button {
  width: 100%;
  background-color: #d9534f;
  color: white;
  padding: 14px;
  border: none;
  border-radius: 6px;
  margin-top: 30px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #c9302c;
}

.checkbox-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 25px;
}

.checkbox-row input[type="checkbox"] {
  width: 20px;
  height: 20px;
  margin: 0;
  cursor: pointer;
}

.checkbox-row label {
  line-height: 1.4;
  cursor: pointer;
  color: #555;
  max-width: 90%;
}

.container1 {
  display: flex;
  justify-content: center; /* توسيط أفقي */
  align-items: center;     /* توسيط عمودي */
  min-height: 80vh;        /* ارتفاع نسبي (يمكن تعديله) */
  flex-direction: column;  /* لو فيه أكثر من عنصر، تكون عمودياً */
}



  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

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

           
              </nav>
              <!-- navbar -->
            </div>
          </div>
          <!-- row -->
        </div>
        <!-- container -->
      </div>
      <!-- navbar area -->

 

 
   

   



      
    


    

       






















      <section style="margin-top: 150px; margin-bottom: 70px;">
        <div class="container1">
          <div class="about-content mt-50 wow fadeInLeftBig" data-wow-duration="0.5s" data-wow-delay="0.2s">
            <div class="section-title text-center">
              <div class="line" style="margin: auto;"></div>
              <h3 class="title">طلب حذف الحساب</h3>
            </div>

            <p style="text-align: center;">يرجى تعبئة النموذج التالي لإرسال طلب حذف الحساب.</p>
      
            <form onsubmit="handleSubmit(event)" method="post" enctype="text/plain"
              style="max-width: 500px; margin: 50px auto 70px auto; padding: 30px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;">
              
              <label for="account-type">نوع الحساب:</label>
              <select id="account-type" name="نوع الحساب" required>
                <option value="">اختر نوع الحساب</option>
                <option value="مستخدم">مستخدم</option>
                <option value="سائق">سائق</option>
              </select>
              

              <label for="name">الاسم الكامل:</label>
              <input type="text" id="name" name="الاسم" required
                style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc;">
      
                <label for="mobile">رقم الموبايل (الإمارات):</label>
                <input type="tel" id="mobile" name="رقم الموبايل" required
                  pattern="^(\+971|0)?5\d{8}$"
                  placeholder="0501234567 أو +971501234567"
                  style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc;">
                
              <label for="message">سبب الحذف (اختياري):</label>
              <textarea id="message" name="سبب الحذف" rows="4"
                style="width: 100%; padding: 10px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #ccc;"></textarea>
                <div class="checkbox-row">
                  <input type="checkbox" id="confirmDelete" required>
                  <label for="confirmDelete">أؤكد أنني أرغب في حذف الحساب نهائيًا وأفهم أن هذه العملية لا يمكن التراجع عنها.</label>
                </div>
                
                
              <button type="submit"
                style="width: 100%; background-color: #d9534f; color: white; padding: 12px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">
                إرسال الطلب عبر البريد
              </button>
            </form>
            
          </div>
        </div>
      </section>







      




      <div id="success-toast" style="
  display: none;
  position: fixed;
  bottom: 30px;
  right: 30px;
  background-color: #28a745;
  color: white;
  padding: 15px 20px;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  font-weight: bold;
  z-index: 9999;
">
  ✅ تم إرسال الطلب بنجاح للمراجعة.
</div>



<script>
  function handleSubmit(event) {
    event.preventDefault();

    const toast = document.getElementById("success-toast");
    toast.style.display = "block";

    setTimeout(() => {
      toast.style.display = "none";
    }, 3000);

    event.target.reset();
  }
</script>











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
                    {{-- <li><a href="javascript:void(0)">خريطة الطريق</a></li> --}}
                    <li><a href="{{route('privacy-policy')}}">سياسة الخصوصية</a></li>
                    {{-- <li><a href="javascript:void(0)">سياسة الاسترجاع</a></li>
                    <li><a href="javascript:void(0)">شروط الخدمة</a></li>
                    <li><a href="javascript:void(0)">التسعير</a></li>                     --}}
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
                    <li><a href="#home">الرئيسية</a></li>
                    <li><a href="#services">خدماتنا</a></li>
                    <li><a href="#about"> حول التطبيق</a></li>
                    <li><a href="#payment">وسائل الدفع</a></li>
                    <li><a href="#video">كيف ؟</a></li>
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
                   اتوستراد  - برج تالا<br/>
                   الامارات العربية المتحدة ، دبي 

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
