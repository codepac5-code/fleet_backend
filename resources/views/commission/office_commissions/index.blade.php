<x-master-layout>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="card-body p-0">
    <div class="py-3 d-flex gap-4 flex-wrap customer-info-detail mb-4">

        <div class="commission-card car-owners"
             data-type="driver_car"
             data-driver-car-commission="{{ $commissions->driver_car_commission_precentage ?? 0 }}"
             data-commission-with-driver-car="{{ $commissions->commission_with_driver_car ?? 0 }}">
            <div class="icon-container"><i class="fas fa-car"></i> <i class="fas fa-percentage"></i></div>
            <span class="com-title">{{ __('messages.drivers_with_car') }}</span>
            <ul class="list-info">
                  <li><span>{{ __('messages.office_commission') }}: {{ $commissions->commission_with_driver_car ?? 0 }}%</span></li>
                <li><span>{{ __('messages.driver_commission') }}: {{ $commissions->driver_car_commission_precentage ?? 0 }}%</span></li>            </ul>
        </div>

        <div class="commission-card regular"
             data-type="office_car"
             data-driver-commission="{{ $commissions->driver_commission_precentage ?? 0 }}"
             data-commission-with-office-car="{{ $commissions->commission_with_office_car ?? 0 }}">
            <div class="icon-container"><i class="fas fa-user-tie"></i> <i class="fas fa-percentage"></i></div>
            <span class="com-title">{{ __('messages.drivers_without_car') }}</span>
            <ul class="list-info">
                <li><span>{{ __('messages.office_commission') }}: {{ $commissions->commission_with_office_car ?? 0 }}%</span></li>
                  <li><span>{{ __('messages.driver_commission') }}: {{ $commissions->driver_commission_precentage ?? 0 }}%</span></li>            </ul>
        </div>
    </div>
</div>

    <div class="col-12">
        <div class="horizontal-separator"></div>
    </div>

    <div class="commission-icon-wrapper">
        <div class="commission-icon-container">
            <i class="fas fa-hand-holding-usd commission-icon"></i>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.commission-card').forEach(card => {
        card.style.cursor = "pointer";

        card.addEventListener('click', () => {
            const type = card.getAttribute('data-type');
            let currentCommission = type === 'driver_car'
                ? parseFloat(card.getAttribute('data-driver-car-commission')) || 0
                : parseFloat(card.getAttribute('data-driver-commission')) || 0;

            Swal.fire({
                title: type === 'driver_car' ? "{{ __('messages.drivers_with_car') }}" : "{{ __('messages.drivers_without_car') }}",
                html: `
                    <div style="text-align:center; font-size:16px;">
                        <label style="font-weight:600; display:block; margin-bottom:8px;">
                            {{ __('messages.driver_commission') }} (%)
                        </label>
                        <input id="commissionInput" type="number" min="0" max="100" step="0.1"
                               class="swal2-input"
                               style="width:60%; font-size:16px; border-radius:8px; padding:6px; text-align:center;"
                               value="${currentCommission}">
                        <div style="margin-top:12px; display:flex; justify-content:space-between; font-weight:600;">
                            <span id="fleetLabel">{{ __('messages.fleet_commission') }}: ${100-currentCommission}%</span>
                            <span id="customLabel">{{ __('messages.driver_commission') }}: ${currentCommission}%</span>
                        </div>
                        <div style="position:relative; height:25px; border-radius:12px; overflow:hidden; background:#eee; margin:10px auto; width:90%; display:flex;">
                            <div id="fleetBar" style="height:100%; background:#312873; width:${100-currentCommission}%; transition:0.3s;"></div>
                            <div id="customBar" style="height:100%; background:#F8A609; width:${currentCommission}%; transition:0.3s;"></div>
                        </div>
                        <div id="warningText" style="color:red; font-weight:600; margin-top:5px; display:none;">
                            {{ __('messages.value_between_0_100') }}
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: "{{ __('messages.save') }}",
                cancelButtonText: "{{ __('messages.cancel') }}",
                customClass: {
                    confirmButton: 'swal2-confirm-btn',
                    cancelButton: 'swal2-cancel-btn'
                },
                didOpen: () => {
                    const input = document.getElementById('commissionInput');
                    const fleetBar = document.getElementById('fleetBar');
                    const customBar = document.getElementById('customBar');
                    const fleetLabel = document.getElementById('fleetLabel');
                    const customLabel = document.getElementById('customLabel');
                    const warning = document.getElementById('warningText');

                    function updateBars() {
                        let val = parseFloat(input.value) || 0;
                        val = Math.min(Math.max(val,0),100);
                        fleetBar.style.width = (100-val)+'%';
                        customBar.style.width = val+'%';
                        fleetLabel.textContent = '{{ __('messages.office_commission') }}: '+(100-val)+'%';
                        customLabel.textContent = '{{ __('messages.driver_commission') }}: '+val+'%';
                        warning.style.display = (val < 0 || val > 100) ? 'block' : 'none';
                    }

                    input.addEventListener('input', () => {
                        input.value = Math.min(Math.max(parseFloat(input.value) || 0,0),100);
                        updateBars();
                    });

                    updateBars();
                },
                preConfirm: () => {
                    let val = parseFloat(document.getElementById('commissionInput').value);
                    if(isNaN(val) || val < 0 || val > 100){
                        Swal.showValidationMessage("{{ __('messages.value_between_0_100') }}");
                        return false;
                    }
                    return val;
                }
            }).then(result => {
                if(result.isConfirmed){
                    const newVal = result.value;
                    const payload = { type: type };

                    if(type === 'driver_car'){
                        payload.driver_car_commission = newVal;
                    } else {
                        payload.driver_commission = newVal;
                    }

                    fetch("{{ route('commissions.office.update') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success){
                            Swal.fire({
                                icon: 'success',
                                title: "{{ __('messages.success') }}",
                                text: data.message
                            });

                            // تحديث الكارد مباشرة بدون عمل Refresh
                            if(type === 'driver_car'){
                                card.setAttribute('data-driver-car-commission', newVal);
                            } else {
                                card.setAttribute('data-driver-commission', newVal);
                            }

                            const customSpan = card.querySelector('ul li:nth-child(2) span');
                            const fleetSpan = card.querySelector('ul li:nth-child(1) span');

                            customSpan.textContent = '{{ __('messages.driver_commission') }}: ' + newVal + '%';
                            fleetSpan.textContent = '{{ __('messages.fleet_commission') }}: ' + (100 - newVal) + '%';

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: "{{ __('messages.error') }}",
                                text: data.message || "{{ __('messages.save_error') }}"
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: "{{ __('messages.error') }}",
                            text: "{{ __('messages.connection_failed') }}"
                        });
                    });
                }
            });
        });
    });
});

</script>



    <style>
    .commission-icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100px;
    }

    .commission-icon-container {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--icon-bg, rgba(122, 241, 24, 0.103));
        transition: background-color 0.3s ease;
    }

    .commission-icon {
        font-size: 32px;
        color: var(--primary-color, #e6ac0f);
        animation: pulse 1.5s infinite alternate ease-in-out;
        transition: color 0.3s ease;
    }

    @keyframes pulse {
        from {
            transform: scale(1);
            opacity: 0.8;
        }
        to {
            transform: scale(1.2);
            opacity: 1;
        }
    }

    body.dark .commission-icon-container {
        background: var(--icon-bg-dark, rgba(255, 255, 255, 0.1));
    }

    body.dark .commission-icon {
        color: var(--text-dark, #f8f9fa);
    }

    .commission-card {
        position: relative;
        background: var(--card-bg, #fff);
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        flex-grow: 1;
        min-width: 320px;
        max-width: 450px;
        text-align: center;
        opacity: 0;
        animation: fadeIn 0.5s forwards, floatUp 1s infinite alternate ease-in-out;
        margin-bottom: 25px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease, color 0.3s ease;
        color: #333;
    }

    body.dark .commission-card {
        background: #1e1e2f;
        box-shadow: 0 6px 15px rgba(248, 166, 9, 0.3);
        color: #f0e68c;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .commission-card:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
    }

    .icon-container {
        font-size: 40px;
        color: var(--icon-color, #555);
        margin-bottom: 15px;
        animation: rotateIcon 1.5s infinite alternate ease-in-out;
        transition: color 0.3s ease;
    }

    body.dark .icon-container {
        color: #f8a609;
    }

    @keyframes rotateIcon {
        from { transform: rotate(-6deg); }
        to { transform: rotate(7deg); }
    }

    .com-title {
        font-size: 22px;
        font-weight: bold;
        color: var(--primary-color, #004085);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
        transition: color 0.3s ease;
    }

    body.dark .com-title {
        color: #f8a609;
    }

    .list-info li {
        font-size: 18px;
        color: #333;
        margin: 6px 0;
        transition: color 0.3s ease;
    }

    body.dark .list-info li {
        color: #f0e68c;
    }

    .customer-info-detail {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
    }

    .car-owners {
        --card-bg: #dcfae3;
        --primary-color: #155724;
        --icon-color: #0B3D0B;
    }

    body.dark .car-owners {
        --card-bg: #214d20;
        --primary-color: #a5d675;
        --icon-color: #7ebc59;
    }

    .regular {
        --card-bg: #d6e8fa;
        --primary-color: #004085;
        --icon-color: #002752;
    }

    body.dark .regular {
        --card-bg: #2b3a5c;
        --primary-color: #f8a609;
        --icon-color: #c48c00;
    }

    body.dark .car-owners,
    body.dark .regular {
        background-color: var(--card-bg);
    }

    body.dark .car-owners .com-title,
    body.dark .regular .com-title {
        color: var(--primary-color);
    }

    body.dark .car-owners .icon-container,
    body.dark .regular .icon-container {
        color: var(--icon-color);
    }

    @media (max-width: 800px) {
        .commission-card { min-width: 100%; }
    }


    .swal2-confirm-btn {
    background: #312873 !important;
    color: #fff !important;
    font-weight: 700;
    border-radius: 12px;
    padding: 12px 30px;
    font-size: 16px;
    box-shadow: 0 6px 15px rgba(49, 40, 115, 0.4);
    transition: all 0.3s ease;
}

.swal2-confirm-btn:hover {
    background: #1e1960 !important;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(49, 40, 115, 0.5);
}

.swal2-cancel-btn {
    background: #f8a609 !important;
    color: #fff !important;
    font-weight: 700;
    border-radius: 12px;
    padding: 12px 30px;
    font-size: 16px;
    box-shadow: 0 6px 15px rgba(248, 166, 9, 0.4);
    transition: all 0.3s ease;
}

.swal2-cancel-btn:hover {
    background: #d17f00 !important;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(248, 166, 9, 0.5);
}

    </style>
</x-master-layout>
