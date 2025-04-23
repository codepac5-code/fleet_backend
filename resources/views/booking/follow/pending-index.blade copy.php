    <head>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    </head>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
           
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
        <div class="row justify-content-between">
            <div>
                {{-- <div class="col-md-12">
                  <form action="{{ route('booking.bulk-action') }}" id="quick-action-form" class="form-disabled d-flex gap-3 align-items-center">
                    @csrf
                  <select name="action_type" class="form-control select2" id="quick-action-type" style="width:100%" disabled>
                      <option value="">{{__('messages.no_action')}}</option>
                      <option value="delete">{{__('messages.delete')}}</option>
                      <option value="restore">{{__('messages.restore')}}</option>
                      <option value="permanently-delete">{{__('messages.permanent_dlt')}}</option>
                  </select>
                  
                <button id="quick-action-apply" class="btn btn-primary" data-ajax="true"
                data--submit="{{ route('booking.bulk-action') }}"
                data-datatable="reload" data-confirmation='true'
                data-title="{{ __('booking',['form'=>  __('booking') ]) }}"
                title="{{ __('booking',['form'=>  __('booking') ]) }}"
                data-message='{{ __("Do you want to perform this action?") }}' disabled>{{__('messages.apply')}}</button>
            </div> --}}
          
            </form>
          </div>
              <div class="d-flex justify-content-end">

                <div class="datatable-filter ml-auto">
                    <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                      <option value="">{{ __('messages.all') }}</option>
                      <option value="search on driver">{{ __('messages.search_on_driver') }}</option>

                      {{-- <option value="0" {{$filter['status'] == '0' ? "selected" : ''}}>{{ __('messages.inactive') }}</option>
                      <option value="1" {{$filter['status'] == '1' ? "selected" : ''}}>{{ __('messages.active') }}</option> --}}
                    </select>
                  </div>

                <div class="input-group ml-2">
                    <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="Search..." aria-label="Search" aria-describedby="addon-wrapping" aria-controls="dataTableBuilder">
                  </div>
              </div>
               
              <div class="table-responsive">
                <table id="pending_datatable" class="table table-striped border">

                </table>
              </div>
            </div>
        </div>
    </div>

    
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {

        window.renderedDataTable1 = $('#pending_datatable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                dom: '<"row align-items-center"><"table-responsive my-3" rt><"row align-items-center" <"col-md-6" l><"col-md-6" p>><"clear">',
                ajax: {
                  "type"   : "GET",
                  "url"    : '{{ route("booking.index_data",["type"=>"pending"]) }}',
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
                        title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" data-type="booking" onclick="selectAllTable(this)">',
                        exportable: false,
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'id',
                        name: 'id',
                        title: "{{__('messages.id')}}",
                        searchable: false,
                    },
                    {
                        data: 'status',
                        name: 'status',
                        title: "{{__('messages.status')}}",
                        searchable: true,
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        title: "{{__('messages.order_date')}}",
                        searchable: false,

                    },
                    {
                        data: 'startAddress',
                        name: 'startAddress',
                        title: "{{__('messages.startAddress')}}"
                    },
                    {
                        data: 'endAddress',
                        name: 'endAddress',
                        title: "{{__('messages.endAddress')}}"
                    },
                    {
                        data: 'rideCommission',
                        name: 'rideCommission',
                        title: "{{__('messages.rideCommission')}}",
                        searchable: false,

                    },
                    {
                        data: 'subservice',
                        name: 'subservice',
                        title: "{{__('messages.service')}}",
                        searchable: false,

                    },
                    {
                        data: 'distance',
                        name: 'distance',
                        title: "{{__('messages.distance'). ' '.__('messages.KM')}}",
                        searchable: false,

                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        title: "{{__('messages.total_amount')}}",
                        searchable: false,

                    },
                    {
                        data: 'discount',
                        name: 'discount',
                        title: "{{__('messages.discount')}}",
                        searchable: false,

                    },



                    // {
                    //     data: 'action',
                    //     name: 'action',
                    //     orderable: false,
                    //     searchable: false,
                    //     title: "{{__('messages.action')}}"
                    // }
                    
                ],
                        order: [
            
                         [9, 'desc'] 
                     ]

                
            });
      });

    function resetQuickAction () {
    const actionValue = $('#quick-action-type').val();
    console.log(actionValue)
    if (actionValue != '') {
        $('#quick-action-apply').removeAttr('disabled');

        if (actionValue == 'change-status') {
            $('.quick-action-field').addClass('d-none');
            $('#change-status-action').removeClass('d-none');
        } else {
            $('.quick-action-field').addClass('d-none');
        }
    } else {
        $('#quick-action-apply').attr('disabled', true);
        $('.quick-action-field').addClass('d-none');
    }
  }

  $('#quick-action-type').change(function () {
    resetQuickAction()
  });

  $(document).on('update_quick_action', function() {
  })

    $(document).on('click', '[data-ajax="true"]', function (e) {
      e.preventDefault();
      const button = $(this);
      const confirmation = button.data('confirmation');

      if (confirmation === 'true') {
          const message = button.data('message');
          if (confirm(message)) {
              const submitUrl = button.data('submit');
              const form = button.closest('form');
              form.attr('action', submitUrl);
              form.submit();
          }
      } else {
          const submitUrl = button.data('submit');
          const form = button.closest('form');
          form.attr('action', submitUrl);
          form.submit();
      }
  });

// socket.on('admins:new-order-search', (data) => {
//         window.renderedDataTable.ajax.reload(null, false); 
//     });
    
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
