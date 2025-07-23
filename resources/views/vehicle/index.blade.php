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
                            <h5 class="font-weight-bold">{{ __('messages.vehicles') }}</h5>
                            @if(auth()->user()->can('add vehicle')) 
                            <a href="{{ route('vehicle.create') }}" class="float-right mr-1 btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('messages.add_form_title',['form' => trans('messages.vehicle')  ]) }}</a>
                            @endif
                        </div>
                        {{-- {{ $dataTable->table(['class' => 'table  w-100'],false) }} --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
        <div class="row justify-content-between">
            <div>
              <div class="col-md-12">
                  <form action="{{ route('vehicle.bulk-action') }}" id="quick-action-form" class="form-disabled d-flex gap-3 align-items-center">
                    @csrf
                  <select name="action_type" class="form-control select2" id="quick-action-type" style="width:100%" disabled>
                      <option value="">{{ __('messages.no_action') }}</option>
                      <option value="change-status">{{ __('messages.status') }}</option>
                      <option value="delete">{{ __('messages.delete') }}</option>
                      <option value="restore">{{ __('messages.restore') }}</option>
                      <option value="permanently-delete">{{ __('messages.permanent_dlt') }}</option>
                  </select>
                  
                <div class="select-status d-none quick-action-field" id="change-status-action" style="width:100%">
                    <select name="status" class="form-control select2" id="status" >
                      <option value="1">{{ __('messages.active') }}</option>
                      <option value="0">{{ __('messages.inactive') }}</option>
                    </select>
                </div>
       
                <button id="quick-action-apply" class="btn btn-primary" data-ajax="true"
                data--submit="{{ route('vehicle.bulk-action') }}"
                data-datatable="reload" data-confirmation='true'
                data-title="{{ __('subservice',['form'=>  __('subservice') ]) }}"
                title="{{ __('subservice',['form'=>  __('subservice') ]) }}"
                data-message='{{ __("Do you want to perform this action??") }}' disabled>{{ __('messages.apply') }}</button>
            </div>
          
            </form>
            
          </div>
              <div class="d-flex justify-content-end">
                <div class="datatable-filter ml-auto">
                  <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="0" {{$filter['status'] == '0' ? "selected" : ''}}>{{ __('messages.inactive') }}</option>
                    <option value="1" {{$filter['status'] == '1' ? "selected" : ''}}>{{ __('messages.active') }}</option>
                  </select>
                </div>
                <div class="input-group ml-2">
                    <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="Search by plate number..." aria-label="Search" aria-describedby="addon-wrapping" aria-controls="dataTableBuilder">
                  </div>
              </div>
               
              <div class="table-responsive">
                <table id="datatable" class="table table-striped border">

                </table>
              </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {

        window.renderedDataTable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                dom: '<"row align-items-center"><"table-responsive my-3" rt><"row align-items-center" <"col-md-6" l><"col-md-6" p>><"clear">',
                ajax: {
                  "type"   : "GET",
                  "url"    : '{{ route("vehicle.index-data") }}',
                  "data"   : function( d ) {
                    d.search = {
                      value: $('.dt-search').val()
                    };
                    d.filter = {
                      column_status: $('#column_status').val()
                    }
                  },
                },
                columns: [
                      {
                          name: 'check',
                          data: 'check',
                          title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" data-type="user" onclick="selectAllTable(this)">',
                          exportable: false,
                          orderable: false,
                          searchable: false,
                      },
                      {
                        data: 'image',
                        name: 'image',
                        title: "{{ __('messages.image') }}",
                        searchable: false,

                      },
                      {
                        data: 'brand',
                        name: 'brand',
                        title: "{{__('messages.Brand')}}",
                        searchable: false,

                      },
                      {
                        data: 'model',
                        name: 'model',
                        title: "{{ __('messages.model') }}",
                        searchable: false,
                      },
                    // {
                    //    name: 'id',
                    //     data: 'id',
                    //     title: "{{ __('messages.id') }}",
                    //     // render: function (data, type, row) {
                    //     //     return '#' + data;
                    //     // }
                    // },
                      {
                        data: 'plate',
                        name: 'plate',
                        title: "{{__('messages.plate')}}",
                        searchable: true,

                      },
                      // {
                        // data: 'provider_contact',
                        // name: 'provider_contact',
                        // title: "{{__('messages.Service')}}"
                      // },
                      // {
                        // data: 'amount',
                        // name: 'amount',
                        // title: "{{__('messages.Subservice')}}"
                      // },
                      {
                        data: 'driver',
                        name: 'driver',
                        title: "{{__('messages.Driver')}}",
                        searchable: false,

                      },
                     
                      // {
                      //   data: 'model',
                      //   name: 'model',
                      //   title: "{{__('messages.Model')}}"
                      // },

                      {
                        data: 'city',
                        name: 'city',
                        title: "{{__('messages.City')}}",
                        searchable: false,

                      },
                    {
                        data: 'model_year',
                        name: 'model_year',
                        title: "{{__('messages.Year')}}",
                        searchable: false,

                    },
                    {
                        data: 'seats',
                        name: 'seats',
                        title: "{{__('messages.Seats')}}",
                        searchable: false,

                    },
                    {
                        data: 'color',
                        name: 'color',
                        title: "{{__('messages.color')}}",
                        searchable: false,

                    },
                    {
                        data: 'licenseNumber',
                        name: 'licenseNumber',
                        title: "{{__('messages.license_number')}}",
                        searchable: false,

                      },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        title: "{{__('messages.action')}}",
                        searchable: false,

                    }

                ]

            });
      });


            
      


    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</x-master-layout>
