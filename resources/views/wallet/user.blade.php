<x-master-layout>

  
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

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
  style="background-color: #302652; transition: box-shadow 0.3s ease; color: #eee;">
  
  <div class="card-body text-center d-flex flex-column align-items-center" style="color: #eee; font-family: 'Cairo', sans-serif;">
    

   
    <div class="position-relative d-inline-block mb-2" style="cursor: pointer;">
      <img id="avatarImg" src="{{ $customerdata->photo }}" alt="Avatar" class="rounded-circle"
        style="width: 140px; height: 140px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: transform 0.3s ease;">
        
        
          <h4 class="text-warning d-flex justify-content-center align-items-center gap-1" style="font-size: 0.4rem; margin: 0;">
            <i class="fas fa-star" style="color: #ffcc00; font-size: 1.2rem;"></i> {{ $customerdata->rating }}
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
        <i class="fas fa-car icon-spin" style="color: #ffc107; font-size: 1.8rem;"></i>
        <h4 class="fw-bold" id="totalCount" style="font-size: 1.6rem; color: #eee;">{{ $customerdata->rideCount }}</h4>
      </div>
    
      <div style="min-width: 150px; flex-grow: 1; border-left: 1px solid rgba(255,255,255,0.3); padding-left: 1rem;">
        <h6 class="text-light fs-6 mb-1 d-flex justify-content-center align-items-center gap-2">
          <span> {{ __('messages.km_count') }} </span>
        </h6>
        <i class="fas fa-tachometer-alt icon-spin" style="color: #ffc107; font-size: 1.4rem;"></i>
        <h4 class="fw-bold" id="totalKm" style="font-size: 1.6rem; color: #eee;">{{ $customerdata->kmCount }} km</h4>
      </div>
    </div>
 

    <div class="mt-4 d-flex justify-content-center gap-3 flex-wrap">
      <span class="badge rounded-pill bg-{{ $customerdata->isActive ? 'success' : 'danger' }} fs-6 py-2 px-4 d-flex align-items-center gap-2">
        <i class="fas fa-user-check"></i>
        {{ $customerdata->isActive ? __('messages.active') : __('messages.inactive') }}
      </span>
      <span class="badge rounded-pill bg-{{ $customerdata->isConected ? 'primary' : 'secondary' }} fs-6 py-2 px-4 d-flex align-items-center gap-2">
        <i class="fas fa-circle" style="font-size: 0.8rem; color: {{ $customerdata->isConected ? '#0d6efd' : '#6c757d' }};"></i>
        {{ $customerdata->isConected ? __('messages.online') : __('messages.offline') }}
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
                <img src="{{ $car->photo }}" alt="Vehicle Photo"
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

            <!-- القسم الثالث: معلومات إضافية -->
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

        <!-- Wallet and Dues Container -->
        <div class="d-flex flex-column gap-4 mt-4">
       
          <div class="card wallet-card text-white bg-success shadow-lg rounded-4">
            <div class="card-body text-center">
                <i class="fas fa-wallet fa-3x mb-3 animated-icon"></i>
                <h5 class="fw-bold">{{ __('messages.wallet_balance') }}</h5>
                <h2 id="walletBalance" class="fw-bolder">{{$customerdata->walletBalance}}</h2>
                <button class="btn btn-light text-success fw-bold mt-3 px-4 py-2 add-balance-btn" data-bs-toggle="modal" data-bs-target="#addBalanceModal">
                    <i class="fas fa-plus-circle me-2"></i> {{ __('messages.add_balance') }}
                </button>
            </div>
        </div>


          <!-- Dues -->
          @if ($isDriver)
          <div class="card earnings-card text-white bg-danger shadow-lg rounded-4">
            <div class="card-body text-center">
                <i class="fas fa-money-bill-wave fa-3x mb-3 animated-icon"></i>
                <h5 class="fw-bold">{{ __('messages.dues') }}</h5>
                <h2 id="dues" class="fw-bolder">{{$customerdata->officeDues +$customerdata->fleetDues }}</h2>
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
            <a href="{{ route('add.wallet') }}" class="btn btn-primary btn-lg d-flex align-items-center gap-2">
              <i class="fas fa-arrow-left fs-5"></i> {{ __('messages.back') }}
            </a>
          </div>
          <div class="card-body px-5 py-4">
            <div class="row mb-4">
              <div class="col-md-6">
                <label for="startDate" class="form-label fw-semibold">من تاريخ:</label>
                <input type="date" id="startDate" class="form-control" />
              </div>
              <div class="col-md-6">
                <label for="endDate" class="form-label fw-semibold">إلى تاريخ:</label>
                <input type="date" id="endDate" class="form-control" />
              </div>
            </div>

            <div class="d-flex justify-content-end mb-4">
              <button id="filterBtn" class="btn btn-primary px-4 py-2 fs-5">بحث</button>
            </div>

            <h5 class="card-title mb-4 fs-4 fw-semibold">{{ __('messages.payment_details') }}</h5>


            <!-- جدول الطلبات -->
            <div class="table-responsive">
              <table id="ordersTable" class="table table-hover align-middle mb-0 fs-5">
                <thead class="table-light fs-5">
                  <tr>
                    <th>رقم الطلب</th>
                    <th>المبلغ</th>
                    <th>المسافة (كم)</th>
                    <th>أرباحي</th>
                    <th>عمولة الأسطول</th>
                    <th>عمولة المكتب</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- سيتم تعبئتها ديناميكيًا -->
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {

      let driverId = "{{ $customerdata->id }}";

      let table = $('#ordersTable').DataTable({
        processing: true,
        serverSide: false,
        searching: false,
        paging: true,
        columns: [
          { data: 'id' },
          { data: 'amount' },
          { data: 'distance' },
          { data: 'myEarning' },
          { data: 'fleetCommission' },
          { data: 'officeCommission' }
        ],
        language: {
          url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/ar.json'
        }
      });

      function loadData() {
        let startDate = $('#startDate').val();
        let endDate = $('#endDate').val();

        if (!startDate || !endDate) {
          alert('يرجى ملء حقلَي التاريخ');
          return;
        }

        $.ajax({
          url: '{{ route("driver.order-history") }}',
          type: 'GET',
          data: {
            driverId: driverId,
            startDate: startDate,
            endDate: endDate,
          },
          success: function(response) {
            table.clear().rows.add(response.orders).draw();

            $('#totalCount').text(response.totalCount);
            $('#totalKm').text(response.totalKm);
            $('#totalEarning').text(response.totalEarning);
            $('#officeDues').text(response.officeDues);
          },
          error: function(xhr) {
            alert('حدث خطأ أثناء جلب البيانات');
          }
        });
      }

      $('#filterBtn').click(function() {
        loadData();
      });

    });

    // مودال عرض صورة السيارة
    function showImageModal(src) {
      const imageModal = document.getElementById('imageModal');
      const modalImg = document.getElementById('modalImg');
      modalImg.src = src;
      imageModal.style.display = 'flex';
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
          color: #ffc107;
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
  background-color: #fad501;
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
