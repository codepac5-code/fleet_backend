<x-master-layout>
    <div class="container-fluid">
        <div class="row justify-content-center">


            <div class="col-12 text-center my-4">
                <div class="wallet-icon-container">
                    <i class="fas fa-wallet wallet-icon"></i>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-3">{{ __('messages.add_balance') }}</h4>

                        {{-- id="searchUserForm"  --}}
                        <form  method="GET" action="{{ route('uuu') }}" enctype="multipart/form-data" id="vehicleForm">
                            @csrf
                            <div class="form-group">
                                <label>{{ __('messages.select_type') }}</label>
                                <select name="userType" id="userType" class="form-control">
                                    <option value="user">{{ __('messages.user') }}</option>
                                    <option value="driver">{{ __('messages.driver') }}</option>
                                </select>
                            </div>


                            <div class="form-group">
                                <label>{{ __('messages.phone_number') }}</label>
                                <input type="text" id="phoneNumber" class="form-control" placeholder="{{ __('messages.enter_phone') }}">
                            </div>

                            
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> {{ __('messages.search') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

           <div class="col-lg-8 mt-4" id="userInfoSection" style="display: none;"> 
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-3">{{ __('messages.user_information') }}</h4>
                        <ul class="list-unstyled">
                            <li><strong>{{ __('messages.name') }}:</strong> <span id="userName"></span></li>
                            <li><strong>{{ __('messages.phone_number') }}:</strong> <span id="userPhone"></span></li>
                            <li><strong>{{ __('messages.address') }}:</strong> <span id="userAddress"></span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 d-flex justify-content-center">

            <div class="col-lg-4 mt-4" id="walletCard" style="display: none;">
                <div class="card wallet-card text-white bg-success shadow-lg rounded-4">
                    <div class="card-body text-center">
                        <i class="fas fa-wallet fa-3x mb-3 animated-icon"></i>
                        <h5 class="fw-bold">{{ __('messages.wallet_balance') }}</h5>
                        <h2 id="walletBalance" class="fw-bolder">0.00</h2>
                        <button class="btn btn-light text-success fw-bold mt-3 px-4 py-2 add-balance-btn">
                            <i class="fas fa-plus-circle me-2"></i> {{ __('messages.add_balance') }}
                        </button>
                    </div>
                </div>
            </div>
        
            <div class="col-lg-4 mt-4" id="earningsCard" style="display: none;">
                <div class="card earnings-card text-white bg-primary shadow-lg rounded-4">
                    <div class="card-body text-center">
                        <i class="fas fa-money-bill-wave fa-3x mb-3 animated-icon"></i>
                        <h5 class="fw-bold">{{ __('messages.total_earnings') }}</h5>
                        <h2 id="totalEarnings" class="fw-bolder">0.00</h2>
                        <button class="btn btn-light text-primary fw-bold mt-3 px-4 py-2 pay-earnings-btn">
                            <i class="fas fa-credit-card me-2"></i> {{ __('messages.pay_earnings') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-striped border">
                </table>
              </div>
        </div>
    </div>


<script>
    document.getElementById("searchUserForm").addEventListener("submit", function (e) {

        window.renderedDataTable = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        responsive: true,
        dom: '<"row align-items-center"><"table-responsive my-3" rt><"row align-items-center" <"col-md-6" l><"col-md-6" p>><"clear">',
        ajax: {
            "type": "GET",
            "url": "", 
            "data": function (d) {
                d.search = {
                    value: $('.dt-search').val()
                };
            },
        },
        columns: [
            {
                name: 'check',
                data: 'check',
                title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                exportable: false,
                orderable: false,
                searchable: false,
            },
            {
                data: 'bookingId',
                name: 'bookingId',
                title: "{{__('messages.bookingId')}}"
            },
            {
                data: 'payment_methode',
                name: 'payment_methode',
                title: "{{__('messages.payment_methode')}}"
            },
            {
                data: 'amount',
                name: 'amount',
                title: "{{__('messages.amount')}}"
            },
            {
                data: 'payment_status',
                name: 'payment_status',
                title: "{{__('messages.payment_status')}}"
            },
            {
                data: 'discount',
                name: 'discount',
                title: "{{__('messages.discount')}}"
            },
            {
                data: 'commission',
                name: 'commission',
                title: "{{__('messages.commission')}}"
            },
            {
                data: 'Payment_datetime',
                name: 'Payment_datetime',
                title: "{{__('messages.Payment_datetime')}}"
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                title: "{{__('messages.action')}}"
            }
        ]
    });
    e.preventDefault();

    let userType = document.getElementById("userType").value;
    let phoneNumber = document.getElementById("phoneNumber").value;

    fetch(`{{ route('wallet.getUserInfo') }}?userType=${userType}&phoneNumber=${phoneNumber}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("userName").textContent = data.user.name;
                document.getElementById("userPhone").textContent = data.user.phone;
                document.getElementById("userAddress").textContent = data.user.address;
                document.getElementById("walletBalance").textContent = data.user.wallet_balance;
                document.getElementById("totalEarnings").textContent = (parseFloat(data.user.wallet_balance) * 1.2).toFixed(2); // مستحقات متوقعة

                document.getElementById("userInfoSection").style.display = "block";
                document.getElementById("walletCard").style.display = "block";
                document.getElementById("earningsCard").style.display = "block";

                let transactionsUrl = `{{ route('wallet.getTransactions') }}?userType=${userType}&phoneNumber=${phoneNumber}`;
                renderedDataTable.ajax.url(transactionsUrl).load();
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));


});

</script>
{{-- 
<script>
    // document.addEventListener('DOMContentLoaded', (event) => {
  
});

</script> --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <style>
        .wallet-icon-container {
            position: relative;
            width: 80px;
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
    border-radius: 50px;
    transition: all 0.3s ease-in-out;
}

.add-balance-btn:hover {
    background-color: #28a745;
    color: white;
    transform: scale(1.05);
}

.pay-earnings-btn:hover {
    background-color: #007bff;
    color: white;
    transform: scale(1.05);
}

    </style>
</x-master-layout>
