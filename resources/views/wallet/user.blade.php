<x-master-layout>

      <div class="card-header d-flex justify-content-between align-items-center bg-white py-4 px-5 rounded-top-5">
        <h4 class="mb-0 fw-bold">{{ $pageTitle ?? 'user info' }}</h4>
        <a href="{{ route('user.index') }}" class="btn btn-primary btn-lg d-flex align-items-center gap-2">
          <i class="fas fa-arrow-left fs-5"></i> {{ __('messages.back') }}
        </a>
      </div>
  <style>
.profile-card {
  background: linear-gradient(135deg, #f8a40938 0%, #1e1a4d81 100%);
  color: #fff;
  box-shadow: 0 8px 25px rgba(49, 40, 115, 0.7);
  border-radius: 20px;
  transition: box-shadow 0.3s ease;
}

.profile-card:hover {
  box-shadow: 0 12px 35px rgba(248, 166, 9, 0.8);
}

.profile-img {
  width: 140px;
  height: 140px;
  object-fit: cover;
  border-radius: 50%;
  border: 5px solid #312873;
  box-shadow: 0 0 20px #312873;
  margin-bottom: 15px;
}

.badge-status.bg-success {
  background-color: #F8A609 !important;
  color: #312873 !important;
  font-weight: 700;
}

.badge-status.bg-danger {
  background-color: #e03e3e !important;
  color: #fff !important;
  font-weight: 700;
}

.badge-status.bg-secondary {
  background-color: #312873 !important;
  color: #fff !important;
  font-weight: 700;
}

.wallet-card {
  flex: 1;
  background: #F8A609;
  color: #312873;
  border-radius: 15px;
  padding: 20px 15px;
  text-align: center;
  box-shadow: 0 8px 15px rgba(248, 166, 9, 0.5);
  cursor: default;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: 2px solid #312873;

}

.wallet-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 25px rgba(248, 166, 9, 0.8);
}

.referral-card {
  flex: 1;
  background: #312873;
  color: #F8A609;
  border-radius: 15px;
  padding: 20px 15px;
  text-align: center;
  box-shadow: 0 8px 15px rgba(49, 40, 115, 0.5);
  cursor: default;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: 2px solid #F8A609;
}

.referral-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 25px rgba(49, 40, 115, 0.8);
}

.wallet-card h6, .referral-card h6 {
  font-weight: 700;
  margin-bottom: 8px;
  letter-spacing: 0.05em;
}

.wallet-card h4, .referral-card h4 {
  font-size: 1.8rem;
  font-weight: 800;
  letter-spacing: 0.02em;
}

.icon-wallet, .icon-referral {
  font-size: 2.2rem;
  margin-bottom: 10px;
  display: block;
}

.wallet-card h6 {
  font-weight: 700;
  margin-bottom: 8px;
  letter-spacing: 0.05em;
  color: #312873;
}

.wallet-card h4 {
  font-size: 1.8rem;
  font-weight: 800;
  letter-spacing: 0.02em;
  color: #1f1a4d; 
}



.referral-card h6 {
  font-weight: 700;
  margin-bottom: 8px;
  letter-spacing: 0.05em;
  color: #ffe066; 
}

.referral-card h4 {
  font-size: 1.8rem;
  font-weight: 800;
  letter-spacing: 0.02em;
  color: #F8A609;
}

.profile-card h3.fw-bold {
  font-size: 2.2rem;   
  color: #312873;     
  font-weight: 900;
  margin-bottom: 10px;
}

.profile-card p.text-muted {
  font-size: 1.1rem;
  color: #fff;         
  font-weight: 600;
}

.badge-status {
  font-size: 1rem;
  padding: 0.5em 1.2em;
  font-weight: 700;
  color: #312873 !important; 
}

.badge-status.bg-danger {
  color: #fff !important;    
}

.badge-status.bg-secondary {
  color: #fff !important;
}

.profile-card p, .profile-card .badge-status {
  margin-top: 6px;
  margin-bottom: 6px;
}


  </style>

  <div class="container py-5">
  
      <div class="card profile-card shadow-lg border-0 rounded-4 mb-4">
        <h4 class="rating-display">
          <i class="fas fa-star"></i> {{ number_format($userdata->rating, 1) }}
        </h4>
        
        <style>
        .rating-display {
          display: flex;
          justify-content: center;
          align-items: center;
          gap: 0.5rem; 
          font-size: 1.5rem; 
          color: #ffcc00; 
          font-weight: bold;
          text-shadow: 1px 1px 3px rgba(0,0,0,0.3); 
        }
        
        .rating-display i {
          font-size: 2rem; 
          color: #ffc107; 
          text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        </style>
        

          <div class="card-body text-center">
              {{-- <img src="{{ $userdata->photo ?? asset('images/default-avatar.png') }}" class="profile-img" alt="User Photo"> --}}
  
<img id="avatarImg" src="{{ asset($userdata->photo) }}" alt="Avatar" class="rounded-circle avatar-img">

<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content modal-style">
      <div class="modal-body p-0">
        <img id="modalImage" src="" alt="Avatar Large" class="modal-img">
      </div>
<button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close">
  &times;
</button>

<style>
.close-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  font-size: 28px;
  color: #fff;
  background: rgba(0,0,0,0.5);
  border: none;
  border-radius: 40%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.3s, transform 0.3s;
  z-index: 1050;
}

.close-btn:hover {
  background: rgba(255,255,255,0.8);
  color: #000;
  transform: scale(1.1);
}
</style>
    </div>
  </div>
</div>

<style>
.avatar-img {
  width: 140px;
  height: 140px;
  object-fit: cover;
  cursor: pointer;
  border: 4px solid #312873;
  border-radius: 50%;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}



  .modal-style {
    background: rgba(0,0,0,0.8);
    border-radius: 12px;
    overflow: hidden;
    border: none;
  }

  .modal-img {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 12px;
  }

  body.modal-open::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    backdrop-filter: blur(6px);
    z-index: 1040;
  }

 

body.dark #ordersTable thead {
    background-color: #1e1e1e;
}


body.dark #ordersTable tbody tr:hover {
    background-color: #2a2a2a;
}

.card-header {
    background-color: #ffffff !important;
    color: #333333 !important;
    border-bottom: 1px solid #e0e0e0;
    font-size: 1.2rem;
}

body.dark .card-header {
    background-color: #1e1e1e !important;
    color: #f0f0f0 !important;
    border-bottom: 1px solid #333333 !important;
}


</style>

<script>
  const avatarImg = document.getElementById('avatarImg');
  const modalImage = document.getElementById('modalImage');

  avatarImg.addEventListener('click', function() {
    modalImage.src = this.src;
    const myModal = new bootstrap.Modal(document.getElementById('imageModal'));
    myModal.show();
  });
</script>


              <h3 class="fw-bold">{{ $userdata->firstName }} {{ $userdata->lastName }}</h3>
              
              <p class="text-muted">
                  <i class="fas fa-phone-alt text-primary"></i> {{ $userdata->phoneNumber }}
              </p>
              <p>
                  <span class="badge badge-status bg-{{ $userdata->isActive ? 'success' : 'danger' }}">
                      {{ $userdata->isActive ? 'نشط' : 'غير نشط' }}
                  </span>
                  @if($userdata->gender)
                  <span class="badge badge-status bg-secondary ms-2">
                      {{ $userdata->gender == 'male' ? 'ذكر' : 'أنثى' }}
                  </span>
                  @endif
              </p>
  
              <div class="d-flex justify-content-center gap-3 mt-4">
                  <div class="wallet-card">
                      <i class="fas fa-wallet icon-wallet"></i>
                      <h6>رصيد المحفظة</h6>
                      <h4>{{ getPriceFormat($userdata->walletBalance) }}</h4>
                  </div>
                  @if($userdata->referralCode)
                  <div class="referral-card">
                      <i class="fas fa-tag icon-referral"></i>
                      <h6>كود الإحالة</h6>
                      <h4>{{ $userdata->referralCode }}</h4>
                  </div>
                  @endif
              </div>
          </div>
      </div>
  
      <div class="card shadow-lg border-0 rounded-4">
          <div class="card-header bg-white py-3 px-4">
              <h5 class="fw-bold mb-0">سجل الطلبات</h5>
          </div>
          <div class="card-body p-4">
  
              <div class="row mb-3">
                  <div class="col-md-4">
                      <label>من تاريخ</label>
                      <input type="date" id="startDate" class="form-control">
                  </div>
                  <div class="col-md-4">
                      <label>إلى تاريخ</label>
                      <input type="date" id="endDate" class="form-control">
                  </div>
                  <div class="col-md-4">
                      <label>بحث برقم الطلب</label>
                      <input type="text" id="orderSearch" class="form-control" placeholder="رقم الطلب">
                  </div>
              </div>
  
              <div class="table-responsive">
                  <table id="ordersTable" class="table table-hover align-middle">
                      <thead class="table-light">
                          <tr>
                              <th>رقم الطلب</th>
                              <th>تاريخ البدء</th>
                              <th>المبلغ الكلي</th>
                              <th>المسافة</th>
                              <th>طريقة الدفع</th>
                              <th>حالة الدفع</th>
                              <th>حالة الطلب</th>
                          </tr>
                      </thead>
                  </table>
              </div>
          </div>
      </div>
  
  </div>
  
  <script>
  $(function(){
      let userId = @json($userdata->id);
      let table = $('#ordersTable').DataTable({
          processing: true,
          serverSide: true,
          searching: false,
          ajax: {
              url: "{{ route('user.bookings.data') }}",
              data: function(d){
                  d.userId = userId;
                  d.startDate = $('#startDate').val();
                  d.endDate = $('#endDate').val();
                  d.orderId = $('#orderSearch').val();
              }
          },
          columns: [
              { data: 'id' },
              { data: 'startAt' },
              { data: 'totalAmount' },
              { data: 'distance' },
              { data: 'paymentType' },
              { data: 'paymentStatus' },
              { data: 'status' },
          ]
      });
  
      $('#startDate, #endDate, #orderSearch').on('change keyup', function(){
          table.ajax.reload();
      });
  });
  </script>

  
</x-master-layout>
