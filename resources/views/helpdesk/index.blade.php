<x-master-layout>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <div class="container-fluid mt-4">


      <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">قائمة التذاكر (Issues)</h5>
                            {{-- @if(auth()->user()->can('add user')) --}}
                            <a href="{{ route('issues.create') }}" class="float-right mr-1 btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ __('messages.add_form_title',['form' => __('messages.issue')  ]) }}</a>
                            {{-- @endif --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

      
      <div class="row mb-3">
          <div class="col-md-3">
              <select id="filter-status" class="form-control">
                  <option value="">{{ __('-- الحالة --') }}</option>
                  <option value="open" style="color: black;">مفتوحة</option>
                  <option value="processing">قيد المعالجة</option>
                  <option value="closed">مغلقة</option>
              </select>
          </div>
          <div class="col-md-3">
              <select id="filter-department" class="form-control">
                  <option value="">{{ __('-- القسم --') }}</option>
                  @foreach($departments as $dept)
                      <option value="{{ $dept->id }}">{{ $dept->name_ar }}</option>
                  @endforeach
              </select>
          </div>
          <div class="col-md-3">
              <select id="filter-agent" class="form-control">
                  <option value="">{{ __('-- الموظف المعين --') }}</option>
                  @foreach($agents as $agent)
                      <option value="{{ $agent->id }}">{{ $agent->firstName . ' ' . $agent->lastName }}</option>
                  @endforeach
              </select>
          </div>
          <div class="col-md-3">
              <select id="filter-priority" class="form-control">
                  <option value="">{{ __('-- الأولوية --') }}</option>
                  <option value="low">منخفضة</option>
                  <option value="medium">متوسطة</option>
                  <option value="high">عالية</option>
              </select>
          </div>
      </div>

      <table id="datatable" class="table table-bordered table-striped" style="width:100%">
          <thead>
              <tr>
                  <th><input type="checkbox" id="select-all"></th>
                  <th>الموضوع</th>
                  <th>الحالة</th>
                  <th>الأولوية</th>
                  <th>القسم</th>
                  <th>الموظف المعين</th>
                  <th>آخر تحديث</th>
                  <th>الإجراءات</th>
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
              { data: 'priority', name: 'priority' },
              { data: 'department', name: 'department' },
              { data: 'agentName', name: 'assigned_to' },
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
          language: {
              search: "بحث:",
              lengthMenu: "أظهر _MENU_ مدخلات",
              info: "عرض _START_ إلى _END_ من أصل _TOTAL_ مدخل",
              infoEmpty: "لا توجد بيانات متاحة",
              processing: "جاري التحميل...",
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

  function deleteIssue(id) {
      if (confirm('هل أنت متأكد من حذف هذه التذكرة؟')) {
          $.ajax({
              url: '/issues/' + id,
              type: 'DELETE',
              headers: {
                  'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              success: function(result) {
                  $('#datatable').DataTable().ajax.reload();
                  alert('تم حذف التذكرة بنجاح');
              },
              error: function() {
                  alert('حدث خطأ أثناء الحذف');
              }
          });
      }
  }
  </script>
</x-master-layout>
