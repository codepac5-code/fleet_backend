
<x-master-layout>
<style>
    body {
        background-color: #f0f4f8;
        font-family: 'Segoe UI', sans-serif;
    }
    .card {
        border-radius: 1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    .card:hover {
        transform: scale(1.02);
    }
    .section-title {
        font-weight: bold;
        margin-bottom: 1rem;
    }
    .section-divider {
        border-top: 2px solid #ccc;
        margin: 2rem 0;
    }
    .dashboard-section {
        background-color: #fff;
        border-radius: 1rem;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .dashboard-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1rem;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 0.5rem;
    }
</style>
  
    <div class="container-fluid p-4">
        {{-- <div class="row mb-4">
            <div class="col">
                <h1 class="text-primary">لوحة التحكم</h1>
                <p class="text-muted">نظرة شاملة على أداء مزود خدمة الإنترنت</p>
            </div>
        </div> --}}

        
        <div class="dashboard-section">
            <div class="dashboard-title">نظرة عامة</div>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card p-3 text-center animate__animated animate__fadeInUp">
                        <div class="h4"><i class="fas fa-users text-primary"></i> 700000</div>
                        <div>عدد المشتركين</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 text-center animate__animated animate__fadeInUp">
                        <div class="h4"><i class="fas fa-file-invoice-dollar text-warning"></i> 700000</div>
                        <div>مشتركين لديهم فواتير</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 text-center animate__animated animate__fadeInUp">
                        <div class="h4"><i class="fas fa-network-wired text-success"></i> 700000</div>
                        <div>علب الإنترنت</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 text-center animate__animated animate__fadeInUp">
                        <div class="h4"><i class="fas fa-check-circle text-info"></i> 700000</div>
                        <div>اشتراكات نشطة</div>
                    </div>
                </div>
            </div>
        </div>

<div class="dashboard-section">
    <div class="dashboard-title">البيانات المالية @if(request('start_date') && request('end_date')) (من {{ request('start_date') }} إلى {{ request('end_date') }}) @endif</div>
    <div class="dashboard-title">
    <form method="GET" action="/dashboard">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="start_date" class="form-label">من تاريخ</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">إلى تاريخ</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> عرض الإحصائيات</button>
            </div>
        </div>
    </form>
</div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card p-3 text-center bg-light">
                <div class="h4 text-success">700000 <i class="fas fa-arrow-up"></i></div>
                <div>الإيرادات</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center bg-light">
                <div class="h4 text-danger">700000 <i class="fas fa-arrow-down"></i></div>
                <div>المدفوعات</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center bg-light">
                <div class="h4 text-primary">700000 <i class="fas fa-coins"></i></div>
                <div>الأرباح</div>
            </div>
        </div>
    </div>
</div>

    {{-- <div class="dashboard-section">
            <div class="dashboard-title">مخططات</div>

            <div class="row align-items-start">
                <div class="col-md-7 mb-4">
                    <div class="dashboard-subtitle" style="font-size: 1.25rem; font-weight: 600; border-bottom: 2px solid #1E90FF; padding-bottom: 6px; margin-bottom: 12px;">
                        نسبة الاشتراكات اليومية
                    </div>
                    <div id="subscriptionsChart" style="min-height: 400px; background: #fff; border-radius: 8px; padding: 10px; box-shadow: 0 3px 15px rgb(0 0 0 / 0.05);"></div>
                </div>

                <div class="col-md-5 mb-4">
                    <div class="dashboard-subtitle" style="font-size: 1.25rem; font-weight: 500; border-bottom: 2px solid #1E90FF; padding-bottom: 6px; margin-bottom: 12px;">
                        تحليل الباقات
                    </div>
                    <canvas id="packageChart" style="background: #fff; border-radius: 8px; padding: 10px; box-shadow: 0 3px 15px rgb(0 0 0 / 0.05); width: 100%; height: 400px;"></canvas>
                </div>
            </div>
        </div>

     
    </div>
@endsection

@section('scripts')
<script>
    const ctx = document.getElementById('packageChart').getContext('2d');
    const packageChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['1 ميغا', '2 ميغا', '3 ميغا'],
            datasets: [{
                label: 'عدد المشتركين',
                data: [{{ $oneMbCount }}, {{ $twoMbCount }}, {{ $threeMbCount }}],
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

<script>
    const options = {
        chart: {
            type: 'line',
            height: 400,
            zoom: {
                enabled: true,
                type: 'x',
                autoScaleYaxis: true
            },
            toolbar: {
                show: true,
                tools: {
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true,
                }
            }
        },
        series: [{
            name: 'نسبة الاشتراكات',
            data: [
                ['2025-07-01', 20],
                ['2025-07-02', 35],
                ['2025-07-03', 40],
                ['2025-07-04', 25],
                ['2025-07-05', 50],
                ['2025-07-06', 45],
                ['2025-07-07', 60],
                ['2025-07-08', 70],
                ['2025-07-09', 65],
                ['2025-07-10', 80],
            ]
        }],
        xaxis: {
            type: 'datetime',
            labels: {
                format: 'dd MMM',
                rotate: -45,
                style: {
                    colors: '#666',
                    fontSize: '12px'
                }
            },
            tooltip: {
                enabled: true,
                formatter: function(val) {
                    return new Date(val).toLocaleDateString('ar-EG', { day: 'numeric', month: 'short', year: 'numeric' });
                }
            }
        },
        yaxis: {
            title: {
                text: 'النسبة (%)',
                style: {
                    color: '#555',
                    fontWeight: 'bold'
                }
            },
            min: 0,
            max: 100,
            labels: {
                formatter: val => val + '%'
            }
        },
        tooltip: {
            shared: true,
            x: {
                format: 'dd MMM yyyy'
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        markers: {
            size: 5,
            hover: {
                size: 7
            }
        },
        colors: ['#1E90FF'],
        grid: {
            borderColor: '#eee',
            row: {
                colors: ['#fafafa', 'transparent'],
                opacity: 0.7
            }
        },
        responsive: [{
            breakpoint: 600,
            options: {
                chart: {
                    height: 350
                },
                xaxis: {
                    labels: {
                        rotate: 0
                    }
                }
            }
        }]
    };
    const chart = new ApexCharts(document.querySelector("#subscriptionsChart"), options);
    chart.render();
</script>

 --}}
</x-master-layout>
