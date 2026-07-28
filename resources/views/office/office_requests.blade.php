<x-master-layout>
<div class="dashboard">

  <!-- HEADER -->
  <div class="header">
    <h1>{{ __('messages.office_requests_title') }}</h1>
    <p>{{ __('messages.office_requests_subtitle') }}</p>
  </div>

  <!-- TABS -->
  <div class="tabs">
    <button class="tab active" onclick="switchTab('new')">
      {{ __('messages.new') }} <span>{{ $new->count() }}</span>
    </button>
    <button class="tab" onclick="switchTab('reviewed')">
      {{ __('messages.reviewed') }} <span>{{ $reviewed->count() }}</span>
    </button>
  </div>

  <!-- NEW -->
  <div id="tab-new" class="tab-content active">
    @foreach($new as $item)
    <div class="card new" onclick="toggleCard(this)">
      <div class="card-header">
        <div>
          <h3>{{ $item->office_name }}</h3>
          <p>{{ $item->city }} · {{ $item->country }}</p>
        </div>
        <span class="badge new">{{ __('messages.new') }}</span>
      </div>
      <div class="card-preview">
        <span>{{ $item->service_type }}</span>
        <span>{{ $item->fleet_size }} {{ __('messages.cars') }}</span>
        <span>{{ $item->timeline }}</span>
      </div>
      <div class="card-body">
        <div class="info-grid">
          <div><strong>{{ __('messages.contact') }}</strong><span>{{ $item->contact_name }}</span></div>
          <div><strong>{{ __('messages.email') }}</strong><span>{{ $item->email }}</span></div>
          <div><strong>{{ __('messages.phone') }}</strong><span>{{ $item->phone }}</span></div>
          <div><strong>{{ __('messages.license') }}</strong><span>{{ $item->license_status }}</span></div>
          <div><strong>{{ __('messages.notes') }}</strong><span>{{ $item->notes }}</span></div>
        </div>
        <button onclick="markReviewed(event, {{ $item->id }})">
          {{ __('messages.mark_as_reviewed') }}
        </button>
      </div>
    </div>
    @endforeach
  </div>

  <!-- REVIEWED -->
  <div id="tab-reviewed" class="tab-content">
    @foreach($reviewed as $item)
    <div class="card reviewed" onclick="toggleCard(this)">
      <div class="card-header">
        <div>
          <h3>{{ $item->office_name }}</h3>
          <p>{{ $item->city }} · {{ $item->country }}</p>
        </div>
        <span class="badge reviewed">{{ __('messages.reviewed') }}</span>
      </div>
      <div class="card-preview">
        <span>{{ $item->service_type }}</span>
        <span>{{ $item->fleet_size }} {{ __('messages.cars') }}</span>
        <span>{{ $item->timeline }}</span>
      </div>
      <div class="card-body">
        <div class="info-grid">
          <div><strong>{{ __('messages.contact') }}</strong><span>{{ $item->contact_name }}</span></div>
          <div><strong>{{ __('messages.email') }}</strong><span>{{ $item->email }}</span></div>
          <div><strong>{{ __('messages.phone') }}</strong><span>{{ $item->phone }}</span></div>
          <div><strong>{{ __('messages.license') }}</strong><span>{{ $item->license_status }}</span></div>
          <div><strong>{{ __('messages.notes') }}</strong><span>{{ $item->notes }}</span></div>
        </div>
      </div>
    </div>
    @endforeach
  </div>

</div>



<!-- TOAST -->
<div id="toast"></div>



<script>
/* فتح وغلق الكارد */
function toggleCard(card){
  card.classList.toggle('open');
}

/* تبديل التبويبات */
function switchTab(tab){
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

  document.querySelector(`[onclick="switchTab('${tab}')"]`).classList.add('active');
  document.getElementById('tab-'+tab).classList.add('active');
}

/* تحديث حالة الطلب */
function markReviewed(e, id){
  e.stopPropagation();
  fetch(`/admin/office-requests/${id}/status`, {
    method: "PATCH",
    headers:{
      'X-CSRF-TOKEN': "{{ csrf_token() }}"
    }
  })
  .then(res => res.json())
  .then(()=>{
      showToast("{{ __('messages.status_updated') }}");
      setTimeout(()=>location.reload(), 700);
  })
  .catch(()=>{
      showToast("{{ __('messages.status_error') }}");
  });
}

/* TOAST */
function showToast(msg){
  let t = document.getElementById('toast');
  t.innerText = msg;
  t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),3000);
}
</script>
<!-- CSS -->
<style>
body{
  background: #f4f5f7;
  font-family:'Plus Jakarta Sans',sans-serif;
}

.dashboard{
  max-width:1100px;
  margin:auto;
  padding:40px 20px;
}

.header h1{
  font-size:2rem;
  font-weight:800;
  color:#312873;
}

.header p{
  color:#4b5563;
}

/* Tabs */
.tabs{
  display:flex;
  gap:10px;
  margin:25px 0;
}

.tab{
  background: #31287372;
  border:none;
  padding:10px 18px;
  border-radius:12px;
  font-weight:700;
  cursor:pointer;
  display:flex;
  gap:8px;
  align-items:center;
  box-shadow:0 5px 15px rgba(0,0,0,0.05);
  transition:.3s;
}

.tab span{
  background:#F29C0B;
  padding:2px 8px;
  border-radius:8px;
  font-size:.7rem;
}

.tab.active{
  background: linear-gradient(135deg,#312873,#4c3bb3);
  color:#fff;
}

.tab.active span{
  background:#F29C0B;
  color:#fff;
}

.tab-content{display:none;}
.tab-content.active{display:block;}

/* Cards */
.card{
  border-radius:18px;
  padding:18px;
  margin-bottom:14px;
  cursor:pointer;
  transition:.3s;
  position:relative;
  overflow:hidden;
  backdrop-filter: blur(10px);
}

/* Hover effect */
.card:hover{
  transform:translateY(-3px);
  box-shadow:0 12px 25px rgba(0,0,0,0.08);
}

/* NEW Card */
.card.new{
  background: #fff7e6;
  border:1px solid #f2b50b;

}

.card.new .card-header h3,
.card.new .info-grid span{
  color:#1f1f1f;
}

.card.new .card-header p,
.card.new .info-grid strong{
  color:#4b5563;
}

.card.new .card-preview span{
  background:#fef3c7;
  color:#78350f;

}

/* REVIEWED Card */
.card.reviewed{
  background: rgba(49, 40, 115, 0.792);
  border:1px solid rgba(76,59,179,0.6);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
  backdrop-filter: blur(8px);
}

.card.reviewed .card-header h3{
  color:#ffffff;
}

.card.reviewed .card-header p{
  color:#d1d5db;
}

.card.reviewed .info-grid span{
  color:#f3f4f6;
}

.card.reviewed .info-grid strong{
  color:#e5e7eb;
}

.card.reviewed .card-preview span{
  background: rgba(76,59,179,0.7);
  color:#fff;
  font-weight:600;
}

.badge.reviewed{
  background:#22c55e3d;
  color:#fff;
  font-weight:800;
  height: 20px;
  border:1px solid rgba(8, 99, 42, 0.771);
}

.card.reviewed:hover{
  transform: translateY(-3px);
  box-shadow:0 12px 30px rgba(0,0,0,0.2);
}

/* Card Header */
.card-header{
  display:flex;
  justify-content:space-between;
}

.card-header h3{
  font-size:1.2rem;
  font-weight:800;
}

.card-header p{
  font-size:.8rem;
}

/* Badges */
.badge{
  font-size:.65rem;
  font-weight:800;
  padding:6px 10px;
  border-radius:8px;
}

.badge.new{
  background:#f2b50b;
  color:#fff;
}

.badge.reviewed{
  background:#16a34aa4;
  color:#fff;
}

/* Card Preview */
.card-preview{
  display:flex;
  gap:10px;
  margin-top:12px;
}

.card-preview span{
  font-size:.75rem;
  padding:5px 10px;
  border-radius:8px;
  font-weight:600;
}

/* Card Body */
.card-body{
  max-height:0;
  overflow:hidden;
  opacity:0;
  transform: translateY(-10px);
  transition: all .35s ease;
}

.card.open .card-body{
  max-height:600px;
  margin-top:15px;
  opacity:1;
  transform: translateY(0);
}

/* Info Grid */
.info-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:12px;
}

.info-grid div{
  border-radius:10px;
  padding:10px;
  background: rgba(188, 149, 6, 0.195);
}

.info-grid strong{
  display:block;
  font-size:.7rem;
}

/* Buttons */
button{
  margin-top:15px;
  width:100%;
  padding:12px;
  border:none;
  border-radius:12px;
  background:linear-gradient(135deg,#16a34a,#22c55e);
  color:#fbf9ffdc;
  font-weight:800;
  cursor:pointer;
  transition:.3s;
}

button:hover{
  transform: translateY(-2px);
  box-shadow:0 10px 25px rgba(34,197,94,0.25);
}

/* TOAST */
#toast{
  position:fixed;
  bottom:30px;
  right:30px;
  background:#312873;
  color:#fff;
  padding:14px 20px;
  border-radius:10px;
  opacity:0;
  transition:.3s;
  font-weight:700;
  z-index:1000;
}
#toast.show{opacity:1;}
</style>


</x-master-layout>
