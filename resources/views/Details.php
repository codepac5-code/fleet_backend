
<x-master-layout>
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    </head>

                <div class="trip-card-modern">
              
                    
                        <div class="modern-trip-card">
                            <!-- العنوان والحالة -->
                            <div class="trip-top">
                                <div class="trip-id">
                                    <i class="fas fa-hashtag"></i>  45447
                                </div>
                                <div class="trip-status waiting">
                                    <i class="fas fa-spinner fa-spin"></i> قيد الانتظار
                                </div>
                            </div>
                    
                            <!-- العنوان -->
                            <div class="trip-route">
                                <i class="fas fa-map-marker-alt text-success"></i>
                                <span>حي الملك فهد</span>
                                <span class="arrow">→</span>
                                <i class="fas fa-map-marker-alt text-danger"></i>
                                <span>حي النرجس</span>
                            </div>
                    
                            <!-- المستخدم والخدمة والمكتب -->
                            <div class="trip-section">
                                <div><i class="fas fa-user"></i> المستخدم: <strong>أحمد محمد</strong></div>
                                <div><i class="fas fa-car"></i> الخدمة: <strong>خدمة فاخرة</strong></div>
                                <div><i class="fas fa-building"></i> المكتب: <strong>مكتب الرياض</strong></div>
                            </div>
                    
                            <!-- بيانات الرحلة -->
                            <div class="trip-section">
                                <div><i class="fas fa-clock"></i> الوقت: <strong>02:15 م</strong></div>
                                <div><i class="fas fa-road"></i> المسافة: <strong>12 كم</strong></div>
                                <div><i class="fas fa-credit-card"></i> الدفع: <strong>إلكتروني</strong></div>
                            </div>
                    
                            <!-- المبالغ -->
                            <div class="trip-finance">
                                <div class="finance-box">
                                    <i class="fas fa-dollar-sign"></i>
                                    <div class="label">السعر</div>
                                    <div class="value">100 ر.س</div>
                                </div>
                                <div class="finance-box">
                                    <i class="fas fa-percentage"></i>
                                    <div class="label">الخصم</div>
                                    <div class="value">10 ر.س</div>
                                </div>
                                <div class="finance-box">
                                    <i class="fas fa-wallet"></i>
                                    <div class="label">الإجمالي</div>
                                    <div class="value">90 ر.س</div>
                                </div>
                            </div>
                    
                            <!-- العمولات -->
                            <div class="trip-section">
                                <div><i class="fas fa-user-tie"></i> عمولة السائق: <strong>20 ر.س</strong></div>
                                <div><i class="fas fa-shield-alt"></i> عمولة الأسطول: <strong>5 ر.س</strong></div>
                            </div>
                    
                            <!-- وجهات متعددة -->
                            <div class="trip-section">
                                <div><i class="fas fa-route"></i> وجهات متعددة:</div>
                                <ul class="multi-dests">
                                    <li>شارع التخصصي</li>
                                    <li>مستشفى الملك فيصل</li>
                                </ul>
                            </div>
                        </div>
                </div>

    <style>


      



            /* تحديد تصميم الأقسام */
.section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #f8f9fa;
    overflow-y: auto;  /* تمكين التمرير عموديًا */
    height: 400px;     /* تحديد ارتفاع الأقسام بحيث يظهر التمرير */
}

/* تخصيص تصميم الكاردات */
.card {
    margin-bottom: 15px; /* إضافة فاصل بين الكاردات */
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease; /* تأثير التحول عند مرور الماوس */
}

/* تأثير تكبير الكارد عند المرور بالماوس */
.card:hover {
    transform: scale(1.05); 
}

/* تصميم العناوين داخل الأقسام */
.section h3 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 2rem;
    color: #333;
}

/* فواصل بين الأقسام */
.vertical-separator {
    width: 1px;
    background-color: #ddd;
    margin: 0 20px;
    height: 100%;
}





























.modern-trip-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    font-size: 1.05rem;
    line-height: 1.7;
    transition: 0.3s ease;
    border-left: 6px solid #ffc107;
}

.modern-trip-card:hover {
    transform: translateY(-4px);
}

/* العنوان العلوي */
.trip-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.trip-id {
    font-weight: bold;
    font-size: 1.1rem;
    color: #333;
}

.trip-id i {
    color: #6c757d;
    margin-left: 8px;
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

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.6); }
    70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}

/* المسار */
.trip-route {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
    font-size: 1.1rem;
    color: #444;
}

.arrow {
    font-weight: bold;
    font-size: 1.4rem;
    color: #999;
}

/* تقسيم الأقسام إلى صفوف متباعدة */
.trip-section {
    display: flex;
    flex-wrap: wrap;
    gap: 16px 40px;
    margin-bottom: 18px;
    font-size: 1.02rem;
    color: #333;
}

.trip-section div {
    min-width: 220px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.trip-section i {
    color: #555;
    font-size: 1.1rem;
}

/* التمويل */
.trip-finance {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 16px;
    margin: 18px 0;
}

.finance-box {
    flex: 1;
    min-width: 150px;
    background: #f9fafb;
    border-radius: 14px;
    padding: 14px 16px;
    text-align: center;
}

.finance-box i {
    font-size: 1.5rem;
    color: #ffc107;
}

.label {
    font-size: 0.95rem;
    color: #777;
    margin-top: 6px;
}

.value {
    font-weight: bold;
    font-size: 1.2rem;
    margin-top: 4px;
    color: #222;
}

/* الوجهات المتعددة */
.multi-dests {
    list-style: disc;
    padding-right: 24px;
    margin: 6px 0 0;
    font-size: 0.95rem;
    color: #555;
}


    </style>

    <script>
        function toggleCard() {
            const card = document.querySelector('.trip-card-modern');
            card.classList.toggle('show-details');
            const button = card.querySelector('.toggle-btn');
            if (card.classList.contains('show-details')) {
                button.textContent = "إخفاء التفاصيل";
            } else {
                button.textContent = "عرض التفاصيل";
            }
        }
    </script>
</x-master-layout>
