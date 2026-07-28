<x-master-layout>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">


  <div class="container-fluid py-5">
    <div class="row gx-5">
      <!-- Left: Driver Info + Vehicle Info + Wallet + Dues -->
      <div class="col-lg-4 mb-5 d-flex flex-column">

        <div id="imageModal"
          style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.7);
                justify-content:center; align-items:center; z-index:1050; cursor:pointer;">
          <img id="modalImg" src="" alt="Image Preview"
            style="max-width:90vw; max-height:90vh; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.5);" />
        </div>

<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">

<style>

.card-body {
    font-family: 'Cairo', sans-serif !important;
  }



  .card-body h3 {
    font-size: 2.4rem !important;
    font-weight: 700 !important;
  }
  .card-body h4 {
    font-size: 1.8rem !important;
  }
  .card-body h6 {
    font-size: 1.1rem !important;
    font-weight: 600 !important;
  }
</style>

<div class="card shadow-sm border-0 rounded-4 p-4 mb-4 flex-grow-0"
  style="background-color: #312873; transition: box-shadow 0.3s ease; color: #eee;">

  <div class="card-body text-center d-flex flex-column align-items-center" style="color: #eee; font-family: 'Cairo', sans-serif;">



    <div class="position-relative d-inline-block mb-2" style="cursor: pointer;">
      <img id="avatarImg" src="{{ asset($customerdata->photo) }}" alt="Avatar" class="rounded-circle"
        style="width: 140px; height: 140px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: transform 0.3s ease;">


          <h4 class="text-warning d-flex justify-content-center align-items-center gap-1" style="font-size: 0.4rem; margin: 0;">
            <i class="fas fa-star" style="color: #F8A609; font-size: 1.2rem;"></i> {{number_format($customerdata->rating,1 )  }}
          </h4>

      <a href="tel:{{ $customerdata->phoneNumber }}"
        class="position-absolute top-60 start-50 translate-middle rounded-circle bg-primary text-white p-3 fs-5 shadow"
        style="opacity: 0; transition: opacity 0.3s ease;" id="call-icon" title="اتصل">
        <i class="fas fa-phone"></i>
      </a>
    </div>

    <h3 class="fw-semibold mb-2" style="color: #f0f0f0;">
      {{ $customerdata->firstName }} {{ $customerdata->lastName }}
    </h3>

    <p class="text-light mb-2 fs-6 d-flex justify-content-center align-items-center gap-2 flex-wrap">
      <i class="fas fa-phone-alt text-info fs-5"></i>
      {{ $customerdata->phoneNumber }}
    </p>

    @if(!empty($customerdata->address))
      <p class="text-light fs-6 d-flex justify-content-center align-items-center gap-2 flex-wrap">
        <i class="fas fa-map-marker-alt text-danger fs-5"></i>
        {{ $customerdata->address }}
      </p>
    @endif



    <hr class="my-4 w-100" style="border-color: rgba(255,255,255,0.2);">

    <div class="d-flex justify-content-between text-center mt-3 px-3 flex-wrap gap-3" style="max-width: 100%;">

      <div style="min-width: 150px; flex-grow: 1; border-left: 1px solid rgba(255,255,255,0.3); padding-left: 1rem;">
        <h6 class="text-light fs-6 mb-1 d-flex justify-content-center align-items-center gap-2">
          <span> {{ __('messages.ride_count') }} </span>
        </h6>
        <i class="fas fa-car icon-spin" style="color: #F8A609; font-size: 1.8rem;"></i>
        <h4 class="fw-bold" id="totalCount" style="font-size: 1.6rem; color: #eee;">{{ $customerdata->rideCount }}</h4>
      </div>

      <div style="min-width: 150px; flex-grow: 1; border-left: 1px solid rgba(255,255,255,0.3); padding-left: 1rem;">
        <h6 class="text-light fs-6 mb-1 d-flex justify-content-center align-items-center gap-2">
          <span> {{ __('messages.km_count') }} </span>
        </h6>
        <i class="fas fa-tachometer-alt icon-spin" style="color: #F8A609; font-size: 1.4rem;"></i>
        <h4 class="fw-bold" id="totalKm" style="font-size: 1.6rem; color: #eee;">{{ $customerdata->kmCount }} km</h4>
      </div>
    </div>


    <div class="mt-4 d-flex justify-content-center gap-3 flex-wrap">
      <span class="badge rounded-pill bg-{{ $customerdata->isActive ? 'success' : 'danger' }} fs-6 py-2 px-4 d-flex align-items-center gap-2">
        <i class="fas fa-user-check"></i>
        {{ $customerdata->isActive ? __('messages.active') : __('messages.inactive') }}
      </span>
      <span class="badge rounded-pill bg-{{ $customerdata->is_online ? 'primary' : 'secondary' }} fs-6 py-2 px-4 d-flex align-items-center gap-2">
        <i class="fas fa-circle" style="font-size: 0.8rem; color: {{ $customerdata->is_online ? '#0d6efd' : '#6c757d' }};"></i>
        {{ $customerdata->is_online ? __('messages.online') : __('messages.offline') }}
      </span>


    </div>
  </div>
</div>





















        <script>
          const card = document.querySelector('.card');
          const callIcon = document.getElementById('call-icon');
          const avatar = document.getElementById('avatarImg');

          card.addEventListener('mouseover', () => {
            callIcon.style.opacity = '1';
            avatar.style.transform = 'scale(1.05)';
          });
          card.addEventListener('mouseout', () => {
            callIcon.style.opacity = '0';
            avatar.style.transform = 'scale(1)';
          });

          const imageModal = document.getElementById('imageModal');
          const modalImg = document.getElementById('modalImg');

          avatar.addEventListener('click', () => {
            modalImg.src = avatar.src;
            imageModal.style.display = 'flex';
          });

          imageModal.addEventListener('click', () => {
            imageModal.style.display = 'none';
          });
        </script>

        <div class="card shadow-lg border-0 rounded-4 p-4">
          <div class="card-body">

            <h3 class="fw-bold text-center text-primary mb-4" style="font-size: 1.8rem;">
              <i class="fas fa-car-side me-2" style="font-size: 1.6rem;"></i>
              {{ __('messages.vehicle_info') }}
            </h3>

            @if(!empty($car->photo))
              <div class="text-center mb-4">
                <img src="{{asset($car->photo)  }}" alt="Vehicle Photo"
                  onclick="showImageModal(this.src)"
                  style="width: 100%; max-height: 240px; object-fit: cover; border-radius: 12px; cursor: pointer;" />
              </div>
            @endif

            <div class="mb-4">
              <h5 class="text-secondary fw-semibold mb-3">
                <i class="fas fa-info-circle me-2" style="font-size: 1.2rem;"></i>
                {{ __('messages.general_info') }}
              </h5>
              <div class="row g-3 fs-6">
                <div class="col-md-6">
                  <strong><i class="fas fa-warehouse me-2 text-primary" style="font-size: 1.1rem;"></i> {{ __('messages.vehicle_brand') }}:</strong>
                  {{ $car->vehicleBrand ?? '-' }}
                </div>
                <div class="col-md-6">
                  <strong><i class="fas fa-layer-group me-2 text-primary" style="font-size: 1.1rem;"></i> {{ __('messages.model') }}:</strong>
                  {{ $car->model ?? '-' }}
                </div>
                <div class="col-md-6">
                  <strong><i class="fas fa-palette me-2 text-primary" style="font-size: 1.1rem;"></i> {{ __('messages.color') }}:</strong>
                  {{ ucfirst($car->color) ?? '-' }}
                </div>
                <div class="col-md-6">
                  <strong><i class="fas fa-calendar-alt me-2 text-primary" style="font-size: 1.1rem;"></i> {{ __('messages.model_year') }}:</strong>
                  {{ $car->modelYear ?? '-' }}
                </div>
              </div>
            </div>

            <div class="mb-4">
              <h5 class="text-secondary fw-semibold mb-3">
                <i class="fas fa-id-badge me-2" style="font-size: 1.2rem;"></i>
                {{ __('messages.license_info') }}
              </h5>
              <div class="row g-3 fs-6">
                <div class="col-md-6">
                  <strong><i class="fas fa-id-card me-2 text-primary" style="font-size: 1.1rem;"></i> {{ __('messages.plate') }}:</strong>
                  {{ $car->plate ?? '-' }}
                </div>
                <div class="col-md-6">
                  <strong><i class="fas fa-certificate me-2 text-primary" style="font-size: 1.1rem;"></i> {{ __('messages.license_number') }}:</strong>
                  {{ $car->licenseNumber ?? '-' }}
                </div>
                <div class="col-md-6">
                  <strong><i class="fas fa-map-marker-alt me-2 text-primary" style="font-size: 1.1rem;"></i> {{ __('messages.city') }}:</strong>
                  {{ $car->city ?? '-' }}
                </div>
              </div>
            </div>

            <div class="mb-4">
              <h5 class="text-secondary fw-semibold mb-3">
                <i class="fas fa-plus-circle me-2" style="font-size: 1.2rem;"></i>
                {{ __('messages.additional_info') }}
              </h5>
              <div class="row g-3 fs-6">
                <div class="col-md-6">
                  <strong><i class="fas fa-chair me-2 text-primary" style="font-size: 1.1rem;"></i> {{ __('messages.seats_count') }}:</strong>
                  {{ $car->seatsCount ?? '-' }}
                </div>
                <div class="col-12 mt-3">
                  <strong><i class="fas fa-align-left me-2 text-primary" style="font-size: 1.1rem;"></i> {{ __('messages.description') }}:</strong>
                  <p class="text-muted mt-2 mb-0">{{ $car->description ?? '-' }}</p>
                </div>
              </div>
            </div>

          </div>
        </div>





        <style>
          body {
            font-family: 'Cairo', sans-serif;
          }

          .custom-card-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-top: 2rem;
          }

          .wallet-card-enhanced,
          .dues-card-enhanced {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 2rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, #4ade80, #16a34a);
            color: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: 0.3s ease;
            min-height: 120px;
          }

          .dues-card-enhanced {
            background: linear-gradient(135deg, #f87171, #b91c1c);
          }

          .wallet-card-enhanced:hover,
          .dues-card-enhanced:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 35px rgba(0, 0, 0, 0.15);
          }

          .card-left {
            display: flex;
            align-items: center;
            gap: 1.2rem;
          }

          .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            animation: float 3s infinite ease-in-out;
          }

          @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
          }

          .card-text-group {
            display: flex;
            flex-direction: column;
            justify-content: center;
          }

          .card-title-enhanced {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
            color: #f8fafc;
          }

          .card-value-enhanced {
            font-size: 1.8rem;
            font-weight: 800;
            color: white;
          }

          .add-balance-enhanced {
            background-color: #ffffff;
            color: #16a34a;
            border: none;
            padding: 0.6rem 1.4rem;
            border-radius: 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
          }

          .add-balance-enhanced:hover {
            background-color: #16a34a;
            color: #fff;
            box-shadow: 0 8px 20px rgba(22, 163, 74, 0.4);
            transform: scale(1.05);
          }

          @media (max-width: 576px) {
            .wallet-card-enhanced,
            .dues-card-enhanced {
              flex-direction: column;
              align-items: flex-start;
            }

            .add-balance-enhanced {
              margin-top: 1rem;
            }

            .card-left {
              width: 100%;
            }
          }
        </style>

        <div class="custom-card-container">














































<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" />

<div id="wb-wallet-container" style="padding:20px; display:flex;justify-content:center;">
  <div class="wb-wallet-card">
    <div class="wb-wallet-header">
      <div class="wb-wallet-icon"><i class="fa-solid fa-wallet"></i></div>
      <div>
        <div class="wb-wallet-balance" id="wb-balanceDisplay" style="color: white;">{{ getPriceFormat($customerdata->balanceWallet) }}</div>
        <div class="wb-wallet-label">{{ __('messages.Wallet_Balance') }}</div>
      </div>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <button class="wb-btn-accent" id="wb-addFundsBtn"><i class="fa-solid fa-plus"></i> {{ __('messages.Add_Balance') }}</button>
      <button class="wb-btn-outline" id="wb-refreshBtn">
        <span id="wb-refreshText"><i class="fa-solid fa-rotate"></i> {{ __('messages.Refresh') }}</span>
        <span id="wb-refreshLoader" class="wb-loader-inline" style="display:none;"></span>
      </button>
    </div>
  </div>
</div>

<div class="wb-modal" id="wb-addModal">
  <div class="wb-modal-content">
    <div class="wb-modal-header">
      <h5><i class="fa-solid fa-coins"></i> {{ __('messages.Add_Balance') }}</h5>
      <span id="wb-closeModal" style="cursor:pointer;float:right;">&times;</span>
    </div>
    <label>{{ __('messages.Enter_Amount') }}</label>
    <input type="text" id="wb-amountInput" placeholder="مثال: 100">
    <div id="wb-messageBox" class="wb-message-box"></div>
    <div style="margin-top:10px;text-align:right;">
      <button class="wb-btn-outline" id="wb-cancelBtn">{{ __('messages.Cancel') }}</button>
      <button class="wb-btn-accent" id="wb-confirmAddBtn">
        <span id="wb-confirmText">{{ __('messages.Confirm') }}</span>
        <span id="wb-confirmLoader" style="display:none;" class="wb-loader-inline"></span>
      </button>
    </div>
  </div>
</div>

<style>
#wb-wallet-container{font-family:"Segoe UI",sans-serif;}
.wb-wallet-card{width:1000px;background:#312873;border-radius:14px;padding:20px;box-shadow:0 8px 30px rgba(0,0,0,0.5);border:1px solid rgba(255,255,255,0.08);}
.wb-wallet-header{display:flex;align-items:center;gap:14px;margin-bottom:18px;}
.wb-wallet-icon{width:60px;height:60px;border-radius:12px;display:flex;justify-content:center;align-items:center;font-size:26px;color:#FCB902;background:#26204d;border:1px solid rgba(255,255,255,0.06);}
.wb-wallet-balance{font-size:28px;font-weight:700;margin-bottom:2px;}
.wb-wallet-label{color:#ddd;font-size:13px;}
.wb-btn-accent{background:#FCB902;color:#000;font-weight:700;border:none;padding:8px 14px;border-radius:10px;font-size:14px;box-shadow:0 5px 14px rgba(252,185,2,0.25);cursor:pointer;}
.wb-btn-outline{border:1px solid rgba(255,255,255,0.15);background:transparent;color:#fff;padding:7px 12px;border-radius:10px;font-size:13px;cursor:pointer;position:relative;}
input{width:100%;padding:8px;border-radius:10px;border:1px solid #555;background:#26204d;color:#fff;}
.wb-modal{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:none;justify-content:center;align-items:center;z-index:9999;}
.wb-modal-content{background:#312873;padding:20px;border-radius:16px;max-width:400px;width:90%;}
.wb-modal-header h5{color:#FCB902;margin:0 0 10px 0;}
.wb-loader-inline{width:16px;height:16px;border-radius:50%;border:2px solid rgba(255,255,255,0.2);border-top-color:#fff;animation:spin .8s linear infinite;display:inline-block;}
@keyframes spin{to{transform:rotate(360deg)}}
.wb-message-box{margin-top:10px;padding:8px;border-radius:10px;font-weight:2000;font-size:13px;display:none;}
.wb-message-success{background:#28a745;color:#fff;}
.wb-message-error{background:#dc3545;color:#fff;}
</style>

<script>
const driverId = @json($customerdata->id);

const balanceDisplay = document.getElementById("wb-balanceDisplay");
const addFundsBtn = document.getElementById("wb-addFundsBtn");
const refreshBtn = document.getElementById("wb-refreshBtn");
const refreshText = document.getElementById("wb-refreshText");
const refreshLoader = document.getElementById("wb-refreshLoader");
const addModal = document.getElementById("wb-addModal");
const closeModal = document.getElementById("wb-closeModal");
const cancelBtn = document.getElementById("wb-cancelBtn");
const amountInput = document.getElementById("wb-amountInput");
const confirmAddBtn = document.getElementById("wb-confirmAddBtn");
const confirmText = document.getElementById("wb-confirmText");
const confirmLoader = document.getElementById("wb-confirmLoader");
const messageBox = document.getElementById("wb-messageBox");

function formatCurrency(v){
  if(Number.isInteger(v)) return Number(v).toLocaleString("en-US");
  return Number(v).toLocaleString("en-US",{minimumFractionDigits:2,maximumFractionDigits:2});
}

function updateUI(balance){ balanceDisplay.textContent = formatCurrency(balance); }

function showMessage(msg,type="success"){
  messageBox.textContent=msg;
  messageBox.className="wb-message-box "+(type==="success"?"wb-message-success":"wb-message-error");
  messageBox.style.display="block";
}
function hideMessage(){ messageBox.style.display="none"; }

async function fetchBalance(){
  try{
    refreshBtn.disabled = true;
    refreshText.style.display = "none";
    refreshLoader.style.display = "inline-block";

    const res = await fetch("{{ route('driver.get-wallet-balance') }}?driver_id=" + driverId);
    const data = await res.json();
    if(data.success) updateUI(data.balance);
  }catch(err){ console.error(err); }
  finally{
    refreshBtn.disabled = false;
    refreshText.style.display = "inline-block";
    refreshLoader.style.display = "none";
  }
}

addFundsBtn.onclick = ()=>{ amountInput.value=""; hideMessage(); addModal.style.display="flex"; }
closeModal.onclick = cancelBtn.onclick = ()=> addModal.style.display="none";
refreshBtn.onclick = fetchBalance;

confirmAddBtn.onclick = async()=>{
  let amount = parseFloat(amountInput.value.replace(/,/g,""));
  hideMessage();
  if(isNaN(amount)||amount<=0){ showMessage("أدخل مبلغ صحيح","error"); return; }
  confirmAddBtn.disabled=true; confirmText.style.display="none"; confirmLoader.style.display="inline-block";
  try{

    const res = await fetch("{{ route('driver.add-blance-to-wallet') }}", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}"
    },
    body: JSON.stringify({amount, driver_id: {{ $customerdata->id }} })
});

    const data = await res.json();
    if(data.success){
      updateUI(data.balance);
      showMessage(data.message,"success");
      amountInput.value="";
    } else showMessage(data.message,"error");
  }catch(e){ showMessage("{{ __('messages.error_occurred') }}","error"); }
  finally{ confirmAddBtn.disabled=false; confirmText.style.display="inline-block"; confirmLoader.style.display="none"; }
}

amountInput.addEventListener("input",()=>{
  let val = amountInput.value.replace(/,/g,"");
  if(val===""||isNaN(val)) return;
  let parts = val.split(".");
  let integerPart = parseInt(parts[0]).toLocaleString("en-US");
  let decimalPart = parts[1]? "."+parts[1] : "";
  amountInput.value = integerPart + decimalPart;
});

fetchBalance();
</script>











































































          {{-- <!-- Wallet Card -->
          <div class="wallet-card-enhanced">
            <div class="card-left">
              <div class="icon-circle">
                <i class="fas fa-wallet"></i>
              </div>
              <div class="card-text-group">
                <div class="card-title-enhanced">{{ __('messages.wallet_balance') }}</div>
                <div class="card-value-enhanced" id="walletBalance">999999999</div>
              </div>
            </div>
            <button class="add-balance-enhanced" data-bs-toggle="modal" data-bs-target="#addBalanceModal">
              <i class="fas fa-plus-circle me-2"></i> {{ __('messages.add_balance') }}
            </button>
          </div> --}}

          <!-- Dues Card -->
          @if ($isDriver)
          <div class="dues-card-enhanced">
            <div class="card-left">
              <div class="icon-circle">
                <i class="fas fa-money-bill-wave"></i>
              </div>
              <div class="card-text-group">
                <div class="card-title-enhanced">{{ __('messages.dues') }}</div>
                <div class="card-value-enhanced" id="dues">
                  {{$customerdata->officeDues + $customerdata->fleetDues}}
                </div>
              </div>
            </div>
          </div>
          @endif

        </div>







      </div>

      <!-- Right: Transactions -->
      <div class="col-lg-8">
        <div class="card mb-5 shadow-lg rounded-5">
          <div class="card-header d-flex justify-content-between align-items-center bg-white py-4 px-5 rounded-top-5">
            <h4 class="mb-0 fw-bold">{{ $pageTitle ?? 'تاريخ طلبات السائق' }}</h4>
            <a href="{{ route('driver.index') }}" class="btn btn-primary btn-lg d-flex align-items-center gap-2">
              <i class="fas fa-arrow-left fs-5"></i> {{ __('messages.back') }}
            </a>
          </div>


          <div class="card-body px-5 py-4">
            <div class="row mb-4">
              <div class="col-md-6">
                <label for="startDate" class="form-label fw-semibold">{{ __('messages.from_date') }}</label>
                <input type="date" id="startDate" class="form-control" />
              </div>
              <div class="col-md-6">
                <label for="endDate" class="form-label fw-semibold">{{ __('messages.to_date') }}</label>
                <input type="date" id="endDate" class="form-control" />
              </div>
            </div>
          </div>



          <div class="card-body p-10">
            {{-- <div class="provider-details-overview mb-30"> --}}

            <div class="provider-details-overview__statistics">

                <div class="statistics-card statistics-card__style2 statistics-card__withdrawable-amount">
                    <h2>{{getPriceFormat(0) ?? 0}}</h2>
                    <h2>{{__('messages.already_withdraw')}} <h2>

                </div>

                <div class="statistics-card statistics-card__style2 statistics-card__total-earning">
                    <h2>{{getPriceFormat($customerdata->walletBalance) ?? 0}}</h2>
                    <h2>{{__('messages.total_earning')}}</h2>
                </div>
            </div>



            <div class="col-12">
              <div class="horizontal-separator"></div>
          </div>

            <h4 class="card-title mb-4 fs-4 fw-semibold">{{ __('messages.earning') }}</h4>


<div class="d-flex justify-content-end mb-3">
  <div class="input-group ml-2" style="max-width: 300px;">
    <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
    <input type="text"
    class="form-control dt-search"
    placeholder="{{ __('messages.search_by_order_id') }}"
    aria-label="{{ __('messages.search_by_order_id') }}"
    aria-describedby="addon-wrapping">
</div>
</div>

            <div class="table-responsive">
              <table id="ordersTable" class="table table-hover align-middle mb-0 fs-5">
                <thead class="table-light fs-5">
                  <tr>
                    <th>{{ __('messages.order_number') }}</th>
                    <th>{{ __('messages.amount') }}</th>
                    <th>{{ __('messages.distance_km') }}</th>
                    <th>{{ __('messages.driver_earnings') }}</th>
                    <th>{{ __('messages.fleet_commission') }}</th>
                    <th>{{ __('messages.office_commission') }}</th>

                  </tr>
                </thead>
              </table>
            </div>

            <script>
              $(document).ready(function () {

                let identifier = @json($customerdata->id);

                const table = $('#ordersTable').DataTable({
                  dom: '<"row"<"col-12">>rt<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"p>>',
                    language: {
    processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>'
  },
                  processing: true,
                  serverSide: true,
                  responsive: true,
                  autoWidth: false,
                  ajax: {
                    url: "{{ route('driver.bookings.data') }}",
                    data: function (d) {
                      d.startDate = $('#startDate').val();
                      d.endDate = $('#endDate').val();
                      d.bookingId = $('.dt-search').val();
                      d.userId = identifier;
                      // d.userType = 'driver';
                    }
                  },
                  columns: [
                    { data: 'id', name: 'id' },
                    { data: 'totalAmount', name: 'totalAmount' },
                    { data: 'distance', name: 'distance' },
                    { data: 'driverCommissionValue', name: 'driverCommissionValue' },
                    { data: 'fleetCommissionValue', name: 'fleetCommissionValue' },
                    { data: 'officeCommissionValue', name: 'officeCommissionValue' },
                  ]
                });

                $('.dt-search').on('keyup', function () {
                  table.ajax.reload();
                });

                $('#startDate, #endDate').on('change', function () {
                  table.ajax.reload();
                });
              });
            </script>



          </div>




        </div>
      </div>
    </div>
  </div>


<!-- Stylish Add Balance Modal -->
<div class="modal fade" id="addBalanceModal" tabindex="-1" aria-labelledby="addBalanceLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glassy-modal rounded-5 shadow-lg border-0 position-relative overflow-hidden">

      <!-- Custom Close Button -->
      <button type="button" class="custom-close-btn" data-bs-dismiss="modal" aria-label="Close">
        <i class="fas fa-times"></i>
      </button>

      <!-- Header -->
      <div class="modal-header border-0 bg-gradient-light text-dark rounded-top-5 py-4 px-4 pt-5">
        <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
          <i class="fas fa-wallet fa-lg text-success"></i> {{ __('messages.add_balance') }}
        </h5>
      </div>

      <!-- Body -->
      <div class="modal-body p-5" id="balanceModalContent">
        <form id="addBalanceForm">
          <!-- Input Field -->
          <div class="form-group stylish-input mb-4">
            <label for="balanceAmount" class="form-label fw-semibold text-muted mb-2">
              <i class="fas fa-hand-holding-usd me-1 text-success"></i> {{ __('messages.enter_amount') }}
            </label>
            <div class="input-wrapper">
              <i class="fas fa-dollar-sign input-icon"></i>
              <input type="number" class="form-control sleek-input" id="balanceAmount" placeholder="0.00" min="1" required />
            </div>
            <input type="hidden" id="userType" value="user">
            <input type="hidden" id="userId" value="123">
          </div>

          <!-- Submit Button -->
          <div class="d-grid">
            <button type="submit" class="btn btn-success btn-lg rounded-pill d-flex align-items-center justify-content-center gap-2" id="confirmAddBtn">
              <span id="confirmAddText">{{ __('messages.confirm') }}</span>
              <span class="spinner-border spinner-border-sm d-none" role="status" id="addSpinner"></span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
  body {
    font-family: 'Cairo', sans-serif;
    background-color: #f9f9fb;
  }

  .glassy-modal {
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(14px);
    border-radius: 2rem;
    animation: fadeInScale 0.4s ease-in-out;
    position: relative;
  }

  .bg-gradient-light {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  }

  .modal-title {
    font-size: 1.35rem;
  }

  /* Custom Close Button */
 /* زر إغلاق يتغير حسب اتجاه الصفحة */
.custom-close-btn {
  position: absolute;
  top: 16px;
  inset-inline-end: 16px; /* بديل ذكي لـ right/left حسب الاتجاه */
  background: rgba(220, 53, 69, 0.1);
  color: #dc3545;
  border: none;
  font-size: 1.1rem;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s ease;
  z-index: 10;
}

.custom-close-btn:hover {
  background: rgba(220, 53, 69, 0.2);
  color: #a71d2a;
}


  /* Minimal Input Design */
  .input-wrapper {
    position: relative;
  }

  .input-icon {
    position: absolute;
    top: 50%;
    left: 1rem;
    transform: translateY(-50%);
    font-size: 1rem;
    color: #aaa;
    pointer-events: none;
  }

  .sleek-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 1px solid #ced4da;
    border-radius: 0.75rem;
    font-size: 1.05rem;
    background-color: #fff;
    transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  }

  .sleek-input:focus {
    border-color: #198754;
    box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1);
    outline: none;
  }

  .btn-success {
    background: linear-gradient(135deg, #28a745, #218838);
    border: none;
    transition: all 0.3s ease;
  }

  .btn-success:hover {
    background: linear-gradient(135deg, #218838, #1e7e34);
  }

  @keyframes fadeInScale {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
  }
</style>




<script>
  document.getElementById('addBalanceForm').addEventListener('submit', function (e) {
    e.preventDefault();

    // عناصر الواجهة
    const amount = parseFloat(document.getElementById('balanceAmount').value);
    const userType = document.getElementById('userType').value;
    const userId = document.getElementById('userId').value;

    const spinner = document.getElementById('addSpinner');
    const confirmText = document.getElementById('confirmAddText');
    const confirmBtn = document.getElementById('confirmAddBtn');

    // تفعيل الانميشن
    spinner.classList.remove('d-none');
    confirmText.textContent = '{{ __("messages.processing") }}';
    confirmBtn.disabled = true;

    fetch('{{ route("add-balance") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ amount, userType, userId })
    })
    .then(response => response.json())
    .then(data => {
      spinner.classList.add('d-none');
      confirmBtn.disabled = false;

      if (data.success) {
        document.getElementById('balanceModalContent').innerHTML = `
          <div class="text-center text-success">
            <i class="fas fa-check-circle fa-3x mb-3"></i>
            <h5 class="fw-bold">{{ __('messages.balance_added_successfully') }}</h5>
            <p class="mt-3 fs-5">{{ __('messages.new_balance') }}: <strong>${data.walletBalance}</strong></p>
          </div>`;

        showToast('success', '{{ __("messages.balance_added_successfully") }}');
      } else {
        document.getElementById('balanceModalContent').innerHTML = `
          <div class="text-center text-danger">
            <i class="fas fa-times-circle fa-3x mb-3"></i>
            <h5 class="fw-bold">{{ __('messages.error') }}</h5>
            <p class="mt-3">${data.message || '{{ __("messages.something_wrong") }}'}</p>
          </div>`;
      }
    })
    .catch(() => {
      spinner.classList.add('d-none');
      confirmBtn.disabled = false;

      document.getElementById('balanceModalContent').innerHTML = `
        <div class="text-center text-danger">
          <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
          <h5 class="fw-bold">{{ __('messages.error') }}</h5>
          <p class="mt-3">{{ __('messages.something_wrong') }}</p>
        </div>`;
    });
  });

  // توست مخصص
  function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0 position-fixed bottom-0 end-0 m-4 show`;
    toast.role = 'alert';
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>`;

    document.body.appendChild(toast);

    setTimeout(() => toast.remove(), 4000);
  }
</script>








<script>
  document.getElementById("confirmAddBalance").addEventListener("click", function () {
      let amount = parseFloat(document.getElementById("balanceAmount").value);
      let userId = "{{ $customerdata->id }}";
      let userType = "{{ $userType}}";

      if (isNaN(amount) || amount <= 0) {
          Swal.fire({
              icon: "error",
              title: "{{ __('messages.invalid_amount') }}",
              text: "{{ __('messages.please_enter_valid_amount') }}",
              confirmButtonColor: "#dc3545",
          });
          return;
      }

      Swal.fire({
          title: "{{ __('messages.confirm_balance_addition') }}",
          text: `{{ __('messages.add_balance_question') }} ${amount.toFixed(2)} {{ __('messages.question_mark')}}`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#28a745",
          cancelButtonColor: "#dc3545",
          confirmButtonText: "{{ __('messages.yes_add_now') }}",
          cancelButtonText: "{{ __('messages.cancel') }}"
      }).then((result) => {
          if (result.isConfirmed) {
              Swal.fire({
                  title: "{{ __('messages.processing') }}",
                  text: "{{ __('messages.please_wait') }}",
                  icon: "info",
                  allowOutsideClick: false,
                  showConfirmButton: false,
                  didOpen: () => {
                      Swal.showLoading();
                  }
              });

              fetch("{{ route('add-balance') }}", {
                  method: "POST",
                  headers: {
                      "Content-Type": "application/json",
                      "X-CSRF-TOKEN": "{{ csrf_token() }}",
                      "Accept": "application/json"
                  },
                  body: JSON.stringify({
                      amount: amount,
                      userId: userId,
                      userType: userType
                  })
              })
              .then(response => response.json())
              .then(data => {
                  Swal.close();
              if (data.success) {
                  let wallet = document.getElementById("walletBalance");
                  if (wallet) {
                      wallet.textContent = data.walletBalance;
                      wallet.classList.add("wallet-shake");
                      setTimeout(() => {
                          wallet.classList.remove("wallet-shake");
                      }, 500);
                  }

                  let dues = document.getElementById("dues");
                  if (dues) {
                      dues.textContent = data.dues;
                  }

                  const modal = bootstrap.Modal.getInstance(document.getElementById('addBalanceModal'));
                  modal.hide();

                  document.getElementById("balanceAmount").value = "";

                      let toast = new bootstrap.Toast(document.getElementById('toast-balance'));
                      toast.show();
                  } else {
                      Swal.fire({
                          icon: "error",
                          title: "{{ __('messages.error') }}",
                          text: data.message,
                          confirmButtonColor: "#dc3545"
                      });
                  }
              })
              .catch(error => {
                  console.error("Error:", error);
                  Swal.close();
                  Swal.fire({
                      icon: "error",
                      title: "{{ __('messages.balance_not_added') }}",
                      text: "{{ __('messages.server_error') }}",
                      confirmButtonColor: "#dc3545"
                  });
              });
          }
      });
  });
</script>




<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <style>

      .wallet-icon-container {
          position: relative;
          width: 150px;
          height: 80px;
          background: linear-gradient(135deg, #1e1e2f, #252540);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          margin: auto;
          box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
          animation: pulse 1.5s infinite;
      }

      .wallet-icon {
          font-size: 40px;
          color: #F8A609;
      }

      @keyframes pulse {
          0% { transform: scale(1); opacity: 1; }
          50% { transform: scale(1.1); opacity: 0.9; }
          100% { transform: scale(1); opacity: 1; }
      }

      .card {
          border-radius: 12px;
          box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
      }

      .wallet-card, .earnings-card {
  transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
  cursor: pointer;
}

.wallet-card:hover, .earnings-card:hover {
  transform: translateY(-5px);
  box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
}

.animated-icon {
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.add-balance-btn, .pay-earnings-btn {
  border-radius: 70px;
  transition: all 0.3s ease-in-out;
}

.add-balance-btn:hover {
  background-color: #F8A609;
  color: white;
  transform: scale(1.05);
}

.pay-earnings-btn:hover {
  background-color: #007bff;
  color: white;
  transform: scale(1.05);
}

@keyframes walletShake {
  0%   { transform: translateX(0); }
  25%  { transform: translateX(-5px); }
  50%  { transform: translateX(5px); }
  75%  { transform: translateX(-5px); }
  100% { transform: translateX(0); }
}

.wallet-shake {
  animation: walletShake 0.5s ease;
}

  </style>

</x-master-layout>
