<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{$pageTitle}}</h5>
                            <a href="{{ route('user.index') }}" class="float-right btn btn-sm btn-primary">
                                <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="float-right">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#resetPassId{{$customerdata->id}}">
                                Reset Password
                            </button>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="resetPassId{{$customerdata->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Reset Password</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to reset 
                                        <small class="text-capitalize font-weight-bold">
                                            {{$customerdata->firstName .' '.$customerdata->lastName}}
                                        </small> password? </br>
                                        Default Password: 12345678
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="resetPassId{{$customerdata->id}}key">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
                        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                        <script>
                            $('#resetPassId{{$customerdata->id}}key').on('click', function(){
                                $.ajax({
                                    url: "{{route('user.userResetPassword')}}",
                                    data: {
                                        reqid: "{{$customerdata->id}}"
                                    },
                                    success: function(result) {
                                        if (result.status == 'success') {
                                            $('#modalClose').trigger('click');
                                            Swal.fire({
                                                title: "Success!",
                                                text: "Reset password success!",
                                                icon: "success"
                                            });
                                        } else {
                                            Swal.fire({
                                                title: "Error!",
                                                text: "Reset password error!",
                                                icon: "error"
                                            });
                                        }
                                    }
                                });
                            });
                        </script>

                        <!-- استبدال الفورم بـ form عادية -->
                        <form method="POST" action="{{ route('user.changepassword') }}" data-toggle="validator" id="user">
                            @csrf
                            <input type="hidden" name="id" value="{{ $customerdata->id }}">

                            <div class="row">
                                <div class="col-md-6 offset-md-3">
                                    <div class="form-group">
                                        <label for="old_password" class="form-control-label col-md-12">
                                            {{ __('messages.old_password') }} <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-md-12">
                                            <input type="password" name="old" id="old_password" class="form-control" placeholder="{{ __('messages.old_password') }}" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="password" class="form-control-label col-md-12">
                                            {{ __('messages.new_password') }} <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-md-12">
                                            <input type="password" name="password" id="password" class="form-control" placeholder="{{ __('messages.new_password') }}" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="password-confirm" class="form-control-label col-md-12">
                                            {{ __('messages.confirm_new_password') }} <span class="text-danger">*</span>
                                        </label>
                                        <div class="col-md-12">
                                            <input type="password" name="password_confirmation" id="password-confirm" class="form-control" placeholder="{{ __('messages.confirm_new_password') }}" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-12">
                                            <button type="submit" id="submit" class="btn btn-md btn-primary float-md-right mt-15">
                                                {{ __('messages.save') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-master-layout>
