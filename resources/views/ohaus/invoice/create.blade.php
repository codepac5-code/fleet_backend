<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الفواتير - OHAUS</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #E30613;
            --primary-dark: #c10510;
            --secondary: #2c3e50;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
            --border: #dee2e6;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ed 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-right: 5px solid var(--primary);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo i {
            font-size: 2.5rem;
            color: var(--primary);
        }

        .logo h1 {
            font-size: 1.8rem;
            color: var(--secondary);
        }

        .steps {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: var(--shadow);
            position: relative;
            transition: all 0.3s ease;
        }

        .step.active {
            background: var(--primary);
            color: white;
        }

        .step.completed {
            background: var(--success);
            color: white;
        }

        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: var(--light);
            color: var(--dark);
            border-radius: 50%;
            line-height: 30px;
            margin-left: 10px;
        }

        .step.active .step-number {
            background: white;
            color: var(--primary);
        }

        .step.completed .step-number {
            background: white;
            color: var(--success);
        }

        .form-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
            color: var(--secondary);
        }

        .section-title i {
            color: var(--primary);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
        }

        input, textarea, select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: 'Cairo', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(227, 6, 19, 0.1);
        }

        .items-section {
            margin-top: 30px;
        }

        .item-row {
            display: grid;
            grid-template-columns: 2fr 1fr 80px 80px 1fr 1fr 1fr auto;
            gap: 10px;
            align-items: end;
            padding: 15px;
            background: var(--light);
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-family: 'Cairo', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        .btn-sm {
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }

        .hidden {
            display: none;
        }

        .preview-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .invoice-preview {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            background: #f9f9f9;
            min-height: 500px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .item-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .steps {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-file-invoice-dollar"></i>
                <h1>نظام إدارة الفواتير - OHAUS</h1>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" onclick="resetForm()">
                    <i class="fas fa-redo"></i> بدء من جديد
                </button>
            </div>
        </header>

        <div class="steps">
            <div class="step active" id="step1">
                <span class="step-number">1</span>
                معلومات الشركة
            </div>
            <div class="step" id="step2">
                <span class="step-number">2</span>
                معلومات العميل
            </div>
            <div class="step" id="step3">
                <span class="step-number">3</span>
                العناصر والمنتجات
            </div>
            <div class="step" id="step4">
                <span class="step-number">4</span>
                المراجعة والإرسال
            </div>
        </div>

        <form id="invoiceForm" action="{{ route('invoice.generate') }}" method="POST">
            @csrf

            <!-- قسم معلومات الشركة -->
            <div class="form-container" id="company-section">
                <div class="section-title">
                    <i class="fas fa-building"></i>
                    <h2>معلومات الشركة</h2>
                </div>

                <div class="form-grid">
                  المعلومات متوفرة لدينا ، اضغط التالي
                    {{-- <div class="form-group">
                        <label for="company_name">اسم الشركة *</label>
                        <input type="text" id="company_name" name="company_name" required value="OHAUS CORPORATION">
                    </div>

                    <div class="form-group">
                        <label for="company_address">عنوان الشركة *</label>
                        <textarea id="company_address" name="company_address" rows="2" required>P.O. Box 5667, Parsippany, NJ 07054</textarea>
                    </div>

                    <div class="form-group">
                        <label for="company_phone">هاتف الشركة *</label>
                        <input type="text" id="company_phone" name="company_phone" required value="(973) 377-9000 / (800) 672-7722">
                    </div>

                    <div class="form-group">
                        <label for="company_fax">فاكس الشركة</label>
                        <input type="text" id="company_fax" name="company_fax" value="(973) 944-7177">
                    </div> --}}

                    {{-- <div class="form-group">
                        <label for="company_website">موقع الويب</label>
                        <input type="url" id="company_website" name="company_website" value="www.ohaus.com">
                    </div> --}}
                </div>

                <div class="actions">
                    <button type="button" class="btn btn-primary" onclick="nextSection('company-section', 'customer-section')">
                        التالي <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            </div>

            <!-- قسم معلومات العميل -->
            <div class="form-container hidden" id="customer-section">
                <div class="section-title">
                    <i class="fas fa-users"></i>
                    <h2>معلومات العميل والعناوين</h2>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="customer_number">رقم العميل *</label>
                        <input type="text" id="customer_number" name="customer_number" required value="400058339">
                    </div>

                    <div class="form-group">
                        <label for="invoice_number">رقم الفاتورة *</label>
                        <input type="text" id="invoice_number" name="invoice_number" required value="637458087">
                    </div>

                    <div class="form-group">
                        <label for="invoice_date">تاريخ الفاتورة *</label>
                        <input type="date" id="invoice_date" name="invoice_date" required value="2024-12-17">
                    </div>

                    <div class="form-group">
                        <label for="order_number">رقم الطلب</label>
                        <input type="text" id="order_number" name="order_number" value="901050786">
                    </div>
                </div>

                <div class="section-title" style="margin-top: 30px;">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>العناوين</h3>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="bill_to_name">اسم جهة الفوترة *</label>
                        <input type="text" id="bill_to_name" name="bill_to_name" required value="Ramo Trading">
                    </div>

                    <div class="form-group">
                        <label for="bill_to_address">عنوان الفوترة *</label>
                        <input type="text" id="bill_to_address" name="bill_to_address" required value="15205 Spectrum">
                    </div>

                    <div class="form-group">
                        <label for="bill_to_city">المدينة والرمز البريدي *</label>
                        <input type="text" id="bill_to_city" name="bill_to_city" required value="IRVINE, CA 92618-3425">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="ship_to_name">اسم جهة الشحن *</label>
                        <input type="text" id="ship_to_name" name="ship_to_name" required value="Asaad Ramo">
                    </div>

                    <div class="form-group">
                        <label for="ship_to_company">شركة الشحن</label>
                        <input type="text" id="ship_to_company" name="ship_to_company" value="Ramo Trading Consulting Inc">
                    </div>

                    <div class="form-group">
                        <label for="ship_to_address">عنوان الشحن *</label>
                        <input type="text" id="ship_to_address" name="ship_to_address" required value="2 chaparral Court">
                    </div>

                    <div class="form-group">
                        <label for="ship_to_city">مدينة الشحن *</label>
                        <input type="text" id="ship_to_city" name="ship_to_city" required value="Rancho Sanat Margerita, CA 92688">
                    </div>
                </div>

                <div class="actions">
                    <button type="button" class="btn btn-outline" onclick="prevSection('customer-section', 'company-section')">
                        <i class="fas fa-arrow-right"></i> السابق
                    </button>
                    <button type="button" class="btn btn-primary" onclick="nextSection('customer-section', 'items-section')">
                        التالي <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            </div>

            <!-- قسم العناصر -->
            <div class="form-container hidden" id="items-section">
                <div class="section-title">
                    <i class="fas fa-boxes"></i>
                    <h2>العناصر والمنتجات</h2>
                </div>

                <div class="items-section">
                    <div id="items-container">
                        <!-- العنصر الأول -->
                        <div class="item-row">
                            <div class="form-group">
                                <label>وصف المنتج</label>
                                <textarea name="item_description[]" rows="2">Standard Conduct 12.88mS/cm 250ml</textarea>
                            </div>

                            <div class="form-group">
                                <label>معرف المنتج</label>
                                <input type="text" name="item_product_id[]" value="30100444">
                            </div>

                            <div class="form-group">
                                <label>الكمية</label>
                                <input type="number" name="item_quantity[]" value="5" min="1">
                            </div>

                            <div class="form-group">
                                <label>الوحدة</label>
                                <input type="text" name="item_unit[]" value="EA">
                            </div>

                            <div class="form-group">
                                <label>السعر للوحدة</label>
                                <input type="number" step="0.01" name="item_price[]" value="29.00" min="0">
                            </div>

                            <div class="form-group">
                                <label>الخصم</label>
                                <input type="text" name="item_discount[]" value="(30.00%)">
                            </div>

                            <div class="form-group">
                                <label>الملاحظات (سطر لكل ملاحظة)</label>
                                <textarea name="item_notes[]" rows="3">Commodity Code 3105100000
Country of Origin US
Ship Via FedEx Ground
Carrier Tracking Number: 283404897460</textarea>
                            </div>

                            <div class="form-group">
                                <button type="button" class="btn btn-outline btn-sm" onclick="removeItem(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <!-- العنصر الثاني -->
                        <div class="item-row">
                            <div class="form-group">
                                <label>وصف المنتج</label>
                                <textarea name="item_description[]" rows="2">Standard Conduct 1413μS/cm 250ml</textarea>
                            </div>

                            <div class="form-group">
                                <label>معرف المنتج</label>
                                <input type="text" name="item_product_id[]" value="30100443">
                            </div>

                            <div class="form-group">
                                <label>الكمية</label>
                                <input type="number" name="item_quantity[]" value="5" min="1">
                            </div>

                            <div class="form-group">
                                <label>الوحدة</label>
                                <input type="text" name="item_unit[]" value="EA">
                            </div>

                            <div class="form-group">
                                <label>السعر للوحدة</label>
                                <input type="number" step="0.01" name="item_price[]" value="29.00" min="0">
                            </div>

                            <div class="form-group">
                                <label>الخصم</label>
                                <input type="text" name="item_discount[]" value="(30.00%)">
                            </div>

                            <div class="form-group">
                                <label>الملاحظات (سطر لكل ملاحظة)</label>
                                <textarea name="item_notes[]" rows="3">Commodity Code 3105100000
Country of Origin US
Ship Via FedEx Ground
Carrier Tracking Number: 283404897460</textarea>
                            </div>

                            <div class="form-group">
                                <button type="button" class="btn btn-outline btn-sm" onclick="removeItem(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline" onclick="addItem()">
                        <i class="fas fa-plus"></i> إضافة عنصر جديد
                    </button>
                </div>

                <div class="actions">
                    <button type="button" class="btn btn-outline" onclick="prevSection('items-section', 'customer-section')">
                        <i class="fas fa-arrow-right"></i> السابق
                    </button>
                    <button type="button" class="btn btn-primary" onclick="nextSection('items-section', 'review-section')">
                        التالي <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            </div>

            <!-- قسم المراجعة -->
            <div class="form-container hidden" id="review-section">
                <div class="section-title">
                    <i class="fas fa-eye"></i>
                    <h2>مراجعة البيانات</h2>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>مراجعة نهائية:</strong> يرجى مراجعة جميع البيانات المدخلة قبل إنشاء الفاتورة.
                    </div>
                </div>

                <div class="preview-container">
                    <h3>معاينة الفاتورة</h3>
                    <div class="invoice-preview">
                        <p>سيتم عرض معاينة للفاتورة هنا بعد إرسال البيانات.</p>
                        <p>اضغط على زر "إنشاء الفاتورة" لمعاينة الفاتورة النهائية.</p>
                    </div>
                </div>

                <div class="actions">
                    <button type="button" class="btn btn-outline" onclick="prevSection('review-section', 'items-section')">
                        <i class="fas fa-arrow-right"></i> السابق
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-invoice"></i> إنشاء الفاتورة
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        // التنقل بين الأقسام
        function nextSection(currentId, nextId) {
            document.getElementById(currentId).classList.add('hidden');
            document.getElementById(nextId).classList.remove('hidden');

            // تحديث خطوات التقدم
            updateSteps(currentId, nextId);
        }

        function prevSection(currentId, prevId) {
            document.getElementById(currentId).classList.add('hidden');
            document.getElementById(prevId).classList.remove('hidden');

            // تحديث خطوات التقدم
            updateSteps(currentId, prevId, false);
        }

        function updateSteps(currentId, nextId, isNext = true) {
            const steps = {
                'company-section': 'step1',
                'customer-section': 'step2',
                'items-section': 'step3',
                'review-section': 'step4'
            };

            if (isNext) {
                document.getElementById(steps[currentId]).classList.remove('active');
                document.getElementById(steps[currentId]).classList.add('completed');
                document.getElementById(steps[nextId]).classList.add('active');
            } else {
                document.getElementById(steps[currentId]).classList.remove('active');
                document.getElementById(steps[nextId]).classList.add('active');
                document.getElementById(steps[nextId]).classList.remove('completed');
            }
        }

        // إدارة العناصر
        function addItem() {
            const container = document.getElementById('items-container');
            const newItem = document.createElement('div');
            newItem.className = 'item-row';
            newItem.innerHTML = `
                <div class="form-group">
                    <label>وصف المنتج</label>
                    <textarea name="item_description[]" rows="2" placeholder="وصف المنتج"></textarea>
                </div>

                <div class="form-group">
                    <label>معرف المنتج</label>
                    <input type="text" name="item_product_id[]" placeholder="معرف المنتج">
                </div>

                <div class="form-group">
                    <label>الكمية</label>
                    <input type="number" name="item_quantity[]" value="1" min="1">
                </div>

                <div class="form-group">
                    <label>الوحدة</label>
                    <input type="text" name="item_unit[]" placeholder="وحدة القياس">
                </div>

                <div class="form-group">
                    <label>السعر للوحدة</label>
                    <input type="number" step="0.01" name="item_price[]" value="0.00" min="0">
                </div>

                <div class="form-group">
                    <label>الخصم</label>
                    <input type="text" name="item_discount[]" placeholder="نسبة الخصم">
                </div>

                <div class="form-group">
                    <label>الملاحظات (سطر لكل ملاحظة)</label>
                    <textarea name="item_notes[]" rows="3" placeholder="ملاحظات إضافية"></textarea>
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-outline btn-sm" onclick="removeItem(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(newItem);
        }

        function removeItem(button) {
            const itemRow = button.closest('.item-row');
            if (document.querySelectorAll('.item-row').length > 1) {
                itemRow.remove();
            } else {
                alert('يجب أن تحتوي الفاتورة على عنصر واحد على الأقل');
            }
        }

        // إعادة تعيين النموذج
        function resetForm() {
            if (confirm('هل أنت متأكد من أنك تريد مسح جميع البيانات والبدء من جديد؟')) {
                document.getElementById('invoiceForm').reset();
                document.querySelectorAll('.form-container').forEach(container => {
                    container.classList.add('hidden');
                });
                document.getElementById('company-section').classList.remove('hidden');

                // إعادة تعيين خطوات التقدم
                document.querySelectorAll('.step').forEach((step, index) => {
                    step.classList.remove('active', 'completed');
                    if (index === 0) {
                        step.classList.add('active');
                    }
                });

                // إزالة جميع العناصر المضافة يدويًا باستثناء الأولين
                const itemsContainer = document.getElementById('items-container');
                const items = itemsContainer.querySelectorAll('.item-row');
                for (let i = 2; i < items.length; i++) {
                    items[i].remove();
                }
            }
        }

        // معاينة الفاتورة (سيتم تنفيذها بعد إرسال النموذج)
        document.getElementById('invoiceForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // إظهار رسالة تحميل
            const reviewSection = document.getElementById('review-section');
            const previewDiv = reviewSection.querySelector('.invoice-preview');
            previewDiv.innerHTML = `
                <div style="text-align: center; padding: 50px;">
                    <i class="fas fa-spinner fa-spin fa-2x" style="color: var(--primary); margin-bottom: 20px;"></i>
                    <p>جاري إنشاء الفاتورة...</p>
                </div>
            `;

            // إرسال النموذج
            this.submit();
        });
    </script>
</body>
</html>
