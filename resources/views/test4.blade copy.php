<x-master-layout>
    <head>
      <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
      />
      <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&family=Roboto+Mono&display=swap" rel="stylesheet" />
      <style>
        /* نفس الستايل الأساسي */
        body, html {
          font-family: 'Cairo', Tahoma, Geneva, Verdana, sans-serif;
          background: #f9fafb;
          color: #222;
          margin: 0;
          padding: 0;
        }
        .container {
          max-width: 1280px;
          margin: 35px auto;
          padding: 0 30px;
        }
        .filter-section {
          display: flex;
          flex-wrap: wrap;
          justify-content: center;
          gap: 16px;
          margin-bottom: 15px;
        }
        .filter-section label {
          font-weight: 700;
          font-size: 1rem;
          align-self: center;
        }
        .filter-section input[type="date"] {
          padding: 9px 18px;
          border-radius: 10px;
          border: 1.5px solid #ddd;
          font-size: 1.1rem;
          width: 185px;
          transition: border-color 0.25s ease;
        }
        .filter-section input[type="date"]:focus {
          border-color: #f59e0b;
          outline: none;
        }
        .filter-section button {
          background-color: #f59e0b;
          border: none;
          color: #fff;
          font-weight: 800;
          font-size: 1.25rem;
          padding: 14px 38px;
          border-radius: 16px;
          cursor: pointer;
          box-shadow: 0 6px 15px rgb(245 158 11 / 0.45);
          transition: background-color 0.3s ease, transform 0.2s ease;
          flex-shrink: 0;
        }
        .filter-section button:hover {
          background-color: #b45309;
          transform: scale(1.1);
        }
        .reset-filter {
          background-color: #6b7280;
          margin-left: 14px;
        }
        .reset-filter:hover {
          background-color: #4b5563;
          transform: scale(1.1);
        }
        .quick-filters {
          display: flex;
          justify-content: center;
          gap: 18px;
          margin-bottom: 35px;
        }
        .quick-filters button {
          background: transparent;
          border: 3px solid #f59e0b;
          color: #f59e0b;
          padding: 12px 32px;
          border-radius: 32px;
          font-weight: 700;
          font-size: 1.2rem;
          cursor: pointer;
          transition: all 0.3s ease;
        }
        .quick-filters button:hover,
        .quick-filters button.active {
          background-color: #f59e0b;
          color: #fff;
          box-shadow: 0 7px 18px rgb(245 158 11 / 0.7);
        }
        section.dashboard-group {
          margin-bottom: 70px;
          background: #fff;
          border-radius: 20px;
          box-shadow: 0 14px 30px rgb(0 0 0 / 0.08);
          padding: 40px 48px;
          display: flex;
          flex-direction: column;
          gap: 36px;
          width: 100%;
          max-width: 1280px;
          margin-left: auto;
          margin-right: auto;
          transition: background 0.3s ease;
        }
        section.dashboard-group:nth-child(even) {
          background: #fff9f0;
        }
        .section-title-container {
          border-left: none;
          border-bottom: 5px solid #f59e0b;
          padding-bottom: 18px;
          margin-bottom: 28px;
          display: flex;
          justify-content: center;
          align-items: center;
          width: 100%;
        }
        h2.section-title {
          font-size: 2.2rem;
          font-weight: 900;
          color: #444;
          margin: 0;
          letter-spacing: 0.08em;
          text-align: center;
        }
        #loader {
          display: none;
          text-align: center;
          margin-bottom: 35px;
          font-size: 1.4rem;
          color: #f59e0b;
          font-weight: 700;
        }
  
        /* كاردات وحجم أصغر وأيقونات أصغر للمستحقات والمبلغ المسحوب */
        .cards-container {
          display: flex;
          flex-wrap: wrap;
          justify-content: flex-start;
          gap: 20px;
          opacity: 0;
          transform: translateY(30px);
          animation: fadeInUp 0.6s ease-out forwards;
        }
        .dashboard-card {
          flex: 1 1 calc(33.333% - 20px);
          min-width: 260px;
          max-width: 33.333%;
          background: #fff;
          border-radius: 16px;
          padding: 20px 28px;
          box-shadow: 0 6px 18px rgb(245 158 11 / 0.15);
          display: flex;
          align-items: center;
          cursor: default;
          transition: box-shadow 0.3s ease, transform 0.25s ease;
          text-align: left;
          user-select: none;
          height: 110px;
        }
        .dashboard-card:hover {
          box-shadow: 0 20px 48px rgb(245 158 11 / 0.35);
          transform: translateY(-6px);
        }
        .dashboard-card i {
          font-size: 2.8rem;
          color: #f59e0b;
          margin-right: 18px;
          transition: color 0.3s ease;
          flex-shrink: 0;
        }
        .dashboard-value {
          font-family: 'Roboto Mono', monospace;
          font-size: 1.9rem;
          font-weight: 900;
          margin-bottom: 4px;
          letter-spacing: 0.06em;
          line-height: 1.2;
          transition: color 0.3s ease;
        }
        .dashboard-label {
          font-size: 1rem;
          color: #444;
          font-weight: 700;
          line-height: 1.2;
          user-select: text;
        }
        .currency {
          font-size: 1rem;
          font-weight: 700;
          color: #f59e0b;
          margin-left: 8px;
          align-self: flex-start;
          margin-top: 4px;
        }
        @keyframes fadeInUp {
          from {
            opacity: 0;
            transform: translateY(30px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }
        @media (max-width: 1100px) {
          .dashboard-card {
            flex: 1 1 calc(50% - 20px);
            max-width: 50%;
          }
        }
        @media (max-width: 480px) {
          .filter-section {
            flex-direction: column;
            align-items: center;
          }
          .filter-section input[type="date"],
          .filter-section button {
            width: 100%;
            max-width: 340px;
          }
          .quick-filters {
            flex-wrap: wrap;
            gap: 14px;
          }
          .dashboard-value {
            font-size: 2.6rem;
          }
          .dashboard-card {
            flex-direction: column;
            text-align: center;
            padding: 28px 20px;
            height: auto;
          }
          .dashboard-card i {
            margin: 0 0 20px 0;
            font-size: 2.4rem;
          }
          .currency {
            margin-left: 0;
            margin-top: 8px;
          }
        }
      </style>
    </head>
  
    <body>
      <div class="container">
        <!-- الأقسام الثابتة خارج الفلترة -->
        <section class="dashboard-group" aria-label="معلومات المستحقات والمبلغ المسحوب">
          <div class="section-title-container">
            <h2 class="section-title">المستحقات والمبلغ المسحوب</h2>
          </div>
          <div class="cards-container">
            <div class="dashboard-card" tabindex="0" aria-label="مستحقات على المكاتب">
              <i class="fa-solid fa-building"></i>
              <div>
                <div id="due-offices" class="dashboard-value">0</div>
                <div class="currency">ج.م</div>
                <div class="dashboard-label">مستحقات على المكاتب</div>
              </div>
            </div>
  
            <div class="dashboard-card" tabindex="0" aria-label="مستحقات على السائقين">
              <i class="fa-solid fa-car"></i>
              <div>
                <div id="due-drivers" class="dashboard-value">0</div>
                <div class="currency">ج.م</div>
                <div class="dashboard-label">مستحقات على السائقين</div>
              </div>
            </div>
  
            <div class="dashboard-card" tabindex="0" aria-label="المبلغ المتاح للسحب حالياً">
              <i class="fa-solid fa-wallet"></i>
              <div>
                <div id="available-amount" class="dashboard-value">0</div>
                <div class="currency">ج.م</div>
                <div class="dashboard-label">المبلغ المتاح للسحب حالياً</div>
              </div>
            </div>
  
            <div class="dashboard-card" tabindex="0" aria-label="المبلغ المسحوب">
              <i class="fa-solid fa-money-bill-transfer"></i>
              <div>
                <div id="withdrawn-amount" class="dashboard-value">0</div>
                <div class="currency">ج.م</div>
                <div class="dashboard-label">المبلغ المسحوب</div>
              </div>
            </div>
          </div>
        </section>
  
        <!-- فلترة حسب التاريخ -->
        <div class="quick-filters" role="group" aria-label="خيارات الفلترة السريعة">
          <button onclick="filterBy('today')" id="btn-today">اليوم</button>
          <button onclick="filterBy('week')" id="btn-week">هذا الأسبوع</button>
          <button onclick="filterBy('month')" id="btn-month">هذا الشهر</button>
          <button onclick="filterBy('year')" id="btn-year">هذا العام</button>
        </div>
  
        <div class="filter-section">
          <label for="startDate">من تاريخ:</label>
          <input type="date" id="startDate" />
          <label for="endDate">حتى تاريخ:</label>
          <input type="date" id="endDate" />
          <button onclick="applyCustomFilter()">تطبيق الفلترة</button>
          <button class="reset-filter" onclick="resetFilter()">إعادة تعيين</button>
        </div>
  
        <div id="loader" aria-live="polite" role="status">جارٍ تحميل البيانات...</div>
  
        <!-- الأقسام التي تعتمد على التاريخ -->
        <section class="dashboard-group" aria-label="معلومات الإيرادات">
          <div class="section-title-container">
            <h2 class="section-title">الإيرادات</h2>
          </div>
          <div class="cards-container">
            <div class="dashboard-card" tabindex="0" aria-label="إجمالي الإيرادات">
              <i class="fa-solid fa-dollar-sign"></i>
              <div>
                <div id="total-income" class="dashboard-value">0</div>
                <div class="currency">ج.م</div>
                <div class="dashboard-label">إجمالي الإيرادات</div>
              </div>
            </div>
            <div class="dashboard-card" tabindex="0" aria-label="المبلغ المتاح للسحب">
              <i class="fa-solid fa-wallet"></i>
              <div>
                <div id="available-amount" class="dashboard-value">0</div>
                <div class="currency">ج.م</div>
                <div class="dashboard-label">المبلغ المتاح للسحب</div>
              </div>
            </div>
          </div>
        </section>
  
        <section class="dashboard-group" aria-label="معلومات الدفعات">
          <div class="section-title-container">
            <h2 class="section-title">الدفعات</h2>
          </div>
          <div class="cards-container">
            <div class="dashboard-card" tabindex="0" aria-label="عدد الدفعات النقدية">
              <i class="fa-solid fa-money-bill-wave"></i>
              <div>
                <div id="cash-payment-count" class="dashboard-value">0</div>
                <div class="dashboard-label">عدد الدفعات النقدية</div>
              </div>
            </div>
  
            <div class="dashboard-card" tabindex="0" aria-label="قيمة الدفعات النقدية">
              <i class="fa-solid fa-coins"></i>
              <div>
                <div id="cash-payment-value" class="dashboard-value">0</div>
                <div class="currency">ج.م</div>
                <div class="dashboard-label">قيمة الدفعات النقدية</div>
              </div>
            </div>
  
            <div class="dashboard-card" tabindex="0" aria-label="عدد الدفعات الإلكترونية">
              <i class="fa-solid fa-credit-card"></i>
              <div>
                <div id="electronic-payment-count" class="dashboard-value">0</div>
                <div class="dashboard-label">عدد الدفعات الإلكترونية</div>
              </div>
            </div>
  
            <div class="dashboard-card" tabindex="0" aria-label="قيمة الدفعات الإلكترونية">
              <i class="fa-solid fa-sack-dollar"></i>
              <div>
                <div id="electronic-payment-value" class="dashboard-value">0</div>
                <div class="currency">ج.م</div>
                <div class="dashboard-label">قيمة الدفعات الإلكترونية</div>
              </div>
            </div>
          </div>
        </section>
      </div>
  
      <script>
        // dummy filter functions
        function filterBy(period) {
          alert(`تصفية حسب: ${period}`);
          // نضع هنا كود تحديث البيانات حسب الفترة
        }
        function applyCustomFilter() {
          const start = document.getElementById('startDate').value;
          const end = document.getElementById('endDate').value;
          if (!start || !end) {
            alert('يرجى اختيار كلا التاريخين');
            return;
          }
          alert(`تصفية من ${start} إلى ${end}`);
          // نضع هنا كود تحديث البيانات حسب التواريخ
        }
        function resetFilter() {
          document.getElementById('startDate').value = '';
          document.getElementById('endDate').value = '';
          alert('تم إعادة تعيين الفلاتر');
          // نضع هنا كود إعادة تحميل البيانات الأصلية
        }
      </script>
    </body>
  </x-master-layout>
  
  


{{-- <x-master-layout>
    <head>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

        <style>
/* تحديث الكارد لتوزيع البيانات بشكل أفضل */
.modern-trip-card {
    background: #fff;
    border-radius: 20px;
    padding: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1); 
    transition: 0.3s ease;
    border: 2px solid #ffcc00; 
    width: 32%;
    max-width: 400px;
    min-width: 280px;
    margin: 20px auto;
}

/* تحسين المحاذاة بين الأقسام داخل الكارد */
.trip-route, .trip-finance, .trip-section {
    display: flex;
    flex-direction: column;
    gap: 12px; /* مسافة بين العناصر */
    margin-bottom: 18px;
}

.trip-status { 
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: bold; 
    display: flex; 
    align-items: center; 
    gap: 8px; 
    background-color: #fff3cd; 
    color: #856404; 
    animation: pulse 1.5s infinite; 
}

/* تحسين الأيقونات */
.trip-route i, .trip-finance i, .trip-section i {
    font-size: 1.4rem;
    color: #1e1e2f;
    transition: transform 0.3s ease, color 0.3s ease;
}

.trip-route i:hover, .trip-finance i:hover, .trip-section i:hover {
    transform: scale(1);
    color: #ffcc00;
}

/* جعل الأقسام أكبر وضبط التباعد */
.trip-section {
    flex-wrap: wrap;
    gap: 16px 40px; /* مسافة بين العناصر الكبيرة والصغيرة */
}

.finance-box {
    background: #801d1d;
    border-radius: 12px;
    padding: 12px 18px; /* تحسين المسافات داخل الكارد المالي */
    text-align: center;
    flex: 1;
    min-width: 160px; /* تعديل الحد الأدنى للعناصر */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* إضافة ظل لتحسين المظهر */
}

.trip-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px; /* زيادة المسافة بين العنوان والحالة */
}

/* تحسين توزيع الأقسام داخل الفقرات */
.trip-route > div {
    display: flex;
    align-items: center;
    gap: 8px; /* إضافة تباعد بين الأيقونة والنص */
}

.trip-section div {
    display: flex;
    gap: 8px; /* تباعد بين الأيقونات والنصوص */
}

.multi-dests {
    margin-top: 8px;
}

.multi-dests li {
    display: list-item;
}

/* تحسين الأيقونات وتوزيع العناصر */
.custom-tabs-link .custom-tabs-text {
    font-size: 0.5rem;
    font-weight: 600;
    color: #333;
    margin-left: 8px; /* تباعد بين الأيقونة والنص */
}

/* توزيع الكاردات في صفوف */
.trip-columns {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    height: 100%;
}

.trip-column {
    background: #f9f9f9;
    padding: 30px 30px 30px 30px;
    border-radius: 12px;
    max-height: 1000px; /* الارتفاع الثابت */
    overflow-y: auto;   /* تمكين السكرول */
    scrollbar-width: thin;
    scrollbar-color: #f9f9f9 transparent;
}

.trip-column::-webkit-scrollbar {
    width: 6px;
}

.trip-column::-webkit-scrollbar-thumb {
    background-color: #ccc;
    border-radius: 10px;
}

.trip-column h3 {
    text-align: center;
    margin-bottom: 15px;
    font-size: 1.3rem;
    font-weight: bold;
    color: #ffcc00;
    position: sticky;
    border-radius: 10px;
    top: 0;
    background: #1e1944cb;
    padding: 10px 0;
    z-index: 1;
}

/* تحديث الكارد مع تأثيرات */
.modern-trip-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    margin-bottom: 15px;
    transition: transform 0.3s ease-in-out;
}

.modern-trip-card:hover {
    transform: scale(1.02);
}

/* تحسين توزيع الأقسام */
.trip-top, .trip-route, .finance-box {
    margin-bottom: 10px;
}

/* تخصيص الأنماط لحالة الرحلة */
.trip-id {
    font-weight: bold;
}

.trip-status.waiting { color: #f39c12; }
.trip-status.ongoing { color: #3498db; }
.trip-status.completed { color: #2ecc71; }

/* تخصيص مظهر الكارد المالي */
.finance-box {
    background: #e2f10962;
    border-radius: 10px;
    padding: 10px;
    font-size: 0.9rem;
    text-align: center;
}

.finance-box .label {
    font-size: 0.9rem;
    color: #666;
}

.finance-box .value {
    font-size: 1.1rem;
    font-weight: bold;
}

/* إضافة فاصل منقط بين الأقسام */
.dashed-separator {
    border: none;
    border-top: 1.5px dashed #bbb;
    margin: 10px 0;
}

/* تخصيص مظهر الفوتر في الكارد */
.trip-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 7px 15px;
    gap: 10px; /* إضافة المسافة بين الأزرار */
}

/* تخصيص أزرار الإجراءات */
.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #1e1e2f;
    border: none;
    border-radius: 12px;
    padding: 8px 12px;
    color: #ffcc00;
    font-weight: 500;
    font-size: 0.9rem;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    cursor: pointer;
    min-width: 100px;
}

.action-btn i {
    font-size: 1.4rem;
    margin-bottom: 3px;
    transition: transform 0.3s ease, color 0.3s ease;
}

.action-btn:hover {
    background: #ffcc00;
    color: #1e1e2f;
}

.action-btn:hover i {
    color: #1e1e2f;
    transform: scale(1.2);
}




/* تصغير حجم الأيقونات في الأقسام */
.trip-route i, .trip-finance i, .trip-section i {
    font-size: 1.1rem;  /* تم تصغير الحجم */
    color: #5e5e63;
    transition: transform 0.3s ease, color 0.3s ease;
}

/* تصغير حجم الأيقونات في الكارد المالي */
.finance-box i {
    font-size: 1.2rem;  /* تم تصغير الحجم */
}

/* تصغير حجم الأيقونات في أزرار الإجراءات */
.action-btn i {
    font-size: 1.2rem;  /* تم تصغير الحجم */
}







/* تخصيص كارد الرحلة في وضع الدارك */
body.dark .modern-trip-card {
    background-color: #2d3549;
    border-color: #ffcc00;
    color: #ffffff;
}

body.dark .trip-column {
    background-color:#242424;
    scrollbar-width: thin;
    scrollbar-color: #242424 transparent;
},


body.dark .trip-column {
    background-color: #2d3549; /* لون أزرق غامق أو أي لون تختاره */
}

/* تحديث النصوص داخل الكارد في الدارك */
body.dark .modern-trip-card .trip-id,
body.dark .modern-trip-card .trip-section,
body.dark .modern-trip-card .trip-route,
body.dark .modern-trip-card .trip-top,
body.dark .modern-trip-card .finance-box,





body.dark .modern-trip-card .trip-card-footer {
    color: #ffffff;
}

body.dark .modern-trip-card i {
    color: #ffcc00;
}

body.dark .modern-trip-card .finance-box {
    background-color: #ffcc0062;
    color: #e4dfdf;
}

body.dark .modern-trip-card .dashed-separator {
    border-top: 1.5px dashed #666;
}

body.dark .modern-trip-card .action-btn {
    background-color: #333;
    color: #ffcc00;
}

body.dark .modern-trip-card .action-btn:hover {
    background-color: #ffcc00;
    color: #19191a;
}

body.dark .modern-trip-card .action-btn:hover i {
    color: #19191a;
}







@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.pulse {
  animation: pulse 0.6s ease-in-out infinite;
}

.pulse-red {
  animation: pulse 0.7s ease-in-out infinite;
  color: rgb(255, 52, 52) !important;
}






        </style>
    </head>

    <div class="container">
        
        <div class="trip-columns">
        <!-- {{ __('messages.pending') }} -->
<div class="trip-column">

    <h3>
        <i class="fa-solid fa-spinner fa-spin pending-icon"></i>
        {{ __('messages.pending') }}
        <span id="pending_count" style="margin-right: 30px; font-size: 25px; font-weight: bold; padding-left: 25px; padding-right: 25px;">0</span>
    </h3>
    
    <div id="pending-orders-wrapper"></div>

    <script>
        let currentPagePending = 1;
        const limitPending = 7;
        let isLoadingPending = false;
        let lastPagePending = false;
    
        document.addEventListener('DOMContentLoaded', function () {
            fetchPendingOrders(currentPagePending);
            window.addEventListener('scroll', handleScrollPending);
        });
    
        function handleScrollPending() {
            if (lastPagePending || isLoadingPending) return;
    
            const scrollPosition = window.innerHeight + window.scrollY;
            const threshold = document.body.offsetHeight - 100;
    
            if (scrollPosition >= threshold) {
                currentPagePending++;
                fetchPendingOrders(currentPagePending);
            }
        }
    
        function showLoaderPending() {
            const wrapper = document.getElementById('pending-orders-wrapper');
            const loader = document.createElement('div');
            loader.id = 'scroll-loader-pending';
            loader.className = 'text-center p-4';
            loader.innerHTML = `<i class="fas fa-spinner fa-spin fa-2x text-warning"></i><p class="mt-2">
                            <div class="text-center p-4" style="color: #f39c12; font-size: 18px; font-weight: 600; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                {{ __('messages.loading') }}</div>
                            </p>`;
            wrapper.appendChild(loader);
        }
    
        function removeLoaderPending() {
            const loader = document.getElementById('scroll-loader-pending');
            if (loader) loader.remove();
        }



        function createOrderCard(order) {
            return ` <div class="modern-trip-card toggle-card">
                            <div class="trip-top card-toggle-header">
                                <div class="trip-id">
                                    <i class="fas fa-hashtag"></i> ${order.id}
                                </div>
                                <div class="trip-status waiting">
                            <i class="fas fa-clock fa-spin"></i> {{ __('messages.pending') }}
                        </div>                            </div>
                            <div class="trip-route card-toggle-header">
                                <div>
                                    <i class="fas fa-map-marker-alt text-success"></i>
                                    <span>${order.startAddress || '—'}</span>
                                </div>
                                <div>
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    <span>${order.endAddress || '—'}</span>
                                </div>

                                ${order.multiDestnationArray && order.multiDestnationArray.length > 0 ? `
                                    <div class="trip-section">
                                        <div><i class="fas fa-route"></i><h4>{{ __('messages.multiple_destinations') }}</h4></div>
                                        <ul class="multi-dests">
                                            ${order.multiDestnationArray.map(dest => `<li>${dest}</li>`).join('')}
                                        </ul>
                                    </div>
                                ` : ''}

                                <hr class="dashed-separator">

                                <div><i class="fas fa-clock"></i> {{ __('messages.time') }}: <strong>${order.time || '--'}</strong></div>
                                <div><i class="fas fa-road"></i> {{ __('messages.distance') }}: <strong>${order.distance || '--'} كم</strong></div>
                                <div><i class="fas fa-car"></i> {{ __('messages.service') }}: <strong>${order.subService.name}</strong></div>
                                <div><i class="fas fa-credit-card"></i> {{ __('messages.payment') }}: <strong>${order.paymentStatus || '--'}</strong></div>

                                <div class="finance-box">
                                    <i class="fas fa-dollar-sign"></i>
                                    <div class="label">{{ __('messages.price') }}</div>
                                    <div class="value">${order.amount.toLocaleString()}</div>
                                </div>
                            </div>

                            
                        <div class="trip-details" style="display: none;">
                            <hr class="dashed-separator">
                                        <div class="trip-section">
                                            <div><i class="fas fa-user"></i> {{ __('messages.user') }}: </div>
                                            <div class="d-flex gap-3 align-items-center">
                                                <img src="${order.user.photo}" alt="avatar" class="avatar avatar-45 rounded-pill">
                                                <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                                                    <h6 class="m-0" style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
                                                        ${order.user.firstName +' '+order.user.lastName}
                                                    </h6>
                                                    <span>${order.user.phoneNumber}</span>
                                                </div>
                                            </div>

                            </div>

                            <div class="trip-finance">
                                <div class="finance-box discount">
                                    <i class="fas fa-percentage"></i>
                                    <div class="label">{{ __('messages.discount') }}</div>
                                    <div class="value">${(order.discount * 100)}%</div>
                                </div>

                                <div class="finance-box total">
                                    <i class="fas fa-wallet"></i>
                                    <div class="label">{{ __('messages.total') }}</div>
                                    <div class="value">${order.totalAmount} </div>
                                </div>
                            </div>

            
                            </div>
                        </div>
                    </div>
                    `;
        }
        let lastPendingOrderId = 0;

    
        function fetchPendingOrders(page) {
            isLoadingPending = true;
            showLoaderPending();
    
            fetch(`{{ route('orders-by-status') }}?status=pending&page=${page}`)
                .then(response => response.json())
                .then(data => {
                    const pendingOrders = data.completed_orders || [];
                    const wrapper = document.getElementById('pending-orders-wrapper');
                    removeLoaderPending();
    
                    if (pendingOrders.length === 0 && page === 1) {
                        wrapper.innerHTML = `<div class="text-center p-4" style="color: #f39c12; font-size: 22px; font-weight: 600; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                        {{ __('messages.no_pending_orders') }}</div>`;
                        lastPagePending = true;
                        return;
                    }
    
                    if (pendingOrders.length === 0) {
                        lastPagePending = true;
                        return;
                    }
    
                    pendingOrders.forEach(order => {
                    const orderHTML = createOrderCard(order);

                    const pending_count = document.getElementById('pending_count');
                    pending_count.textContent = data.count;
                    wrapper.insertAdjacentHTML('beforeend', orderHTML);

                    });
    
                    isLoadingPending = false;
                    if (data.current_page >= data.total_pages) {
                        lastPagePending = true;
                    }
                })
                .catch(error => {
                    console.error('Error loading pending orders:', error);
                    removeLoaderPending();
                    isLoadingPending = false;
                });
        }


        function fetchNewPendingOrders() {
  fetch(`/get/only-new-orders-by-status?last_order_id=${lastPendingOrderId}&status=pending`)
    .then(response => response.json())
    .then(data => {
      const newOrders = data.orders || [];
      const wrapper = document.getElementById('pending-orders-wrapper');
      removeLoaderPending();

      if (newOrders.length === 0) {
        return;
      }

      const firstOrder =  newOrders.at(0);
      lastPendingOrderId = firstOrder.id;  


      newOrders.forEach(order => {
        const orderHTML = createOrderCard(order);
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = orderHTML;
        const orderElement = tempDiv.firstElementChild;

        orderElement.classList.add('pulse');

        wrapper.prepend(orderElement);

        // wrapper.insertAdjacentElement('afterbegin', orderElement);

        setTimeout(() => {
          orderElement.classList.remove('pulse');
        }, 7000);

      });

      const pendingCount = document.getElementById('pending_count');
      pendingCount.textContent = data.count;

      pendingCount.classList.add('pulse-red');

      setTimeout(() => {
        pendingCount.classList.remove('pulse-red');
      }, 7000);
    })
    .catch(error => {
      console.error('Error fetching new orders:', error);
      removeLoaderPending();
    });
}



        setInterval(() => {
            const wrapper = document.getElementById('pending-orders-wrapper');
            completedLastPage = false;
            fetchNewPendingOrders(1);
        }, 30000);
    </script> --}}
    


























    
   