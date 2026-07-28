














<div class="container mt-4">
    <div class="trip-tabs">
        <button class="tab-button active" data-target="#ongoing">
            <i class="fas fa-car-side animated-icon"></i>
        </button>
        <button class="tab-button" data-target="#pending">
            <i class="fas fa-hourglass-half animated-icon"></i>
        </button>
        <button class="tab-button" data-target="#finished">
            <i class="fas fa-flag-checkered animated-icon"></i>
        </button>
    </div>

    <div class="tab-content">
        <div class="tab-pane active" id="ongoing">
            <div class="trip-list">
                @for ($i=0 ; $i < 12 ; $i++)
                <div class="trip-card finished">
                    <div class="trip-icon"><i class="fas fa-flag-checkered animated-icon"></i></div>
                    <div class="trip-info">
                        <h5>✅ الرحلة #{{'1' }}</h5>
                        <p><strong>السائق:</strong> {{ 'driver_name' }}</p>
                        <p><strong>الراكب:</strong> {{ 'passenger_name' }}</p>
                        <p><strong>التكلفة:</strong> {{'55555' }} $</p>
                    </div>
                </div>
            @endfor
            </div>
        </div>

        <div class="tab-pane" id="pending">
            <div class="trip-list">
                @for ($i=0 ; $i < 12 ; $i++)
                <div class="trip-card finished">
                    <div class="trip-icon"><i class="fas fa-flag-checkered animated-icon"></i></div>
                    <div class="trip-info">
                        <h5>✅ الرحلة #{{'1' }}</h5>
                        <p><strong>السائق:</strong> {{ 'driver_name' }}</p>
                        <p><strong>الراكب:</strong> {{ 'passenger_name' }}</p>
                        <p><strong>التكلفة:</strong> {{'55555' }} $</p>
                    </div>
                </div>
            @endfor
            </div>
        </div>

        <div class="tab-pane" id="finished">
            <div class="trip-list">
                @for ($i=0 ; $i < 12 ; $i++)
                    <div class="trip-card finished">
                        <div class="trip-icon"><i class="fas fa-flag-checkered animated-icon"></i></div>
                        <div class="trip-info">
                            <h5>✅ الرحلة #{{'1' }}</h5>
                            <p><strong>السائق:</strong> {{ 'driver_name' }}</p>
                            <p><strong>الراكب:</strong> {{ 'passenger_name' }}</p>
                            <p><strong>التكلفة:</strong> {{'55555' }} $</p>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>

































{{-- @for ($i=0 ; $i < 12 ; $i++)
<div class="trip-item ongoing">
<h5>🚗 رقم الرحلة: {{ '5'}}</h5>
<p>السائق: {{ 'driver_name' }}</p>
<p>الراكب: {{ 'passenger_name' }}</p>
<p>الموقع: {{ 'location' }}</p>
</div>
@endfor --}}












<script>

    document.addEventListener("DOMContentLoaded", function () {
        const buttons = document.querySelectorAll(".tab-button");
        const tabs = document.querySelectorAll(".tab-pane");

        buttons.forEach((button) => {
            button.addEventListener("click", function () {
                buttons.forEach((btn) => btn.classList.remove("active"));
                tabs.forEach((tab) => tab.classList.remove("active"));

                this.classList.add("active");
                document.querySelector(this.getAttribute("data-target")).classList.add("active");
            });
        });
    });
    </script>























.trip-tabs {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 20px;
}

.tab-button {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab-button:hover,
.tab-button.active {
    background: linear-gradient(135deg, #f9c74f, #f9844a);
    color: black;
    transform: scale(1.05);
}

.animated-icon {
    font-size: 20px;
    transition: transform 0.3s ease-in-out;
}

.tab-button:hover .animated-icon {
    transform: rotate(15deg);
}

.tab-content {
    background: rgba(255, 255, 255, 0.05);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1);
}

.trip-card {
    background: rgba(255, 255, 255, 0.1);
    padding: 15px;
    border-radius: 12px;
    display: flex;
    gap: 15px;
    align-items: center;
    box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    position: relative;
    overflow: hidden;
}

.trip-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.05);
    transition: left 0.5s ease-in-out;
}

.trip-card:hover::before {
    left: 100%;
}

.trip-card:hover {
    transform: translateY(-5px);
}

.trip-icon {
    font-size: 32px;
    padding: 15px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.1);
}

.trip-card .trip-icon i {
    animation: pulse 1.5s infinite alternate;
}

@keyframes pulse {
    from { transform: scale(1); }
    to { transform: scale(1.2); }
}

.ongoing {
    border-left: 5px solid #6a57ff;
    background: rgba(106, 87, 255, 0.2);
}

.pending {
    border-left: 5px solid #f9c74f;
    background: rgba(249, 199, 79, 0.2);
}

.finished {
    border-left: 5px solid #28a745;
    background: rgba(40, 167, 69, 0.2);
}

.trip-info h5 {
    font-size: 18px;
    margin-bottom: 5px;
    color: white;
}

.trip-info p {
    font-size: 16px;
    color: #e0e0e0;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}
