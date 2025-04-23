<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                        <h5 class="font-weight-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('sendPushNotification') }}" method="POST" enctype="multipart/form-data" id="push_notification">
                            @csrf
                            <input type="hidden" name="id">

                            <div class="form-group">
                                <label class="form-control-label d-block">{{ __('messages.type') }} <span class="text-danger">*</span></label>
                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-outline-primary active">
                                        <input type="radio" name="is_type" value="user" checked> {{ __('messages.user') }}
                                    </label>
                                    <label class="btn btn-outline-primary">
                                        <input type="radio" name="is_type" value="driver"> {{ __('messages.Driver') }}
                                    </label>
                                </div>
                            </div>

                            <div class="row">
                                <!-- اللغة العربية -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm p-3">
                                        <div class="card-header bg-light d-flex align-items-center">
                                            <img src="/images/arabic-flag.png" width="30" class="mr-2"> 
                                            <h6 class="mb-0">{{ __('messages.arabic') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>{{ __('messages.title_ar') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="title_ar" class="form-control" required>
                                                @error('title_ar')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>{{ __('messages.body_ar') }} <span class="text-danger">*</span></label>
                                                <textarea name="body_ar" class="form-control" rows="3" required></textarea>
                                                @error('body_ar')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- اللغة الإنجليزية -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm p-3">
                                        <div class="card-header bg-light d-flex align-items-center">
                                            <img src="/images/english-flag.png" width="30" class="mr-2"> 
                                            <h6 class="mb-0">{{ __('messages.english') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>{{ __('messages.title_en') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="title_en" class="form-control" required>
                                                @error('title_en')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>{{ __('messages.body_en') }} <span class="text-danger">*</span></label>
                                                <textarea name="body_en" class="form-control" rows="3" required></textarea>
                                                @error('body_en')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- زر الإرسال -->
                            <button type="button" class="btn btn-primary btn-lg mt-3 float-right" data-toggle="modal" data-target="#confirmSendModal">
                                <i class="fas fa-paper-plane"></i> {{ __('messages.send') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Modal لتأكيد الإرسال -->
<div class="modal fade" id="confirmSendModal" tabindex="-1" aria-labelledby="confirmSendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="confirmSendModalLabel">
                    <i class="fas fa-exclamation-circle"></i> {{ __('messages.confirmation') }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <!-- أيقونة متحركة -->
                <div id="lottie-animation" class="d-flex justify-content-center my-3"></div>
                <p class="font-weight-bold">{{ __('messages.confirm_send_notification') }}</p>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
                    <i class="fas fa-times"></i> {{ __('messages.cancel') }}
                </button>
                <button type="button" class="btn btn-primary px-4" id="confirmSend">
                    <i class="fas fa-paper-plane"></i> {{ __('messages.send') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- تضمين مكتبة Lottie -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var animation = lottie.loadAnimation({
            container: document.getElementById("lottie-animation"),
            renderer: "svg",
            loop: true,
            autoplay: true,
            path: "https://assets10.lottiefiles.com/packages/lf20_x62chJ.json" // رابط الأيقونة المتحركة
        });

        document.getElementById('confirmSend').addEventListener('click', function() {
            document.getElementById('push_notification').submit();
        });
    });
</script>


    <!-- دعم Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        @if (session('success'))
            toastr.success("{{ session('success') }}");
        @endif
        @if (session('error'))
            toastr.error("{{ session('error') }}");
        @endif

        document.getElementById('confirmSend').addEventListener('click', function() {
            document.getElementById('push_notification').submit();
        });
    </script>
</x-master-layout>
