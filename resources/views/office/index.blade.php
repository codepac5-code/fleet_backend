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
                            <h5 class="font-weight-bold">{{ trans('messages.Office') }}</h5>
                            @if($list_status != 'pending')
                            @if($auth_user->can('add office'))
                            <a href="{{ route('office.create-page') }}" class="float-right mr-1 btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ __('messages.add_form_title',['form' => __('messages.Office')  ]) }}</a>
                            @endif
                            @endif
                        </div>

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
                  <form action="{{ route('office.bulk-action') }}" id="quick-action-form" class="form-disabled d-flex gap-3 align-items-center">
                    @csrf
                  <select name="action_type" class="form-control select2" id="quick-action-type" style="width:100%" disabled>
                      <option value="">{{ __('messages.no_action') }}</option>
                      <option value="change-status">{{ __('messages.status') }}</option>
                      <option value="delete">{{ __('messages.delete') }}</option>
                      <option value="restore">{{ __('messages.restore') }}</option>
                      <option value="permanently-delete">{{ __('messages.permanent_dlt') }}</option>
                  </select>

                <div class="select-status d-none quick-action-field" id="change-status-action" style="width:100%">
                    <select name="status" class="form-control select2" id="status" style="width:100%">
                    @if($list_status == 'pending')
                      <option value="1">{{ __('messages.approve') }}</option>
                    @else
                      <option value="1">{{ __('messages.active') }}</option>
                      <option value="0">{{ __('messages.inactive') }}</option>
                    @endif
                    </select>
                </div>
                <button id="quick-action-apply" class="btn btn-primary" data-confirmation='true' data-ajax="true"
                data--submit="{{ route('office.bulk-action') }}"
                data-datatable="reload"
                data-title="{{ __('provider',['form'=>  __('provider') ]) }}"
                title="{{ __('provider',['form'=>  __('provider') ]) }}"
                data-message='{{ __("Do you want to perform this action?") }}' disabled>{{ __('messages.apply') }}</button>
            </div>

            </form>
          </div>
              <div class="d-flex justify-content-end">
                {{-- <div class="datatable-filter ml-auto">
                  <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="0" {{$filter['status'] == '0' ? "selected" : ''}}>{{ __('messages.inactive') }}</option>
                    <option value="1" {{$filter['status'] == '1' ? "selected" : ''}}>{{ __('messages.active') }}</option>
                  </select>
                </div> --}}
                <div class="input-group ml-2">
                    <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="Search..." aria-label="Search" aria-describedby="addon-wrapping" aria-controls="dataTableBuilder">
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
                  "url"    : '{{ route("office.index_data",["list_status"=>$list_status]) }}',
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
                        searchable: false,
                        exportable: false,
                        orderable: false,
                    },
                    {
                        data: 'display_name',
                        name: 'display_name',
                        title: "{{ __('messages.name') }}",
                        orderable: false,
                    },

                   
                    // {
                    //   data:'providertype_id',
                    //   name:'providertype_id',
                    //   title:"{{ __('messages.providertype') }}"
                    // },

                    {
                      data:'contactNumber',
                      name:'contactNumber',
                      title:"{{ __('messages.contact_number') }}"
                    },


                    {
                      data:'wallet',
                      name:'wallet',
                      title:"{{ __('messages.wallet_amt') }}",
                      searchable: false,
                      orderable: false,
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        title: "{{ __('messages.joining_date') }}"
                    },
                    {
                        data: 'status',
                        name: 'status',
                        title: "{{ __('messages.status') }}"
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        title: "{{ __('messages.action') }}"
                    }

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



    </script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.edit-commission-btn');
        if (!btn) return;

        const officeId = btn.getAttribute('data-office-id');
        
        openCommissionModal(officeId);
        
        function openCommissionModal(officeId) {
            Swal.fire({
                title: "{{ __('messages.edit_commission') }}",
                width: 450,
                padding: '2rem',
                background: '#fefefe',
                color: '#312873',
                customClass: { popup: 'custom-swal-popup' },
                html: `
                <div style="text-align:center; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:16px;">
                    <div style="margin-bottom:20px;">
                        <label style="font-weight:600; display:block; margin-bottom:5px; color:#312873;">
                            {{ __('messages.office_commission') }} (%)
                        </label>
                        <input id="officeCommission${officeId}" type="number" min="0" max="100" 
                               class="swal2-input" placeholder="{{ __('messages.example_value', ['value' => 20]) }}" 
                               style="width:90%; font-size:16px; border-radius:6px;">
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="font-weight:600; display:block; margin-bottom:5px; color:#312873;">
                            {{ __('messages.fleet_commission') }} (%)
                        </label>
                        <input id="fleetCommission${officeId}" type="number" min="0" max="100" 
                               class="swal2-input" placeholder="{{ __('messages.example_value', ['value' => 80]) }}" 
                               style="width:90%; font-size:16px; border-radius:6px;">
                    </div>
                    <div style="margin-bottom:10px;">
                        <label style="font-weight:600; display:block; margin-bottom:5px; color:#312873;">
                            {{ __('messages.percentage') }}
                        </label>
                        <div style="position:relative; height:25px; background:#eee; border-radius:8px; overflow:hidden; width:90%; margin:auto;">
                            <div id="officeBar${officeId}" style="height:100%; background:#312873; width:0%; float:left;"></div>
                            <div id="fleetBar${officeId}" style="height:100%; background:#F8A609; width:0%; float:left;"></div>
                            <span id="progressText${officeId}" 
                                  style="position:absolute; top:0; left:50%; transform:translateX(-50%); font-weight:bold; line-height:25px; color:black;">
                                100% {{ __('messages.remaining') }}
                            </span>
                        </div>
                        <div id="warningText${officeId}" style="color:red; font-weight:600; margin-top:5px; display:none;">
                            {{ __('messages.over_100_warning') }}
                        </div>
                    </div>
                </div>
                <div class="col-12">
                <div class="horizontal-separator"></div>
            </div>
                `,
                showCancelButton: true,
                confirmButtonText: "{{ __('messages.save') }}",
                cancelButtonText: "{{ __('messages.cancel') }}",
                didOpen: () => {
                    const officeInput = document.getElementById(`officeCommission${officeId}`);
                    const fleetInput = document.getElementById(`fleetCommission${officeId}`);
                    const officeBar = document.getElementById(`officeBar${officeId}`);
                    const fleetBar = document.getElementById(`fleetBar${officeId}`);
                    const progressText = document.getElementById(`progressText${officeId}`);
                    const warningText = document.getElementById(`warningText${officeId}`);

                    function updateProgress() {
                        let office = parseInt(officeInput.value) || 0;
                        let fleet = parseInt(fleetInput.value) || 0;
                        const total = office + fleet;
                        const remaining = 100 - total;

                        officeBar.style.width = office + '%';
                        fleetBar.style.width = fleet + '%';
                        progressText.textContent = remaining + '% {{ __('messages.remaining') }}';

                        if (total > 100) {
                            progressText.style.color = 'red'; 
                            warningText.style.display = 'block';
                        } else if (total >= 50) {
                            progressText.style.color = 'white'; 
                            warningText.style.display = 'none';
                        } else {
                            progressText.style.color = 'black'; 
                            warningText.style.display = 'none';
                        }
                    }

                    officeInput.addEventListener('input', updateProgress);
                    fleetInput.addEventListener('input', updateProgress);
                    updateProgress();
                },
                preConfirm: () => {
                    const office = parseInt(document.getElementById(`officeCommission${officeId}`).value);
                    const fleet = parseInt(document.getElementById(`fleetCommission${officeId}`).value);

                    if (isNaN(office) || isNaN(fleet)) {
                        Swal.showValidationMessage("{{ __('messages.validation_empty') }}");
                        return false;
                    }
                    if (office + fleet > 100) {
                        Swal.showValidationMessage("{{ __('messages.validation_over_100') }}");
                        return false;
                    }

                    return { office, fleet };
                }
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(`/office/update-commission`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                          officeId: officeId,
                          office_commission: result.value.office,
                          fleet_commission: result.value.fleet,
                            
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.fire({
                            icon: data.success ? 'success' : 'error',
                            title: data.success ? "{{ __('messages.updated') }}" : "{{ __('messages.failed_update') }}",
                            text: data.message || (data.success ? "{{ __('messages.updated_success') }}" : "{{ __('messages.update_failed') }}")
                        });
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: "{{ __('messages.failed_update') }}",
                            text: "{{ __('messages.connection_error') }}"
                        });
                    });
                }
            });
        }
    });
});
</script>
<style>
.custom-swal-popup {
    border: 3px solid #F8A609; 
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    background-color: rgba(255, 255, 255, 0.95); 
    font-family: "Helvetica Neue", Helvetica;
    font-size: 18px;
    padding: 20px;
}

.swal2-confirm {
    background-color: #312873 !important; 
    color: white !important;
    font-weight: bold;
    font-size: 18px;
}

.swal2-cancel {
    font-size: 18px;
}

.swal2-input:focus {
    border: 2px solid #312873 !important;
    outline: none;
    box-shadow: 0 0 5px rgba(49,40,115,0.5);
}

.swal2-input {
    font-size: 20px;
    padding: 8px 12px;
}

#progressText {
    font-size: 14px;
    font-weight: 600;
}

.swal2-content label {
    font-size: 17px;
    font-weight: 600;
}
</style>


</x-master-layout>