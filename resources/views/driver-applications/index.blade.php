<x-master-layout>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .btn-outline-primary:hover {
        background-color: #ffcc0081;
        color: white;
    }
    .driver-status-pending { color: #ffc107; font-weight: bold; }
    .driver-status-approved { color: #28a745; font-weight: bold; }
    .driver-status-rejected { color: #dc3545; font-weight: bold; }
    .card-image-preview {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
    }
</style>

<div class="container-fluid mt-4">

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ __('messages.driver_applications_list') }}</h5>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="exportBtn">
                                    <i class="fas fa-download"></i> {{ __('messages.export') }}
                                </button>
                                {{-- @if($auth_user->can('driver_applications add')) --}}
                                <a href="{{ route('driver-applications.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-plus-circle"></i> {{ __('messages.add_form_title', ['form' => __('messages.driver_application') ]) }}
                                </a>
                                {{-- @endif --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- فلترة متقدمة -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label>{{ __('messages.status') }}</label>
                    <select id="filter-status" class="form-control select2js">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="pending">{{ __('messages.status_pending') }}</option>
                        <option value="approved">{{ __('messages.status_approved') }}</option>
                        <option value="rejected">{{ __('messages.status_rejected') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>{{ __('messages.office') }}</label>
                    <select id="filter-office" class="form-control select2js">
                        <option value="">{{ __('messages.all_offices') }}</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>{{ __('messages.from_date') }}</label>
                    <input type="date" id="filter-from-date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>{{ __('messages.to_date') }}</label>
                    <input type="date" id="filter-to-date" class="form-control">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12 text-end">
                    <button type="button" id="reset-filters" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i> {{ __('messages.reset_filters') }}
                    </button>
                    <button type="button" id="apply-filters" class="btn btn-primary">
                        <i class="fas fa-filter"></i> {{ __('messages.apply_filters') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول طلبات التوظيف -->
    <div class="card">
        <div class="card-body">
            <table id="datatable" class="table table-bordered table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>{{ __('messages.driver_info') }}</th>
                        <th>{{ __('messages.car_info') }}</th>
                        <th>{{ __('messages.documents') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.office') }}</th>
                        <th>{{ __('messages.application_date') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم ملؤها بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal لعرض الصور -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('messages.image_preview') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css" rel="stylesheet" />

<script>
$(document).ready(function () {
    // تهيئة Select2
    $('.select2js').select2({
        placeholder: "{{ __('messages.choose') }}",
        allowClear: true
    });

    const profileImage = "{{ get_default_image()}}";

    const translations = {
    car_images: '{{ __("messages.car_images") }}',
    id_card: '{{ __("messages.id_card") }}',
    driving_license: '{{ __("messages.driving_license") }}',
    no_documents: '{{ __("messages.no_documents") }}'
    };

    var table = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("driver-applications.data") }}',
            data: function (d) {
                d.status = $('#filter-status').val();
                d.office = $('#filter-office').val();
                d.from_date = $('#filter-from-date').val();
                d.to_date = $('#filter-to-date').val();
            }
        },
        columns: [
            {
                data: 'check',
                name: 'check',
                orderable: false,
                searchable: false,
                className: 'dt-body-center'
            },
            {
                data: 'driver_info',
                name: 'driver_info',
                render: function(data, type, row) {
                    return `<div class="d-flex align-items-center">
                        <img src="${row.profileImage || profileImage }"
                            class="card-image-preview me-3"
                            style="margin-inline-end: 1.5rem;"
                            alt="image")">
                        <div>
                            <strong>${row.name}</strong><br>
                            <small class="text-muted">${row.phoneNumber}</small>
                        </div>
                    </div>`;
                }
            },
            {
                data: 'car_info',
                name: 'car_info',
                render: function(data, type, row) {
                    return `
                        <div>
                            <strong>${row.brand} ${row.model}</strong><br>
                            <small>${row.year} - ${row.color}</small><br>
                            <small class="text-muted">${row.plateNumber}</small>
                        </div>
                    `;
                }
            },
            {
                data: 'documents',
                name: 'documents',
                orderable: false,
                render: function(data, type, row) {
                    let documents = [];
                    if(row.idFrontImage) documents.push(translations.id_card);
                    if(row.licenseFrontImage) documents.push(translations.driving_license);
                    if(row.mechanicalImage) documents.push(translations.car_images);

                    return documents.length > 0 ?
                        `<span class="badge bg-success">${documents.join('، ')}</span>` :
                        `<span class="badge bg-secondary">${translations.no_documents}</span>`;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    const statusClass = {
                        'pending': 'driver-status-pending',
                        'approved': 'driver-status-approved',
                        'rejected': 'driver-status-rejected'
                    }[row.status] || '';

                    const statusText = {
                        'pending': 'قيد المراجعة',
                        'approved': 'مقبول',
                        'rejected': 'مرفوض'
                    }[row.status] || row.status;

                    return `<span class="${statusClass}">${statusText}</span>`;
                }
            },
            {
                data: 'created_at',
                name: 'created_at',
                render: function(data) {
                    return new Date(data).toLocaleDateString('ar-EG');
                }
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="/driver-applications/${row.id}">
                                        <i class="fas fa-eye"></i> عرض التفاصيل
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/driver-applications/${row.id}/edit">
                                        <i class="fas fa-edit"></i> تعديل
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-success" href="#" onclick="updateStatus(${row.id}, 'approved')">
                                        <i class="fas fa-check"></i> قبول الطلب
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="updateStatus(${row.id}, 'rejected')">
                                        <i class="fas fa-times"></i> رفض الطلب
                                    </a>
                                </li>
                            </ul>
                        </div>
                    `;
                }
            },
        ],
        order: [[6, 'desc']],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        language: {
            search: "بحث:",
            lengthMenu: "أظهر _MENU_ طلب في الصفحة",
            info: "عرض _START_ إلى _END_ من أصل _TOTAL_ طلب",
            infoEmpty: "لا توجد طلبات متاحة",
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

    // تطبيق الفلترة
    $('#apply-filters').click(function () {
        table.draw();
    });

    // إعادة تعيين الفلترة
    $('#reset-filters').click(function () {
        $('#filter-status').val('').trigger('change');
        $('#filter-office').val('').trigger('change');
        $('#filter-from-date').val('');
        $('#filter-to-date').val('');
        table.draw();
    });

    // اختيار الكل
    $('#select-all').on('click', function(){
        var rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });

    // تصدير البيانات
    $('#exportBtn').click(function() {
        const params = new URLSearchParams({
            status: $('#filter-status').val(),
            office: $('#filter-office').val(),
            from_date: $('#filter-from-date').val(),
            to_date: $('#filter-to-date').val(),
            export: true
        });

        window.location.href = '{{ route("driver-applications.data") }}?' + params.toString();
    });
});

// دالة عرض الصورة
function showImage(imageUrl) {
    $('#modalImage').attr('src', imageUrl);
    $('#imageModal').modal('show');
}

// دالة تحديث حالة الطلب
function updateStatus(applicationId, status) {
    if(confirm('هل أنت متأكد من تغيير حالة الطلب؟')) {
        $.ajax({
            url: `/driver-applications/${applicationId}/status`,
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                status: status
            },
            success: function(response) {
                alert('تم تحديث حالة الطلب بنجاح');
                $('#datatable').DataTable().ajax.reload();
            },
            error: function() {
                alert('حدث خطأ أثناء تحديث حالة الطلب');
            }
        });
    }
}
</script>
</x-master-layout>
