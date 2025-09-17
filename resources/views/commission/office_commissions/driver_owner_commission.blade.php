<x-master-layout>
    <div class="container-fluid py-4">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                        <h5 class="font-weight-bold">{{ __('messages.commission') }}</h5>
                        <a href="{{ route('commission.index') }}" class="float-right btn btn-sm btn-primary">
                            <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="custom-commission-container p-4 shadow rounded">
                    <h4 class="custom-commission-title text-center mb-4">{{ __('messages.commission_settings') }}</h4>
                    
                    <!-- Progress Bar -->
                    <div class="progress mb-3">
                        <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%">
                            0%
                        </div>
                    </div>
                    
                    <div class="total-percentage-container text-center">
                        {{ __('messages.remaining_percentage') }}: <span class="total-percentage font-weight-bold" id="remainingPercentage">100%</span>
                        <p class="comm-warning-text text-danger mt-1 d-none" id="warningMessage">{{ __('messages.commission_warning_text') }}</p>
                    </div>
                    
                    <form action="{{ route('commissions.update') }}" method="POST" class="mt-3">
                        @csrf
                        @method('POST')

                        <input type="hidden" name="type" value="free-driver">
                        
                        <!-- Office Commission Title -->
                        <div class="mb-2">
                            <h5 class="font-weight-bold">{{ __('messages.office_commission') }}</h5>
                        </div>

                        <!-- Office Commission -->
                        <div class="custom-commission-input-group mb-3 d-flex align-items-center">
                            <i class="fas fa-building fa-lg me-2 text-primary"></i>
                            <input type="number" name="office_percentage" class="form-control" id="office" 
                                value="{{ $office->commission_with_driver_car ?? 0 }}" min="0" max="100">
                            <span class="ms-2">%</span>
                        </div>
                        
                        <!-- Driver Commission Title -->
                        <div class="mb-2">
                            <h5 class="font-weight-bold">{{ __('messages.driver_commission') }}</h5>
                        </div>
                        <!-- Driver Commission -->
                        <div class="custom-commission-input-group mb-3 d-flex align-items-center">
                            <i class="fas fa-building fa-lg me-2 text-primary"></i>
                            <input type="number" name="driver_percentage" class="form-control" id="driver" 
                                value="{{ $office->driver_car_commission_precentage ?? 0 }}" min="0" max="100">
                            <span class="ms-2">%</span>
                        </div>
                        
                        <!-- Reset Defaults Button -->
                        <button type="button" id="resetDefaults" class="btn btn-secondary w-100 py-2 mb-2">
                            {{ __('messages.reset_defaults') }}
                        </button>
                        
                        <button id="commissionBtn" class="btn btn-primary w-100 py-2" disabled>
                            <span id="buttonText">{{ __('messages.update_commission') }}</span>
                            <span id="loadingSpinner" class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    
    
    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const inputs = document.querySelectorAll("input[type='number']");
            const remainingPercentage = document.getElementById("remainingPercentage");
            const warningMessage = document.getElementById("warningMessage");
            const progressBar = document.getElementById("progressBar");
            const resetButton = document.getElementById("resetDefaults");
            const commissionBtn = document.getElementById("commissionBtn");
    
            function updatePercentage() {
                let total = 0;
                inputs.forEach(input => total += parseFloat(input.value) || 0);
                let remaining = 100 - total;
                remainingPercentage.textContent = remaining + "%";
                progressBar.style.width = (100 - remaining) + "%";
                progressBar.textContent = (100 - remaining) + "%";
                
                if (remaining < 0) {
                    warningMessage.classList.remove("d-none");
                    progressBar.classList.remove("bg-success");
                    progressBar.classList.add("bg-danger");
                    commissionBtn.disabled = true; 
                } else {
                    warningMessage.classList.add("d-none");
                    progressBar.classList.remove("bg-danger");
                    progressBar.classList.add("bg-success");
                    commissionBtn.disabled = false; 
                }
            }
            
            inputs.forEach(input => {
                input.addEventListener("input", function() {
                    if (parseFloat(input.value) > 100) {
                        input.value = 100;
                    }
                    updatePercentage();
                });
            });
    
            resetButton.addEventListener("click", function () {
                inputs.forEach(input => input.value = 0);
                updatePercentage();
            });
    
            updatePercentage(); 
        });
    </script>
</x-master-layout>

<style>
    .custom-commission-container {
        padding: 30px; 
        max-width: 90%; 
        margin: auto;
    }

    .custom-commission-title {
        font-size: 30px; 
        font-weight: bold; 
    }

    

    .comm-warning-text {
    font-size: 20px; 
    font-weight: bold;
    padding: 10px;
}


    .custom-commission-input-group input.form-control {
        font-size: 18px;
        padding: 15px;
    }

    .btn {
        font-size: 18px;
        padding: 15px;
    }

    .progress {
        height: 20px; 
    }

    .progress-bar {
        font-size: 18px;
    }

    .d-flex {
        gap: 20px; 
    }

    .container-fluid {
        padding-left: 30px; 
        padding-right: 30px; 
    }

    .custom-commission-container .total-percentage {
        font-size: 24px;
    }
</style>