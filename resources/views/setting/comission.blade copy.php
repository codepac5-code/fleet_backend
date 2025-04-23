<x-master-layout>
    <main class="main-area">
        <div class="main-content">
            <div class="container-fluid">
                @include('partials._office')

                <div class="card mb-30">
                    <div class="card-body p-30">
                        <div class="col-lg-12">
                            <div class="card overview-detail mb-0">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label class="form-control-label">
                                                {{ trans('messages.office_name') }} <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control"
                                                   placeholder="{{ $officedata->name }}" readonly>
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label class="form-control-label">
                                                {{ trans('messages.commission') }} <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control"
                                                   placeholder="{{ $officedata->commission_formatted }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </main>
</x-master-layout>
