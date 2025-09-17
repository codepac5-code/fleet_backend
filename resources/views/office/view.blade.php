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



    <form action="{{ route('office.destroy', $office->id) }}" method="POST" data--submit="office{{ $office->id }}">
        @csrf
        @method('DELETE') 
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


                    <div class="col-md-6">
                        <div class="new-stat-card office-card">
                            <span class="top-line"></span>
                            <span class="bottom-line"></span>
                            <div class="icon-wrapper">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="title">{{ __('messages.unpaid_fleet_dues') }}</div>
                            <div class="value" id="officeDues">{{ getPriceFormat($fleetDues) }}</div>
                        </div>
                    </div>


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
</form>



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