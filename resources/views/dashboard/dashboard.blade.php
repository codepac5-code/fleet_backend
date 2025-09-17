@php
$sitesetup = App\Models\Setting::where('type','site-setup')->where('key', 'site-setup')->first();
$datetime = $sitesetup ? json_decode($sitesetup->value) : null;
@endphp
<x-master-layout>


       <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet" />
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
          

        
       
            <script>
              document.addEventListener('DOMContentLoaded', () => {
                const sampleData = {
                  revenue: [2000, 2500, 1800, 2200, 2700, 3000, 3200],
                  withdrawalAvailable: [1500, 1800, 1600, 2000, 2300, 2100, 2500],
                  withdrawn: [1000, 1200, 1100, 1150, 1300, 1400, 1450],
                  officesOutstanding: [800, 900, 750, 700, 650, 600, 620],
                  driversOutstanding: [600, 650, 700, 720, 680, 630, 700]
                };
          
                function createChart(ctx, data, color) {
                  return new Chart(ctx, {
                    type: 'line',
                    data: {
                      labels: ['أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'],
                      datasets: [{
                        label: '',
                        data,
                        borderColor: color,
                        backgroundColor: color + '44',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 0,
                        borderWidth: 3,
                        hoverRadius: 5
                      }]
                    },
                    options: {
                      responsive: true,
                      maintainAspectRatio: false,
                      scales: {
                        x: { display: false },
                        y: { display: false }
                      },
                      plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                      }
                    }
                  });
                }
          
                createChart(document.getElementById('revenueChart').getContext('2d'), sampleData.revenue, '#28a745');
                createChart(document.getElementById('withdrawalChart').getContext('2d'), sampleData.withdrawalAvailable, '#007bff');
                createChart(document.getElementById('withdrawnChart').getContext('2d'), sampleData.withdrawn, '#ffc107');
                createChart(document.getElementById('officesChart').getContext('2d'), sampleData.officesOutstanding, '#dc3545');
                createChart(document.getElementById('driversChart').getContext('2d'), sampleData.driversOutstanding, '#e83e8c');
          
                document.getElementById('totalRevenue').textContent = '8,450.00 ر.س';
                document.getElementById('availableWithdrawal').textContent = '5,200.00 ر.س';
                document.getElementById('withdrawnAmount').textContent = '3,250.00 ر.س';
                document.getElementById('officesOutstanding').textContent = '1,850.00 ر.س';
                document.getElementById('driversOutstanding').textContent = '1,230.00 ر.س';
                document.getElementById('numEPayments').textContent = '215';
                document.getElementById('valEPayments').textContent = '12,500.00 ر.س';
                document.getElementById('numCashPayments').textContent = '120';
                document.getElementById('valCashPayments').textContent = '7,800.00 ر.س';
          
                document.querySelectorAll('.btn[data-range]').forEach(btn => {
                  btn.addEventListener('click', () => {
                  });
                });
              });
            </script>
          


  
    <div class="container-fluid p-4">
  
       
        

        
        <style>
            .NewCard {
                padding: 28px 20px;
                text-align: center;
                border-radius: 110px;
                transition: all 0.3s ease;
                color: #fff;
                position: relative;
                overflow: hidden;
                box-shadow: 0 6px 18px rgba(0,0,0,0.12);
                background: linear-gradient(135deg, #312873, #3e3499);
                cursor: pointer;
            }
            
            .NewCard .icon-badge {
                font-size: 36px;
                margin-bottom: 15px;
                transition: transform 0.3s ease, color 0.3s ease;
            }
            
            .NewCard:hover .icon-badge {
                transform: scale(1.15) rotate(3deg);
            }
            
            .NewCard .text-number {
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 6px;
                text-shadow: 0 1px 3px rgba(0,0,0,0.25);
                transition: color 0.3s ease;
            }
            
            .NewCard .text-title {
                font-size: 17px;
                font-weight: 600;
                opacity: 0.9;
                transition: color 0.3s ease;
            }
            
            .NewCard:hover .text-title,
            .NewCard:hover .text-number {
                color: #ffd75e;
            }
            
            .NewCard.electronic {
                background: linear-gradient(135deg, #312873, #4e3da6);
                color: #fff;
            }
            .NewCard.electronic .icon-badge,
            .NewCard.electronic .text-number {
                color: #F8A609;
            }
            .NewCard.electronic .text-title {
                color: #F8A609;
            }
            
            .NewCard.cash {
                background: linear-gradient(135deg, #F8A609, #f3b94d);
                color: #312873;
            }
            .NewCard.cash .icon-badge,
            .NewCard.cash .text-number {
                color: #312873; 
            }
            .NewCard.cash .text-title {
                color: #312873;
            }
            
            .NewCard.wallet {
                background: linear-gradient(135deg, #312873, #5c4bb0);
                color: #fff;
            }
            .NewCard.wallet .icon-badge,
            .NewCard.wallet .text-number {
                color: #F8A609;
            }
            .NewCard.wallet .text-title {
                color: #F8A609;
            }
            
            .NewCard.travel {
                background: linear-gradient(135deg, #F8A609, #e0a400);
                color: #312873;
            }
            .NewCard.travel .icon-badge,
            .NewCard.travel .text-number {
                color: #312873;
            }
            .NewCard.travel .text-title {
                color: #312873;
            }
            
   
            </style>
            
            

            <style>
                .filter-section {
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: center;
                    gap: 16px;
                    margin-bottom: 15px;
                }
                .filter-section label {
                    font-weight: 700;
                    font-size: 1rem;
                    align-self: center;
                    color: #312873;
                }
                .filter-section input[type="date"] {
                    padding: 8px 14px;
                    border-radius: 8px;
                    border: 1.5px solid #312873;
                    font-size: 1.05rem;
                    width: 170px;
                    transition: all 0.25s ease;
                    color: #312873;
                    background-color: #fff;
                }
                .filter-section input[type="date"]:focus {
                    border-color: #F8A609;
                    box-shadow: 0 0 6px rgba(248,166,9,0.6);
                    outline: none;
                }
                .filter-section button {
                    background-color: #F8A609;
                    border: none;
                    color: #fff;
                    font-weight: 600;
                    font-size: 1.1rem;
                    padding: 10px 22px;
                    border-radius: 12px;
                    cursor: pointer;
                    box-shadow: 0 5px 12px rgba(49,40,115,0.25);
                    transition: background-color 0.3s ease, transform 0.2s ease;
                    flex-shrink: 0;
                }
                .filter-section button:hover {
                    background-color: #d48d07;
                    transform: scale(1.07);
                }
                .reset-filter {
                    background-color: #312873;
                    margin-left: 10px;
                }
                .reset-filter:hover {
                    background-color: #221d55;
                    transform: scale(1.07);
                }
                .quick-filters {
                    display: flex;
                    justify-content: center;
                    gap: 16px;
                    margin-bottom: 20px;
                }
                .quick-filters button {
                    background: transparent;
                    border: 2px solid #312873;
                    color: #312873;
                    padding: 8px 20px;
                    border-radius: 24px;
                    font-weight: 600;
                    font-size: 1.05rem;
                    cursor: pointer;
                    transition: all 0.3s ease;
                }
                .quick-filters button:hover,
                .quick-filters button.active {
                    background-color: #312873;
                    color: #fff;
                    box-shadow: 0 5px 14px rgba(49,40,115,0.4);
                }
                
                /* 🌙 الوضع الليلي */
                body.dark .filter-section label,
                body.dark .filter-section input[type="date"],
                body.dark .filter-section button,
                body.dark .reset-filter,
                body.dark .quick-filters button {
                    color: #F8A609;
                    border-color: #F8A609;
                }
                
                body.dark .filter-section input[type="date"] {
                    background-color: #1e163e;
                    color: #F8A609;
                }
                
                body.dark .filter-section button {
                    background-color: #F8A609;
                    color: #1e163e;
                    box-shadow: 0 5px 12px rgba(248,166,9,0.3);
                }
                
                body.dark .filter-section button:hover {
                    background-color: #ffd75e;
                    color: #312873;
                }
                
                body.dark .reset-filter {
                    background-color: #1e163e;
                    color: #F8A609;
                }
                
                body.dark .reset-filter:hover {
                    background-color: #312873;
                    color: #fff;
                }
                
                body.dark .quick-filters button {
                    background: transparent;
                    border: 2px solid #F8A609;
                    color: #F8A609;
                }
                
                body.dark .quick-filters button:hover,
                body.dark .quick-filters button.active {
                    background-color: #F8A609;
                    color: #1e163e;
                    box-shadow: 0 5px 14px rgba(248,166,9,0.4);
                }
                </style>
                

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

.stat-info-Y h3 {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  font-size: 1.3rem;
  font-weight: 700;
  letter-spacing: 0.03em;
  margin-bottom: 10px;
  color: #F8A609;
  text-shadow: 0 1px 3px rgba(0,0,0,0.3);
  transition: color 0.3s ease;
}

.stat-info-B h3 {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  font-size: 1.3rem;
  font-weight: 700;
  letter-spacing: 0.03em;
  margin-bottom: 10px;
  color: #312873;
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

.stat-value-Y {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  font-size: 2rem;
  font-weight: 500;
  letter-spacing: 0.05em;
  color: #F8A609;
  text-shadow: 0 2px 3px rgba(0,0,0,0.4);
  line-height: 1.1;
  transition: color 0.3s ease;
}

.stat-value-B {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  font-size: 2rem;
  font-weight: 500;
  letter-spacing: 0.05em;
  color: #312873;
  text-shadow: 0 2px 3px rgba(0,0,0,0.4);
  line-height: 1.1;
  transition: color 0.3s ease;
}

.stat-value:hover {
  color: #ffa500;
}


/* .stat-card.revenue {
      background: linear-gradient(135deg, #312873, #4640a1);
    } */
    
    .stat-card.withdrawal-available {
        background: linear-gradient(135deg, #312873, #F8A609);
    }
    
    .stat-card.cash-payments {
      background: linear-gradient(135deg, #312873, #F8A609);
    }
    
    /* .stat-card.offices-outstanding {
      background: linear-gradient(135deg, #F8A609, #312873);
    } */
    
                            .stat-card.revenue {
                                background: linear-gradient(135deg, #312873, #3f348fc7);
                                /* color:forestgreen */
                              /* background: linear-gradient(135deg, #0052cc, #4c8cff); */
                            }
                            /* .stat-card.withdrawal-available {
                              background: linear-gradient(135deg, #1a7f37, #55bb6a);
                            }
                            .stat-card.cash-payments {
                              background: linear-gradient(135deg, #d35f00, #e69122);
                            } */
                            .stat-card.offices-outstanding {
                              background: linear-gradient(135deg, #F8A609, #f3b94dbe);
                            }
                        
                          
                            canvas {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    width: 100% !important;
    height: 60px !important;
    max-height: 60px;
    pointer-events: none;
    opacity: 0.85;
    filter: brightness(1.05);
    border-radius: 0 0 14px 14px;
  }

                          </style>

                          
                        </head>
                        <body>
                         
                        
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <a  href="{{ route('office.index') }}">
                                <div class="taxi-card">
                                    <div class="taxi-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="taxi-info">
                                        <h3>{{__('messages.offices')}}</h3>
                                        <div class="d-flex gap-2">
                                        <p id="offices">{{$data['dashboard']['count_total_office'] }}</p>
                                        <p>{{' '.__('messages.office')}}</p>
                                    </div>
                                    </div>
                                </div>
                        </a>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <a href="{{ route('user.index') }}">
                                <div class="taxi-card">
                                    <div class="taxi-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="taxi-info">
                                        <h3>{{__('messages.users')}}</h3>
                                        <div class="d-flex gap-2">
                                        <p id="users">{{$data['dashboard']['count_total_user'] }}</p>
                                        <p>{{' '.__('messages.user')}}</p>
                                    </div>
                                    </div>
                                </div>
                        </a>
                    </div>



                    
                    <div class="col-lg-3 col-md-6">
                        <a  href="{{ route('service.index') }}">
                                <div class="taxi-card">
                                    <div class="taxi-icon">
                                        <i class="fas fa-handshake"></i> 
                                    </div>
                                    <div class="taxi-info">
                                        <h3>{{__('messages.services')}}</h3>
                                        <div class="d-flex gap-2">
                                        <p id="services">{{$data['dashboard']['count_total_service']}}</p>
                                        <p>{{' '.__('messages.service')}}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <a  href="{{ route('driver.index') }}"> 
                            <div class="taxi-card">
                                <div class="taxi-icon">
                                    <i class="fas fa-taxi"></i>
                                   </div>
                                <div class="taxi-info">
                                    <h3>{{__('messages.drivers')}}</h3>
                                    <div class="d-flex gap-2">
                                    <p id="drivers">{{$data['dashboard']['count_total_driver']}}</p> 
                                    <p>{{' '.__('messages.driver')}}</p>
                                   </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>    
            </div>
        </div>
    </div>

 





        <div class="col-md-12 mt-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h4 class="">{{__('messages.monthly_revenue')}}</h4>
                    </div>
                    <div id="monthly-revenue" class="custom-chart"></div>
                </div>
            </div>
        </div>


            <div class="col-md-12 mt-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-start align-items-center"> 
                            <div class="event-icon-wrapper">
                                <i class="fas fa-chart-line" style="font-size: 24px; color: white; animation: bounceUp 2s infinite;"></i>
                            </div>
                            <div class="col-md-6"> 
                                <h4> {{ __('messages.wallet_status_and_dues') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-section">
                <div class="dashboard-title">{{ __('messages.wallet') }}</div>
            
                <div class="row">
                    <div class="col-md-6">
                        <article class="stat-card withdrawal-available">
                            <div class="stat-icon">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <div class="stat-info">
                                <h3>{{ __('messages.current_wallet_balance') }}</h3>
                                <p class="stat-value" id="availableWithdrawal"> {{ getPriceFormat($walletBalance) }}</p>
                            </div>
                            <canvas id="withdrawalChart"></canvas>
                        </article>
                    </div>
            
                    <div class="col-md-6">
                        <article class="stat-card cash-payments">
                            <div class="stat-icon">
                                <i class="bi bi-credit-card-2-front"></i>
                            </div>
                            <div class="stat-info">
                                <h3>{{ __('messages.pending_amount') }}</h3>
                                <p class="stat-value" id="withdrawnAmount"> {{ getPriceFormat($pendingAmount) }}</p>
                            </div>
                            <canvas id="withdrawnChart"></canvas>
                        </article>
                    </div>
                </div>
            </div>
            
            <div class="col-12">
                <div class="horizontal-separator"></div>
            </div>
            
            <div class="dashboard-section">
                <div class="dashboard-title">{{ __('messages.dues') }}</div>
            
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="new-stat-card driver-card">
                            <span class="top-line"></span>
                            <span class="bottom-line"></span>
                            <div class="icon-wrapper">
                                <i class="fas fa-taxi"></i>
                            </div>
                            <div class="title">{{ __('messages.unpaid_driver_dues') }}</div>
                            <div class="value" id="driverDues">{{ getPriceFormat($driverDues) }}</div>
                        </div>
                    </div>
            

                    
                    <div class="col-md-6">
                        <div class="new-stat-card office-card">
                            <span class="top-line"></span>
                            <span class="bottom-line"></span>
                            <div class="icon-wrapper">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="title">{{ __('messages.unpaid_office_dues') }}</div>
                            <div class="value" id="officeDues">{{ getPriceFormat($officeDues) }}</div>
                        </div>
                    </div>

                    


                </div>
            </div>
            
              
              <script>
                function fetchWalletStats() {
                  fetch('/api/wallet-stats')
                    .then(res => res.json())
                    .then(data => {
                    //   const formatCurrency = value => value;
              
                      document.getElementById("availableWithdrawal").textContent = data.walletBalance;
                      document.getElementById("withdrawnAmount").textContent = data.pendingAmount;
                      document.getElementById("driverDues").textContent = data.driverDues;
                      document.getElementById("officeDues").textContent = data.officeDues;
                    })
                    .catch(error => {
                      console.error("error :", error);
                    });
                }
              
                document.addEventListener("DOMContentLoaded", () => {
                  fetchWalletStats();
                  setInterval(fetchWalletStats, 30000);
                });
              </script>
              
              <div class="col-12">
                <div class="horizontal-separator"></div>
            </div>

            <div class="dashboard-container" style="padding-top: 70px;">

                <div class="quick-filters" role="group" aria-label="{{ __('messages.quick_filters') }}">
                    <button onclick="setDateRange('today')" id="btn-today">{{ __('messages.today') }}</button>
                    <button onclick="setDateRange('week')" id="btn-week">{{ __('messages.this_week') }}</button>
                    <button onclick="setDateRange('month')" id="btn-month">{{ __('messages.this_month') }}</button>
                    <button onclick="setDateRange('year')" id="btn-year">{{ __('messages.this_year') }}</button>
                </div>
                  
                <div class="filter-section">
                    <label for="startDate">{{ __('messages.from_date') }}</label>
                    <input type="date" id="startDate" />
                    <label for="endDate">{{ __('messages.to_date') }}</label>
                    <input type="date" id="endDate" />
                    <button onclick="applyCustomFilter()">{{ __('messages.apply_filter') }}</button>
                    <button class="reset-filter" onclick="resetFilter()">{{ __('messages.reset_filter') }}</button>
                </div>
            
                <div id="filterMessage" style="display:none; position: relative; color: #571515; background-color: #edd4d4; border: 1px solid #e6c3c3; padding: 10px 40px 10px 10px; border-radius: 5px; margin-top: 10px; font-weight: bold;">
                    <span id="filterMessageText"></span>
                    <button id="closeFilterMessage" style="padding-bottom: 80px; position: absolute; top: 5px; right: 5px; background: transparent; border: none; font-size: 17px; font-weight: bold; color: #571515; cursor: pointer;">×</button>
                </div>
            
                <div class="dashboard-section">
                    <div class="dashboard-title mb-4 fs-4 fw-bold">{{ __('messages.overview') }}</div>
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="NewCard electronic">
                                <div class="icon-badge">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div class="text-number">0</div>
                                <div class="text-title">{{ __('messages.electronic_payments_count') }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="NewCard cash">
                                <div class="icon-badge">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="text-number">0</div>
                                <div class="text-title">{{ __('messages.cash_payments_count') }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="NewCard wallet">
                                <div class="icon-badge">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <div class="text-number">0</div>
                                <div class="text-title">{{ __('messages.wallet_payments_count') }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="NewCard travel">
                                <div class="icon-badge">
                                    <i class="fas fa-route"></i>
                                </div>
                                <div class="text-number">0</div>
                                <div class="text-title">{{ __('messages.ride_count') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            
                <div class="col-12">
                    <div class="horizontal-separator"></div>
                </div>
            
                <div class="dashboard-section">
                    <div class="dashboard-title">{{ __('messages.financial_data') }}</div>
            
                    <div class="row">
                        <div class="col-md-6">
                            <article class="stat-card revenue">
                                <div class="stat-icon">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                                <div class="stat-info-Y">
                                    <h3>{{ __('messages.total_revenue') }}</h3>
                                    <p class="stat-value-Y" id="totalRevenue">0.00</p>
                                </div>
                                <canvas id="revenueChart"></canvas>
                            </article>
                        </div>
            
                        <div class="col-md-6">
                            <article class="stat-card offices-outstanding">
                                <div class="stat-icon">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                                <div class="stat-info-B">
                                    <h3>{{ __('messages.withdrawn_from_wallet') }}</h3>
                                    <p class="stat-value-B" id="officesOutstanding">0.00 </p>
                                </div>
                                <canvas id="officesChart"></canvas>
                            </article>
                        </div>
                    </div>
                </div>
            
            </div>
            

<div class="col-12">
    <div class="horizontal-separator"></div>
</div>

<script>

    
function applyCustomFilter() {
    const start = document.getElementById("startDate").value;
    const end = document.getElementById("endDate").value;
    const messageDiv = document.getElementById("filterMessage");
    const messageText = document.getElementById("filterMessageText");

    if (!start || !end) {
        alert("يرجى تحديد التاريخ أولاً.");
        return;
    }

    fetchDashboardStats(start, end);

    window.translations = {
        filterMessage: @json(__('messages.filter_message')),
    };

    let msgTemplate = window.translations.filterMessage;

    let finalMessage = msgTemplate
    .replace(':start', start)
    .replace(':end', end);

    messageText.textContent = finalMessage;
    // messageText.textContent = `أنت تستعرض البيانات من ${start} إلى ${end}`;
    messageDiv.style.display = "block";
}

document.addEventListener("DOMContentLoaded", () => {
    const closeBtn = document.getElementById("closeFilterMessage");
    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            document.getElementById("filterMessage").style.display = "none";
        });
    }
});

function setDateRange(period) {
    const today = new Date();
    let startDate, endDate = new Date();

    switch (period) {
        case 'today':
            startDate = new Date(today);
            break;
        case 'week':
            startDate = new Date(today);
            startDate.setDate(today.getDate() - today.getDay());
            break;
        case 'month':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            break;
        case 'year':
            startDate = new Date(today.getFullYear(), 0, 1);
            break;
    }

    document.getElementById("startDate").value = formatDate(startDate);
    document.getElementById("endDate").value = formatDate(endDate);

    document.querySelectorAll(".quick-filters button").forEach(btn => btn.classList.remove("active"));
    document.getElementById(`btn-${period}`).classList.add("active");
}

function formatDate(date) {
    return date.toISOString().split("T")[0];
}

function fetchDashboardStats(startDate, endDate) {
    const params = new URLSearchParams({ start_date: startDate, end_date: endDate });

    fetch(`/api/dashboard-stats?${params.toString()}`, {
    headers: {
        'Accept': 'application/json'
    }
}) 
        .then(res => res.json())
        .then(data => {
            
            document.querySelector(".electronic .text-number").textContent = data.electronicPayments;
            document.querySelector(".cash .text-number").textContent = data.cashPayments;
            document.querySelector(".wallet .text-number").textContent = data.walletPayments;
            document.querySelector(".travel .text-number").textContent = data.trips;

            document.getElementById("totalRevenue").textContent =data.totalRevenue;
            document.getElementById("officesOutstanding").textContent =data.walletWithdrawn;
        })
        .catch(error => console.error("erorr: ", error));
}

function resetFilter() {
    setDateRange('today');
    applyCustomFilter();
}

document.addEventListener("DOMContentLoaded", () => {
    setDateRange('today'); 
    applyCustomFilter();  
});
</script>

    


<script>
    function filterBy(period) {
        const today = new Date();
        let startDate, endDate;
    
        switch (period) {
            case 'today':
                startDate = endDate = today.toISOString().split('T')[0];
                break;
            case 'week':
                const startOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
                startDate = startOfWeek.toISOString().split('T')[0];
                endDate = new Date().toISOString().split('T')[0];
                break;
            case 'month':
                const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                startDate = startOfMonth.toISOString().split('T')[0];
                endDate = new Date().toISOString().split('T')[0];
                break;
            case 'year':
                const startOfYear = new Date(today.getFullYear(), 0, 1);
                startDate = startOfYear.toISOString().split('T')[0];
                endDate = new Date().toISOString().split('T')[0];
                break;
        }
    
        fetchDashboardStats(startDate, endDate);
    }
    </script>
    









            <div class="col-md-12 mt-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-start align-items-center"> 
                            <div class="event-icon-wrapper">
                                <svg width="24" height="24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11" stroke="white" stroke-width="2" fill="none"/>
                                    <circle cx="12" cy="12" r="7" stroke="white" stroke-width="2" fill="none" stroke-dasharray="56,56" stroke-dashoffset="0">
                                        <animate attributeName="stroke-dashoffset" from="0" to="112" dur="1.5s" repeatCount="indefinite"/>
                                    </circle>
                                </svg>   
                                
                                
                                
                            </div>
                            <div class=" col-md-6 "> 
                                <h4 > {{__('messages.live_events')}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
    




                    

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDEutBE5WTbVYGM4uw58MrkdsfX1othIoQ&callback=initMap" async defer></script>


<div class="map-container">
    <div class="map-header">
        <div class="text-center p-1" style="color: #ffffff; font-size: 19px; font-weight: 700; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <i class="fas fa-taxi"></i>
            {{ __('messages.live_driver_locations') }}</div>
        <div class="refresh-container" onclick="refreshMap()">
            <div class="refresh-icon"></div>
        </div>
    </div>
    <div id="map"></div>
    <div class="map-footer">
        <span id="last-update">{{ __('messages.last_update').': ' }}  --/--/---- --:--:--</span>
    </div>
</div>

<style>
    .map-container {
        width: 100%;
        max-width: 900px;
        margin: 20px auto;
        border-radius: 12px;
        overflow: hidden;
        background: linear-gradient(135deg, #dda50b, #ff9966); 
        box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
        border: 2px solid #dda50b;
    }

    .map-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 18px;
        background: #dda50b;
        color: #222;
        font-weight: bold;
        font-size: 16px;
        border-bottom: 3px solid #dda50b;
        border-radius: 10px 10px 0 0;
    }

    .map-header h2 {
        margin: 0;
        font-size: 20px;
        color: #333;
        font-family: 'Poppins', sans-serif; 
    }

    
    .refresh-container {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fdfdfa;
        border-radius: 50%;
        cursor: pointer;
        box-shadow: 0px 4px 10px rgba(255, 204, 0, 0.4);
        transition: 0.3s;
    }

    .refresh-icon {
        width: 20px;
        height: 20px;
        border: 4.2px solid transparent;
        border-top-color: #F8A609;
        border-radius: 50%;
        animation: rotate 1.5s linear infinite;
    }

    .refresh-container:active {
        transform: scale(0.9);
    }

    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    #map {
        width: 100%;
        height: 500px;
        border-radius: 0 0 10px 10px;
    }

    .map-footer {
        text-align: center;
        padding: 8px;
        font-size: 17px;
        color: #fff;
        background: #ff9966; 
        border-radius: 0 3 10px 10px;
    }

    .driver-info {
        font-size: 14px;
        font-weight: bold;
        color: #333;
        background: #fff;
        padding: 8px;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    }

</style>

<script>
    var map;
    var markers = {};

    function initMap() {
        map = new google.maps.Map(document.getElementById("map"), {
            center: { lat: 33.51389 , lng: 36.27639 },
            zoom: 10
        });

        fetchDriverLocations(); 
        setInterval(fetchDriverLocations, 300000); 
    }

    function fetchDriverLocations() {
    fetch("{{ route('live-drivers-locations') }}")
        .then(response => response.json())
        .then(data => {
            const updatedDriverIds = new Set();

            data.forEach(driver => {
                let driverId = driver.driver_id;
                let position = new google.maps.LatLng(parseFloat(driver.latitude), parseFloat(driver.longitude));
                updatedDriverIds.add(driverId);

                if (markers[driverId]) {
                    markers[driverId].setPosition(position);
                } else {
                    let infoWindow = new google.maps.InfoWindow({
                        content: `<div class="driver-info">
                            <strong>👤 ${driver.name}</strong><br>
                            📞 ${driver.phoneNumber}<br>
                            🚗 ${driver.carBrand} - ${driver.carNumber}
                        </div>`,
                    });

                    let marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        icon: {
                            url: "storage/system/images/map/driver_map_marker.png",
                            scaledSize: new google.maps.Size(85, 120),
                        },
                        title: `🚖{{ __('messages.driver_number')}}: ${driverId}`,
                    });

                    marker.addListener("mouseover", () => infoWindow.open(map, marker));
                    marker.addListener("mouseout", () => infoWindow.close());

                    markers[driverId] = marker;
                }
            });

            for (let id in markers) {
                if (!updatedDriverIds.has(id)) {
                    markers[id].setMap(null); 
                    delete markers[id];       
                }
            }

            updateLastUpdatedTime();
        })
        .catch(error => console.error("error:", error));
}



    function refreshMap() {
        fetchDriverLocations();
    }

    function updateLastUpdatedTime() {
        let now = new Date();
        let formattedTime = now.toLocaleDateString() + " " + now.toLocaleTimeString();
        document.getElementById("last-update").innerText = "{{ __('messages.last_update') }}" +':  '+ formattedTime;
    }

</script>
            <div class="container d-flex justify-content-center">
                <div class="trip-status-container">
                    <div class="trip-card pending">
                        <div class="trip-icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="trip-info">
                            <h3>{{ __('messages.pending_orders') }}</h3>
                            <p id="pending-ride">{{ $data['system_pending_rides'] }} {{ __('messages.order') }}</p>
                        </div>
                    </div>
    
            
                    <div class="trip-card ongoing">
                        <div class="trip-icon">
                            <i class="fas fa-car-side"></i>
                        </div>
                        <div class="trip-info">
                            <h3>{{ __('messages.ongoing_rides') }}</h3>
                            <p id="ongoing-ride">{{ $data['system_ongoing_rides'] }} {{ __('messages.ride') }}</p>
                        </div>
                    </div>
            
    
                    <div class="trip-card finished">
                        <div class="trip-icon">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <div class="trip-info">
                            <h3>{{ __('messages.completed_rides') }}</h3>
                            <p id="completed-ride">{{ $data['system_completed_rides'] }} {{ __('messages.ride') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            




       
            <div class="col-12">
                <div class="horizontal-separator"></div>
                    </div>
            















            















            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="card top-providers">
                        <div class="card-header d-flex justify-content-between gap-10">
                            <h4 class="font-weight-bold">{{ __('messages.recent_Office') }}</h4>
                            <a href="{{ route('office.index') }}" class="btn-link btn-link-hover"><u>{{__('messages.view_all')}}</u></a>
                        </div>
                        <div class="card-body p-0">
                            <ul class="common-list list-unstyled">
                                @foreach($offices as $office)
                                <li style="pointer-events:none;">
                                    <div class="media gap-3">
                                        <div class="h-avatar is-medium h-5">
                                            <img class="avatar-50 rounded-circle bg-light" alt="user-icon" src="{{ $office->logo }}">
                                        </div>
                                        <div class="media-body ">
                                            <h5><span class="font-weight-bold">{{!empty($office->officeName) ? $office->officeName : '-'}}</span> </h5>
                                            <span class="common-list_rating d-flex gap-1">
                                                <i class="ri-star-s-fill"></i>
                                                {{round(3.2, 1)}}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            
                <div class="col-md-4 col-sm-6">
                    <div class="card top-providers">
                        <div class="card-header d-flex justify-content-between gap-10">
                            <h4 class="font-weight-bold">{{ __('messages.recent_customer') }}</h4>
                            <a href="{{ route('user.index') }}" class="btn-link btn-link-hover"><u>{{__('messages.view_all')}}</u></a>
                        </div>
                        <div class="card-body p-0">
                            <ul class="common-list list-unstyled">
                                @foreach($users as $customer) 
                                <li style="pointer-events:none;">
                                    <div class="media gap-3">
                                        <div class="h-avatar is-medium h-5">
                                            <img class="avatar-50 rounded-circle bg-light" alt="user-icon" src="{{ $customer->photo }}">
                                        </div>
                                        <div class="media-body ">
                                            <h5><span class="font-weight-bold">{{!empty($customer->firstName) ? $customer->firstName .' '.$customer->lastName : '-'}}</span>  </h5>
                                            <span>{{
                                                optional($datetime)->date_format && optional($datetime)->time_format
                                                ? date(optional($datetime)->date_format, strtotime($customer->created_at)) . ' / ' . date(optional($datetime)->time_format, strtotime($customer->created_at))
                                                : ''
                                            }}</span>
                                        </div>
                                    </div>
                                </li>
                                @endforeach 
                            </ul>
                        </div>
                    </div>
                </div>
            
                <div class="col-md-4 col-sm-6">
                    <div class="card recent-activities">
                        <div class="card-header d-flex justify-content-between gap-10">
                            <h4>{{__('messages.recent_booking')}}</h4>
                            <a href="{{ route('booking.index') }}" class="btn-link btn-link-hover"><u>{{__('messages.view_all')}}</u></a>
                        </div>
                        <div class="card-body">
                            <ul class="common-list p-0">
                                @foreach($orders as $booking)
                                <li class="d-flex flex-wrap gap-2 align-items-start align-items-lg-center justify-content-between flex-column flex-lg-row"  style="pointer-events:none;">
                                    <div class="media align-items-center gap-3">
                                        <div class="h-avatar is-medium h-5">
                                            <img class="avatar-50 rounded-circle bg-light" alt="user-icon" src="{{ $booking->user->photo }}">
                                        </div>
                                        <div class="media-body ">
                                            <h5>#{{$booking->id}}</h5>
                                            <span>{{
                                                optional($datetime)->date_format && optional($datetime)->time_format
                                                ? date(optional($datetime)->date_format, strtotime($booking->date)) . ' / ' . date(optional($datetime)->time_format, strtotime($booking->start))
                                                : ''
                                            }}</span>    
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill py-2 px-3 badge-pending text-capitalize">{{$booking->status}}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            
   
<div id="data-container" data-revenue='<?php echo json_encode($data['revenueData']); ?>'></div>

<div id="monthly-revenue"></div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var container = document.getElementById('data-container');
        var revenueData = JSON.parse(container.getAttribute('data-revenue'));
        
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        if (document.querySelector('#monthly-revenue')) {
            var options = {
                series: [{
                    name: "{{ __('messages.revenue') }}",
                    data: revenueData
                }],
                chart: {
                    height: 300,
                    type: 'line',
                    toolbar: { show: true },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: { enabled: true, delay: 150 },
                        dynamicAnimation: { enabled: true, speed: 350 }
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                    colors: ['#f5b041'] 
                },
                markers: {
                    size: 5,
                    colors: ['#f39c12'],
                    strokeWidth: 2,
                    hover: { size: 7 }
                },
                xaxis: {
                    categories: months,
                    labels: { style: { fontSize: '13px', fontWeight: 'bold', colors: '#aaa' } }
                },
                yaxis: {
                    labels: { style: { fontSize: '12px', fontWeight: 'bold', colors: '#aaa' } },
                    title: { text: '', style: { fontSize: '14px', fontWeight: 'bold', color: '#666' } }
                },
                tooltip: {
                    theme: 'dark',
                    y: { formatter: function(val) { return 'AED' + val.toLocaleString(); } }
                },
                grid: { borderColor: '#ddd', strokeDashArray: 5 }
            };

            var chart = new ApexCharts(document.querySelector("#monthly-revenue"), options);
            chart.render();
        }
    });
</script>


    
{{-- <script>
    document.addEventListener("DOMContentLoaded", function () {
        function fetchStatistics() {
            fetch("{{ route('home.statistics') }}")
                .then(response => response.json())
                .then(data => {

                    document.getElementById("offices").textContent  = data.count_total_office;
                    document.getElementById("users").textContent    = data.count_total_user;
                    document.getElementById("services").textContent = data.count_total_service;
                    document.getElementById("drivers").textContent  = data.count_total_driver;
    
                    document.getElementById("withdrawn-amount").textContent = data["withdrawn-amount"];
                    document.getElementById("available-amount").textContent = data["available-amount"];
                    document.getElementById("pending-amount").textContent   = data["pending-amount"] ?? "0.00";
                    document.getElementById("total-amount").textContent     = data["total-amount"];
    
                    document.getElementById("office-due-amount").textContent = data["offices-due-amount"];
                    document.getElementById("driver-due-amount").textContent = data["drivers-due-amount"];
    
                    document.getElementById("completed-ride").textContent = `${data.system_completed_rides} {{ __('messages.ride') }}`;
                    document.getElementById("ongoing-ride").textContent   = `${data.system_ongoing_rides} {{ __('messages.ride') }}`;
                    document.getElementById("pending-ride").textContent   = `${data.system_pending_rides} {{ __('messages.order') }}`;
                })
                .catch(error => console.error("Error fetching statistics:", error));
        }
    
        // fetchStatistics();
    
        // setInterval(fetchStatistics, 5 * 60 * 1000);
        setInterval(fetchStatistics, 6* 1000);

    });
    </script> --}}
    


<style>
    @keyframes bounceUp {
        0% {
            transform: translateY(0);
        }
        25% {
            transform: translateY(-10px);
        }
        50% {
            transform: translateY(0);
        }
        75% {
            transform: translateY(-5px);
        }
        100% {
            transform: translateY(0);
        }
    }
    </style>

<script>


    socket.on('admins:admin-satistic', (data) => {
    console.log('Received message:', data);

    const currentAmountElement = document.getElementById(data.name);
    let currentAmount = currentAmountElement.textContent.replace('$', '').replace(',', '');
   currentAmount = parseFloat(currentAmount);

    
    const newAmount = parseFloat(data.value);

    const updatedAmount = newAmount;

    currentAmountElement.textContent = `${updatedAmount.toLocaleString()}`;
});


</script>





</x-master-layout>
