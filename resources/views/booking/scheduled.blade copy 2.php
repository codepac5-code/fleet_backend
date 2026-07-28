<x-master-layout>

<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Smart Trips Dashboard</title>

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
*{margin:0;padding:0;box-sizing:border-box;font-family:Inter,system-ui}
body{
  background: radial-gradient(circle at 10% 20%, #eef2ff, transparent 40%),
  radial-gradient(circle at 90% 80%, #f5f3ff, transparent 40%),
  linear-gradient(135deg,#f8fafc,#eef2ff);
  color:#0f172a;
}
.container{padding:20px}
.header{margin:20px;padding:16px 20px;border-radius:18px;background:rgba(255,255,255,0.7);backdrop-filter:blur(18px);border:1px solid rgba(255,255,255,0.4);box-shadow:0 10px 30px rgba(0,0,0,0.08);display:flex;justify-content:space-between;align-items:center}
.btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;padding:10px 18px;border-radius:14px;font-weight:600;cursor:pointer}
.tabs{display:flex;justify-content:center;gap:10px;margin:10px 20px}
.tab{padding:8px 16px;border-radius:999px;font-weight:600;cursor:pointer;background:rgba(255,255,255,0.6)}
.tab.active{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff}
.filter-box{margin:20px;padding:20px;border-radius:20px;background:rgba(255,255,255,0.7)}
.filter-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:10px}
.filter-input{padding:10px;border-radius:12px;border:1px solid #e2e8f0}
.stats{margin:20px;display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.stat{padding:16px;border-radius:18px;background:rgba(255,255,255,0.7)}
.stat-value{font-size:26px;font-weight:700}
.section{margin:20px;padding:20px;border-radius:20px;background:rgba(255,255,255,0.6)}
.scroll-x{display:flex;gap:16px;overflow-x:auto}
.card{min-width:340px;border-radius:20px;background:#fff;box-shadow:0 10px 30px rgba(0,0,0,0.08)}
.chip{background:rgba(99,102,241,0.1);padding:6px 12px;border-radius:999px}
</style>
</head>

<body>

<div class="topbar"></div>

<div class="header">
  <h3>لوحة الرحلات</h3>
  <button>+ رحلة</button>
</div>

<!-- TABS -->
<div class="tabs">
  <div class="tab active" onclick="setTab('scheduled',this)">مجدولة</div>
  <div class="tab" onclick="setTab('in_progress',this)">جاري</div>
  <div class="tab" onclick="setTab('completed',this)">مكتملة</div>
  <div class="tab" onclick="setTab('cancelled',this)">ملغاة</div>
</div>

<!-- FILTERS -->
<div class="filters">
  <div class="grid">
    <input type="date" id="datePicker">
    <select id="driverFilter"></select>
    <select id="fromFilter"></select>
    <select id="toFilter"></select>
    <button onclick="resetFilters()">Reset</button>
  </div>
</div>

<!-- STATS -->
<div class="stats">
  <div class="stat">Trips <div id="totalTrips">0</div></div>
  <div class="stat">Revenue <div id="revenue">0</div></div>
  <div class="stat">Distance <div id="distance">0</div></div>
</div>

<!-- CONTENT -->
<div id="container"></div>

<!-- MODAL -->
<div id="modal" class="modal hidden">
  <div class="modal-box">
    <div id="modalContent"></div>
    <button onclick="closeModal()">close</button>
  </div>
</div>

<script>

let trips = [];
let expanded = null;
let tab = "scheduled";

const container = document.getElementById("container");
const datePicker = document.getElementById("datePicker");

/* FETCH */
async function fetchTrips(){

  const res = await fetch("/scheduled-ride-data");
  const json = await res.json();

  trips = json.data.map(normalize);

  render();
  stats(json);
}

/* NORMALIZE */
function normalize(t){
  return {
    id:t.id,
    status:t.status,
    date:(t.startAt||"").split(" ")[0],
    time:(t.startAt||"").split(" ")[1]?.slice(0,5) || "-",
    from:t.startAddress,
    to:t.endAddress,
    amount:t.totalAmount,
    distance:t.distance,
    driver:t.driver
  };
}

/* GROUP */
function group(data){

  const today = new Date().toISOString().split("T")[0];

  if(datePicker.value){

    return {
      "صباح": data.filter(t=>t.time < "12:00"),
      "ظهر": data.filter(t=>t.time >= "12:00" && t.time < "18:00"),
      "مساء": data.filter(t=>t.time >= "18:00")
    };
  }

  return {
    "اليوم": data.filter(t=>t.date===today),
    "كل الرحلات": data
  };
}

/* CARD */
function card(t){
  return `
  <div class="card">

    <div class="card-header" onclick="toggle(${t.id})">
      <div>#${t.id}</div>
      <div>${t.status}</div>
    </div>

    ${expanded===t.id ? `
    <div class="card-body">
      <div>${t.from} → ${t.to}</div>
      <div>${t.time}</div>
      <div>${t.amount}</div>
      <div>${t.distance}</div>
    </div>
    `:''}

  </div>`;
}

/* RENDER */
function render(){

  const grouped = group(trips);

  container.innerHTML = Object.keys(grouped).map(k=>`
    <div>
      <div class="section-title">${k}</div>
      ${grouped[k].map(card).join("")}
    </div>
  `).join("");
}

/* STATS */
function stats(json){
  document.getElementById("totalTrips").innerText = json.total_trips || 0;
  document.getElementById("revenue").innerText = json.total_revenue || 0;
  document.getElementById("distance").innerText = json.total_distance || 0;
}

/* TOGGLE */
function toggle(id){
  expanded = expanded===id ? null : id;
  render();
}

/* TAB */
function setTab(t,el){
  tab=t;
  document.querySelectorAll(".tab").forEach(x=>x.classList.remove("active"));
  el.classList.add("active");
  fetchTrips();
}

/* RESET */
function resetFilters(){
  datePicker.value="";
  fetchTrips();
}

/* INIT */
fetchTrips();

</script>

</body>
</x-master-layout>
