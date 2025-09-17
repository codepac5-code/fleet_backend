<x-master-layout>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <style>
        .filters-wrapper {
            gap: 10px;
            padding: 10px;
        }
        .filters-wrapper select,
        .filters-wrapper input {
            min-width: 160px;
        }
        .table-container {
            padding: 15px;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? trans('messages.list') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row justify-content-between">
                <div class="d-flex gap-2 flex-wrap" style="padding: 15px 20px; gap: 15px;">

                    <select id="filter-from-type" class=" form-control select2" style="width: 180px;">
                        <option value="">{{ __('messages.all_from') }}</option>
                        <option value="App\Models\Driver">{{ __('messages.driver') }}</option>
                        <option value="App\Models\Office">{{ __('messages.office') }}</option>
                        <option value="App\Models\User">{{ __('messages.user') }}</option>
                        <option value="App\Models\FleetOffice">{{ __('messages.fleet') }}</option>
                    </select>

                    <select id="filter-to-type" class="form-control select2" style="width: 180px;">
                        <option value="">{{ __('messages.all_to') }}</option>
                        <option value="App\Models\Driver">{{ __('messages.driver') }}</option>
                        <option value="App\Models\Office">{{ __('messages.office') }}</option>
                        <option value="App\Models\User">{{ __('messages.user') }}</option>
                        <option value="App\Models\FleetOffice">{{ __('messages.fleet') }}</option>
                    </select>

                    <input type="date" id="date_from" class="form-control" style="width: 180px;" placeholder="{{ __('messages.date_from') }}">

                    <input type="date" id="date_to" class="form-control" style="width: 180px;" placeholder="{{ __('messages.date_to') }}">
                </div>
            </div>

            <div class="table-responsive table-container">
                <table id="datatable" class="table table-striped border"></table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        window.renderedDataTable = $('#datatable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            searching: false, 
            responsive: true,
            language: {
                url: "{{ asset('datatable/ar.json') }}",
                emptyTable: "{{ __('messages.no_data_available') }}"
            },
            dom: 'rt<"bottom-row d-flex justify-content-between align-items-center"<"info"i><"pagination"p>>',
            ajax: {
                type: "GET",
                url: '{{ route("wallet-transactions.data") }}',
                data: function (d) {
                    d.search = { value: $('.dt-search').val() };
                    d.filter = {
                        column_status: $('#column_status').val(),
                        from_type: $('#filter-from-type').val(),
                        to_type: $('#filter-to-type').val(),
                        date_from: $('#date_from').val(),
                        date_to: $('#date_to').val()
                    };
                },
            },
            columns: [
                { name: 'check', data: 'check', orderable: false, searchable: false,
                title: '<input type="checkbox" id="select-all-table" onclick="selectAllTable(this)">' },

                { data: 'id', name: 'id', title: "#" },                    
                // { data: 'transaction_reference', name: 'transaction_reference', title: "{{__('messages.transaction_reference')}}" }, 

                { data: 'from_name', name: 'from_name', title: "{{__('messages.from')}}" },  
                { data: 'to_name', name: 'to_name', title: "{{__('messages.to')}}" },       

                { data: 'amount', name: 'amount', title: "{{__('messages.amount')}}" },
                { data: 'description', name: 'description', title: "{{__('messages.description')}}" },      
      
                { data: 'balance_before', name: 'balance_before', title: "{{__('messages.balance_before')}}" }, 
                { data: 'balance_after', name: 'balance_after', title: "{{__('messages.balance_after')}}" },   

                { data: 'status', name: 'status', title: "{{__('messages.status')}}" },     
                // { data: 'transaction_type', name: 'transaction_type', title: "{{__('messages.transaction_type')}}" }, 

                { data: 'created_at', name: 'created_at', title: "{{__('messages.Payment_datetime')}}" }, 

                { data: 'action', name: 'action', orderable: false, searchable: false, title: "{{__('messages.action')}}" }  
            ]

        });

        $('#filter-from-type , #filter-to-type, #column_status, #date_from, #date_to').on('change', function () {
            $('#datatable').DataTable().ajax.reload();
        });

        $('.dt-search').on('keyup', function () {
            $('#datatable').DataTable().ajax.reload();
        });
    });
    </script>
</x-master-layout>
