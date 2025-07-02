<x-master-layout>
  <div class="container-fluid py-5">
    <div class="row gx-5">
      <!-- Left: Driver Info + Vehicle Info + Wallet + Dues -->
      <div class="col-lg-4 mb-5 d-flex flex-column">
        <!-- مودال الصورة -->
        <div id="imageModal"
          style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.7);
                justify-content:center; align-items:center; z-index:1050; cursor:pointer;">
          <img id="modalImg" src="" alt="Image Preview"
            style="max-width:90vw; max-height:90vh; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.5);" />
        </div>

        <!-- Driver Info Card -->
        <div class="card shadow-sm border-0 rounded-4 p-4 mb-4 flex-grow-0"
          style="background: #ffffff; transition: box-shadow 0.3s ease;">
          <div class="card-body text-center">
            <div class="position-relative d-inline-block mb-4" style="cursor: pointer;">
              <img id="avatarImg" src="{{ $customerdata->photo }}" alt="Avatar" class="rounded-circle"
                style="width: 140px; height: 140px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: transform 0.3s ease;">
              <a href="tel:{{ $customerdata->phoneNumber }}"
                class="position-absolute top-50 start-50 translate-middle rounded-circle bg-primary text-white p-3 fs-5 shadow"
                style="opacity: 0; transition: opacity 0.3s ease;" id="call-icon" title="اتصل">
                <i class="fas fa-phone"></i>
              </a>
            </div>

            <h3 class="fw-semibold mb-2" style="font-size: 2rem; font-family: 'Poppins', sans-serif; color: #212529;">
              {{ $customerdata->firstName }} {{ $customerdata->lastName }}
            </h3>

            <p class="text-muted mb-2 fs-6 d-flex justify-content-center align-items-center gap-2">
              <i class="fas fa-phone-alt text-primary fs-5"></i>
              {{ $customerdata->phoneNumber }}
            </p>

            @if(!empty($customerdata->address))
              <p class="text-muted fs-6 d-flex justify-content-center align-items-center gap-2">
                <i class="fas fa-map-marker-alt text-danger fs-5"></i>
                {{ $customerdata->address }}
              </p>
            @endif

            <hr class="my-4">

            <div class="d-flex justify-content-between text-center mt-3 px-5" style="gap: 3rem;">
              <div>
                <h6 class="text-muted fs-6 mb-1">{{ __('messages.rating') }}</h6>
                <h4 class="fw-bold text-warning d-flex justify-content-center align-items-center gap-2"
                  style="font-size: 1.6rem;">
                  <i class="fas fa-star" style="color: #ffc107;"></i> {{ $customerdata->rating }}
                </h4>
              </div>
              <div>
                <h6 class="text-muted fs-6 mb-1">{{ __('messages.ride_count') }}</h6>
                <h4 class="fw-bold" style="font-size: 1.6rem;">{{ $customerdata->rideCount }}</h4>
              </div>
              <div>
                <h6 class="text-muted fs-6 mb-1">{{ __('messages.km_count') }}</h6>
                <h4 class="fw-bold" style="font-size: 1.6rem;">{{ $customerdata->kmCount }} km</h4>
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

          // مودال الصورة
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

        <!-- Vehicle Info Card -->
        <div class="card shadow-lg border-0 rounded-4 p-4">
          <div class="card-body">

            <h3 class="fw-bold text-center text-primary mb-4" style="font-size: 1.8rem;">
              <i class="fas fa-car-side me-2" style="font-size: 1.6rem;"></i>
              {{ __('messages.vehicle_info') }}
            </h3>

            <!-- صورة السيارة -->
            @if(!empty($car->photo))
              <div class="text-center mb-4">
                <img src="{{ $car->photo }}" alt="Vehicle Photo"
                  onclick="showImageModal(this.src)"
                  style="width: 100%; max-height: 240px; object-fit: cover; border-radius: 12px; cursor: pointer;" />
              </div>
            @endif

            <!-- القسم الأول: معلومات عامة -->
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

            <!-- القسم الثاني: معلومات الترخيص -->
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
          <!-- Wallet -->
          <div class="card bg-success text-white shadow-lg rounded-5 p-4 text-center flex-grow-0">
            <i class="fas fa-wallet fa-4x mb-4"></i>
            <h4 class="mb-2 fw-bold">{{ __('messages.wallet_balance') }}</h4>
            <h1 id="walletBalance" class="fw-extrabold" style="font-size: 3rem;">{{ number_format($customerdata->walletBalance, 2) }}</h1>
            <button class="btn btn-light text-success fw-bold mt-4 px-5 py-3 fs-5" data-bs-toggle="modal" data-bs-target="#addBalanceModal">
              <i class="fas fa-plus-circle me-3"></i> {{ __('messages.add_balance') }}
            </button>
          </div>

          <!-- Dues -->
          @if ($isDriver)
            <div class="card bg-danger text-white shadow-lg rounded-5 p-4 text-center flex-grow-0">
              <i class="fas fa-money-bill-wave fa-4x mb-4"></i>
              <h4 class="mb-2 fw-bold">{{ __('messages.dues') }}</h4>
              <h1 id="duesAmount" class="fw-extrabold" style="font-size: 3rem;">
                {{ number_format($customerdata->officeDues + $customerdata->fleetDues, 2) }}
              </h1>
              <button class="btn btn-light text-danger fw-bold mt-4 px-5 py-3 fs-5" data-bs-toggle="modal" data-bs-target="#payDuesModal">
                <i class="fas fa-wallet me-3"></i> {{ __('messages.pay_dues') }}
              </button>
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

            <!-- ملخص الطلبات -->
            <div class="mb-4 fs-5">
              <p><strong>إجمالي الطلبات: </strong> <span id="totalCount">0</span></p>
              <p><strong>إجمالي الكيلومترات: </strong> <span id="totalKm">0</span> كم</p>
              <p><strong>إجمالي الأرباح: </strong> <span id="totalEarning">0</span> $</p>
              <p><strong>مستحقات المكتب: </strong> <span id="officeDues">0</span> $</p>
            </div>

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
</x-master-layout>
