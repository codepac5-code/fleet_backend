<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="fw-bold">{{ __('Create New Issue') }}</h5>
                            <a href="{{ route('issues.index') }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-angle-double-left"></i> {{ __('Back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
    
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('issues.store') }}" enctype="multipart/form-data" id="issue-form">
                            @csrf
    
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="form-control-label" for="subject">{{ __('Issue Title') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" id="subject" class="form-control" required placeholder="Subject">
                                </div>
    
                                <div class="form-group col-md-6">
                                    <label class="form-control-label">{{ __('Assign To') }} <span class="text-danger">*</span></label>
                                    <select name="assigned_to_id" class="form-control select2js" required>
                                        <option value="">{{ __('Select Employee') }}</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->firstName . ' ' . $emp->lastName }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="assigned_to_type" value="App\Models\Employee">
                                </div>
    
                                <div class="form-group col-md-6">
                                    <label class="form-control-label">{{ __('Department') }}</label>
                                    <select name="department_id" class="form-control select2js">
                                        <option value="">{{ __('Select Department') }}</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name_en }}</option>
                                        @endforeach
                                    </select>
                                </div>
    
                                <div class="form-group col-md-6">
                                    <label class="form-control-label">{{ __('Reporter Type') }} <span class="text-danger">*</span></label>
                                    <select name="owner_type" id="owner_type" class="form-control" required>
                                        <option value="">{{ __('Select Type') }}</option>
                                        <option value="user">{{ __('User') }}</option>
                                        <option value="driver">{{ __('Driver') }}</option>
                                        <option value="office">{{ __('Office') }}</option>
                                    </select>
                                </div>
    
                                <div class="form-group col-md-6">
                                    <label class="form-control-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                                    <select name="owner_id" id="owner_id" class="form-control select2js" required>
                                        <option value="">{{ __('Select Name') }}</option>
                                    </select>
                                </div>
    

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ownerTypeSelect = document.getElementById('owner_type');
        const ownerIdSelect = document.getElementById('owner_id');

        ownerTypeSelect.addEventListener('change', function () {
            const selectedType = this.value;

            // تفريغ القائمة السابقة
            ownerIdSelect.innerHTML = '<option value="">جاري التحميل...</option>';

            if (!selectedType) {
                ownerIdSelect.innerHTML = '<option value="">اختر الاسم</option>';
                return;
            }

            fetch(`/owners/by-type?type=${encodeURIComponent(selectedType)}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(data => {
                    ownerIdSelect.innerHTML = '<option value="">اختر الاسم</option>';
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name;
                        ownerIdSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('خطأ في جلب الأسماء:', error);
                    ownerIdSelect.innerHTML = '<option value="">تعذر التحميل</option>';
                });
        });
    });
</script>


                                {{-- الأولوية --}}
                                <div class="form-group col-md-6">
                                    <label class="form-control-label">{{ __('الأولوية') }} <span class="text-danger">*</span></label>
                                    <select name="priority" class="form-control" required>
                                        <option value="low">{{ __('منخفضة') }}</option>
                                        <option value="medium">{{ __('متوسطة') }}</option>
                                        <option value="high">{{ __('مرتفعة') }}</option>
                                    </select>
                                </div>

                                {{-- الحالة --}}
                                <div class="form-group col-md-6">
                                    <label class="form-control-label">{{ __('الحالة') }} <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="open">{{ __('مفتوحة') }}</option>
                                        <option value="processing">{{ __('قيد المعالجة') }}</option>
                                        <option value="closed">{{ __('مغلقة') }}</option>
                                    </select>
                                </div>

                                {{-- المرفقات --}}
                                <div class="form-group col-md-6">
                                    <label class="form-control-label">{{ __('المرفقات') }}</label>
                                    <input type="file" name="photo" class="form-control-file" accept="image/*">
                                </div>

                                {{-- الوصف --}}
                                <div class="form-group col-md-12">
                                    <label class="form-control-label">{{ __('الوصف') }} <span class="text-danger">*</span></label>
                                    <textarea name="description" class="form-control" rows="4" required placeholder="اكتب التفاصيل هنا..."></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary float-end mt-3">
                                <i class="fas fa-save"></i> {{ __('حفظ البلاغ') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-master-layout>
