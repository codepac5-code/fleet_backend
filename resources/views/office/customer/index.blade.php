<x-master-layout>
    <head>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    </head>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ __('messages.customers') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-striped border"></table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.renderedDataTable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                dom: '<"row align-items-center"><"table-responsive my-3" rt><"row align-items-center" <"col-md-6" l><"col-md-6" p>><"clear">',
                ajax: {
                    type: "POST",
                    url: '{{ route("office.customers.data") }}',
                    data: function (d) {
                        d._token = '{{ csrf_token() }}';
                        d.officeId = '{{ $officeId ?? "" }}';
                    },
                },
                columns: [
                    {
                        name: 'check',
                        data: 'check',
                        title: '<input type="checkbox" class="form-check-input" id="select-all-table" data-type="customer">',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'display_name',
                        name: 'display_name',
                        title: "{{__('messages.name')}}",
                        orderable: false,
                    },
                    {
                        data: 'gender',
                        name: 'gender',
                        title: "{{__('messages.gender')}}"
                    },
                    {
                        data: 'totalBookings',
                        name: 'totalBookings',
                        title: "{{__('messages.totalOrders')}}",
                        searchable: false,
                    },
                    {
                        data: 'totalAmount',
                        name: 'totalAmount',
                        title: "{{__('messages.totalAmount')}}",
                        searchable: false,
                    },
                    {
                        data: 'totalDistance',
                        name: 'totalDistance',
                        title: "{{__('messages.totalDistance')}}",
                        searchable: false,
                    },
                    {
                        data: 'lastBookingAt',
                        name: 'lastBookingAt',
                        title: "{{__('messages.lastOrder')}}",
                        searchable: false,
                    },
                    {
                        data: 'averageRating',
                        name: 'averageRating',
                        title: "{{__('messages.averageRating')}}",
                        searchable: false,
                    },
                    {
                        data: 'lastPaymentStatus',
                        name: 'lastPaymentStatus',
                        title: "{{__('messages.lastPaymentStatus')}}",
                        searchable: false,
                    }
                ]
            });
        });
    </script>
</x-master-layout>
