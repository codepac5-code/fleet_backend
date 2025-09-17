    <style>

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

        </style>
    
    <div class="dashboard-container">

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
  
      <main class="stats-grid">
  
        <!-- كارد إحصائيات مع رسم بياني صغير -->
        <article class="stat-card revenue">
          <div class="stat-icon">
            <i class="bi bi-currency-dollar"></i>
          </div>
          <div class="stat-info">
            <h3>الإيرادات الإجمالية</h3>
            <p class="stat-value" id="totalRevenue">0.00 ر.س</p>
          </div>
          <canvas id="revenueChart"></canvas>
        </article>
  
        <article class="stat-card withdrawal-available">
          <div class="stat-icon">
            <i class="bi bi-wallet2"></i>
          </div>
          <div class="stat-info">
            <h3>المبلغ القابل للسحب</h3>
            <p class="stat-value" id="availableWithdrawal">0.00 ر.س</p>
          </div>
          <canvas id="withdrawalChart"></canvas>
        </article>
  
        <article class="stat-card withdrawn">
          <div class="stat-icon">
            <i class="bi bi-cash-stack"></i>
          </div>
          <div class="stat-info">
            <h3>المبلغ المسحوب</h3>
            <p class="stat-value" id="withdrawnAmount">0.00 ر.س</p>
          </div>
          <canvas id="withdrawnChart"></canvas>
        </article>
  
        <article class="stat-card offices-outstanding">
          <div class="stat-icon">
            <i class="bi bi-exclamation-circle"></i>
          </div>
          <div class="stat-info">
            <h3>المبالغ المستحقة على المكاتب</h3>
            <p class="stat-value" id="officesOutstanding">0.00 ر.س</p>
          </div>
          <canvas id="officesChart"></canvas>
        </article>
  
        <article class="stat-card drivers-outstanding">
          <div class="stat-icon">
            <i class="bi bi-person-badge"></i>
          </div>
          <div class="stat-info">
            <h3>المبالغ المستحقة على السائقين</h3>
            <p class="stat-value" id="driversOutstanding">0.00 ر.س</p>
          </div>
          <canvas id="driversChart"></canvas>
        </article>
  
        <article class="stat-card e-payments">
          <div class="stat-icon">
            <i class="bi bi-credit-card-2-front"></i>
          </div>
          <div class="stat-info">
            <h3>عدد عمليات الدفع الإلكترونية</h3>
            <p class="stat-value" id="numEPayments">0</p>
          </div>
        </article>
  
        <article class="stat-card e-payments-value">
          <div class="stat-icon">
            <i class="bi bi-currency-exchange"></i>
          </div>
          <div class="stat-info">
            <h3>قيمة عمليات الدفع الإلكترونية</h3>
            <p class="stat-value" id="valEPayments">0.00 ر.س</p>
          </div>
        </article>
  
        <article class="stat-card cash-payments">
          <div class="stat-icon">
            <i class="bi bi-cash"></i>
          </div>
          <div class="stat-info">
            <h3>عدد عمليات الدفع الكاش</h3>
            <p class="stat-value" id="numCashPayments">0</p>
          </div>
        </article>
  
        <article class="stat-card cash-payments-value">
          <div class="stat-icon">
            <i class="bi bi-wallet-fill"></i>
          </div>
          <div class="stat-info">
            <h3>قيمة عمليات الدفع الكاش</h3>
            <p class="stat-value" id="valCashPayments">0.00 ر.س</p>
          </div>
        </article>
  
      </main>
  
    </div>
  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
    <style>
      /* Reset & base */
      body {
        font-family: 'Cairo', sans-serif;
        background: linear-gradient(135deg, #e0f7fa, #ffffff);
        margin: 0; padding: 20px;
        color: #222;
        direction: rtl;
      }
  
      .dashboard-container {
        max-width: 1200px;
        margin: auto;
      }
  
      /* Header - الفلترة */
      .filter-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        background: #fff;
        border-radius: 15px;
        padding: 20px 25px;
        box-shadow: 0 5px 15px rgb(0 0 0 / 0.1);
        margin-bottom: 40px;
        user-select: none;
      }
  
      .date-picker {
        display: flex;
        flex-direction: column;
        font-weight: 600;
        color: #444;
        min-width: 140px;
      }
  
      .date-picker input {
        margin-top: 8px;
        padding: 8px 12px;
        border-radius: 10px;
        border: 1.5px solid #ccc;
        transition: border-color 0.3s ease;
        font-size: 16px;
      }
      .date-picker input:focus {
        outline: none;
        border-color: #0097a7;
        box-shadow: 0 0 10px #0097a7aa;
      }
  
      .filter-buttons {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
      }
  
      .btn {
        padding: 10px 20px;
        border-radius: 40px;
        border: 2px solid #0097a7;
        background: transparent;
        color: #0097a7;
        font-weight: 700;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s ease;
        user-select: none;
        box-shadow: 0 0 6px transparent;
      }
      .btn:hover {
        background: #0097a7;
        color: white;
        box-shadow: 0 0 15px #0097a7aa;
        transform: translateY(-2px);
      }
      .btn:active {
        transform: translateY(0);
        box-shadow: 0 0 8px #007c8caa;
      }
  
      .filter-info {
        flex-basis: 100%;
        font-size: 15px;
        color: #666;
        font-style: italic;
        margin-top: 5px;
        user-select: text;
      }
  

      
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(390px, 1fr)); /* زيادة الحد الأدنى للعرض */
  gap: 25px;
}

.stat-card {
  position: relative;
  min-width:200px; /* تم تقليص الحجم */
  height: 160px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  padding: 15px 15px 15px 80px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}

.stat-icon {
  position: absolute;
  top: 50%;
  left: 25px;
  transform: translateY(-50%);
  width: 65px;
  height: 65px;
  background-color: #0097a7;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  color: white;
  box-shadow: 0 8px 15px #0097a733;
  z-index: 2;
}

/* محتوى الكارد */
.stat-info {
  display: flex;
  flex-direction: column;
  gap: 5px;
  z-index: 3;
}

/* العنوان */
.stat-info h3 {
  font-size: 1rem;
  color: #333;
  font-weight: 600;
  margin: 0;
  line-height: 1.2;
}

/* القيمة */
.stat-value {
  font-size: 1.8rem;
  font-weight: 800;
  color: #111;
  user-select: text;
  margin: 0;
}

/* الوحدة (ر.س) بجانب القيمة */
.stat-currency {
  font-size: 1.1rem;
  font-weight: 700;
  color: #666;
  margin-top: 2px;
}

/* الرسم البياني في أسفل الكارد */
.stat-chart {
  width: 100%;
  max-height: 70px;
  margin-top: auto; /* لضبط الرسم في الأسفل */
}

/* مثال على استخدام الكلاس داخل الكارد */
/* اذا كنت تستخدم canvas للرسم */
.stat-card canvas {
  width: 100% !important;
  max-height: 70px !important;
  display: block;
  margin-top: 10px;
}

/* استجابة للشاشات الصغيرة */
@media (max-width: 600px) {
  .stat-card {
    min-width: auto;
    height: auto;
    padding: 15px 15px 15px 90px;
  }
  .stat-icon {
    width: 50px;
    height: 50px;
    font-size: 22px;
    left: 15px;
  }
  .stat-info h3 {
    font-size: 0.9rem;
  }
  .stat-value {
    font-size: 1.4rem;
  }
  .stat-currency {
    font-size: 0.9rem;
  }
  .stat-card canvas {
    max-height: 50px !important;
  }


        
}


  
      .stat-card.revenue .stat-icon { background: #28a745; box-shadow: 0 8px 15px #28a745aa; }
      .stat-card.withdrawal-available .stat-icon { background: #007bff; box-shadow: 0 8px 15px #007bffaa; }
      .stat-card.withdrawn .stat-icon { background: #ffc107; box-shadow: 0 8px 15px #ffc107aa; }
      .stat-card.offices-outstanding .stat-icon { background: #dc3545; box-shadow: 0 8px 15px #dc3545aa; }
      .stat-card.drivers-outstanding .stat-icon { background: #e83e8c; box-shadow: 0 8px 15px #e83e8caa; }
      .stat-card.e-payments .stat-icon { background: #17a2b8; box-shadow: 0 8px 15px #17a2b8aa; }
      .stat-card.e-payments-value .stat-icon { background: #6f42c1; box-shadow: 0 8px 15px #6f42c1aa; }
      .stat-card.cash-payments .stat-icon { background: #fd7e14; box-shadow: 0 8px 15px #fd7e14aa; }
      .stat-card.cash-payments-value .stat-icon { background: #20c997; box-shadow: 0 8px 15px #20c997aa; }
  
      /* معلومات الإحصائية */
      .stat-info {
        margin-left: 80px;
        z-index: 3;
        position: relative;
      }
      .stat-info h3 {
        font-size: 1.2rem;
        margin-bottom: 8px;
        color: #333;
        font-weight: 700;
      }
      .stat-value {
        font-size: 1.6rem;
        font-weight: 900;
        color: #111;
        letter-spacing: 0.5px;
        user-select: text;
      }
  
      /* الرسم البياني الصغير */
      canvas {
        margin-top: 15px;
        width: 100% !important;
        max-height: 70px !important;
      }
  
      /* Responsive */
      @media (max-width: 600px) {
        .filter-header {
          flex-direction: column;
          align-items: flex-start;
        }
        .date-picker {
          width: 100%;
        }
        .filter-buttons {
          width: 100%;
          justify-content: flex-start;
        }
      }

      .stat-card.revenue .stat-info h3 {
  color: #28a745;
}
.stat-card.revenue .stat-value {
  color: #28a745;
  font-size: 1.8rem;
}

.stat-card.withdrawal-available .stat-info h3 {
  color: #007bff;
}
.stat-card.withdrawal-available .stat-value {
  color: #007bff;
  font-size: 1.8rem;
}

.stat-card.withdrawn .stat-info h3 {
  color: #ffc107;
}
.stat-card.withdrawn .stat-value {
  color: #ffc107;
  font-size: 1.8rem;
}

.stat-card.offices-outstanding .stat-info h3 {
  color: #dc3545;
}
.stat-card.offices-outstanding .stat-value {
  color: #dc3545;
  font-size: 1.8rem;
}

.stat-card.drivers-outstanding .stat-info h3 {
  color: #e83e8c;
}
.stat-card.drivers-outstanding .stat-value {
  color: #e83e8c;
  font-size: 1.8rem;
}

.stat-card.e-payments .stat-info h3 {
  color: #17a2b8;
}
.stat-card.e-payments .stat-value {
  color: #17a2b8;
  font-size: 1.8rem;
}

.stat-card.e-payments-value .stat-info h3 {
  color: #6f42c1;
}
.stat-card.e-payments-value .stat-value {
  color: #6f42c1;
  font-size: 1.8rem;
}

.stat-card.cash-payments .stat-info h3 {
  color: #fd7e14;
}
.stat-card.cash-payments .stat-value {
  color: #fd7e14;
  font-size: 1.8rem;
}

.stat-card.cash-payments-value .stat-info h3 {
  color: #20c997;
}
.stat-card.cash-payments-value .stat-value {
  color: #20c997;
  font-size: 1.8rem;
}

    </style>
  
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        // أمثلة بيانات عشوائية للرسم البياني
        const sampleData = {
          revenue: [2000, 2500, 1800, 2200, 2700, 3000, 3200],
          withdrawalAvailable: [1500, 1800, 1600, 2000, 2300, 2100, 2500],
          withdrawn: [1000, 1200, 1100, 1150, 1300, 1400, 1450],
          officesOutstanding: [800, 900, 750, 700, 650, 600, 620],
          driversOutstanding: [600, 650, 700, 720, 680, 630, 700]
        };
  
        function createChart(ctx, data, color) {
          return new Chart(ctx, {
            type: 'line',
            data: {
              labels: ['أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'],
              datasets: [{
                label: '',
                data,
                borderColor: color,
                backgroundColor: color + '44',
                fill: true,
                tension: 0.3,
                pointRadius: 0,
                borderWidth: 3,
                hoverRadius: 5
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              scales: {
                x: { display: false },
                y: { display: false }
              },
              plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
              }
            }
          });
        }
  
        // إنشاء الرسوم البيانية
        createChart(document.getElementById('revenueChart').getContext('2d'), sampleData.revenue, '#28a745');
        createChart(document.getElementById('withdrawalChart').getContext('2d'), sampleData.withdrawalAvailable, '#007bff');
        createChart(document.getElementById('withdrawnChart').getContext('2d'), sampleData.withdrawn, '#ffc107');
        createChart(document.getElementById('officesChart').getContext('2d'), sampleData.officesOutstanding, '#dc3545');
        createChart(document.getElementById('driversChart').getContext('2d'), sampleData.driversOutstanding, '#e83e8c');
  
        // تحديث القيم - ممكن ربطها مع API أو بيانات حقيقية
        document.getElementById('totalRevenue').textContent = '8,450.00 ر.س';
        document.getElementById('availableWithdrawal').textContent = '5,200.00 ر.س';
        document.getElementById('withdrawnAmount').textContent = '3,250.00 ر.س';
        document.getElementById('officesOutstanding').textContent = '1,850.00 ر.س';
        document.getElementById('driversOutstanding').textContent = '1,230.00 ر.س';
        document.getElementById('numEPayments').textContent = '215';
        document.getElementById('valEPayments').textContent = '12,500.00 ر.س';
        document.getElementById('numCashPayments').textContent = '120';
        document.getElementById('valCashPayments').textContent = '7,800.00 ر.س';
  
        // تفاعل أزرار الفلترة - مثال فقط
        document.querySelectorAll('.btn[data-range]').forEach(btn => {
          btn.addEventListener('click', () => {
            alert(`فلترة حسب: ${btn.textContent}`);
            // هنا يمكن تحديث البيانات والرسوم البيانية حسب الفلترة
          });
        });
      });
    </script>
  