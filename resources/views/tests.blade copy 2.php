<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Smart Trips Dashboard</title>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        scheduled: "#4f46e5",
        completed: "#16a34a",
        cancelled: "#dc2626",
        dark: "#2b255f"
      }
    }
  }
}
</script>

<style>
body{
  background: radial-gradient(circle at 20% 20%, #eef2ff, transparent 40%),
              radial-gradient(circle at 80% 80%, #f5f3ff, transparent 40%),
              linear-gradient(135deg,#f8fafc,#eef2ff);
}
colors: {
  primary: "#6366f1",
  secondary: "#8b5cf6",
  accent: "#06b6d4",
  success: "#22c55e",
  danger: "#ef4444",
  warning: "#f59e0b",
  dark: "#1e1b4b"
}
button:hover i{
  transform: scale(1.1);
}
.glass{
  background: rgba(255,255,255,0.6);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(255,255,255,0.4);
  box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}
.card{
  background: linear-gradient(145deg, rgba(255,255,255,0.9), rgba(255,255,255,0.6));
  backdrop-filter: blur(14px);
  border-radius: 20px;
  border: 1px solid rgba(255,255,255,0.3);
  box-shadow:
    0 10px 30px rgba(0,0,0,0.08),
    inset 0 1px 0 rgba(255,255,255,0.6);
  transition: all .3s ease;
}

.card:hover{
  transform: translateY(-6px) scale(1.01);
  box-shadow: 0 20px 50px rgba(0,0,0,0.12);
}
.tab{
  transition: all .25s;
}

.tab.active{
  background: linear-gradient(135deg,#6366f1,#8b5cf6);
  color:white;
  box-shadow: 0 6px 20px rgba(99,102,241,0.4);
}
.scroll-x{
  display:flex;
  gap:14px;
  overflow-x:auto;
  scroll-snap-type:x mandatory;
  padding-bottom:10px;
}


.card:hover{ transform:translateY(-5px); }

.stat:hover{
  transform: translateY(-3px);
}
.btn{
  background: linear-gradient(135deg,#6366f1,#8b5cf6);
  color:white;
  border-radius: 14px;
  transition:.25s;
}

.btn:hover{
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(99,102,241,0.4);
}

.section{
  border-radius: 22px;
  padding: 18px;
  backdrop-filter: blur(14px);
  border: 1px solid rgba(255,255,255,0.4);
  box-shadow: 0 10px 30px rgba(0,0,0,0.06);
}
.section-today{
  background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(139,92,246,0.08));
}

.section-tomorrow{
  background: linear-gradient(135deg, rgba(6,182,212,0.12), rgba(14,165,233,0.08));
}

.section-all{
  background: linear-gradient(135deg, rgba(34,197,94,0.12), rgba(16,185,129,0.08));
}

/* وقت */
.section-morning{
  background: linear-gradient(135deg, rgba(251,191,36,0.15), rgba(245,158,11,0.08));
}

.section-noon{
  background: linear-gradient(135deg, rgba(59,130,246,0.12), rgba(14,165,233,0.08));
}

.section-night{
  background: linear-gradient(135deg, rgba(30,27,75,0.15), rgba(79,70,229,0.1));
  color:#1e1b4b;
}



.filter-box{
  background: linear-gradient(135deg, rgba(255,255,255,0.7), rgba(255,255,255,0.4));
  backdrop-filter: blur(18px);
  border-radius: 22px;
  padding: 20px;
  border: 1px solid rgba(255,255,255,0.5);
  box-shadow:
    0 10px 30px rgba(0,0,0,0.06),
    inset 0 1px 0 rgba(255,255,255,0.6);
  position: relative;
  overflow: hidden;
}

.filter-box::before{
  content:"";
  position:absolute;
  width:200px;
  height:200px;
  background: radial-gradient(circle, rgba(99,102,241,0.15), transparent 70%);
  top:-50px;
  left:-50px;
}


.filter-input{
  border-radius:14px;
  border:1px solid rgba(0,0,0,0.1);
  padding:10px 12px;
  font-size:14px;
  transition:.2s;
  background:white;
}

.filter-input:focus{
  outline:none;
  border-color:#4f46e5;
  box-shadow:0 0 0 3px rgba(79,70,229,0.1);
}

.filter-btn{
  background: linear-gradient(135deg,#4f46e5,#6366f1);
  color:white;
  padding:10px 16px;
  border-radius:14px;
  display:flex;
  align-items:center;
  gap:6px;
  transition:.2s;
}

.filter-btn:hover{
  transform:translateY(-2px);
  box-shadow:0 6px 20px rgba(79,70,229,0.3);
}
.stats-box{
  background: linear-gradient(135deg, #eef2ff, #f8fafc);
  border-radius: 24px;
  padding: 20px;
  border: 1px solid rgba(99,102,241,0.15);
  box-shadow:
    0 15px 40px rgba(99,102,241,0.08);
}



.chip:hover{
  background: rgba(79,70,229,0.2);
}
.chip{
  background: rgba(79,70,229,0.08);
  color:#4f46e5;
  padding:6px 12px;
  border-radius:999px;
  display:flex;
  align-items:center;
  gap:8px;
  font-size:13px;
  backdrop-filter: blur(6px);
  border:1px solid rgba(79,70,229,0.15);
  transition:.2s;
}

.chip:hover{
  background: rgba(79,70,229,0.15);
}
.scroll-x::-webkit-scrollbar{
  height:6px;
}

.scroll-x::-webkit-scrollbar-thumb{
  background: linear-gradient(90deg,#6366f1,#8b5cf6);
  border-radius:10px;
}
.section-title{
  font-weight: bold;
  margin-bottom: 10px;
  opacity: 0.8;
}
.stat{
  background: rgba(255,255,255,0.8);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 14px;
  border: 1px solid rgba(0,0,0,0.05);
  transition: .25s;
}

.stat:hover{
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}
.stat-scheduled{ border-left: 4px solid #6366f1; }
.stat-progress{ border-left: 4px solid #f59e0b; }
.stat-completed{ border-left: 4px solid #22c55e; }
.stat-cancelled{ border-left: 4px solid #ef4444; }
.stat-revenue{ border-left: 4px solid #f97316; }

.stats-box::before{
  content:"";
  display:block;
  height:4px;
  border-radius:10px;
  background: linear-gradient(90deg,#6366f1,#8b5cf6,#06b6d4);
  margin-bottom:15px;
}

/* =========================
   DARK MODE ROOT
========================= */
.dark{
  color-scheme: dark;
}

/* =========================
   BODY BACKGROUND
========================= */
.dark body{
  background:
    radial-gradient(circle at 20% 20%, #1e1b4b, transparent 40%),
    radial-gradient(circle at 80% 80%, #020617, transparent 50%),
    linear-gradient(135deg,#020617,#0f172a);
  color:#e5e7eb;
}

/* =========================
   HEADER
========================= */
.dark header,
.dark .header{
  background: linear-gradient(135deg,#1e1b4b,#312e81);
  color:white;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}

/* =========================
   GLASS / SECTIONS
========================= */
.dark .section,
.dark .filter-box,
.dark .stats-box{
  background: rgba(15,23,42,0.65);
  backdrop-filter: blur(18px);
  border: 1px solid rgba(255,255,255,0.08);
  box-shadow: 0 10px 30px rgba(0,0,0,0.4);
}

/* =========================
   FILTER BOX
========================= */
.dark .filter-box{
  background: linear-gradient(135deg, rgba(30,41,59,0.85), rgba(15,23,42,0.7));
}

.dark .filter-box::before{
  content:"";
  position:absolute;
  width:200px;
  height:200px;
  background: radial-gradient(circle, rgba(99,102,241,0.2), transparent 70%);
  top:-50px;
  left:-50px;
}

/* =========================
   FILTER TITLE
========================= */
.dark .filter-title{
  color:#a5b4fc;
}

/* =========================
   INPUTS
========================= */
.dark .filter-input{
  background: rgba(30,41,59,0.85);
  color:#e5e7eb;
  border:1px solid rgba(255,255,255,0.08);
}

.dark .filter-input:focus{
  border-color:#6366f1;
  box-shadow:0 0 0 3px rgba(99,102,241,0.2);
}

.dark .filter-input::placeholder{
  color:#94a3b8;
}

/* =========================
   BUTTONS
========================= */
.dark .filter-btn{
  background: linear-gradient(135deg,#6366f1,#8b5cf6);
}

.dark button{
  transition:.25s;
}

.dark button:hover{
  transform: translateY(-2px);
}

/* =========================
   TABS
========================= */
.dark .tab{
  background: rgba(255,255,255,0.05);
  color:#cbd5f5;
}

.dark .tab.active{
  background: linear-gradient(135deg,#6366f1,#8b5cf6);
  color:white;
  box-shadow: 0 6px 20px rgba(99,102,241,0.4);
}

/* =========================
   CARDS (TRIPS)
========================= */
.dark .card{
  background: linear-gradient(145deg, rgba(30,41,59,0.95), rgba(15,23,42,0.75));
  border: 1px solid rgba(255,255,255,0.06);
  box-shadow:
    0 15px 40px rgba(0,0,0,0.5),
    inset 0 1px 0 rgba(255,255,255,0.04);
}

.dark .card:hover{
  transform: translateY(-6px);
  box-shadow: 0 25px 60px rgba(0,0,0,0.6);
}

/* =========================
   CARD INNER SECTIONS
========================= */
.dark .card .bg-white{
  background: rgba(15,23,42,0.6) !important;
}

.dark .card .bg-gray-50{
  background: rgba(30,41,59,0.6) !important;
}

/* =========================
   STATS BOX
========================= */
.dark .stats-box{
  background: linear-gradient(135deg,#0f172a,#1e293b);
  border: 1px solid rgba(99,102,241,0.2);
}

/* =========================
   STAT CARDS
========================= */
.dark .stat{
  background: rgba(30,41,59,0.7);
  border: 1px solid rgba(255,255,255,0.05);
  backdrop-filter: blur(10px);
}

.dark .stat:hover{
  transform: translateY(-4px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.4);
}

/* =========================
   STATUS COLORS
========================= */
.dark .text-scheduled{ color:#818cf8; }
.dark .text-green-600{ color:#4ade80; }
.dark .text-red-600{ color:#f87171; }
.dark .text-yellow-600{ color:#facc15; }

/* =========================
   CHIPS
========================= */
.dark .chip{
  background: rgba(99,102,241,0.15);
  color:#a5b4fc;
  border:1px solid rgba(99,102,241,0.3);
}

/* =========================
   MODALS
========================= */
.dark #driverModal > div,
.dark #confirmDriverModal > div,
.dark #cancelModal > div{
  background: rgba(15,23,42,0.95);
  color:#e5e7eb;
  border:1px solid rgba(255,255,255,0.08);
}

/* =========================
   SCROLLBAR
========================= */
.dark ::-webkit-scrollbar{
  width:6px;
  height:6px;
}

.dark ::-webkit-scrollbar-thumb{
  background: linear-gradient(180deg,#6366f1,#8b5cf6);
  border-radius:10px;
}

/* =========================
   ICONS
========================= */
.dark svg{
  stroke:#cbd5f5;
}

/* =========================
   LINKS
========================= */
.dark a{
  color:#818cf8;
}
</style>
</head>

<body >
<div class="p-4 bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-500 text-white shadow-lg"></div>
<!-- HEADER -->
<div class="header p-4 bg-white/70 backdrop-blur-xl border-b flex justify-between items-center">
      <h1 class="text-dark font-bold flex items-center gap-2">
    <i data-lucide="layout-dashboard"></i>
    لوحة الرحلات
  </h1>

  <button class="bg-orange-500 text-white px-4 py-2 rounded-xl">
    + رحلة
  </button>
</div>

<!-- TABS -->
<div class="p-3 flex justify-center gap-2 bg-white/60 backdrop-blur-xl">

  <button onclick="setTab('scheduled',this)"
    class="tab active px-4 py-2 rounded-full bg-scheduled/10 text-scheduled font-semibold">
    📅 مجدولة
  </button>

  <button onclick="setTab('in_progress',this)"
  class="tab px-4 py-2 rounded-full bg-yellow-100 text-yellow-700">
  🚕 جاري
</button>

  <button onclick="setTab('completed',this)"
    class="tab px-4 py-2 rounded-full bg-green-100 text-green-700">
    ✅ مكتملة
  </button>

  <button onclick="setTab('cancelled',this)"
    class="tab px-4 py-2 rounded-full bg-red-100 text-red-600">
    ❌ ملغاة
  </button>

</div>



<!-- FILTERS -->
<div class="p-4">

  <div class="filter-box">

    <div class="filter-title">
      <i data-lucide="sliders-horizontal"></i>
      الفلاتر الذكية
    </div>

    <div class="grid md:grid-cols-5 gap-3">

      <div class="relative">
        <i data-lucide="calendar" class="absolute right-3 top-3 w-4"></i>
        <input id="datePicker" type="date" class="filter-input w-full pr-8">
      </div>

      <select id="driverFilter" placeholder="اختر سائق..." class="filter-input"></select>

      <select id="fromFilter" class="filter-input"></select>

      <select id="toFilter" class="filter-input"></select>

      <button onclick="resetFilters()" class="filter-btn">
        <i data-lucide="x-circle"></i>
        إزالة الفلترة
      </button>

    </div>

    <div id="activeFilters" class="flex flex-wrap gap-2 mt-4"></div>
    {{-- <div class="chip ${colorMap[f.key]} ${f.strong ? 'ring-2 ring-indigo-300' : ''}"></div> --}}
  </div>

</div>


<!-- GLOBAL STATS -->

<div class="p-4">
<div class="stats-box">
    <div class="flex items-center gap-2 font-bold text-dark mb-3">
      <i data-lucide="globe"></i>
      الإحصائيات العامة
    </div>

    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 text-sm">

      <div class="stat stat-scheduled">
        <p class="text-gray-400">إجمالي</p>
        <p id="totalTrips" class="font-bold text-dark">0</p>
      </div>

      <div class="stat stat-scheduled">
        <p class="text-gray-400">مجدولة</p>
        <p id="scheduledTrips" class="font-bold text-scheduled">0</p>
      </div>

      <div class="p-3 rounded-xl border bg-yellow-50">
        <p class="text-gray-400">جاري</p>
        <p id="progressTrips" class="font-bold text-yellow-600">0</p>
    </div>

      <div class="p-3 rounded-xl border bg-green-50">
        <p class="text-gray-400">مكتملة</p>
        <p id="completedTrips" class="font-bold text-green-600">0</p>
      </div>

      <div class="p-3 rounded-xl border bg-red-50">
        <p class="text-gray-400">ملغاة</p>
        <p id="cancelledTrips" class="font-bold text-red-600">0</p>
      </div>

      <div class="p-3 rounded-xl border bg-orange-50">
        <p class="text-gray-400">الإيرادات</p>
        <p id="revenue" class="font-bold text-orange-500">$0</p>
      </div>


    </div>
  </div>
</div>

<!-- CONTENT -->
<div id="container" class="p-6 space-y-6"></div>


<div id="driverModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center">
  <div class="bg-white w-[420px] p-4 rounded-xl">

    <input id="driverSearch"
      placeholder="ابحث عن سائق"
      class="w-full border p-2 rounded mb-3">

    <div id="driverList" class="space-y-2 max-h-[350px] overflow-auto"></div>

    <button onclick="closeDriverModal()"
      class="mt-3 w-full bg-gray-500 text-white py-2 rounded">
      إغلاق
    </button>

  </div>
</div>


<div id="confirmDriverModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">

  <div class="bg-white w-[360px] p-5 rounded-xl space-y-4">

    <div class="text-lg font-bold text-indigo-600">
      تأكيد اختيار السائق
    </div>

    <div class="flex items-center gap-3">
      <img id="selectedDriverImage" class="w-12 h-12 rounded-full border">
      <div id="selectedDriverName" class="font-semibold"></div>
    </div>

    <div id="assignLoader" class="hidden text-sm text-gray-500">
      جاري تعيين السائق...
    </div>

    <div class="flex gap-2">

      <button onclick="confirmAssignDriver()"
        class="flex-1 bg-indigo-600 text-white py-2 rounded-xl">
        تأكيد
      </button>

      <button onclick="closeConfirmDriverModal()"
        class="flex-1 bg-gray-400 text-white py-2 rounded-xl">
        إلغاء
      </button>

    </div>

  </div>

</div>



<div id="cancelModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
  <div class="bg-white w-[380px] p-5 rounded-xl space-y-4">

    <div class="text-lg font-bold text-red-600 flex items-center gap-2">
      <i data-lucide="alert-triangle"></i>
      تأكيد إلغاء الرحلة
    </div>

    <p class="text-gray-600 text-sm">
      هل أنت متأكد أنك تريد إلغاء هذه الرحلة؟ لا يمكن التراجع بعد التنفيذ.
    </p>

    <input type="hidden" id="cancelTripId">

    <div class="flex gap-2">

      <button onclick="confirmCancelTrip()"
        class="flex-1 bg-red-600 text-white py-2 rounded-xl">
        نعم، إلغاء
      </button>

      <button onclick="closeCancelModal()"
        class="flex-1 bg-gray-400 text-white py-2 rounded-xl">
        تراجع
      </button>

    </div>

  </div>
</div>
<script>

let expandedCard = null;


let trips = [];
let currentPage = 1;
let lastPage = 1;
let loading = false;
let tab = "scheduled";

/* API */
async function fetchTrips(reset = false){

  if(loading) return;
  loading = true;

  if(reset){
    currentPage = 1;
    trips = [];
  }

const params = new URLSearchParams();

    params.append("page", currentPage);

    // status
    params.append("status", tab);

    // date
    if(datePicker.value){
    params.append("date", datePicker.value);
    }

    // filters
    if(driverFilter.value) params.append("driver", driverFilter.value);
    if(fromFilter.value) params.append("from", fromFilter.value);
    if(toFilter.value) params.append("to", toFilter.value);



    // request
    const res = await fetch(`/api/scheduled-ride-data?` + params.toString());
    const json = await res.json();

  const mapped = json.data.map(t => normalizeTrip(t));

  trips = reset ? mapped : [...trips, ...mapped];

  lastPage = json.last_page || 1;

  loading = false;

  syncUI();
  renderActiveFilters();
}





function renderActiveFilters(){

  const filters = [];

  if(datePicker.value)
    filters.push({
      key:'date',
      label: datePicker.value,
      icon:'calendar',
      strong:true
    });

  if(driverFilter.value)
    filters.push({
      key:'driver',
      label: driverFilter.options[driverFilter.selectedIndex].text,
      icon:'car'
    });

  if(fromFilter.value)
    filters.push({
      key:'from',
      label: fromFilter.options[fromFilter.selectedIndex].text,
      icon:'map-pin'
    });

  if(toFilter.value)
    filters.push({
      key:'to',
      label: toFilter.options[toFilter.selectedIndex].text,
      icon:'flag'
    });

  activeFilters.innerHTML = filters.map(f => `
    <div class="chip ${f.strong ? 'ring-2 ring-indigo-300' : ''} flex items-center gap-2">

      <i data-lucide="${f.icon}" class="w-4 h-4"></i>

      <span>${f.label}</span>

      <button onclick="removeFilter('${f.key}')"
        class="hover:text-red-500 transition">
        <i data-lucide="x" class="w-3 h-3"></i>
      </button>

    </div>
  `).join('');

  lucide.createIcons();
}


function removeFilter(key){

  if(key === 'date') datePicker.value = "";
  if(key === 'driver') driverFilter.value = "";
  if(key === 'from') fromFilter.value = "";
  if(key === 'to') toFilter.value = "";

  fetchTrips(true);
}

function normalizeTrip(t){

  let status = (t.status || "").toLowerCase();

if(status === "completed") status = "completed";
else if(status === "cancelled") status = "cancelled";
else if(status === "in_progress") status = "in_progress";
else status = "scheduled";

  return {
    id: t.id,
    status,

    date: (t.startAt || "").split(" ")[0],
    time: t.time || (t.startAt ? t.startAt.split(" ")[1].slice(0,5) : "-"),

    from: t.startAddress,
    to: t.endAddress,

    startAddress: t.startAddress,
    endAddress: t.endAddress,

    totalAmount: t.totalAmount,

    distance: t.distance,
    paymentType: t.paymentType,
    paymentStatus: t.paymentStatus,
    rating: t.rating,

    is_scheduled: t.is_scheduled,
    scheduled_time: t.scheduled_time,
    startAt: t.startAt,

    driver: t.driver ? {
        id: t.driver.id,
        name: `${t.driver.firstName} ${t.driver.lastName}`,
        phone: t.driver.phoneNumber,
        image: t.driver.photo
        } : null,

    driverPhone: t.driver ? t.driver.phoneNumber : null,
    driverImage: t.driver ? t.driver.photo : null,

    carName: "",
    carPlate: "",

    customerName: t.user ? `${t.user.firstName} ${t.user.lastName}` : null,
    customerPhone: t.user ? t.user.phoneNumber : null,
    customerImage: t.user ? t.user.photo : null,
  };
}


/* TAB */
function setTab(t,el){
  tab = t;
  document.querySelectorAll(".tab").forEach(b=>b.classList.remove("active"));
    el.classList.add("active");
  fetchTrips(true);
}

function toggleCard(id){
  expandedCard = expandedCard === id ? null : id;
  syncUI();
}

/* RESET */
function resetFilters(){
  datePicker.value="";
  driverFilter.value="";
  fromFilter.value="";
  toFilter.value="";

  renderActiveFilters();
  fetchTrips(true);
}
/* GROUP (same design) */
function group(data){

  const today=new Date().toISOString().split("T")[0];
  const tomorrow=new Date(Date.now()+86400000).toISOString().split("T")[0];

  if(!datePicker.value){
    return {
      "اليوم":data.filter(t=>t.date===today),
      "غداً":data.filter(t=>t.date===tomorrow),
      "كل الرحلات":data
    };
  }

  return {
    "☀️ صباحاً":data.filter(t=>t.time >= "06:00" && t.time < "12:00"),
    "🌤️ ظهراً":data.filter(t=>t.time >= "12:00" && t.time < "18:00"),
    "🌙 مساءً":data.filter(t=>t.time >= "18:00")
  };
}

/* CARD (UNCHANGED UI) */
function card(t){

  const isOpen = expandedCard === t.id;
  const hasDriver = t.driver !== null;
  const isScheduled = t.is_scheduled;

  const time = isScheduled
    ? (t.scheduled_time || (t.startAt ? t.startAt.split(" ")[1]?.slice(0,5) : "-"))
    : (t.startAt ? t.startAt.split(" ")[1]?.slice(0,5) : "-");

  const statusMap = {
    scheduled:{text:"مجدولة",icon:"calendar",color:"from-indigo-500/10 to-indigo-100 text-indigo-700"},
    completed:{text:"مكتملة",icon:"check-circle",color:"from-green-500/10 to-green-100 text-green-700"},
    cancelled:{text:"ملغاة",icon:"x-circle",color:"from-red-500/10 to-red-100 text-red-700"},
    in_progress:{text:"جاري",icon:"car",color:"from-yellow-500/10 to-yellow-100 text-yellow-700"}
  };



  const s = statusMap[t.status] || statusMap.scheduled;

   return `
  <div class="card min-w-[340px] p-0 overflow-hidden border shadow-xl hover:shadow-2xl transition-all duration-300">

    <!-- HEADER -->
    <div onclick="toggleCard(${t.id})"
      class="p-5 flex justify-between items-center cursor-pointer bg-gradient-to-r ${s.color}">

      <div class="flex items-center gap-2 text-base font-bold">
        <i data-lucide="hash" class="w-5 h-5"></i>
        #${t.id}
      </div>

      <div class="flex items-center gap-2 text-sm">
        <i data-lucide="${s.icon}" class="w-5 h-5"></i>
        ${s.text}
        <i data-lucide="chevron-down" class="w-5 h-5 ${isOpen ? 'rotate-180' : ''} transition"></i>
      </div>

    </div>

    <!-- BASIC -->
    <div class="p-5 space-y-4 bg-white">

      <div class="flex items-center gap-3 text-base font-semibold">
        <i data-lucide="map-pin" class="w-5 h-5 text-indigo-500"></i>
        ${t.startAddress || "-"}
        <i data-lucide="arrow-left" class="w-5 h-5 text-gray-400"></i>
        ${t.endAddress || "-"}
      </div>

      <div class="flex items-center gap-2 text-sm text-gray-600">
        <i data-lucide="clock" class="w-5 h-5"></i>
        ${time}
      </div>

      <div class="flex justify-between items-center">

        <div class="text-2xl font-extrabold text-orange-500">
          $${t.totalAmount || 0}
        </div>

            ${hasDriver ? `
        <div class="flex items-center gap-2 text-sm text-gray-600">
        <img src="${t.driverImage || 'https://i.pravatar.cc/50'}"
            class="w-6 h-6 rounded-full">
        ${t.driver}
        </div>
        ` : `
        <span class="text-sm text-red-500 font-medium">غير مسند</span>
        `}

      </div>

    </div>

    <!-- EXPANDED -->
    ${isOpen ? `
    <div class="border-t p-5 bg-gray-50 space-y-5">

      <!-- DRIVER -->
      ${hasDriver ? `
      <div class="flex items-center gap-3">

        <img src="${t.driverImage || 'https://i.pravatar.cc/100'}"
          class="w-14 h-14 rounded-full border object-cover">

        <div class="flex-1">
          <div class="font-semibold">${t.driver}</div>
          <div class="text-sm text-gray-500">
            ${t.carName || '-'} • ${t.carPlate || '-'}
          </div>
          <div class="text-sm flex items-center gap-2 text-gray-600">
            <i data-lucide="phone" class="w-4 h-4"></i>
            ${t.driverPhone || "-"}
          </div>
        </div>

      </div>
      ` : `
      <button onclick="openAssignModal(${t.id})"
        class="w-full bg-indigo-600 text-white py-3 rounded-xl flex items-center justify-center gap-2">
        <i data-lucide="user-plus" class="w-5 h-5"></i>
        إسناد لسائق
      </button>
      `}

      <!-- CUSTOMER -->
      <div class="bg-white p-4 rounded-xl border flex items-center gap-3">

        <img src="${t.customerImage || 'https://i.pravatar.cc/120?img=12'}"
          class="w-12 h-12 rounded-full border object-cover">

        <div class="flex-1">
          <div class="font-semibold flex items-center gap-2">
            <i data-lucide="user" class="w-4 h-4 text-indigo-500"></i>
            ${t.customerName || "-"}
          </div>

          <div class="text-sm text-gray-600 flex items-center gap-2 mt-1">
            <i data-lucide="phone" class="w-4 h-4"></i>
            ${t.customerPhone || "-"}
          </div>
        </div>

      </div>

      <!-- EXTRA -->
      <div class="grid grid-cols-2 gap-3 text-sm">

        <div class="bg-white p-3 rounded-xl border flex items-center gap-2">
          <i data-lucide="credit-card" class="w-4 h-4"></i>
          ${t.paymentType || "-"}
        </div>

        <div class="bg-white p-3 rounded-xl border">
          ${t.paymentStatus || "pending"}
        </div>

        <div class="bg-white p-3 rounded-xl border flex items-center gap-2">
          <i data-lucide="route" class="w-4 h-4"></i>
          ${t.distance || 0} km
        </div>

        ${t.rating ? `
        <div class="bg-white p-3 rounded-xl border flex items-center gap-2 text-yellow-500">
          <i data-lucide="star" class="w-4 h-4"></i>
          ${t.rating}
        </div>` : ""}

      </div>

            <!-- ACTIONS -->
        <div class="flex gap-2 flex-wrap">

        ${t.status === "scheduled" && hasDriver ? `
        <button onclick="openAssignModal(${t.id})"
            class="flex-1 bg-indigo-600 text-white py-2 rounded-xl flex items-center justify-center gap-2">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            تغيير السائق
        </button>
        ` : ""}

        ${(t.status === "scheduled" || t.status === "in_progress") ? `
            <button onclick="openCancelModal(${t.id})"
            class="flex-1 bg-red-500 text-white py-2 rounded-xl flex items-center justify-center gap-2">
            <i data-lucide="x-circle" class="w-4 h-4"></i>
            إلغاء الرحلة
            </button>
        ` : ""}

        ${t.status === "completed" ? `
            <button onclick="viewTrip(${t.id})"
            class="flex-1 bg-green-600 text-white py-2 rounded-xl flex items-center justify-center gap-2">
            <i data-lucide="eye" class="w-4 h-4"></i>
            عرض التفاصيل
            </button>
        ` : ""}

        </div>

    </div>
    ` : ""}


  </div>`;
}



let selectedDriver = null;
let assigning = false;
let selectedTripId = null;

function openAssignModal(id){
  selectedTripId = id;
  document.getElementById('driverModal').classList.remove('hidden');
  loadDrivers();
}


async function confirmAssignDriver(){

  if(assigning) return;

  assigning = true;

  document.getElementById("assignLoader").classList.remove("hidden");

  const res = await fetch(`/api/trips/assign-driver`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      trip_id: selectedTripId,
      driver_id: selectedDriver.id
    })
  });

  const json = await res.json();

  assigning = false;

  document.getElementById("assignLoader").classList.add("hidden");

  if(json.statusCode === 200){

    updateTripInUI(json.data);

    closeConfirmDriverModal();
    closeDriverModal();
  }
}

function closeConfirmDriverModal(){
  document.getElementById("confirmDriverModal").classList.add("hidden");
}
const colorMap = {
  date: "bg-indigo-100 text-indigo-700",
  driver: "bg-yellow-100 text-yellow-700",
  from: "bg-blue-100 text-blue-700",
  to: "bg-green-100 text-green-700"
};


async function loadDrivers(search=''){

  const res = await fetch(`/api/drivers?search=${search}`);
  const json = await res.json();

  const container = document.getElementById('driverList');

  container.innerHTML = json.data.map(d => `
    <div onclick="selectDriver(${d.id}, '${d.firstName} ${d.lastName}', '${d.photo || ''}')"
      class="flex items-center gap-3 p-2 border rounded hover:bg-gray-100 cursor-pointer">

      <img src="${d.photo || 'https://i.pravatar.cc/80'}"
        class="w-10 h-10 rounded-full object-cover border">

      <div>
        <div class="font-semibold">${d.firstName} ${d.lastName}</div>
        <div class="text-xs text-gray-500">${d.phoneNumber || ''}</div>
      </div>

    </div>
  `).join('');
}




function selectDriver(id, name, image){
  selectedDriver = { id, name, image };

  document.getElementById("selectedDriverName").innerText = name;
  document.getElementById("selectedDriverImage").src = image || 'https://i.pravatar.cc/100';

  document.getElementById("confirmDriverModal").classList.remove("hidden");
}


async function assignDriver(driverId, driverName, driverImage){

  const res = await fetch(`/api/trips/assign-driver`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
    },
    body: JSON.stringify({
      trip_id: selectedTripId,
      driver_id: driverId
    })
  });

  const json = await res.json();

  if(json.statusCode === 200){

    updateTripInUI(json.data);

    closeDriverModal();
  }
}

function updateTripInUI(updatedTrip){

  const index = trips.findIndex(t => t.id === updatedTrip.id);

  if(index !== -1){
    trips[index] = normalizeTrip(updatedTrip);
    syncUI();
  }
}


function viewTrip(id){
  window.location.href = `/booking-details?trip_id=${id}`;
}







function closeCancelModal(){
  document.getElementById("cancelModal").classList.add("hidden");
}

let cancelling = false;

async function confirmCancelTrip(){

  if(cancelling) return;
  cancelling = true;

  const id = document.getElementById("cancelTripId").value;

  const res = await fetch(`/api/trips/cancel`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "Accept": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ trip_id: id })
  });

  const json = await res.json();

  cancelling = false;

  if(json.statusCode === 200){
    updateTripInUI(json.data);
    closeCancelModal();
  }
}

function openCancelModal(id){
  document.getElementById("cancelTripId").value = id;
  document.getElementById("cancelModal").classList.remove("hidden");
  lucide.createIcons();
}





async function loadFilters(){

  // cities
  const citiesRes = await fetch('/api/cities');
  const cities = await citiesRes.json();

  fromFilter.innerHTML += cities.map(c =>
    `<option value="${c.id}">${c.name}</option>`
  ).join('');

  toFilter.innerHTML += cities.map(c =>
    `<option value="${c.id}">${c.name}</option>`
  ).join('');

  // drivers
  const driversRes = await fetch('/api/drivers-list');
  const drivers = await driversRes.json();

  driverFilter.innerHTML += drivers.map(d =>
    `<option value="${d.id}">${d.firstName} ${d.lastName}</option>`
  ).join('');
}

new TomSelect("#driverFilter",{
  valueField: "id",
  labelField: "name",
  searchField: "name",

  load: function(query, callback){
    fetch(`/api/drivers?search=${query}`)
      .then(res => res.json())
      .then(json => {
        callback(json.data.map(d => ({
          id: d.id,
          name: d.firstName + " " + d.lastName
        })));
      }).catch(()=>callback());
  },

  maxOptions: 10,
  preload: true
});


/* RENDER */
function render(){
  const grouped = group(trips);

  container.innerHTML = Object.keys(grouped).map(sec=>`
    <div class="section ${getSectionClass(sec)}">
      <div class="font-bold mb-3">${sec}</div>

      <div class="scroll-x">
        ${grouped[sec].length ? grouped[sec].map(card).join("") : "لا يوجد بيانات"}
      </div>
    </div>
  `).join("");

  lucide.createIcons();
}

/* STATS */
function renderStats(){

  const s = trips.filter(t=>t.status==="scheduled").length;
  const c = trips.filter(t=>t.status==="completed").length;
  const x = trips.filter(t=>t.status==="cancelled").length;
  const p = trips.filter(t=>t.status==="in_progress").length;

  totalTrips.innerText = trips.length;
  scheduledTrips.innerText = s;
  completedTrips.innerText = c;
  cancelledTrips.innerText = x;
  progressTrips.innerText = p;

  const revenue = trips.reduce((a,b)=>a+(b.totalAmount||0),0);
  document.getElementById("revenue").innerText="$"+revenue;

  successRate.innerText = trips.length ? Math.round((c/trips.length)*100)+"%" : "0%";
}
function getSectionClass(name){

  if(name === "اليوم") return "section-today";
  if(name === "غداً") return "section-tomorrow";
  if(name === "كل الرحلات") return "section-all";

  if(name.includes("صباح")) return "section-morning";
  if(name.includes("ظهراً")) return "section-noon";
  if(name.includes("مساء")) return "section-night";

  return "";
}

function closeDriverModal(){
  document.getElementById('driverModal').classList.add('hidden');
}

/* SYNC */
function syncUI(){
  render();
  renderStats();
}

/* SCROLL PAGINATION */
window.addEventListener("scroll",()=>{
  if(window.innerHeight + window.scrollY >= document.body.offsetHeight - 100){
    if(currentPage < lastPage){
      currentPage++;
      fetchTrips();
    }
  }
});

/* FILTER CHANGE */
[datePicker,driverFilter,fromFilter,toFilter].forEach(el=>{
  el.addEventListener("change",()=>{
    fetchTrips(true);
    renderActiveFilters();
  });
});

document.addEventListener("DOMContentLoaded", () => {

  document.getElementById('driverSearch').addEventListener('input', function(){
    loadDrivers(this.value);
  });

  loadFilters();


});

/* INIT */
fetchTrips(true);

</script>

</body>
</html>
