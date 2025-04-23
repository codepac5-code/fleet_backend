<x-master-layout>
    <form action="{{ route('office.destroy', $office->id) }}" method="POST" data--submit="office{{ $office->id }}">
        @csrf
        @method('DELETE')
        
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
                                                    {{ trans('messages.type') }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" 
                                                       placeholder="{{ $commissionType}}" 
                                                       readonly>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label class="form-control-label">
                                                    {{ trans('messages.commission') }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" 
                                                       placeholder="{{ $commission }}" 
                                                       readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </main>
    </form>
</x-master-layout>
