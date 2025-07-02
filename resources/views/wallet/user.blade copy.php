<x-master-layout>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card card-block card-stretch">
                                <div class="card-body p-0">
                                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                                        <h5 class="font-weight-bold">{{$pageTitle}}</h5>
                                        <a href="{{ route('add.wallet') }}   " class="float-right btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                                                


                        <div class="col-lg-12">
                            <div class="col-lg-12">
                                <div class="card card-block card-stretch">
                                    <div class="card-body">
                                        <h5 class="card-title">{{__('messages.pending_trans')}}</h5>
                                        <div class="table-responsive-sm">
                                            <table class="table mb-0">
                                                <thead class="table-color-heading">
                                                    <tr class="text-secondary">
                                                        <th scope="col">{{__('messages.service')}}</th>
                                                        <th scope="col">{{__('messages.date')}}</th>
                                                        <th scope="col">{{__('messages.payment_status')}}</th>
                                                        <th scope="col" class="text-right">{{__('messages.total_amount')}}</th>
                                                    </tr>
                                                </thead>
                                                {{-- <tbody>
                                                    @if(count($customer_pending_trans) > 0)
                                                        @foreach($customer_pending_trans as $pending)
                                                            <tr class="white-space-no-wrap">
                                                                <td>{{$pending->booking->service->name}}</td>
                                                                <td> <div class="d-flex align-items-center">{{date("D, d M Y", strtotime($pending->booking->date))}}</div></td>
                                                                <td>{{ $pending->payment_status}}</td>
                                                                <td class="text-right">{{ getPriceFormat($pending->total_amount)}}</td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                    <tr>
                                                        <td colspan="4" class="text-center font-weight-bold">{{__('messages.record_not_found')}}</td>
                                                    </tr>
                                                    @endif
                                                </tbody> --}}
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-block card-stretch">

                                
                                <div class="card-body">
                                <h5 class="card-title">{{__('messages.payment_details')}}</h5>
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <thead class="table-color-heading">
                                                <tr class="text-secondary">
                                                <th scope="col">{{__('messages.service')}}</th>
                                                <th scope="col">{{__('messages.date')}}</th>
                                                <th scope="col">{{__('messages.status')}}</th>
                                                <th scope="col">{{__('messages.provider')}}</th>
                                                <th scope="col" class="text-right">{{__('messages.total_amount')}}</th>
                                                </tr>
                                            </thead>


                                            <tbody>
                                                @if(count($data['transactions']) > 0)
                                                    @foreach($data['transactions'] as $transaction)
                                                        <tr class="white-space-no-wrap">
                                                            <td>{{optional($transaction->subService)->name}}</td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                            
                                                                    <div>{{date("D, d M Y", strtotime($transaction->created_at))}}</div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <p class="mb-0  d-flex justify-content-start align-items-center">
                                                                    
                                                                    {{str_replace("_"," ",ucfirst($transaction->status))}}
                                                                </p>
                                                            </td>
                                                            <td>{{optional($transaction->office)->officeName ?? '-' }}</td>
                                                            <td class="text-right">{{getPriceFormat($transaction->totalAmount ?? '0') }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="5" class="text-center font-weight-bold">{{__('messages.record_not_found')}}</td>
                                                    </tr>
                                                @endif
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                  
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="row">
                        <div class="col-lg-12 col-md-6">
                            <div class="card card-block p-card">
                                <div class="profile-box">
                                    <div class="profile-card rounded">
                                        <img src="{{$customerdata->photo}}" alt="profile-bg" class="avatar-100 d-block mx-auto img-fluid mb-3  avatar-rounded">
                                        <h3 class="font-600 text-black text-center mb-5">{{$customerdata->firstName .' ' .$customerdata->lastName }}</h3>
                                    </div>
                                    <div class="pro-content rounded">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="p-icon mr-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="text-primary" width="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path>
                                                </svg>
                                            </div>
                                            <p class="mb-0 eml">{{$customerdata->firstName .' '.$customerdata->lastName}}</p>
                                        </div>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="p-icon mr-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="text-primary" width="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path>
                                                </svg>
                                            </div>
                                            <p class="mb-0">{{$customerdata->phoneNumber}}</p>
                                        </div>
                                        @if(!empty($customerdata->address))
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="p-icon mr-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="text-primary" width="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                            </div>
                                            <p class="mb-0">{{$customerdata->address}}</p>
                                        </div>
                                        @endif
                                        
                         
                                    </div>
                                </div>

                                @if ($isDriver)
                                <div class="card earnings-card text-white bg-danger shadow-lg rounded-4">
                                    <div class="card-body text-center">
                                        <i class="fas fa-money-bill-wave fa-3x mb-3 animated-icon"></i>
                                        <h5 class="fw-bold">{{ __('messages.dues') }}</h5>
                                        <h2 id="dues" class="fw-bolder">{{$customerdata->officeDues +$customerdata->fleetDues }}</h2>
                                    </div>
                                </div>
                                @endif



                                <div class="card wallet-card text-white bg-success shadow-lg rounded-4">
                                    <div class="card-body text-center">
                                        <i class="fas fa-wallet fa-3x mb-3 animated-icon"></i>
                                        <h5 class="fw-bold">{{ __('messages.wallet_balance') }}</h5>
                                        <h2 id="walletBalance" class="fw-bolder">{{$customerdata->walletBalance}}</h2>
                                        <button class="btn btn-light text-success fw-bold mt-3 px-4 py-2 add-balance-btn" data-bs-toggle="modal" data-bs-target="#addBalanceModal">
                                            <i class="fas fa-plus-circle me-2"></i> {{ __('messages.add_balance') }}
                                        </button>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="addBalanceModal"  >
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-money-bill-wave me-2"></i> {{ __('messages.add_balance') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="fw-semibold">{{ __('messages.enter_balance_amount') }}</p>
                <input type="number" id="balanceAmount" class="form-control text-center fw-bold fs-4 border-success" min="0" step="500" placeholder="0.00">
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button class="btn btn-outline-success fw-bold px-4 py-2" id="confirmAddBalance">
                    <i class="fas fa-check-circle me-2"></i> {{ __('messages.confirm') }}
                </button>
                <button class="btn btn-danger px-4 py-2" data-bs-dismiss="modal">
                    <i class="fas fa-times-circle me-2"></i> {{ __('messages.cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast-balance" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-bold">
                <i class="fas fa-check-circle me-2"></i> {{ __('messages.balance_added_successfully') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>


<script>
    document.getElementById("confirmAddBalance").addEventListener("click", function () {
        let amount = parseFloat(document.getElementById("balanceAmount").value);
        let userId = "{{ $customerdata->id }}";
        let userType = "{{ $userType}}";

        if (isNaN(amount) || amount <= 0) {
            Swal.fire({
                icon: "error",
                title: "{{ __('messages.invalid_amount') }}",
                text: "{{ __('messages.please_enter_valid_amount') }}",
                confirmButtonColor: "#dc3545",
            });
            return;
        }

        Swal.fire({
            title: "{{ __('messages.confirm_balance_addition') }}",
            text: `{{ __('messages.add_balance_question') }} ${amount.toFixed(2)} {{ __('messages.question_mark')}}`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#dc3545",
            confirmButtonText: "{{ __('messages.yes_add_now') }}",
            cancelButtonText: "{{ __('messages.cancel') }}"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "{{ __('messages.processing') }}",
                    text: "{{ __('messages.please_wait') }}",
                    icon: "info",
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch("{{ route('add-balance') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        amount: amount,
                        userId: userId,
                        userType: userType
                    })
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                if (data.success) {
                    let wallet = document.getElementById("walletBalance");
                    if (wallet) {
                        wallet.textContent = data.walletBalance;
                        wallet.classList.add("wallet-shake");
                        setTimeout(() => {
                            wallet.classList.remove("wallet-shake");
                        }, 500);
                    }

                    let dues = document.getElementById("dues");
                    if (dues) {
                        dues.textContent = data.dues;
                    }

                    const modal = bootstrap.Modal.getInstance(document.getElementById('addBalanceModal'));
                    modal.hide();

                    document.getElementById("balanceAmount").value = "";
                        
                        let toast = new bootstrap.Toast(document.getElementById('toast-balance'));
                        toast.show();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "{{ __('messages.error') }}",
                            text: data.message,
                            confirmButtonColor: "#dc3545"
                        });
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.close(); 
                    Swal.fire({
                        icon: "error",
                        title: "{{ __('messages.balance_not_added') }}",
                        text: "{{ __('messages.server_error') }}",
                        confirmButtonColor: "#dc3545"
                    });
                });
            }
        });
    });
</script>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <style>

        .wallet-icon-container {
            position: relative;
            width: 150px;
            height: 80px;
            background: linear-gradient(135deg, #1e1e2f, #252540);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            animation: pulse 1.5s infinite;
        }

        .wallet-icon {
            font-size: 40px;
            color: #ffc107;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.9; }
            100% { transform: scale(1); opacity: 1; }
        }

        .card {
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .wallet-card, .earnings-card {
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    cursor: pointer;
} 

 .wallet-card:hover, .earnings-card:hover {
    transform: translateY(-5px);
    box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
} 

.animated-icon {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.add-balance-btn, .pay-earnings-btn {
    border-radius: 70px;
    transition: all 0.3s ease-in-out;
}

.add-balance-btn:hover {
    background-color: #fad501;
    color: white;
    transform: scale(1.05);
}

.pay-earnings-btn:hover {
    background-color: #007bff;
    color: white;
    transform: scale(1.05);
}

@keyframes walletShake {
    0%   { transform: translateX(0); }
    25%  { transform: translateX(-5px); }
    50%  { transform: translateX(5px); }
    75%  { transform: translateX(-5px); }
    100% { transform: translateX(0); }
}

.wallet-shake {
    animation: walletShake 0.5s ease;
}

    </style>
</x-master-layout>
