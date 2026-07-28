<x-master-layout>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="card-body p-0">
    <div class="py-3 d-flex gap-4 flex-wrap customer-info-detail mb-4">
        <div class="commission-card car-owners"
             data-type="driver"
             data-fleet-commission="{{ $commissions->fleet_commission_value_with_driver ?? 0 }}"
             data-driver-commission="{{ $commissions->driver_commission_value ?? 0 }}">
            <div class="icon-container"><i class="fas fa-car"></i></div>
            <span class="com-title">{{ __('messages.drivers_commission') }}</span>
            <ul class="list-info">
                <li><span>{{ __('messages.fleet_commission') }}: {{ $commissions->fleet_commission_value_with_driver ?? 0 }}%</span></li>
                <li><span>{{ __('messages.driver_commission') }}: {{ $commissions->driver_commission_value ?? 0 }}%</span></li>
            </ul>
        </div>

        <div class="commission-card regular"
             data-type="office"
             data-fleet-commission="{{ $commissions->fleet_commission_value_with_office ?? 0 }}"
             data-office-commission="{{ $commissions->office_commission_value ?? 0 }}">
            <div class="icon-container"><i class="fas fa-percentage"></i></div>
            <span class="com-title">{{ __('messages.offices_commission') }}</span>
            <ul class="list-info">
                <li><span>{{ __('messages.fleet_commission') }}: {{ $commissions->fleet_commission_value_with_office ?? 0 }}%</span></li>
                <li><span>{{ __('messages.office_commission') }}: {{ $commissions->office_commission_value ?? 0 }}%</span></li>
            </ul>
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
            const type = card.getAttribute('data-type'); // 'driver' أو 'office'
            let currentCommission = type === 'driver'
                ? parseFloat(card.getAttribute('data-driver-commission')) || 0
                : parseFloat(card.getAttribute('data-office-commission')) || 0;

            Swal.fire({
                title: type === 'driver' ? "{{ __('messages.driver_commission') }}" : "{{ __('messages.office_commission') }}",
                html: `
                    <div style="text-align:center; font-size:16px;">
                        <label style="font-weight:600; display:block; margin-bottom:8px;">
                            ${type === 'driver' ? "{{ __('messages.driver_commission') }} (%)" : "{{ __('messages.office_commission') }} (%)"}
                        </label>
                        <input id="commissionInput" type="number" min="0" max="100" step="0.1"
                               class="swal2-input"
                               style="width:60%; font-size:16px; border-radius:8px; padding:6px; text-align:center;"
                               value="${currentCommission}">
                        <div style="margin-top:12px; display:flex; justify-content:space-between; font-weight:600;">
                            <span id="fleetLabel">{{ __('messages.fleet_commission') }}: ${100-currentCommission}%</span>
                            <span id="customLabel">${type==='driver' ? "{{ __('messages.driver_commission') }}" : "{{ __('messages.office_commission') }}"}: ${currentCommission}%</span>
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
                        fleetLabel.textContent = '{{ __('messages.fleet_commission') }}: '+(100-val)+'%';
                        customLabel.textContent = (type==='driver' ? "{{ __('messages.driver_commission') }}" : "{{ __('messages.office_commission') }}") + ': '+val+'%';
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
                    const payload = { type: type === 'driver' ? 'fleet_driver' : 'fleet_office' };

                    if(type === 'driver'){
                        payload.driver_commission = newVal;
                        card.setAttribute('data-driver-commission', newVal);
                        card.querySelector('ul li:nth-child(2) span').textContent = "{{ __('messages.driver_commission') }}: "+newVal+"%";
                        card.querySelector('ul li:nth-child(1) span').textContent = "{{ __('messages.fleet_commission') }}: "+(100-newVal)+"%";
                    } else {
                        payload.office_commission = newVal;
                        card.setAttribute('data-office-commission', newVal);
                        card.querySelector('ul li:nth-child(2) span').textContent = "{{ __('messages.office_commission') }}: "+newVal+"%";
                        card.querySelector('ul li:nth-child(1) span').textContent = "{{ __('messages.fleet_commission') }}: "+(100-newVal)+"%";
                    }

                    fetch("{{ route('commissions.fleet.update') }}", {
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

:root {
            --icon-bg: rgba(122, 241, 24, 0.103);
            --primary-color: #004085;
            --text-color: #333;
            --card-bg-car-owners: #dcfae3;
            --primary-color-car-owners: #155724;
            --icon-color-car-owners: #0B3D0B;
            --card-bg-regular: #d6e8fa;
            --primary-color-regular: #004085;
            --icon-color-regular: #002752;
            --card-bg-default: #fff;
            --card-shadow-default: rgba(0, 0, 0, 0.15);
        }

        body.dark {
            --icon-bg: rgba(255, 255, 255, 0.1);
            --primary-color: #f8a609;
            --text-color: #f8f9fa;
            --card-bg-car-owners: #254724;
            --primary-color-car-owners: #a8d5a1;
            --icon-color-car-owners: #b4d8b4;
            --card-bg-regular: #223e66;
            --primary-color-regular: #a1c0f9;
            --icon-color-regular: #8aaee8;
            --card-bg-default: #1e1e2f;
            --card-shadow-default: rgba(248, 166, 9, 0.3);
        }

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
            background: var(--icon-bg);
            transition: background-color 0.3s ease;
        }

        .commission-icon {
            font-size: 32px;
            color: var(--primary-color);
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

        .commission-card {
            position: relative;
            background: var(--card-bg-default);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 6px 15px var(--card-shadow-default);
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
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
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
            color: var(--icon-color-regular);
            margin-bottom: 15px;
            animation: rotateIcon 1.5s infinite alternate ease-in-out;
            transition: color 0.3s ease;
        }

        .car-owners .icon-container {
            color: var(--icon-color-car-owners);
        }

        @keyframes rotateIcon {
            from { transform: rotate(-6deg); }
            to { transform: rotate(7deg); }
        }

        .com-title {
            font-size: 22px;
            font-weight: bold;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            transition: color 0.3s ease;
        }

        .car-owners .com-title {
            color: var(--primary-color-car-owners);
        }

        .list-info li {
            font-size: 18px;
            color: var(--text-color);
            margin: 6px 0;
            transition: color 0.3s ease;
        }

        .customer-info-detail {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }

        .car-owners {
            background: var(--card-bg-car-owners);
            --primary-color: var(--primary-color-car-owners);
            --icon-color: var(--icon-color-car-owners);
            transition: background-color 0.3s ease;
        }

        .regular {
            background: var(--card-bg-regular);
            --primary-color: var(--primary-color-regular);
            --icon-color: var(--icon-color-regular);
            transition: background-color 0.3s ease;
        }

        @media (max-width: 800px) {
            .commission-card { min-width: 100%; }
        }
.commission-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}
.commission-card .icon-container {
    font-size: 45px;
    color: #004085;
    margin-bottom: 15px;
    transition: transform 0.3s;
}
.commission-card:hover .icon-container { transform: scale(1.2); }
.commission-card .com-title {
    font-size: 24px;
    font-weight: 700;
    color: #004085;
    margin-bottom: 10px;
}
.commission-card ul.list-info li {
    font-size: 18px;
    margin: 6px 0;
}
.swal2-confirm-btn {
    background: #312873 !important;
    color: #fff !important;
    font-weight: bold;
    border-radius: 8px;
    padding: 10px 25px;
}
.swal2-confirm-btn:hover {
    background: #1e1960 !important;
}
.swal2-cancel-btn {
    background: #f8a609 !important;
    color: #fff !important;
    font-weight: bold;
    border-radius: 8px;
    padding: 10px 25px;
}
.swal2-cancel-btn:hover {
    background: #d17f00 !important;
}
</style>
</x-master-layout>
