<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إضافة اشتراك جديد</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: #f8f9fa;
      font-family: 'Tajawal', sans-serif;
    }
    .form-section {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
      padding: 2rem;
      margin-top: 2rem;
      position: relative;
    }
    .form-section:hover {
      transform: translateY(-4px);
    }
    .form-control:focus {
      box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    .submit-btn {
      background-color: #0d6efd;
      color: white;
      font-weight: bold;
      transition: all 0.3s ease;
    }
    .submit-btn:hover {
      background-color: #0b5ed7;
      transform: scale(1.05);
    }
    .animation-overlay {
      position: absolute;
      top: -60px;
      left: 50%;
      transform: translateX(-50%);
      width: 120px;
      height: 120px;
      z-index: 10;
      pointer-events: none;
    }
    @media (max-width: 768px) {
      .animation-overlay {
        top: -40px;
        width: 80px;
        height: 80px;
      }
    }
  </style>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.10.0/lottie.min.js"></script>
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="form-section">
          <!-- Animation Overlay -->
          <div id="subscriptionAnimation" class="animation-overlay"></div>

          <h2 class="text-center mb-4">إضافة اشتراك جديد</h2>
          <form method="POST" action="/subscriptions/store">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">اسم العميل الثلاثي <i class="fa fa-user"></i></label>
                <input type="text" name="full_name" class="form-control" placeholder="الاسم الكامل" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">رقم الهاتف <i class="fa fa-phone"></i></label>
                <input type="tel" name="phone" class="form-control" placeholder="09XXXXXXXX" pattern="09[0-9]{8}" required>
              </div>

              <div class="col-12">
                <label class="form-label">العنوان / مكان العلبة <i class="fa fa-map-marker-alt"></i></label>
                <input type="text" name="address" class="form-control" placeholder="مثال: علبة 74 فرن النهضة" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">اسم المستخدم <i class="fa fa-user-circle"></i></label>
                <input type="text" name="username" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">كلمة المرور <i class="fa fa-lock"></i></label>
                <input type="password" name="password" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">السرعة <i class="fa fa-bolt"></i></label>
                <select name="speed" class="form-select" required>
                  <option selected disabled>اختر السرعة</option>
                  <option value="1 MB">1 MB</option>
                  <option value="2 MB">2 MB</option>
                  <option value="4 MB">4 MB</option>
                  <option value="8 MB">8 MB</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">تاريخ التسجيل <i class="fa fa-calendar"></i></label>
                <input type="date" name="registration_date" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">المبلغ (ل.س) <i class="fa fa-money-bill-wave"></i></label>
                <input type="number" name="amount" class="form-control" placeholder="أدخل المبلغ" required>
              </div>

              <div class="col-12">
                <label class="form-label">ملاحظات <i class="fa fa-sticky-note"></i></label>
                <textarea name="notes" class="form-control" rows="2" placeholder="تفاصيل إضافية إن وجدت"></textarea>
              </div>

              <div class="col-12 text-center">
                <button type="submit" class="btn submit-btn px-5 mt-3">
                  <i class="fa fa-plus"></i> إضافة الاشتراك
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // تحميل أنيميشن برج بث الإنترنت - رابط جديد موثوق
    lottie.loadAnimation({
      container: document.getElementById('subscriptionAnimation'),
      renderer: 'svg',
      loop: true,
      autoplay: true,
      path: 'https://assets10.lottiefiles.com/packages/lf20_YXD37q.json'
    });
  </script>
  
</body>
</html>