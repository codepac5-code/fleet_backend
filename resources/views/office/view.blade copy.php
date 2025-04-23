<x-master-layout>
    <form action="{{ route('office.destroy', $office->id) }}" method="POST" data--submit="office{{ $office->id }}">
        @csrf
        @method('DELETE')    
        <main class="main-area">
            <div class="main-content">
                <div class="container-fluid">
                    @include('partials._office')

                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <label for="startDate">{{ __('messages.start_date') }}</label>
                            <input type="date" id="startDate" name="startDate" class="form-control" value="{{ request()->startDate ?? now()->toDateString() }}">
                        </div>
                        <div class="col-lg-6">
                            <label for="endDate">{{ __('messages.end_date') }}</label>
                            <input type="date" id="endDate" name="endDate" class="form-control" value="{{ request()->endDate ?? now()->toDateString() }}">
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body p-30">
                            <div class="provider-details-overview mb-30">

                                <div class="provider-details-overview__collect-cash">
                                    <div class="statistics-card statistics-card__collect-cash h-100">
                                        <h3>{{ __('messages.collect_cash_Office') }}</h3>
                                    </div>
                                </div>

                                <div class="provider-details-overview__order-overview">
                                    <div class="statistics-card statistics-card__order-overview h-100 pb-2">
                                        <h3 class="mb-0">{{__('messages.booking_overview')}}</h3>
                                        @if($data['pendingStatusCount'] + $data['cancelledstatuscount'] + $data['Completedstatuscount'] + $data['Acceptedstatuscount'] > 0)
                                            <div id="chart" class="d-flex justify-content-center"></div>
                                        @else
                                            <p style="color:#009900; font-size:20px; font-style:italic; text-align:center; margin-top: 20%;">{{__('messages.nodata')}}</p>
                                        @endif
                                    </div>
                                </div>
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
                                                    <a href="tel: {{ $office->contactNumber }}" class="contact-info-text p-0">{{ !empty($office->contactNumber) ? $office->contactNumber : '-' }}</a>
                                                </li>
                                                <li>
                                                    <i class="ri-mail-line"></i>
                                                    <a href="mailto: {{ $office->email }}" class="contact-info-text p-0">{{ $office->email }}</a>
                                                </li>
                                                <li>
                                                    <i class="ri-map-2-line"></i>
                                                    <span class="contact-info-text">{{ !empty($office->address) ? $office->address : '-' }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </form>
    @section('bottom_script')

    <script type="text/javascript">
        var pendingCount = "{{ $data['pendingStatusCount'] }}";
        var cancelledCount = parseInt("{{ $data['cancelledstatuscount'] }}");
        var Completedcount = parseInt("{{ $data['Completedstatuscount'] }}");
        var Acceptedcount  = parseInt("{{ $data['Acceptedstatuscount'] }}");

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

        document.getElementById('startDate').addEventListener('change', updateData);
        document.getElementById('endDate').addEventListener('change', updateData);

        function updateData() {
            var startDate = document.getElementById('startDate').value;
            var endDate = document.getElementById('endDate').value;

            var url = "{{ route('office.show', ['office' => $office->id]) }}";
            var params = new URLSearchParams();

            if (startDate) params.append('startDate', startDate);
            if (endDate) params.append('endDate', endDate);

            fetch(`${url}?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    chart.updateOptions({
                        series: [data.pendingStatusCount, data.cancelledStatusCount, data.CompletedStatusCount, data.AcceptedStatusCount]
                    });
                });
        }
    </script>
    @endsection
</x-master-layout>
