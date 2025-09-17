<x-master-layout>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-9">
                <div class="profile-card shadow-lg border-0 rounded-4 p-5">
    
                    <!-- Header Section -->
                    <div class="profile-header text-center mb-5">
                        <div class="avatar-wrapper mb-3">
                            <img src="{{ $customer->photo ? asset($customer->photo) : asset('default-avatar.png') }}" 
                                 alt="Avatar" class="customer-avatar">
                        </div>
                        <h1 class="fw-bold mb-1">{{ $customer->firstName }} {{ $customer->lastName }}</h1>
                        <p class="text-muted mb-2"><i class="fas fa-phone-alt text-warning me-1"></i> {{ $customer->phoneNumber }}</p>
                        <p class="text-muted mb-0"><i class="fas fa-credit-card text-warning me-1"></i> حالة الدفع الأخيرة: {{ $lastPaymentStatus }}</p>
                    </div>
    
                    <!-- Stats Section -->
                    <div class="stats-wrapper">
                        <div class="stat-item">
                            <h6>عدد الرحلات</h6>
                            <div class="stat-circle bg-gradient-1">
                                <i class="fas fa-route"></i>
                                <span>{{ $totalBookings }}</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <h6>إجمالي المبلغ</h6>
                            <div class="stat-circle bg-gradient-2">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>{{ number_format($totalAmount,2) }} ر.س</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <h6>إجمالي المسافة</h6>
                            <div class="stat-circle bg-gradient-3">
                                <i class="fas fa-map-signs"></i>
                                <span>{{ $totalDistance }} كم</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <h6>آخر رحلة</h6>
                            <div class="stat-circle bg-gradient-4">
                                <i class="fas fa-calendar-alt"></i>
                                <span>{{ $lastBookingAt ?? 'لا يوجد' }}</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <h6>متوسط التقييم</h6>
                            <div class="stat-circle bg-gradient-5">
                                <i class="fas fa-star"></i>
                                <span>{{ round($averageRating,1) }}</span>
                            </div>
                        </div>
                    </div>
    
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.stat-circle span').forEach(el => {
    const len = el.textContent.length;
    if(len > 5){ 
        el.parentElement.style.width = `${120 + len*5}px`;
        el.parentElement.style.height = `${120 + len*5}px`;
    }
});

        </script>
    
    
    <style>
    /* Background */
    body {
        background: linear-gradient(135deg, #F3F3F3 0%, #E1E1E1 100%);
    }
    
    /* Profile Card */
    .profile-card {
        background: rgba(255,255,255,0.95);
        border-radius: 25px;
        padding: 50px 35px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .profile-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 30px 60px rgba(0,0,0,0.15);
    }
    
    /* Avatar */
    .avatar-wrapper {
        width: 180px;
        height: 180px;
        margin: 0 auto;
        padding: 5px;
        border-radius: 50%;
        background: linear-gradient(135deg, #F8A609, #312873);
    }
    .customer-avatar {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 6px solid #fff;
    }
    
    /* Header Text */
    .profile-header h1 {
        color: #312873;
        font-weight: 800;
        font-size: 2.2rem;
    }
    .profile-header p {
        font-size: 1.1rem;
    }
    
    /* Stats */
    .stats-wrapper {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 25px;
        margin-top: 30px;
    }
    
    .stat-item {
        flex: 1 1 30%;
        text-align: center;
    }
    
    .stat-item h6 {
        margin-bottom: 12px;
        font-size: 1.1rem;
        color: #312873;
        letter-spacing: 0.5px;
    }
    
    /* Stat Circle */
    .stat-circle {
        min-width: 120px;
    min-height: 120px;
    padding: 10px;
    font-size: 1.3rem;
        width: 120px;
        height: 120px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #fff;
        font-weight: 700;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-align: center;
        padding: 10px;
    }
    
    .stat-circle i {
        font-size: 1.5rem;
        margin-bottom: 5px;
    }
    
    .stat-circle span {
        font-size: 1.3rem;
    }
    
    /* Hover */
    .stat-circle:hover {
        transform: scale(1.15);
        box-shadow: 0 12px 25px rgba(0,0,0,0.2);
    }
    
    /* Gradients */
    .bg-gradient-1 { background: linear-gradient(135deg, #F8A609, #FFD46B); }
    .bg-gradient-2 { background: linear-gradient(135deg, #312873, #6156B7); }
    .bg-gradient-3 { background: linear-gradient(135deg, #F8A609, #FFB74D); }
    .bg-gradient-4 { background: linear-gradient(135deg, #312873, #7E6CBF); }
    .bg-gradient-5 { background: linear-gradient(135deg, #F8A609, #FFD54F); }
    
    /* Responsive */
    @media(max-width: 991px){
        .profile-card { padding: 40px 25px; }
        .avatar-wrapper { width: 160px; height: 160px; }
        .profile-header h1 { font-size: 1.8rem; }
        .stat-circle { width: 100px; height: 100px; padding: 8px; }
        .stat-circle i { font-size: 1.3rem; }
        .stat-circle span { font-size: 1.1rem; }
        .stat-item { flex: 1 1 45%; }
    }
    @media(max-width: 575px){
        .profile-card { padding: 30px 20px; }
        .stat-circle { width: 90px; height: 90px; padding: 6px; }
        .stat-circle i { font-size: 1.2rem; }
        .stat-circle span { font-size: 1rem; }
        .stat-item { flex: 1 1 100%; }
    }

    /* الوضع الليلي */
body.dark {
    background: #121212;
    color: #E0E0E0;
}

/* Profile Card Dark Mode */
body.dark .profile-card {
    background: rgba(30,30,30,0.95);
    box-shadow: 0 25px 50px rgba(0,0,0,0.8);
}

/* Header Text */
body.dark .profile-header h1,
body.dark .profile-header h6 {
    color: #FFD700; /* لون بارز للنصوص المهمة */
}
body.dark .profile-header p {
    color: #CCCCCC;
}

/* Stat Circle Text */
body.dark .stat-circle span {
    color: #fff;
}
body.dark .stat-item h6 {
    color: #FFD700;
}

/* Gradients: يمكن المحافظة عليها كما هي أو تعديلها لتكون أكثر وضوحاً على خلفية داكنة */
.bg-gradient-1 { background: linear-gradient(135deg, #F8A609, #FFD46B); }
.bg-gradient-2 { background: linear-gradient(135deg, #312873, #6156B7); }
.bg-gradient-3 { background: linear-gradient(135deg, #F8A609, #FFB74D); }
.bg-gradient-4 { background: linear-gradient(135deg, #312873, #7E6CBF); }
.bg-gradient-5 { background: linear-gradient(135deg, #F8A609, #FFD54F); }

/* Avatar Border */
body.dark .avatar-wrapper {
    background: linear-gradient(135deg, #FFD700, #8C52FF);
}
body.dark .customer-avatar {
    border: 5px solid #1E1E1E; /* تناسق مع خلفية البطاقة الداكنة */
}

/* Hover Effects */
body.dark .stat-circle:hover {
    transform: scale(1.15);
    box-shadow: 0 12px 25px rgba(255, 255, 255, 0.2);
}
body.dark .profile-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 30px 60px rgba(255,255,255,0.15);
}

    </style>
    
    </x-master-layout>
    