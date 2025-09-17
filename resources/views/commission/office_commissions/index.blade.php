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
            
            <a href="{{ route('commissions.offcie') }}" title="{{ __('messages.update_form_title',['form' => __('messages.commission') ]) }}">
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
            
            {{-- <a href="{{ route('commissions.offcie') }}" title="{{ __('messages.update_form_title',['form' => __('messages.commission') ]) }}"> --}}
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
    </style>
</x-master-layout>
