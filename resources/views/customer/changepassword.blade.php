<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle }}</h5>
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
                        <form method="POST" action="{{ route('user.change-password') }}" id="handyman" data-toggle="validator">
                            @csrf
                            <input type="hidden" name="id" value="{{ $user->id ?? '' }}">

                            <div class="row">
                                <div class="col-md-6 offset-md-3">

                                    <div class="form-group col-md-12">
                                        <label for="old_password" class="form-control-label">
                                            {{ __('messages.old_password') }} <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="old_password" name="old_password" id="old_password" class="form-control" placeholder="{{ __('messages.old_password') }}" required>
                                            <span class="input-group-text toggle-password" data-target="old_password" style="cursor: pointer;">
                                                <i class="fa fa-eye"></i>
                                            </span>
                                        </div>
                                        @error('old_password') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>





                                    <div class="form-group col-md-12">
                                        <label for="new_password" class="form-control-label">
                                            {{ __('messages.new_password') }} <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="{{ __('messages.new_password') }}" required>
                                            <span class="input-group-text toggle-password" data-target="new_password" style="cursor: pointer;">
                                                <i class="fa fa-eye"></i>
                                            </span>
                                        </div>
                                        @error('new_password') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                 
                                    
                                    <div class="form-group col-md-12">
                                        <label for="password_confirmation" class="form-control-label">
                                            {{ __('messages.confirm_new_password') }} <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="password" name="confirm_new_password" id="confirm_new_password" class="form-control" placeholder="{{ __('messages.confirm_new_password') }}" required>
                                            <span class="input-group-text toggle-password" data-target="confirm_new_password" style="cursor: pointer;">
                                                <i class="fa fa-eye"></i>
                                            </span>
                                        </div>
                                        @error('confirm_new_password') <small class="text-danger">{{ $message }}</small> @enderror
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

    <script>
        document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            let input = document.getElementById(this.getAttribute('data-target'));
            let icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    </script>
</x-master-layout>
