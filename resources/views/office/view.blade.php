<x-master-layout>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


    <style>

        .new-stat-card:hover {
          transform: translateY(-6px);
          box-shadow: 0 12px 36px rgba(220, 53, 69, 0.3);
          background: rgba(255, 238, 0, 0.11);
          justify-content: center;
        }

        .icon-wrapper {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          width: 66px;
          height: 66px;
          border-radius: 50%;
          margin-bottom: 16px;
          font-size: 2rem;
          background-color: rgba(220, 53, 69, 0.15);
          color: #b02a37;
          transition: all 0.3s ease;
        }

        .new-stat-card:hover .icon-wrapper {
          transform: scale(1.15);
          background-color: rgba(220, 53, 69, 0.25);
        }

        .new-stat-card .title {
          font-size: 1.15rem;
          font-weight: 600;
          margin-bottom: 6px;
          color: #842029;
          transition: transform 0.3s ease;
        }

        .new-stat-card:hover:dir(rtl) .title {
          transform: translateY(-40px);
          transform: translateX(-120px);
        }

        .new-stat-card:hover:dir(ltr) .title {
          transform: translateY(-40px);
          transform: translateX(120px);
        }


        .new-stat-card .value {
          font-size: 2.2rem;
          font-weight: 700;
          color: #b02a37;
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          transition: transform 0.3s ease, font-size 0.3s ease;
        }

        .new-stat-card:hover:dir(ltr) .value {
          transform: translateX(120px);
          font-size: 3rem;
        }

        .new-stat-card:hover:dir(rtl) .value {
          transform: translateX(-120px);
          font-size: 3rem;
        }

        .new-stat-card::before,
        .new-stat-card::after,
        .new-stat-card .top-line,
        .new-stat-card .bottom-line {
          content: "";
          position: absolute;
          border-radius: 6px;
          opacity: 0.35;
        }


        .new-stat-card::before {
        width: 2px;
        height: 60%;
        top: 20%;
        left: 0;
        background: linear-gradient(to bottom, transparent, #dc3545, transparent);
        }

        .new-stat-card::after {
        width: 2px;
        height: 60%;
        top: 20%;
        right: 0;
        background: linear-gradient(to bottom, transparent, #dc3545, transparent);
        }

        .new-stat-card .top-line {
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 40%;
        height: 3px;
        background: linear-gradient(to right, transparent, #dc3545, transparent);
        }

        .new-stat-card .bottom-line {
          bottom: 0;
          right: 50%;
          transform: translateX(50%);
          width: 40%;
          height: 3px;
          background: linear-gradient(to left, transparent, #dc3545, transparent);
        }

        .stat-card {
          display: flex;
          align-items: center;
          justify-content: space-between;
          border-radius: 16px;
          padding: 24px 30px;
          position: relative;
          min-height: 160px;
          color: #fff;
          overflow: hidden;
          box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
          transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
          transform: translateY(-5px);
          box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }

        .stat-icon {
          width: 64px;
          height: 64px;
          font-size: 28px;
          display: flex;
          align-items: center;
          justify-content: center;
          border-radius: 14px;
          background-color: rgba(255, 255, 255, 0.1);
          box-shadow: inset 0 0 12px rgba(0, 0, 0, 0.1);
          margin-inline-end: 20px;
        }

        .stat-info h3 {
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          font-size: 1.3rem;
          font-weight: 700;
          letter-spacing: 0.03em;
          margin-bottom: 10px;
          color: #fff;
          text-shadow: 0 1px 3px rgba(0,0,0,0.3);
          transition: color 0.3s ease;
        }

        .stat-info h3:hover {
          color: #ffd700;
        }

        .stat-value {
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          font-size: 2rem;
          font-weight: 500;
          letter-spacing: 0.05em;
          color: #fff;
          text-shadow: 0 2px 3px rgba(0,0,0,0.4);
          line-height: 1.1;
          transition: color 0.3s ease;
        }

        .stat-value:hover {
          color: #ffa500;
        }

                                </style>




    <main class="main-area">
        <div class="main-content">
            <div class="container-fluid">
                @include('partials._office')
                <div class="card">
                    <div class="card-body p-30">
                        <div class="provider-details-overview mb-30">
                            <div class="provider-details-overview__collect-cash">
                                <div class="statistics-card statistics-card__collect-cash h-100">
                                    <h3>{{ __('messages.collect_cash_Office') }}</h3>
                                        {{-- <a href="{{route('providerpayout.create',$office->id)}}" class="btn btn--primary text-capitalize btn--lg mw-75">{{ __('messages.collect') }}</a> --}}
                                </div>
                            </div>
                            <div class="provider-details-overview__statistics">
                                <div class="statistics-card statistics-card__style2 statistics-card__pending-withdraw">
                                    <h2>{{ getPriceFormat($officeData['officeTotEarning']) ?? 0}}</h2>
                                    <h3>{{__('messages.pending_withdraw')}}</h3>
                                </div>

                            <div class="statistics-card statistics-card__style2 statistics-card__already-withdraw">
                                <h2>{{getPriceFormat($officeData['officeTotWithdrableAmt']) ?? 0}}</h2>
                                <h3>{{__('messages.withdrawble_amount')}}</h3>
                            </div>

                            <div
                                class="statistics-card statistics-card__style2 statistics-card__withdrawable-amount">
                                <h2>{{getPriceFormat($officeData['officeAlreadyWithdrawAmt']) ?? 0}}</h2>
                                <h3>{{__('messages.already_withdraw')}}</h3>

                            </div>

                            <div class="statistics-card statistics-card__style2 statistics-card__total-earning">
                                <h2>{{getPriceFormat($officeData['pendWithdrwan']) ?? 0}}</h2>
                                <h3>{{__('messages.total_earning')}}</h3>
                            </div>
                        </div>
                        <div class="provider-details-overview__order-overview">
                            <div class="statistics-card statistics-card__order-overview h-100 pb-2">
                                <h3 class="mb-0">{{__('messages.booking_overview')}}</h3>
                                @if($data['pendingStatusCount']+$data['cancelledstatuscount']+$data['Completedstatuscount']+$data['Acceptedstatuscount'] > 0)
                                <div id="chart" class="d-flex justify-content-center">

                                </div>
                                @else
                                <p style = "color:#366d36ea; font-size:20px;
                                     font-style:italic; text-align:center; margin-top: 20%;">
                                      {{__('messages.nodata')}}

                                  </p>
                                  @endif
                                <div class="resize-triggers">
                                    <div class="expand-trigger">
                                        <div style="width: 310px; height: 234px"></div>
                                    </div>
                                    <div class="contract-trigger"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="horizontal-separator"></div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="information-details-box media flex-column flex-sm-row gap-20">
                                <img class="avatar-img radius-5" src="./img/1.png" alt="" />
                                <div class="media-body">
                                    <h2 class="information-details-box__title">
                                        {{ $office->displayName }}
                                    </h2>
                                    <ul class="contact-list">
                                        <li>
                                            <i class="ri-smartphone-line"></i>
                                            <a
                                                href="tel: {{ $office->contact_number }}" class="contact-info-text p-0">{{ !empty($office->contactNumber) ? $office->contactNumber: '-' }}</a>
                                        </li>
                                        <li>
                                            <i class="ri-mail-line"></i>
                                            <a href="mailto: {{ $office->email }}" class="contact-info-text p-0">{{ $office->email }}</a>
                                        </li>
                                        <li>
                                            <i class="ri-map-2-line"></i>
                                            <span class="contact-info-text">{{ !empty($office->address) ?$office->address : '-' }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- <div
                        class="statistics-card statistics-card__style2 statistics-card__withdrawable-amount" style="margin-left: 70px; background:rgb(209, 74, 74); border-radius: 15px;">
                        <h2 style="color: white;">{{__('messages.fleet_dues')}}</h2>
                        <h2 style="color: white;">{{getPriceFormat($fleetDues) ?? 0}}</h2>

                    </div> --}}


                    {{-- <div class="col-md-6">
                        <div class="new-stat-card office-card">
                            <span class="top-line"></span>
                            <span class="bottom-line"></span>
                            <div class="icon-wrapper">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="title">{{ __('messages.unpaid_fleet_dues') }}</div>
                            <div class="value" id="officeDues">{{ getPriceFormat($fleetDues) }}</div>
                        </div>
                    </div> --}}










































<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" />

<div id="wb-wallet-container" style="padding:20px; display:flex;justify-content:center;">
  <div class="wb-wallet-card">
    <div class="wb-wallet-header">
      <div class="wb-wallet-icon"><i class="fa-solid fa-wallet"></i></div>
      <div>
        <div class="wb-wallet-balance" id="wb-balanceDisplay" style="color: white;">{{ getPriceFormat($office->balanceWallet) }}</div>
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
.wb-wallet-card{max-width:480px;background:#312873;border-radius:14px;padding:20px;box-shadow:0 8px 30px rgba(0,0,0,0.5);border:1px solid rgba(255,255,255,0.08);}
.wb-wallet-header{display:flex;align-items:center;gap:14px;margin-bottom:18px;}
.wb-wallet-icon{width:60px;height:60px;border-radius:12px;display:flex;justify-content:center;align-items:center;font-size:26px;color:#FCB902;background:#26204d;border:1px solid rgba(255,255,255,0.06);}
.wb-wallet-balance{font-size:28px;font-weight:700;margin-bottom:2px;}
.wb-wallet-label{color:#ddd;font-size:13px;}
.wb-btn-accent{background:#FCB902;color:#000;font-weight:700;border:none;padding:8px 14px;border-radius:10px;font-size:14px;box-shadow:0 5px 14px rgba(252,185,2,0.25);cursor:pointer;}
.wb-btn-outline{border:1px solid rgba(255,255,255,0.15);background:transparent;color:#fff;padding:7px 12px;border-radius:10px;font-size:13px;cursor:pointer;position:relative;}
input{width:100%;padding:8px;border-radius:10px;border:1px solid #555;background:#26204d;color:#fff;}
/* المودال */
.wb-modal{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:none;justify-content:center;align-items:center;z-index:9999;}
.wb-modal-content{background:#312873;padding:20px;border-radius:16px;max-width:400px;width:90%;}
.wb-modal-header h5{color:#FCB902;margin:0 0 10px 0;}
.wb-loader-inline{width:16px;height:16px;border-radius:50%;border:2px solid rgba(255,255,255,0.2);border-top-color:#fff;animation:spin .8s linear infinite;display:inline-block;}
@keyframes spin{to{transform:rotate(360deg)}}
.wb-message-box{margin-top:10px;padding:8px;border-radius:10px;font-weight:600;font-size:13px;display:none;}
.wb-message-success{background:#28a745;color:#fff;}
.wb-message-error{background:#dc3545;color:#fff;}
</style>

<script>
const officeId = @json($office->id);

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
  if(Number.isInteger(v)) return Number(v).toLocaleString("en-US")+"ل.س";
  return Number(v).toLocaleString("en-US",{minimumFractionDigits:2,maximumFractionDigits:2})+"ل.س";
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

    const res = await fetch("{{ route('office.get-wallet-balance') }}?office_id=" + officeId);
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

    const res = await fetch("{{ route('office.add-blance-to-wallet') }}", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}"
    },
    body: JSON.stringify({amount, office_id: {{ $office->id }} })
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


































                    {{-- <div
                    class="statistics-card statistics-card__style2 statistics-card__withdrawable-amount" style="margin-left: 40px; background:rgba(65, 126, 25, 0.863); border-radius: 15px;">
                    <h2 style="color: white;">{{__('messages.wallet_balance')}}</h2>
                    <h2 style="color: white;">{{getPriceFormat($officeData['officeAlreadyWithdrawAmt']) ?? 0}}</h2>

                </div> --}}


                    </div>
                </div>
            </div>
        </div>
    </div>
</main>



<script>
    var pendingCount = parseInt("{{ $data['pendingStatusCount'] }}");
    var cancelledCount = parseInt("{{ $data['cancelledstatuscount'] }}");
    var Completedcount = parseInt("{{ $data['Completedstatuscount'] }}");
    var Acceptedcount = parseInt("{{ $data['Acceptedstatuscount'] }}");

    var options = {
        series: [pendingCount, cancelledCount, Completedcount, Acceptedcount],
        chart: {
            width: 380,
            type: 'pie',
        },
        labels: ['{{ __("messages.pending") }}', '{{ __("messages.cancelled") }}', '{{ __("messages.completed") }}', '{{ __("messages.accepted") }}'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
</script>

    {{-- <script type="text/javascript">
        var pendingCount = "{{ $data['pendingStatusCount'] }}";
        var cancelledCount = parseInt("{{ $data['cancelledstatuscount'] }}");
        var Completedcount = parseInt("{{ $data['Completedstatuscount'] }}");
        var Acceptedcount = parseInt("{{ $data['Acceptedstatuscount'] }}");

        var options = {
            series: [parseInt(pendingCount), cancelledCount, Completedcount, Acceptedcount],
            chart: {
                width: 380,
                type: 'pie',
            },
            labels: ['Pending', 'cancell', 'completed', 'accepted'],
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    </script> --}}
</x-master-layout>
