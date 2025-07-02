<div class="container">
    <h4 class="mb-4 text-primary">إعدادات وسائل الدفع</h4>

    <div class="row">
        @foreach ($paymentMethods as $method)
        <div class="col-md-4 mb-4" onclick="toggleSettings('{{ $method['id'] }}')">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <img src="{{ asset( $method['image']) }}" 
                         alt="{{ $method['name'] }}" 
                         style="height: 60px; cursor: pointer;">
                    <h5 class="mt-3">{{ $method['name'] }}</h5>
                </div>

                {{-- إعدادات --}}
                <div id="settings-{{ $method['id'] }}" class="settings-area px-3 pb-3" style="display: none;">
                    <form action="" method="POST">
                        {{-- {{ route('payment.settings.update', $method['id']) }} --}}
                        @csrf
                        @method('PUT')
                        
                        <div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline mt-2">
                            <div class="custom-switch-inner">
                                <input 
                                    type="checkbox" 
                                    class="custom-control-input status-switch" 
                                    name="status" 
                                    id="status-{{ $method['id'] }}" 
                                    {{ $method['status'] ? 'checked' : '' }} 
                                    value="true"
                                >
                                <label class="custom-control-label" for="status-{{ $method['id'] }}" data-on-label="" data-off-label=""></label>
                            </div>
                            <label class="ms-2" for="status-{{ $method['id'] }}">تفعيل الوسيلة</label>
                        </div>

                        <div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline mt-2">
                            <div class="custom-switch-inner">
                                <input 
                                    type="checkbox" 
                                    class="custom-control-input payment-wallet-switch" 
                                    name="payment_wallet" 
                                    id="payment_wallet-{{ $method['id'] }}" 
                                    {{ $method['payment_wallet'] ? 'checked' : '' }} 
                                    value="true"
                                >
                                <label class="custom-control-label" for="payment_wallet-{{ $method['id'] }}" data-on-label="" data-off-label=""></label>
                            </div>
                            <label class="ms-2" for="payment_wallet-{{ $method['id'] }}">يمكن استخدامها لشحن المحفظة</label>
                        </div>

                        <div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline mt-2">
                            <div class="custom-switch-inner">
                                <input 
                                    type="checkbox" 
                                    class="custom-control-input payment-trip-switch" 
                                    name="payment_trip" 
                                    id="payment_trip-{{ $method['id'] }}" 
                                    {{ $method['payment_trip'] ? 'checked' : '' }} 
                                    value="true"
                                >
                                <label class="custom-control-label" for="payment_trip-{{ $method['id'] }}" data-on-label="" data-off-label=""></label>
                            </div>
                            <label class="ms-2" for="payment_trip-{{ $method['id'] }}">يمكن استخدامها للدفع للرحلة</label>
                        </div>

                        <div class="mt-3 text-end">
                            <button type="submit" class="btn btn-sm btn-primary">حفظ الإعدادات</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
.settings-area .custom-control {
    display: flex;
    align-items: center; 
    gap: 0.5rem; 
    margin-top: 1rem;
    justify-content: flex-start;
}

.settings-area .custom-switch-inner {
    margin: 0;
}

.settings-area label.ms-2 {
    margin-left: 0;
    margin-right: 0.5rem;
    cursor: pointer;
    user-select: none;
}
</style>

<script>
    function toggleSettings(id) {
        const settingsDiv = document.getElementById('settings-' + id);
        if (settingsDiv.style.display === 'none' || !settingsDiv.style.display) {
            settingsDiv.style.display = 'block';
        } else {
            settingsDiv.style.display = 'none';
        }
    }

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(event) {
            ['status', 'payment_wallet', 'payment_trip'].forEach(name => {
                const checkbox = this.querySelector(`input[name="${name}"]`);
                if (checkbox) {
                    checkbox.value = checkbox.checked ? 'true' : 'false';
                }
            });
        });
    });
</script>
