<x-master-layout>
    <div class="container d-flex flex-column align-items-center justify-content-center min-vh-100">
        <!-- أيقونة المحفظة التفاعلية -->
        <div class="wallet-animation-container">
            <div class="wallet-icon-box">
                <i class="fas fa-wallet wallet-icon"></i>
                <div class="coin"></div>
            </div>
        </div>

        <!-- كارد البحث -->
        <div class="card glassmorphism mt-4 p-4" style="max-width: 600px; width: 100%;">
            <div class="card-header text-center">
                <h4 class="mb-0 text-gradient">{{ __('messages.add_balance') }}</h4>
            </div>
            <div class="card-body">
                <form id="wallet-form">
                    <div class="form-group">
                        <label>{{ __('messages.select_user_type') }}</label>
                        <select name="userType" class="form-control select2js stylish-select" id="userType" required>
                            <option value="">{{ __('messages.select') }}</option>
                            <option value="driver">{{ __('messages.driver') }}</option>
                            <option value="user">{{ __('messages.user') }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ __('messages.phone_number') }}</label>
                        <input type="text" name="phoneNumber" id="phoneNumber" class="form-control input-glow" placeholder="{{ __('messages.enter_phone') }}" required>
                    </div>

                    <button type="button" id="searchUser" class="btn btn-primary btn-block mt-3 stylish-btn">
                        <i class="fa fa-search"></i> {{ __('messages.search') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- بيانات المستخدم -->
        <div class="card glassmorphism mt-4 p-4 d-none" id="userInfoCard" style="max-width: 600px; width: 100%;">
            <div class="card-body text-center">
                <h5 class="text-gradient">{{ __('messages.user_information') }}</h5>
                <div class="wallet-balance-container">
                    <i class="fas fa-wallet text-success fa-3x wallet-pulse"></i>
                    <h2 class="text-success font-weight-bold" id="walletBalance">0.00</h2>
                </div>
                <ul class="list-group mt-3">
                    <li class="list-group-item"><strong>{{ __('messages.name') }}:</strong> <span id="userName">-</span></li>
                    <li class="list-group-item"><strong>{{ __('messages.phone_number') }}:</strong> <span id="userPhone">-</span></li>
                    <li class="list-group-item"><strong>{{ __('messages.address') }}:</strong> <span id="userAddress">-</span></li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('searchUser').addEventListener('click', function () {
    let userType = document.getElementById('userType').value;
    let phoneNumber = document.getElementById('phoneNumber').value;

    if (!userType || !phoneNumber) {
        showNotification('{{ __("messages.fill_all_fields") }}', 'error');
        return;
    }

    fetch(`/get-user-info?userType=${userType}&phoneNumber=${phoneNumber}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('userName').innerText = data.user.name;
                document.getElementById('userPhone').innerText = data.user.phone;
                document.getElementById('userAddress').innerText = data.user.address ?? '-';

                let balanceElement = document.getElementById('walletBalance');
                balanceElement.innerText = data.user.wallet_balance + ' {{ __("messages.currency") }}';

                // تأثير وميض عند تحديث الرصيد
                balanceElement.classList.add('balance-update');
                setTimeout(() => balanceElement.classList.remove('balance-update'), 1500);

                // إظهار الكارد بتأثير سلس
                let userInfoCard = document.getElementById('userInfoCard');
                userInfoCard.classList.remove('d-none');
                userInfoCard.classList.add('fade-in');
            } else {
                showNotification('{{ __("messages.user_not_found") }}', 'error');
            }
        })
        .catch(error => console.error('Error:', error));
});

/**
 * دالة لعرض رسالة تنبيه جمالية
 */
function showNotification(message, type = 'info') {
    let notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerText = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

    </script>

    <style>
        /* تأثيرات عصرية */
        .wallet-animation-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        .wallet-icon-box {
            position: relative;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: bounce 1.5s infinite;
        }

        .wallet-icon {
            font-size: 40px;
            color: white;
        }

        .coin {
            position: absolute;
            top: -10px;
            width: 20px;
            height: 20px;
            background: gold;
            border-radius: 50%;
            animation: dropCoin 2s infinite;
        }

        .glassmorphism {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .wallet-balance-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .wallet-pulse {
            animation: pulse 1.5s infinite;
        }

        .balance-update {
            animation: pop 0.5s ease-in-out;
        }

        .input-glow {
            transition: 0.3s;
        }

        .input-glow:focus {
            box-shadow: 0 0 10px rgba(0, 150, 255, 0.5);
        }

        .stylish-btn {
            transition: 0.3s;
            background: linear-gradient(135deg, #1e90ff, #007bff);
            border: none;
        }

        .stylish-btn:hover {
            background: linear-gradient(135deg, #007bff, #1e90ff);
        }

        .text-gradient {
            background: linear-gradient(135deg, #ff7eb3, #ff758c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes dropCoin {
            0% { transform: translateY(-10px); opacity: 1; }
            100% { transform: translateY(20px); opacity: 0; }
        }

        @keyframes pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</x-master-layout>
