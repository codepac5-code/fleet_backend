<x-master-layout>

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>داشبورد إدارة الخدمات - احترافي وتفاعلي</title>
{{-- <script src="https://cdn.tailwindcss.com"></script> --}}
<style>

/* =========================
   Dark Mode شامل وناعم
========================= */
body.dark {
    background: #1e1f2f; /* داكن أزرق-رمادي */
    color: #e0e0e0; /* نص فاتح */
    transition: all 0.3s ease;
}

/* Sections & Cards */
body.dark section,
body.dark .bg-white {
    background: #2a2a40; /* بطانات داكنة ناعمة */
}
body.dark .bg-gray-50 {
    background: #252536;
}
body.dark .bg-gray-100 {
    background: #2c2c42;
}

/* Headers & Text */
body.dark h1,
body.dark h2,
body.dark p,
body.dark label,
body.dark th,
body.dark td,
body.dark .text-gray-600,
body.dark .text-gray-700 {
    color: #e0e0e0c5;
}

/* Tables */
body.dark table thead {
    background: #3c3c65;
    color: #c6c6c6;
}
body.dark table tbody tr:nth-child(even) {
    background: #2c2c42;
}
body.dark table tbody tr:hover {
    background: #3a3a55;
}
body.dark th,
body.dark td {
    border-color: #555576;
}

/* Inputs & Selects */
body.dark input,
body.dark select {
    background: #2d2d46;
    color: #e0e0e0;
    border-color: #555576;
}
body.dark input::placeholder,
body.dark select option {
    color: #aaa;
}
body.dark input:focus,
body.dark select:focus {
    border-color: #F8A609;
    box-shadow: 0 0 0 2px rgba(248,166,9,0.25);
}

/* Buttons */
body.dark button {
    color: #fff;
}
body.dark .bg-\[\#312873\] {
    background: #1f1b50; /* أزرق داكن ناعم */
}
body.dark .bg-\[\#312873\]:hover {
    background: #161342;
}
body.dark .bg-\[\#F8A609\] {
    background: #d9a204; /* أصفر معتدل */
}
body.dark .bg-\[\#F8A609\]:hover {
    background: #c79300;
}
body.dark .bg-gray-300 {
    background: #555576;
}
body.dark .bg-gray-300:hover {
    background: #777799;
    color: #cacaca;
}

/* Modal */
body.dark #modal {
    background: rgba(0,0,0,0.65);
}
body.dark #modal-content {
    background: #2a2a44;
    border-color: #555576;
    color: #cfcfcf;
}
body.dark #modal-title {
    color: #F8A609;
}

/* Shadows */
body.dark .shadow-lg,
body.dark .shadow-2xl {
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
}

/* Scrollbars */
body.dark ::-webkit-scrollbar {
    width: 8px;
}
body.dark ::-webkit-scrollbar-track {
    background: #2d2d46;
}
body.dark ::-webkit-scrollbar-thumb {
    background: #555576;
    border-radius: 4px;
}
body.dark ::-webkit-scrollbar-thumb:hover {
    background: #777799;
}

/* Extra elements */
body.dark .text-red-500 {
    color: #ff7b7b;
}
body.dark .text-red-500:hover {
    transform: scale(1.15);
}

/* Hover effects تعديل */
body.dark button:hover,
body.dark .modal-btn:hover {
    filter: brightness(1.1);
}

/* Tables responsive select inputs */
body.dark select option {
    background: #2d2d46;
    color: #e0e0e0;
}

/* Borders عامة */
body.dark .border,
body.dark .border-gray-300,
body.dark .border-\[\#312873\] {
    border-color: #555576;
}


/* كل Sections */
body.dark section,
body.dark .bg-white,
body.dark .rounded-xl {
    background: #2a2a40; /* خلفية داكنة ناعمة */
    box-shadow: 0 10px 25px rgba(0,0,0,0.5); /* Shadow مناسب للمود الليلي */
    transition: all 0.3s ease;
}

/* Hover للـ Sections */
body.dark section:hover,
body.dark .hover\:shadow-2xl:hover {
    box-shadow: 0 25px 50px rgba(0,0,0,0.7);
}

/* Cards و Divs عامة */
body.dark .bg-gray-50 { background: #252536; }
body.dark .bg-gray-100 { background: #2c2c42; }

/* Tables داخل Sections */
body.dark table,
body.dark table th,
body.dark table td {
    background: #2a2a40;
    border-color: #555576;
    color: #d0d0d0;
}
body.dark table tbody tr:nth-child(even) {
    background: #2c2c42;
}
body.dark table tbody tr:hover {
    background: #3a3a55;
}

/* Buttons داخل Sections */
body.dark button.bg-\[\#312873\] { background: #1f1b50; }
body.dark button.bg-\[\#F8A609\] { background: #d9a204; }
body.dark button.bg-gray-300 { background: #555576; color: #e0e0e0; }

/* Modals */
body.dark #modal-content { background: #2a2a44; border-color: #555576; }

body.dark section.bg-white {
    background-color: #2a2a40; /* خلفية داكنة ناعمة */
    box-shadow: 0 10px 25px rgba(0,0,0,0.5); /* ظل مناسب للوضع الليلي */
    transition: all 0.3s ease;
}

body.dark section.bg-white:hover {
    box-shadow: 0 25px 50px rgba(0,0,0,0.7); /* ظل أقوى عند hover */
}
body.dark .md\:w-1\/3 {
    background-color: #313143; /* لون داكن ناعم */
}
/* الجزء الكبير داخل section */
body.dark .md\:w-2\/3 {
    background-color: #2a2a3c; /* أغمق شوي عن الخلفية */
    color: #e0e0e0;
}
/* الوضع الليلي للمودال */
body.dark #modal-content {
    background-color: #2c2c3e; /* أغمق من الخلفية العامة */
    color: #e0e0e0; /* نص فاتح */
    border-color: #5c5ca0; /* لون الحدود أغمق شوي عن الأساسي */
    box-shadow: 0 30px 60px rgba(0,0,0,0.5); /* ظل أغمق شوي */
}

/* العنوان داخل المودال */
body.dark #modal-title {
    color: #c5c5ff;
}

/* Inputs داخل المودال */
body.dark #modal input,
body.dark #modal select {
    background-color: #3a3a52;
    border-color: #5c5ca0;
    color: #e0e0e0;
}

/* أزرار المودال */
body.dark #modal button.bg-[#F8A609] {
    background-color: #c68a00;
    color: #fff;
}

body.dark #modal button.bg-gray-300 {
    background-color: #55556a;
    color: #e0e0e0;
}

body.dark #modal button.bg-[#312873] {
    background-color: #5c5ca0;
    color: #fff;
}

body.dark #modal button.bg-[#312873]:hover {
    background-color: #474780;
}























/* Reset */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

/* Colors */
:root {
    --primary: #312873;
    --secondary: #F8A609;
    --gray-50: #eecd8fad;
    --gray-100: #f3f4f6;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-600: #4b5563;
    --gray-700: #374151;
}

/* Backgrounds */
.bg-gray-100 { background: var(--gray-100); }
.bg-gray-50 { background: var(--gray-50); }
.bg-white { background: #fff; }
.bg-black { background: #000; }
.bg-opacity-50 { background: rgba(0,0,0,.5); }

.bg-\[\#312873\] { background: var(--primary); }
.bg-\[\#F8A609\] { background: var(--secondary); }

/* Text */
.text-white { color: #fff; }
.text-center { text-align: center; }
.text-gray-600 { color: var(--gray-600); }
.text-gray-700 { color: var(--gray-700); }
.text-\[\#312873\] { color: var(--primary); }

/* Font sizes */
.text-4xl { font-size: 2.25rem; }
.text-2xl { font-size: 1.5rem; }
.font-bold { font-weight: 700; }
.font-semibold { font-weight: 600; }

/* Layout */
.flex { display: flex; }
.flex-col { flex-direction: column; }
.items-center { align-items: center; }
.justify-center { justify-content: center; }
.justify-between { justify-content: space-between; }
.gap-4 { gap: 16px; }
.space-y-4 > * + * { margin-top: 16px; }
.space-y-12 > * + * { margin-top: 48px; }

/* Width */
.w-full { width: 100%; }
.max-w-lg { max-width: 32rem; }
.max-w-6xl { max-width: 72rem; }
.w-3\/4 { width: 75%; }

/* Padding & Margin */
.p-8 { padding: 32px; }
.p-6 { padding: 24px; }
.p-4 { padding: 16px; }
.mb-8 { margin-bottom: 32px; }
.mb-4 { margin-bottom: 16px; }
.mt-4 { margin-top: 16px; }

/* Border */
.border { border: 1px solid var(--gray-300); }
.border-2 { border-width: 2px; }
.border-gray-300 { border-color: var(--gray-300); }
.border-\[\#312873\] { border-color: var(--primary); }
.rounded { border-radius: 6px; }
.rounded-xl { border-radius: 16px; }

/* Shadow */
.shadow-lg { box-shadow: 0 10px 25px rgba(0,0,0,.12); }
.shadow-2xl { box-shadow: 0 25px 50px rgba(0,0,0,.25); }

/* Buttons */
button {
    cursor: pointer;
    border: none;
    transition: all .25s ease;
}

button:hover {
    transform: translateY(-1px);
}

.hover\:bg-\[\#e59400\]:hover { background: #e59400; }
.hover\:bg-\[\#1f1b5a\]:hover { background: #1f1b5a; }
.hover\:bg-gray-100:hover { background: #e5e7eb; }
.hover\:bg-gray-400:hover { background: var(--gray-400); }

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 8px;
    border: 1px solid var(--gray-300);
}

/* Modal */
.fixed { position: fixed; }
.inset-0 { inset: 0; }
.z-50 { z-index: 50; }

.hidden { display: none !important; }

.transform { transition: all .3s ease; }
.scale-90 { transform: scale(.9); }
.opacity-0 { opacity: 0; }

/* Inputs */
input, select {
    border: 1px solid var(--gray-300);
    border-radius: 6px;
    padding: 6px 8px;
    text-align: center;
}

input:focus, select:focus {
    outline: none;
    border-color: var(--primary);
}

/* Responsive */
@media (min-width: 768px) {
    .md\:flex-row { flex-direction: row; }
    .md\:w-1\/3 { width: 33.333%; }
    .md\:w-2\/3 { width: 66.666%; }
}










:root {
    --primary: #312873;
    --primary-dark: #1f1b5a;
    --secondary: #F8A609;
    --secondary-dark: #e59400;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-600: #4b5563;
    --danger: #dc2626;
}

body {
    background: var(--gray-100);
    color: #111827;
    font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
}

/* =========================
   Modal
========================= */
#modal {
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,.55);
}

#modal-content {
    background: #fff;
    width: 100%;
    max-width: 560px;
    border-radius: 18px;
    border: 2px solid var(--primary);
    padding: 24px;
    box-shadow: 0 30px 60px rgba(0,0,0,.35);
    transform: scale(.9);
    opacity: 0;
    transition: all .3s ease;
}

#modal:not(.hidden) #modal-content {
    transform: scale(1);
    opacity: 1;
}

/* Modal title */
#modal-title {
    color: var(--primary);
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 20px;
}

/* =========================
   Tables
========================= */
table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 12px;
    overflow: hidden;
}

thead {
    background: var(--primary);
    color: #fff;
}

th {
    padding: 10px;
    font-size: 14px;
    font-weight: 600;
}

td {
    padding: 10px;
    font-size: 14px;
    border-bottom: 1px solid var(--gray-200);
}

tbody tr:nth-child(even) {
    background: #fafafa;
}

tbody tr:hover {
    background: var(--gray-200);
}

/* =========================
   Inputs & Selects
========================= */
input,
select {
    width: 100%;
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid var(--gray-300);
    font-size: 14px;
    text-align: center;
}

input:focus,
select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(49,40,115,.15);
}

/* =========================
   Buttons
========================= */
button {
    cursor: pointer;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    transition: all .25s ease;
}

/* Primary */
.bg-\[\#312873\] {
    background: var(--primary);
    color: #fff;
}

.bg-\[\#312873\]:hover {
    background: var(--primary-dark);
}

/* Secondary */
.bg-\[\#F8A609\] {
    background: var(--secondary);
    color: #fff;
}

.bg-\[\#F8A609\]:hover {
    background: var(--secondary-dark);
}

/* Gray */
.bg-gray-300 {
    background: var(--gray-300);
}

.bg-gray-300:hover {
    background: var(--gray-600);
    color: #dcdbdb;
}

/* Modal Add Button */
.modal-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--primary);
    color: #fff;
    padding: 10px 16px;
    border-radius: 12px;
}

.modal-btn svg {
    width: 18px;
    height: 18px;
}

.modal-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
}

/* Delete icon */
.text-red-500 {
    color: var(--danger);
    font-size: 18px;
}

.text-red-500:hover {
    transform: scale(1.15);
}

/* =========================
   Helpers
========================= */
.hidden {
    display: none !important;
}

.text-center {
    text-align: center;
}

.rounded {
    border-radius: 10px;
}

.rounded-xl {
    border-radius: 18px;
}

.shadow-lg {
    box-shadow: 0 12px 25px rgba(0,0,0,.15);
}

.shadow-2xl {
    box-shadow: 0 25px 50px rgba(0,0,0,.35);
}








</style>

<main class="p-8 max-w-6xl mx-auto space-y-12">
    <h1 class="text-4xl font-bold text-[#312873] mb-8 text-center">إدارة اسعار الخدمات</h1>

    @foreach($services as $service)
    <section class="bg-gray-100 rounded-xl shadow-lg overflow-hidden flex flex-col md:flex-row hover:shadow-2xl transition-shadow duration-300">
        <div class="bg-gray-50 md:w-1/3 h-64 md:h-auto">
            <img src="{{ $service->image }}" alt="Service Image" class="w-full h-full object-cover">
        </div>
        <div class="md:w-2/3 p-6 flex flex-col justify-between">
            <div>
                <h2 class="font-bold text-2xl text-[#312873] mb-2">{{ $service->title }}</h2>
                <p class="text-gray-600 mb-4">{{ $service->description }}</p>
                <button onclick="toggleSubService('sub{{ $service->id }}')"
                    class="mb-4 w-full md:w-auto bg-[#F8A609] hover:bg-[#e59400] text-white font-semibold px-4 py-2 rounded transition">
                    عرض الخدمات الفرعية
                </button>

                <div id="sub{{ $service->id }}" class="hidden overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300 mb-4">
                        <thead class="bg-[#312873] text-white">
                            <tr>
                                <th class="border px-3 py-2">الخدمة الفرعية</th>
                                <th class="border px-3 py-2">النوع</th>
                                @if(!$service->travel_service)
                                <th class="border px-3 py-2">التسعير</th>
                                @else
                                <th class="border px-3 py-2">عدد الخطوط</th>
                                @endif
                                <th class="border px-3 py-2">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($service->subServices as $sub)
                                <tr class="bg-gray-50 hover:bg-gray-100 transition">
                                    <td class="border px-3 py-2 font-semibold">{{ $sub->name }}</td>
                                    <td class="border px-3 py-2">{{ $service->travel_service ? 'سفر' : 'عادية' }}</td>

                                    @if(!$service->travel_service)
                                    <td class="border px-3 py-2">
                                        Open: {{ $sub->openPrice }}$ | KM: {{ $sub->kmPrice }}$ | Minute: {{ $sub->minutePrice }}$
                                    </td>
                                    @else
                                    <td class="border px-3 py-2 text-center">{{ $sub->travelRoutes->count() }}</td>
                                    @endif

                                    <td class="border px-3 py-2 text-center">
                                        <button class="bg-[#312873] hover:bg-[#1f1b5a] text-white px-3 py-1 rounded"
                                            onclick="openModal('{{ $service->travel_service ? 'travel' : 'normal' }}', {{ $sub->id }})">
                                            تعديل
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </section>
    @endforeach
</main>

<!-- Modal -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div id="modal-content" class="bg-gray-50 rounded-xl shadow-2xl border-2 border-[#312873] w-full max-w-lg p-6 transform scale-90 opacity-0 transition-all duration-300 flex flex-col items-center">
        <h2 class="text-2xl font-bold mb-6 text-center text-[#312873]" id="modal-title">تعديل التسعير</h2>

        <!-- Normal Service Form -->
        <div id="normal-service" class="hidden w-full flex flex-col items-center space-y-4">
            <div class="flex flex-col w-3/4">
                <label class="mb-1 font-semibold text-gray-700 text-right">السعر الابتدائي</label>
                <input type="number" class="border rounded px-3 py-2 w-full max-w-xs text-center" placeholder="Open Price">
            </div>
            <div class="flex flex-col w-3/4">
                <label class="mb-1 font-semibold text-gray-700 text-right">سعر الكيلومتر</label>
                <input type="number" class="border rounded px-3 py-2 w-full max-w-xs text-center" placeholder="KM Price">
            </div>
            <div class="flex flex-col w-3/4">
                <label class="mb-1 font-semibold text-gray-700 text-right">سعر الدقيقة</label>
                <input type="number" class="border rounded px-3 py-2 w-full max-w-xs text-center" placeholder="Minute Price">
            </div>
            <div class="flex justify-center gap-4 mt-4">
                <button id="save-normal" class="bg-[#F8A609] text-white px-6 py-2 rounded hover:bg-[#e59400] transition" style="min-width: 120px;">حفظ</button>
                <button class="bg-gray-300 px-6 py-2 rounded hover:bg-gray-400 transition" onclick="closeModal()" style="min-width: 120px;">إلغاء</button>
            </div>
        </div>

        <!-- Travel Service Form -->
        <div id="travel-service" class="hidden w-full flex flex-col items-center space-y-4">
            <div class="table-wrapper" style="max-height: 250px; overflow-y: auto; width: 100%;">
                <table id="routes-table" class="w-full">
                    <thead class="bg-[#312873] text-white">
                        <tr>
                            <th class="border px-2 py-1">الدولة + المدينة الانطلاق</th>
                            <th class="border px-2 py-1">الدولة + المدينة الوصول</th>
                            <th class="border px-2 py-1">السعر</th>
                            <th class="border px-2 py-1">حذف</th>
                        </tr>
                    </thead>
                    <tbody id="routes-table-body">
                        {{-- JS يملأها --}}
                    </tbody>
                </table>
            </div>

            <button onclick="addRow()" class="modal-btn flex items-center gap-1 px-4 py-2 bg-[#F8A609] text-white rounded hover:bg-[#e59400] transition">
                إضافة خط جديد
            </button>

            <div class="flex justify-center gap-4 mt-4">
                <button id="save-travel" class="bg-[#F8A609] text-white px-6 py-2 rounded hover:bg-[#e59400] transition" style="min-width: 120px;">حفظ</button>
                <button class="bg-gray-300 px-6 py-2 rounded hover:bg-gray-400 transition" onclick="closeModal()" style="min-width: 120px;">إلغاء</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentSubId = null;
let countriesList = [];
let citiesList = {}; // cache

function toggleSubService(id) {
    document.getElementById(id).classList.toggle('hidden');
}

// جلب الدول عند التحميل
fetch('/my-services/countries')
    .then(res => res.json())
    .then(data => { countriesList = data; });

async function loadCities(countryId){
    if(!countryId) return [];
    if(citiesList[countryId]) return citiesList[countryId];
    const res = await fetch(`/my-services/cities/${countryId}`);
    const data = await res.json();
    citiesList[countryId] = data;
    return data;
}

function generateOptions(list, selectedId=''){
    return list.map(item => `<option value="${item.id}" ${item.id==selectedId ? 'selected':''}>${item.name}</option>`).join('');
}







async function openModal(type, subId) {
    currentSubId = subId;
    const modal = document.getElementById('modal');
    const content = document.getElementById('modal-content');
    modal.classList.remove('hidden');
    content.offsetHeight;
    content.style.opacity = '1';
    content.style.transform = 'scale(1)';

    if(type==='normal'){
        document.getElementById('normal-service').classList.remove('hidden');
        document.getElementById('travel-service').classList.add('hidden');
        document.getElementById('modal-title').innerText="تعديل التسعير - خدمة عادية";

        fetch(`/my-services/show/${subId}`)
            .then(res => res.json())
            .then(data=>{
                document.querySelector('#normal-service input[placeholder="Open Price"]').value = data.openPrice;
                document.querySelector('#normal-service input[placeholder="KM Price"]').value = data.kmPrice;
                document.querySelector('#normal-service input[placeholder="Minute Price"]').value = data.minutePrice;
            });

    } else {
        document.getElementById('travel-service').classList.remove('hidden');
        document.getElementById('normal-service').classList.add('hidden');
        document.getElementById('modal-title').innerText="تعديل التسعير - خدمة سفر";

        fetch(`/my-services/get-routes/${subId}/routes`)
            .then(res=>res.json())
            .then(async routes=>{
                const tbody = document.getElementById('routes-table-body');
                tbody.innerHTML = '';

                for(let r of routes){
                    // أنشئ صف لكل خط
                    await addRow(
                        r.departure_city_id, r.departure_country_id, r.departure_city_name, r.departure_country_name,
                        r.arrival_city_id, r.arrival_country_id, r.arrival_city_name, r.arrival_country_name,
                        r.trip_price
                    );
                }
            });
    }
}

async function addRow(depCityId='', depCountryId='', depCityName='', depCountryName='',
                      arrCityId='', arrCountryId='', arrCityName='', arrCountryName='',
                      price='') {

    const tbody = document.getElementById('routes-table-body');
    const row = document.createElement('tr');

    const countryOptions = generateOptions(countriesList);

    row.innerHTML = `
        <td>
            <select class="border px-2 py-1 w-full country-select" onchange="updateCities(this,'dep')">
                <option value="">اختر الدولة</option>${countryOptions}
            </select>
            <select class="border px-2 py-1 w-full mt-1 city-select">
                <option value="">اختر المدينة</option>
            </select>
        </td>
        <td>
            <select class="border px-2 py-1 w-full country-select" onchange="updateCities(this,'arr')">
                <option value="">اختر الدولة</option>${countryOptions}
            </select>
            <select class="border px-2 py-1 w-full mt-1 city-select">
                <option value="">اختر المدينة</option>
            </select>
        </td>
        <td><input type="number" class="border px-2 py-1 w-full" value="${price}"></td>
        <td class="text-center"><button class="text-red-500" onclick="removeRow(this)">✖</button></td>
    `;

    tbody.appendChild(row);

    // تحديد الدولة
    if(depCountryId) row.querySelectorAll('select.country-select')[0].value = depCountryId;
    if(arrCountryId) row.querySelectorAll('select.country-select')[1].value = arrCountryId;

    // تحميل المدن وتحديدها
    if(depCityId) await updateCities(row.querySelectorAll('select.country-select')[0], 'dep', depCityId);
    if(arrCityId) await updateCities(row.querySelectorAll('select.country-select')[1], 'arr', arrCityId);

    // إذا كان الاسم موجود، ضع الخيار المحدد بالاسم (عرض في select)
    if(depCityName && row.querySelectorAll('select.city-select')[0]){
        const citySelect = row.querySelectorAll('select.city-select')[0];
        if(!Array.from(citySelect.options).some(opt => opt.value==depCityId)){
            const opt = document.createElement('option');
            opt.value = depCityId;
            opt.text = depCityName;
            opt.selected = true;
            citySelect.appendChild(opt);
        }
    }
    if(arrCityName && row.querySelectorAll('select.city-select')[1]){
        const citySelect = row.querySelectorAll('select.city-select')[1];
        if(!Array.from(citySelect.options).some(opt => opt.value==arrCityId)){
            const opt = document.createElement('option');
            opt.value = arrCityId;
            opt.text = arrCityName;
            opt.selected = true;
            citySelect.appendChild(opt);
        }
    }

    updateRouteCount();
}









async function updateCities(select, type, selectedCityId=''){
    const countryId = select.value;
    const citySelect = select.nextElementSibling;
    const cities = await loadCities(countryId);
    citySelect.innerHTML = '<option value="">اختر المدينة</option>' + generateOptions(cities, selectedCityId);

    if(selectedCityId) citySelect.value = selectedCityId;
}


function closeModal() {
    const modal = document.getElementById('modal');
    const content = document.getElementById('modal-content');
    content.style.opacity='0';
    content.style.transform='scale(0.9)';
    setTimeout(()=>{ modal.classList.add('hidden'); }, 300);
}
document.getElementById('modal').addEventListener('click', e=>{if(e.target.id==='modal') closeModal();});
document.addEventListener('keydown', e=>{if(e.key==="Escape") closeModal();});





function removeRow(btn){
    btn.closest('tr').remove();
    updateRouteCount();
}

function updateRouteCount(){
    const count = document.querySelectorAll('#routes-table-body tr').length;
    const mainRow = document.querySelector(`button[onclick*="openModal('travel', ${currentSubId})"]`).closest('tr');
    if(mainRow){
        mainRow.children[2].innerText = count;
    }
}






function removeRow(btn){ btn.closest('tr').remove(); }






function showToast(message) {
    const toast = document.getElementById('toast');
    toast.innerText = message;
    toast.classList.remove('opacity-0');
    toast.classList.add('opacity-100');

    setTimeout(() => {
        toast.classList.remove('opacity-100');
        toast.classList.add('opacity-0');
    }, 3000); // يظهر لمدة 3 ثواني
}

// حفظ البيانات العادية
document.getElementById('save-normal').addEventListener('click', () => {
    const payload = {
        openPrice: document.querySelector('#normal-service input[placeholder="Open Price"]').value,
        kmPrice: document.querySelector('#normal-service input[placeholder="KM Price"]').value,
        minutePrice: document.querySelector('#normal-service input[placeholder="Minute Price"]').value
    };

    if(!payload.openPrice || !payload.kmPrice || !payload.minutePrice){
        showToast('الرجاء تعبئة جميع الحقول قبل الحفظ!');
        return;
    }

    fetch(`/my-services/update/${currentSubId}`, {
        method: 'PUT',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify(payload)
    }).then(()=>closeModal());
});

// حفظ بيانات السفر
document.getElementById('save-travel').addEventListener('click', () => {
    const rows = document.querySelectorAll('#routes-table-body tr');
    const routes = [];

    for(let r of rows){
        const countries = r.querySelectorAll('select.country-select');
        const cities = r.querySelectorAll('select.city-select');
        const price = r.querySelector('input').value;

        if(!countries[0].value || !cities[0].value || !countries[1].value || !cities[1].value || !price){
            showToast('الرجاء تعبئة جميع الحقول قبل الحفظ!');
            return;
        }

        routes.push({
            departure_country_id: countries[0].value,
            departure_city_id: cities[0].value,
            arrival_country_id: countries[1].value,
            arrival_city_id: cities[1].value,
            trip_price: price
        });
    }

    fetch(`/my-services/update-routes/${currentSubId}/routes`, {
        method: 'PUT',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify({routes})
    }).then(()=>closeModal());
});




</script>

<!-- Toast container -->
<div id="toast" class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg opacity-0 transition-opacity duration-300 z-50"></div>


</x-master-layout>
