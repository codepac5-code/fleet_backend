<x-master-layout>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
<style>
    .btn-outline-primary:hover {
        background-color: #ffcc0081;
        color: white;
    }
</style>

<div class="container-fluid mt-4">

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ __('messages.issues_list') }}</h5>
                            @if($auth_user->can('issues add'))
                            <a href="{{ route('issues.create') }}" class="float-right mr-1 btn btn-sm btn-primary">
                                <i class="fa fa-plus-circle"></i> {{ __('messages.add_form_title', ['form' => __('messages.issue') ]) }}
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <select id="filter-status" class="form-control select2js">
                <option value="">{{ __('messages.filter_status_placeholder') }}</option>
                <option value="open">{{ __('messages.status_open') }}</option>
                <option value="processing">{{ __('messages.status_processing') }}</option>
                <option value="closed">{{ __('messages.status_closed') }}</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="filter-department" class="form-control select2js">
                <option value="">{{ __('messages.filter_department_placeholder') }}</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select id="filter-agent" class="form-control select2js">
                <option value="">{{ __('messages.filter_agent_placeholder') }}</option>
                @foreach($agents as $agent)
                    <option value="{{ $agent->id }}">{{ $agent->firstName . ' ' . $agent->lastName }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select id="filter-priority" class="form-control select2js">
                <option value="">{{ __('messages.filter_priority_placeholder') }}</option>
                <option value="low">{{ __('messages.priority_low') }}</option>
                <option value="medium">{{ __('messages.priority_medium') }}</option>
                <option value="high">{{ __('messages.priority_high') }}</option>
            </select>
        </div>
    </div>


    
    <table id="datatable" class="table table-bordered table-striped" style="width:100%">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th>{{ __('messages.subject') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.department') }}</th>
                <th>{{ __('messages.assigned_agent') }}</th>
                <th>{{ __('messages.priority') }}</th>
                <th>{{ __('messages.last_updated') }}</th>
                <th>{{ __('messages.actions') }}</th>
            </tr>
        </thead>
    </table>

</div>


  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
  <link href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css" rel="stylesheet" />

  <script>
  $(document).ready(function () {
      var table = $('#datatable').DataTable({
        
          processing: true,
          serverSide: true,
          ajax: {
              url: '{{ route("issues.data") }}',
              data: function (d) {
                  d.status = $('#filter-status').val();
                  d.department = $('#filter-department').val();
                  d.agent = $('#filter-agent').val();
                  d.priority = $('#filter-priority').val();
              }
          },
          columns: [
              { data: 'check', name: 'check', orderable: false, searchable: false },
              { data: 'subject', name: 'subject' },
              { data: 'status', name: 'status' },
              { data: 'department', name: 'department' },
              { data: 'agentName', name: 'assigned_to' },
              { data: 'priority', name: 'priority' },
              { data: 'updated_at', name: 'updated_at' },
              { data: 'action', name: 'action', orderable: false, searchable: false },
          ],
          order: [[6, 'desc']],
          columnDefs: [
              {
                  targets: 0,
                  className: 'dt-body-center',
              }
          ],
          dom: '<"row"<"col-12">>rt<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"p>>',
          language: {
            //   search: "بحث:",
              lengthMenu: "أظهر _MENU_ مدخلات",
              info: "عرض _START_ إلى _END_ من أصل _TOTAL_ مدخل",
              infoEmpty: "لا توجد بيانات متاحة",
              processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>',
               paginate: {
                  first: "الأول",
                  last: "الأخير",
                  next: "التالي",
                  previous: "السابق"
              },
          },
          drawCallback: function () {
              $('#select-all').prop('checked', false);
          }
      });

     
      $('#filter-status, #filter-department, #filter-agent, #filter-priority').change(function () {
          table.draw();
      });

      
      $('#select-all').on('click', function(){
          var rows = table.rows({ 'search': 'applied' }).nodes();
          $('input[type="checkbox"]', rows).prop('checked', this.checked);
      });

  });

 
      
  
  </script>
</x-master-layout>
