<x-master-layout>


  <div class="container-fluid mt-4">
    <div class="row">
      <div class="col-lg-12">

        {{-- Header --}}
        <div class="card card-block card-stretch">
          <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
              <h4 class="font-weight-bold">{{ __('messages.departments') }}</h4>
              {{-- @can('add department') --}}
                <a href="{{ route('departments.create') }}" class="btn btn-sm btn-primary">
                  <i class="fa fa-plus-circle"></i> {{ __('messages.add_new_department') }}
                </a>
              {{-- @endcan --}}
            </div>
          </div>
        </div>

        {{-- Content --}}
        <div class="card mt-3">
          <div class="card-body">

   {{-- Actions Row (Search + Bulk Action) --}}
<div class="d-flex justify-content-between flex-wrap gap-3 align-items-center mb-3">



  {{-- Bulk Action --}}
  <form action="{{ route('departments.bulk-action') }}" id="bulk-action-form"
        class="form-disabled d-flex gap-2 align-items-center">
      @csrf
      <select name="action_type" class="form-control select2" id="bulk-action-type" style="width: 200px;" disabled>
          <option value="">{{ __('messages.no_action') }}</option>
          <option value="delete">{{ __('messages.delete') }}</option>
      </select>
      <button id="bulk-action-apply" class="btn btn-primary" disabled>{{ __('messages.apply') }}</button>
  </form>


    {{-- Search Input --}}
    <div class="input-group me-3" style="max-width: 300px;">
      <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search_by_name') }}..." aria-label="Search">
      <span class="input-group-text"><i class="fas fa-search"></i></span>
  </div>

</div>


            {{-- Departments Table --}}
            <div class="table-responsive">
              <table id="departments-table" class="table table-striped border">
                <thead>
                  <tr>
                    <th><input type="checkbox" class="form-check-input" id="select-all-table"></th>
                    <th>{{ __('messages.department_name_en') }}</th>
                    <th>{{ __('messages.department_name_ar') }}</th>
                    <th>{{ __('messages.employees_count') }}</th>
                    <th>{{ __('messages.created_at') }}</th>
                    <th style="min-width: 120px;">{{ __('messages.actions') }}</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>


    <!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteDepartmentModal" tabindex="-1" aria-labelledby="deleteDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title text-white" id="deleteDepartmentModalLabel">{{ __('messages.confirm_delete_department') }}</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>{{ __('messages.are_you_sure') }}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">{{ __('messages.delete') }}</button>
      </div>
    </div>
  </div>
</div>



    {{-- Scripts --}}
    <script>

let departmentToDelete = null;

function deleteDepartment(id) {
  departmentToDelete = id;
  const modal = new bootstrap.Modal(document.getElementById('deleteDepartmentModal'));
  modal.show();
}


document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
  if (!departmentToDelete) return;

  $.ajax({
    url: '/department/destroy/' + departmentToDelete,
    method: 'DELETE',
    data: {
      _token: '{{ csrf_token() }}'
    },
    success: function (response) {
      // var toast = new bootstrap.Toast(document.getElementById('successToast'));
      // toast.show();
      $('#departments-table').DataTable().ajax.reload(null, false);
      bootstrap.Modal.getInstance(document.getElementById('deleteDepartmentModal')).hide();
      departmentToDelete = null;
    },
    error: function () {
      alert('{{ __("messages.error_occurred") }}');
    }
  });
});

      $(document).ready(function () {
        const table = $('#departments-table').DataTable({
          processing: true,
          serverSide: true,
          dom: 't<"d-flex justify-content-between align-items-center mt-3"lip>', // remove default search bar
          ajax: {
            url: '{{ route("department.index-data") }}',
            data: function (d) {
              d.search.value = $('.dt-search').val();
            }
          },
          columns: [
            { data: 'check', orderable: false, searchable: false },
            { data: 'name_en', name: 'name_en' },
            { data: 'name_ar', name: 'name_ar' },
            { data: 'employees_count', name: 'employees_count', searchable: false },
            { data: 'created_at', name: 'created_at', searchable: false },
            { data: 'action', orderable: false, searchable: false }
          ],
          order: [[4, 'desc']],
          drawCallback: function () {
            $('#select-all-table').prop('checked', false);
            $('#bulk-action-type').prop('disabled', true);
            $('#bulk-action-apply').prop('disabled', true);
          }
        });

        $('.dt-search').on('keyup', function () {
          table.draw();
        });

        $('#select-all-table').on('click', function () {
          const rows = table.rows({ 'search': 'applied' }).nodes();
          $('input.select-row', rows).prop('checked', this.checked);
          toggleBulkActions();
        });

        $('#departments-table tbody').on('change', 'input.select-row', function () {
          if (!this.checked) {
            $('#select-all-table').prop('checked', false);
          }
          toggleBulkActions();
        });

        $('#bulk-action-type').on('change', function () {
          toggleBulkActions();
        });

        function toggleBulkActions() {
          const anyChecked = $('input.select-row:checked').length > 0;
          $('#bulk-action-type').prop('disabled', !anyChecked);
          $('#bulk-action-apply').prop('disabled', !(anyChecked && $('#bulk-action-type').val() !== ''));
        }

        $('#bulk-action-apply').on('click', function (e) {
          e.preventDefault();

          const action = $('#bulk-action-type').val();
          const ids = $('input.select-row:checked').map(function () { return this.value; }).get();

          if (!action || ids.length === 0) {
            alert("{{ __('messages.select_action_first') }}");
            return;
          }

          if (confirm("{{ __('messages.are_you_sure') }}")) {
            $.ajax({
              url: '{{ route("departments.bulk-action") }}',
              method: 'POST',
              data: {
                _token: '{{ csrf_token() }}',
                action_type: action,
                ids: ids
              },
              success: function (res) {
                alert(res.message);
                table.ajax.reload(null, false);
                $('#bulk-action-type').val('').trigger('change');
                $('#select-all-table').prop('checked', false);
              },
              error: function (xhr) {
                alert(xhr.responseJSON.message || 'Error');
              }
            });
          }
        });
      });
    

    </script>
</x-master-layout>
