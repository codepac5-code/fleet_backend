<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OHAUS Invoice {{ $invoiceNumber }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Questrial&display=swap" rel="stylesheet">
    <style>
        /* جميع أنماط الفاتورة الأصلية هنا */
        /* ... (نفس الأنماط من الكود الأصلي) ... */
    </style>
</head>
<body>
    <!-- كود الفاتورة المعدل مع Blade -->
    <!-- ... (نفس كود الفاتورة المعدل مع Blade) ... -->

    <div style="text-align: center; margin: 30px;">
        <form action="{{ route('invoice.export-pdf') }}" method="POST">
            @csrf
            <input type="hidden" name="companyName" value="{{ $companyName }}">
            <input type="hidden" name="invoiceNumber" value="{{ $invoiceNumber }}">
            <!-- إضافة جميع المتغيرات الأخرى كحقول مخفية -->

            <button type="submit" class="btn btn-primary" style="padding: 12px 25px; font-size: 16px;">
                <i class="fas fa-file-pdf"></i> تصدير كـ PDF
            </button>
            <a href="{{ route('invoice.create') }}" class="btn btn-outline" style="padding: 12px 25px; font-size: 16px;">
                <i class="fas fa-edit"></i> تعديل البيانات
            </a>
        </form>
    </div>
</body>
</html>
