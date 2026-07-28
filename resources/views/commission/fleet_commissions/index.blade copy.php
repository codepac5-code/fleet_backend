<x-master-layout>
    <div class="card-body p-0">
        <div class="py-3 d-flex gap-4 flex-wrap customer-info-detail mb-4">

            @if(auth()->user()->can('edit commission'))

            <a href="{{ route('commissions.free-driver') }}" title="{{ __('messages.update_form_title',['form' => __('messages.commission') ]) }}">
                <div class="commission-card car-owners">
                    <div class="icon-container"><i class="fas fa-car"></i></div>
                    <span class="com-title">{{ __('messages.drivers_commission') }}</span>
                    <ul class="list-info">
                        <li><span>{{ __('messages.fleet_commission') }}: {{ $commissions->fleet_commission_value_with_driver ?? 0 }}%</span></li>
                        <li><span>{{ __('messages.driver_commission') }}: {{ $commissions->driver_commission_value ?? 0 }}%</span></li>
                    </ul>
                </div>
            </a>

            <a href="{{ route('commissions.fleet.office') }}" title="{{ __('messages.update_form_title',['form' => __('messages.commission') ]) }}">
                <div class="commission-card regular">
                    <div class="icon-container"><i class="fas fa-percentage"></i></div>
                    <span class="com-title">{{ __('messages.offices_commission') }}</span>
                    <ul class="list-info">
                        <li><span>{{ __('messages.fleet_commission') }}: {{ $commissions->fleet_commission_value_with_office ?? 0 }}%</span></li>
                        <li><span>{{ __('messages.office_commission') }}: {{ $commissions->office_commission_value ?? 0 }}%</span></li>
                    </ul>
                </div>
            </a>
            @else

            {{-- <a href="{{ route('commissions.free-driver') }}" title="{{ __('messages.update_form_title',['form' => __('messages.commission') ]) }}"> --}}
                <div class="commission-card car-owners">
                    <div class="icon-container"><i class="fas fa-car"></i></div>
                    <span class="com-title">{{ __('messages.drivers_commission') }}</span>
                    <ul class="list-info">
                        <li><span>{{ __('messages.fleet_commission') }}: {{ $commissions->fleet_commission_value_with_driver ?? 0 }}%</span></li>
                        <li><span>{{ __('messages.driver_commission') }}: {{ $commissions->driver_commission_value ?? 0 }}%</span></li>
                    </ul>
                </div>
            {{-- </a> --}}

            {{-- <a href="{{ route('commissions.fleet.office') }}" title="{{ __('messages.update_form_title',['form' => __('messages.commission') ]) }}"> --}}
                <div class="commission-card regular">
                    <div class="icon-container"><i class="fas fa-percentage"></i></div>
                    <span class="com-title">{{ __('messages.offices_commission') }}</span>
                    <ul class="list-info">
                        <li><span>{{ __('messages.fleet_commission') }}: {{ $commissions->fleet_commission_value_with_office ?? 0 }}%</span></li>
                        <li><span>{{ __('messages.office_commission') }}: {{ $commissions->office_commission_value ?? 0 }}%</span></li>
                    </ul>
                </div>
            {{-- </a> --}}
            @endif
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
            max-width: 750px;
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
    </style>
</x-master-layout>
