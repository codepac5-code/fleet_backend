<x-master-layout>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-4 p-4">
                    <div class="text-center mb-4">
                        <img src="{{ $user->photo ? asset($user->photo) : asset('default-avatar.png') }}" 
                             alt="Avatar" class="rounded-circle user-avatar mb-3">
                        <h3 class="fw-bold" style="color:#312873;">{{ $user->firstName }} {{ $user->lastName }}</h3>
                        <p class="text-muted mb-0"><i class="fas fa-phone-alt text-primary"></i> {{ $user->phoneNumber }}</p>
                    </div>
    
                    <div class="row g-3 mt-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6>عدد الرحلات</h6>
                                <p class="info-number">{{ $user->totalBookings }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6>إجمالي المبلغ</h6>
                                <p class="info-number">{{ number_format($user->totalAmount,2) }} ر.س</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6>إجمالي المسافة</h6>
                                <p class="info-number">{{ $user->totalDistance }} كم</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6>آخر رحلة</h6>
                                <p class="info-number">{{ $user->lastBookingAt?->format('d/m/Y H:i') ?? 'لا يوجد' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6>متوسط التقييم</h6>
                                <p class="info-number">{{ round($user->averageRating,1) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6>حالة الدفع الأخيرة</h6>
                                <p class="info-number">{{ $user->lastPaymentStatus ?? 'لا يوجد' }}</p>
                            </div>
                        </div>
                    </div>
    
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .user-avatar {
        width: 140px;
        height: 140px;
        object-fit: cover;
        border: 5px solid #F8A609;
    }
    
    .info-card {
        background: #31287310; /* شفافية للون الأساسي */
        border-left: 5px solid #F8A609;
        padding: 15px;
        border-radius: 10px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    
    .info-card h6 {
        font-size: 0.9rem;
        color: #312873;
        margin-bottom: 5px;
    }
    
    .info-number {
        font-size: 1.2rem;
        font-weight: bold;
        color: #F8A609;
    }
    </style>
    
    </x-master-layout>
    