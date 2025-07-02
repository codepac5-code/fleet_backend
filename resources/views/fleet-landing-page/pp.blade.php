<!DOCTYPE html>
<html class="no-js" lang="en" dir="ltr">
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
    <!--====== HEADER PART ENDS ======-->

   <!--Why START-->
   <section id="why"  >
    <div id="booking" class="ride_section " >
      <div class="container">
        <div class="ride_main">
          <h1 class="ride_text wow zoomIn"> <span style="color: #f4db31;">Privacy Policy</span></h1>
      </div>
        </div>
    </div>
    <!--====== ABOUT PART START ======-->
    <div class="about-area" style="margin-top: 70px; ">
      <div class="container center">
        
  
    </div>
    <!--====== ABOUT PART ENDS ======-->

    <!--====== ABOUT PART START ======-->
    <div class="about-area pt-70">
      <div class="center " style="margin-top: 100px; margin-left:50px; color:gray; font-size:30px; " >
      
      <div id="pp"></div>

    </div>    
  </div>
   <!-- area -->

    <!--====== ABOUT PART ENDS ======-->

  </body>


  <script>

document.addEventListener('DOMContentLoaded', function () {

                 
  fetch(`/ppp`)
    .then(response => response.json())
    .then(data => {
      const pp = data.pp || [];
      const wrapper = document.getElementById('pp');
       wrapper.innerHTML = pp;                   
    });       
});
  </script>
</html>
