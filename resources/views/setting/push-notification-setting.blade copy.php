<x-master-layout>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('sendPushNotification') }}" method="POST" enctype="multipart/form-data" id="push_notification">
                            @csrf
                            
                            <div class="form-group">
                                <label class="form-control-label">{{ __('messages.type') }} <span class="text-danger">*</span></label>
                                <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                    <label class="btn btn-outline-primary w-50 active">
                                        <input type="radio" name="is_type" value="user" checked> {{ __('messages.user') }}
                                    </label>
                                    <label class="btn btn-outline-primary w-50">
                                        <input type="radio" name="is_type" value="driver"> {{ __('messages.Driver') }}
                                    </label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-light d-flex align-items-center">
                                            <img src="/images/arabic-flag.png" width="30" class="mr-2"> 
                                            <h6 class="mb-0">{{ __('messages.arabic') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>{{ __('messages.title_ar') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="title_ar" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>{{ __('messages.body_ar') }} <span class="text-danger">*</span></label>
                                                <textarea name="body_ar" class="form-control" rows="3" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-light d-flex align-items-center">
                                            <img src="/images/english-flag.png" width="30" class="mr-2"> 
                                            <h6 class="mb-0">{{ __('messages.english') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>{{ __('messages.title_en') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="title_en" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>{{ __('messages.body_en') }} <span class="text-danger">*</span></label>
                                                <textarea name="body_en" class="form-control" rows="3" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-primary btn-lg px-5 d-flex align-items-center justify-content-center" data-toggle="modal" data-target="#confirmSendModal">
                                    <span id="sendIcon"><i class="fas fa-paper-plane"></i></span>
                                    <span class="ml-2">{{ __('messages.send') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmSendModal" tabindex="-1" aria-labelledby="confirmSendModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-circle"></i> {{ __('messages.confirmation') }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <p class="font-weight-bold">{{ __('messages.confirm_send_notification') }}</p>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
                        <i class="fas fa-times"></i> {{ __('messages.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary px-4 d-flex align-items-center" id="confirmSend">
                        <div id="lottieSend" style="width: 30px; height: 30px; display: none;"></div>
                        <span class="ml-2">{{ __('messages.send') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.9.6/lottie.min.js"></script>
    <script>
        if (session('success')) {
            toastr.success("{{ session('success') }}");
        }

        document.getElementById('confirmSend').addEventListener('click', function() {
            document.getElementById('push_notification').submit();
        });

        var animation = lottie.loadAnimation({
            container: document.getElementById('lottieSend'),
            renderer: 'svg',
            loop: false,
            autoplay: false,
            path: 'https://assets3.lottiefiles.com/packages/lf20_touohxv0.json'
        });

        document.getElementById('confirmSend').addEventListener('click', function() {
            document.getElementById('lottieSend').style.display = 'block';
            animation.play();
        });
    </script>
</x-master-layout>