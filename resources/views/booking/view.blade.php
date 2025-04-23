<x-master-layout>
    <head>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

        <style>
.modern-trip-card {
    background: #fff;
    border-radius: 20px;
    padding: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1); 
    transition: 0.3s ease;
    border: 2px solid #ffcc00; 
    width: 32%;
    max-width: 400px;
    min-width: 280px;
    margin: 20px auto;
}

.trip-route, .trip-finance, .trip-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 18px;
}

.trip-status { 
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: bold; 
    display: flex; 
    align-items: center; 
    gap: 8px; 
    background-color: #fff3cd; 
    color: #856404; 
    animation: pulse 1.5s infinite; 
}

.trip-route i, .trip-finance i, .trip-section i {
    font-size: 1.4rem;
    color: #1e1e2f;
    transition: transform 0.3s ease, color 0.3s ease;
}

.trip-route i:hover, .trip-finance i:hover, .trip-section i:hover {
    transform: scale(1);
    color: #ffcc00;
}

.trip-section {
    flex-wrap: wrap;
    gap: 16px 40px; 
}

.finance-box {
    background: #801d1d;
    border-radius: 12px;
    padding: 12px 18px; 
    text-align: center;
    flex: 1;
    min-width: 160px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
}

.trip-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px; 
}


.trip-route > div {
    display: flex;
    align-items: center;
    gap: 8px; 
}

.trip-section div {
    display: flex;
    gap: 8px; 
}

.multi-dests {
    margin-top: 8px;
}

.multi-dests li {
    display: list-item;
}


.custom-tabs-link .custom-tabs-text {
    font-size: 0.5rem;
    font-weight: 600;
    color: #333;
    margin-left: 8px;
}

.trip-columns {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    height: 100%;
}

.trip-column {
    background: #f9f9f9;
    padding: 30px 30px 30px 30px;
    border-radius: 12px;
    max-height: 1000px; 
    overflow-y: auto;   
    scrollbar-width: thin;
    scrollbar-color: #f9f9f9 transparent;
}

.trip-column::-webkit-scrollbar {
    width: 6px;
}

.trip-column::-webkit-scrollbar-thumb {
    background-color: #ccc;
    border-radius: 10px;
}

.trip-column h3 {
    text-align: center;
    margin-bottom: 15px;
    font-size: 1.3rem;
    font-weight: bold;
    color: #ffcc00;
    position: sticky;
    border-radius: 10px;
    top: 0;
    background: #1e1944cb;
    padding: 10px 0;
    z-index: 1;
}

.modern-trip-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    margin-bottom: 15px;
    transition: transform 0.3s ease-in-out;
}

.modern-trip-card:hover {
    transform: scale(1.02);
}

.trip-top, .trip-route, .finance-box {
    margin-bottom: 10px;
}

.trip-id {
    font-weight: bold;
}

.trip-status.waiting { color: #f39c12; }
.trip-status.ongoing { color: #3498db; }
.trip-status.completed { color: #2ecc71; }

.finance-box {
    background: #e2f10962;
    border-radius: 10px;
    padding: 10px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.9rem;
    text-align: center;
}

.finance-box .label {
    font-size: 0.9rem;
    color: #666;
    font-family: 'Poppins', sans-serif;

}

.finance-box .value {
    font-size: 1.1rem;
    font-weight: bold;
    font-family: 'Poppins', sans-serif;

}

.dashed-separator {
    border: none;
    border-top: 1.5px dashed #bbb;
    margin: 10px 0;
}

.trip-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 7px 15px;
    gap: 10px; 
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #1e1e2f;
    border: none;
    border-radius: 12px;
    padding: 8px 12px;
    color: #ffcc00;
    font-weight: 500;
    font-size: 0.9rem;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    cursor: pointer;
    min-width: 100px;
}

.action-btn i {
    font-size: 1.4rem;
    margin-bottom: 3px;
    transition: transform 0.3s ease, color 0.3s ease;
}

.action-btn:hover {
    background: #ffcc00;
    color: #1e1e2f;
}

.action-btn:hover i {
    color: #1e1e2f;
    transform: scale(1.2);
}




.trip-route i, .trip-finance i, .trip-section i {
    font-size: 1.1rem;  
    color: #5e5e63;
    transition: transform 0.3s ease, color 0.3s ease;
}

.finance-box i {
    font-size: 1.2rem;  
    font-family: 'Poppins', sans-serif;

}

.action-btn i {
    font-size: 1.2rem;  
}


.poppins-font {
  font-family: 'Poppins', sans-serif;
}






body.dark .modern-trip-card {
    background-color: #2d3549;
    border-color: #ffcc00;
    color: #ffffff;
}

body.dark .trip-column {
    background-color:#242424;
    scrollbar-width: thin;
    scrollbar-color: #242424 transparent;
},


body.dark .trip-column {
    background-color: #2d3549; 
}

body {
  font-family: 'Poppins', sans-serif !important;
}

body.dark .modern-trip-card .trip-id,
body.dark .modern-trip-card .trip-section,
body.dark .modern-trip-card .trip-route,
body.dark .modern-trip-card .trip-top,
body.dark .modern-trip-card .finance-box,





body.dark .modern-trip-card .trip-card-footer {
    color: #ffffff;
}

body.dark .modern-trip-card i {
    color: #ffcc00;
}

body.dark .modern-trip-card .finance-box {
    background-color: #ffcc0062;
    color: #e4dfdf;
}

body.dark .modern-trip-card .dashed-separator {
    border-top: 1.5px dashed #666;
}

body.dark .modern-trip-card .action-btn {
    background-color: #333;
    color: #ffcc00;
}

body.dark .modern-trip-card .action-btn:hover {
    background-color: #ffcc00;
    color: #19191a;
}

body.dark .modern-trip-card .action-btn:hover i {
    color: #19191a;
}







@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}


.pulse {
  animation: pulse 0.6s ease-in-out infinite;
}

.pulse-red {
  animation: pulse 0.7s ease-in-out infinite;
  color: rgb(255, 52, 52) !important;
}






        </style>
    </head>

    <div class="container">
        
        <div class="trip-columns">
        <!-- {{ __('messages.pending') }} -->
<div class="trip-column">

    <h3>
        <i class="fa-solid fa-spinner fa-spin pending-icon"></i>
        {{ __('messages.pending') }}
        <span id="pending_count" style="margin-right: 30px; font-size: 25px; font-weight: bold; padding-left: 25px; padding-right: 25px;">0</span>
    </h3>
    
    <div id="pending-orders-wrapper"></div>

    <script>
        let currentPagePending = 1;
        const limitPending = 7;
        let isLoadingPending = false;
        let lastPagePending = false;
    
        document.addEventListener('DOMContentLoaded', function () {
            fetchPendingOrders(currentPagePending);
            window.addEventListener('scroll', handleScrollPending);
        });
    
        function handleScrollPending() {
            if (lastPagePending || isLoadingPending) return;
    
            const scrollPosition = window.innerHeight + window.scrollY;
            const threshold = document.body.offsetHeight - 100;
    
            if (scrollPosition >= threshold) {
                currentPagePending++;
                fetchPendingOrders(currentPagePending);
            }
        }
    
        function showLoaderPending() {
            const wrapper = document.getElementById('pending-orders-wrapper');
            const loader = document.createElement('div');
            loader.id = 'scroll-loader-pending';
            loader.className = 'text-center p-4';
            loader.innerHTML = `<i class="fas fa-spinner fa-spin fa-2x text-warning"></i><p class="mt-2">
                            <div class="text-center p-4" style="color: #f39c12; font-size: 18px; font-weight: 600; font-family: 'Poppins', sans-serif;">
                                {{ __('messages.loading') }}</div>
                            </p>`;
            wrapper.appendChild(loader);
        }
    
        function removeLoaderPending() {
            const loader = document.getElementById('scroll-loader-pending');
            if (loader) loader.remove();
        }



        function createOrderCard(order) {
            return ` <div class="modern-trip-card toggle-card">
                            <div class="trip-top card-toggle-header">
                                <div class="trip-id">
                                    <i class="fas fa-hashtag"></i> ${order.id}
                                </div>
                                <div class="trip-status waiting">
                            <i class="fas fa-clock fa-spin"></i> {{ __('messages.pending') }}
                        </div>                            </div>
                            <div class="trip-route card-toggle-header">
                                <div>
                                    <i class="fas fa-map-marker-alt text-success"></i>
                                    <span   font-family: 'Poppins', sans-serif;>${order.startAddress || '—'}</span>
                                </div>
                                <div>
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    <span>${order.endAddress || '—'}</span>
                                </div>

                                ${order.multiDestnationArray && order.multiDestnationArray.length > 0 ? `
                                    <div class="trip-section">
                                        <div><i class="fas fa-route"></i><h4>{{ __('messages.multiple_destinations') }}</h4></div>
                                        <ul class="multi-dests">
                                            ${order.multiDestnationArray.map(dest => `<li>${dest}</li>`).join('')}
                                        </ul>
                                    </div>
                                ` : ''}

                                <hr class="dashed-separator">

                                <div><i class="fas fa-clock"></i> {{ __('messages.time') }}: <strong >${order.time || '--'}</strong></div>
                                <div><i class="fas fa-road"></i> {{ __('messages.distance') }}: <strong>${order.distance || '--'} كم</strong></div>
                                <div><i class="fas fa-car"></i> {{ __('messages.service') }}: <strong class="poppins-font" >${order.subService.name}</strong></div>
                                <div><i class="fas fa-credit-card"></i> {{ __('messages.payment') }}: <strong>${order.paymentStatus || '--'}</strong></div>

                                <div class="finance-box">
                                    <i class="fas fa-dollar-sign"></i>
                                    <div class="label">{{ __('messages.price') }}</div>
                                    <div class="value">${order.amount.toLocaleString()}</div>
                                </div>
                            </div>

                            
                        <div class="trip-details" style="display: none;">
                            <hr class="dashed-separator">
                                        <div class="trip-section">
                                            <div><i class="fas fa-user"></i> {{ __('messages.user') }}: </div>
                                            <div class="d-flex gap-3 align-items-center">
                                                <img src="${order.user.photo}" alt="avatar" class="avatar avatar-45 rounded-pill">
                                                <div class="d-flex flex-column text-start" style="font-family: 'Poppins', sans-serif;">
                                                    <h6 class="m-0" style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
                                                        ${order.user.firstName +' '+order.user.lastName}
                                                    </h6>
                                                    <span>${order.user.phoneNumber}</span>
                                                </div>
                                            </div>
                                        </div>

                            <div class="trip-finance">
                                <div class="finance-box discount">
                                    <i class="fas fa-percentage"></i>
                                    <div class="label">{{ __('messages.discount') }}</div>
                                    <div class="value">${(order.discount * 100)}%</div>
                                </div>


                                <div class="finance-box total">
                                    <i class="fas fa-wallet"></i>
                                    <div class="label">{{ __('messages.total') }}</div>
                                    <div class="value">${order.totalAmount} </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                `;
        }
        let lastPendingOrderId = 0;

    
    function fetchPendingOrders(page) {
            isLoadingPending = true;
            showLoaderPending();
    
            fetch(`{{ route('orders-by-status') }}?status=pending&page=${page}`)
                .then(response => response.json())
                .then(data => {
                    const pendingOrders = data.orders || [];
                    const wrapper = document.getElementById('pending-orders-wrapper');
                    removeLoaderPending();
    
                    if (pendingOrders.length === 0 && page === 1) {
                        wrapper.innerHTML = `<div class="text-center p-4" style="color: #f39c12; font-size: 22px; font-weight: 600; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                        {{ __('messages.no_pending_orders') }}</div>`;
                        lastPagePending = true;
                        return;
                    }
    
                    if (pendingOrders.length === 0) {
                        lastPagePending = true;
                        return;
                    }
    
                    pendingOrders.forEach(order => {
                    const orderHTML = createOrderCard(order);

                    if(order.id > lastPendingOrderId ){
                        lastPendingOrderId = order.id;
                    }
                    const pending_count = document.getElementById('pending_count');
                    pending_count.textContent = data.count;
                    wrapper.insertAdjacentHTML('beforeend', orderHTML);

                    });
    
                    isLoadingPending = false;
                    if (data.current_page >= data.total_pages) {
                        lastPagePending = true;
                    }
                })
            .catch(error => {
                    console.error('Error loading pending orders:', error);
                    removeLoaderPending();
                    isLoadingPending = false;
                });
        }


function fetchNewPendingOrders() {
  fetch(`/get/only-new-orders-by-status?last_order_id=${lastPendingOrderId}&status=pending`)
    .then(response => response.json())
    .then(data => {
      const newOrders = data.orders || [];
      const wrapper = document.getElementById('pending-orders-wrapper');
      removeLoaderPending();

      if (newOrders.length === 0) {
        return;
      }

      const firstOrder =  newOrders.at(0);
      lastPendingOrderId = firstOrder.id;  


      newOrders.forEach(order => {
        const orderHTML = createOrderCard(order);
        if(order.id > lastPendingOrderId ){
                        lastPendingOrderId = order.id;
                    }
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = orderHTML;
        const orderElement = tempDiv.firstElementChild;

        orderElement.classList.add('pulse');

        wrapper.prepend(orderElement);

        // wrapper.insertAdjacentElement('afterbegin', orderElement);

        setTimeout(() => {
          orderElement.classList.remove('pulse');
        }, 7000);
      });

      const pendingCount = document.getElementById('pending_count');
      pendingCount.textContent = data.count;

      pendingCount.classList.add('pulse-red');

      setTimeout(() => {
        pendingCount.classList.remove('pulse-red');
      }, 7000);
    })
    .catch(error => {
      console.error('Error fetching new orders:', error);
      removeLoaderPending();
    });
}

    setInterval(() => {
            const wrapper = document.getElementById('pending-orders-wrapper');
            lastPagePending = false;
            fetchNewPendingOrders(1);
    }, 30000);

</script>
    

    

    {{-- @for ($i = 0; $i < 7; $i++)
    <div class="modern-trip-card toggle-card">

        <!-- العنوان والحالة -->
        <div class="trip-top card-toggle-header">
            <div class="trip-id">
                <i class="fas fa-hashtag"></i> 457
            </div>
            <div class="trip-status waiting">
                <i class="fas fa-clock fa-spin"></i> {{ __('messages.pending') }}
            </div>
        </div>

        <div class="trip-route card-toggle-header">
            <div>
                <i class="fas fa-map-marker-alt text-success"></i>
                <span>{{ __('messages.from_location') }}</span>
            </div>
            <div>
                <i class="fas fa-map-marker-alt text-danger"></i>
                <span>{{ __('messages.to_location') }}</span>
            </div>

            @if (null)
            <!-- وجهات متعددة -->
            <div class="trip-section">
                <div><i class="fas fa-route"></i><h4>{{ __('messages.multiple_destinations') }}</h4></div>
                <ul class="multi-dests">
                    <li>شارع التخصصي</li>
                    <li>دوار سانا عند الكازية بناء مستشفى الملك فيصل</li>
                </ul>
            </div>

     @endif

            <hr class="dashed-separator">

            <div><i class="fas fa-clock"></i> {{ __('messages.time') }}: <strong>02:15 م</strong></div>
            <div><i class="fas fa-road"></i> {{ __('messages.distance') }}: <strong>12 كم</strong></div>
            <div><i class="fas fa-car"></i> {{ __('messages.service') }}: <strong>{{ __('messages.luxury_service') }}</strong></div>
            <div><i class="fas fa-credit-card"></i> {{ __('messages.payment') }}: <strong>{{ __('messages.cash') }}</strong></div>

            <div class="finance-box">
                <i class="fas fa-dollar-sign"></i>
                <div class="label">{{ __('messages.price') }}</div>
                <div class="value">85000 ل.س</div>
            </div>
        </div>

        <div class="trip-details" style="display: none;">
            <hr class="dashed-separator">

            <div class="trip-section">
                <div><i class="fas fa-user"></i> {{ __('messages.user') }}:</div>
                <div class="d-flex gap-3 align-items-center">
                    <img src="{{ get_default_image($type = 'user') }}" alt="avatar" class="avatar avatar-45 rounded-pill">
                    <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                        <h6 class="m-0" style="font-size: 0.9rem;">
                            محمد محمد
                        </h6>
                        <span>0933817393</span>
                    </div>
                </div>
                <div><i class="fas fa-building"></i> {{ __('messages.office') }}: <strong>مكتب المليح</strong></div>
            </div>

            <div class="trip-finance">
                <div class="finance-box discount">
                    <i class="fas fa-percentage"></i>
                    <div class="label">{{ __('messages.discount') }}</div>
                    <div class="value">4200 ل.س</div>
                </div>

                <div class="finance-box total">
                    <i class="fas fa-wallet"></i>
                    <div class="label">{{ __('messages.total') }}</div>
                    <div class="value">70000 ل.س</div>
                </div>
            </div>

            <div class="trip-card-footer d-flex justify-content-between align-items-center px-3 py-2 mt-3 border-top">
                <a href="{{ route('order.follow.map', ['orderId'=>1]) }}" class="action-btn map-btn">
                    <i class="fas fa-map-marked-alt"></i>
                   <span>{{ __('messages.follow_on_map') }}</span>
                </a>

                <button class="action-btn status-btn change-status-btn">
                    <i class="fas fa-random"></i>
                    <span>{{ __('messages.change_status') }}</span>
                </button>
            </div>

        </div>
    </div>
    @endfor --}}

</div>        
          <div class="trip-column">
                <h3>
                    <i class="fa fa-clock fa-spin"></i>
                    {{ __('messages.ongoing') }}
                    <span id="ongoing_count" style="margin-right: 30px; font-size: 25px; font-weight: bold; padding-left: 25px; padding-right: 25px;">0</span>

                </h3>

                <div id="ongoing-orders-wrapper"></div>

                <script>
                    let ongoingPage = 1;
                    const ongoingLimit = 7;
                    let isOngoingLoading = false;
                    let ongoingLastPage = false;
                    let lastOngoingOrderId = 0;
                
                    document.addEventListener('DOMContentLoaded', function () {
                        fetchOngoingOrders(ongoingPage);
                        window.addEventListener('scroll', handleOngoingScroll);
                    });
                
                    function handleOngoingScroll() {
                        if (ongoingLastPage || isOngoingLoading) return;
                
                        const scrollPosition = window.innerHeight + window.scrollY;
                        const threshold = document.body.offsetHeight - 100;
                
                        if (scrollPosition >= threshold) {
                            ongoingPage++;
                            fetchOngoingOrders(ongoingPage);
                        }
                    }
                
                    function showOngoingLoader() {
                        const wrapper = document.getElementById('ongoing-orders-wrapper');
                        const loader = document.createElement('div');
                        loader.id = 'scroll-loader-ongoing';
                        loader.className = 'text-center p-4';
                        loader.innerHTML = `
                            <i class="fas fa-spinner fa-spin fa-2x text-warning"></i>
                            <p class="mt-2" style="color: #f39c12; font-size: 18px; font-weight: 600; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                {{ __('messages.loading') }}
                            </p>
                        `;
                        wrapper.appendChild(loader);
                    }
                
                    function removeOngoingLoader() {
                        const loader = document.getElementById('scroll-loader-ongoing');
                        if (loader) loader.remove();
                    }
                
                    function createOngoingOrderCard(order) {
                        return `
                        <div class="modern-trip-card toggle-card id="p-order-${order.id}">
                            <div class="trip-top card-toggle-header">
                                <div class="trip-id">
                                    <i class="fas fa-hashtag"></i> ${order.id}
                                </div>
                                <div class="trip-status waiting">
                            <i class="fas fa-clock fa-spin"></i> {{ __('messages.ongoing') }}
                        </div>                          
                            </div>


                            <div class="trip-route card-toggle-header">
                                <div>
                                    <i class="fas fa-map-marker-alt text-success"></i>
                                    <span>${order.startAddress || '—'}</span>
                                </div>
                                <div>
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    <span>${order.endAddress || '—'}</span>
                                </div>

                                ${order.multiDestnationArray && order.multiDestnationArray.length > 0 ? `
                                    <div class="trip-section">
                                        <div><i class="fas fa-route"></i><h4>{{ __('messages.multiple_destinations') }}</h4></div>
                                        <ul class="multi-dests">
                                            ${order.multiDestnationArray.map(dest => `<li>${dest}</li>`).join('')}
                                        </ul>
                                    </div>
                                ` : ''}

                                <hr class="dashed-separator">

                                <div><i class="fas fa-clock"></i> {{ __('messages.time') }}: <strong>${order.time || '--'}</strong></div>
                                <div><i class="fas fa-road"></i> {{ __('messages.distance') }}: <strong>${order.distance || '--'} </strong></div>
                                <div><i class="fas fa-car"></i> {{ __('messages.service') }}: <strong>${order.subService.name}</strong></div>
                                <div><i class="fas fa-credit-card"></i> {{ __('messages.payment') }}: <strong>${order.paymentStatus || '--'}</strong></div>

                                <div class="finance-box">
                                    <i class="fas fa-dollar-sign"></i>
                                    <div class="label">{{ __('messages.price') }}</div>
                                    <div class="value">${order.amount.toLocaleString()}</div>
                                </div>
                            </div>

            <div class="trip-details" style="display: none;">
                <hr class="dashed-separator">
                        <div class="trip-section">
                            <div><i class="fas fa-user"></i> {{ __('messages.user') }}: </div>
                                            <div class="d-flex gap-3 align-items-center">
                                                <img src="${order.user.photo}" alt="avatar" class="avatar avatar-45 rounded-pill">
                                                <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                                                    <h6 class="m-0" style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
                                                        ${order.user.firstName +' '+order.user.lastName}
                                                    </h6>
                                                    <span>${order.user.phoneNumber}</span>
                                                </div>
                                            </div>
                                        <div><i class="fas fa-user"></i> {{ __('messages.Driver') }}: </div>
                                            <div class="d-flex gap-3 align-items-center">
                                                <img src="${order.driver.photo}" alt="avatar" class="avatar avatar-45 rounded-pill">
                                                <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                                                    <h6 class="m-0" style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
                                                        ${order.driver.firstName +' '+order.driver.lastName}
                                                    </h6>
                                                    <span>${order.driver.phoneNumber}</span>
                                                </div>
                                            </div>




                                     <div><i class="fas fa-car"></i> {{ __('messages.car_brand') }}: <strong>كيا</strong></div>
                                    <div><i class="fas fa-tags"></i> {{ __('messages.car_number') }}: <strong>7822956</strong></div>
              
                                    ${order.withOffice ? `
                                        <div><i class="fas fa-building"></i> {{ __('messages.office') }}: <strong>مكتب المليح</strong></div>

                                ` : '<div><i class="fas fa-building"></i> {{ __('messages.office') }}: <strong> {{ __('messages.fleet') }}</strong></div>'}

            </div>
            <div class="trip-finance">
                <div class="finance-box discount">
                    <i class="fas fa-percentage"></i>
                    <div class="label">{{ __('messages.discount') }}</div>
                    <div class="value">${(order.discount * 100)}%</div>
                </div>


                <div class="finance-box total">
                    <i class="fas fa-wallet"></i>
                    <div class="label">{{ __('messages.total') }}</div>
                    <div class="value">${order.totalAmount}</div>
                </div>
            </div>

            <div class="trip-section">
                <div><i class="fas fa-user-tie"></i> {{ __('messages.driver_commission') }}: <strong>${order.driverCommissionValue} </strong></div>
                <div><i class="fas fa-building"></i> {{ __('messages.office_commission') }}: <strong>${order.officeCommissionValue}</strong></div>
                <div><i class="fas fa-shield-alt"></i> {{ __('messages.fleet_commission') }}: <strong>${order.fleetCommissionValue}</strong></div>
            </div>

            <div class="trip-card-footer d-flex justify-content-between align-items-center px-3 py-2 mt-3 border-top" style="background: rgba(255, 255, 255, 0.03); border-style: dashed; border-color: #ccc;">
                <a href="{{ route('order.follow.map', ['orderId' => '__orderId__']) }}" class="action-btn map-btn" id="follow-map-btn">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>{{ __('messages.follow_map') }}</span>
                </a>
                

                <button class="action-btn status-btn change-status-btn" id="change-status-btn">
                    <i class="fas fa-random"></i>
                    <span>{{ __('messages.change_status') }}</span>
                </button>
            </div>
        </div>
    </div>
                        `;
                    }
                
                    function fetchOngoingOrders(page) {
                        isOngoingLoading = true;
                        showOngoingLoader();
                
                        fetch(`{{ route('orders-by-status') }}?status=ongoing&page=${page}`)
                            .then(response => response.json())
                            .then(data => {
                                const ongoingOrders = data.orders || [];
                                const wrapper = document.getElementById('ongoing-orders-wrapper');
                                removeOngoingLoader();
                
                                if (ongoingOrders.length === 0) {
                                    if (page === 1) {
                                        wrapper.innerHTML = `
                                            <div class="text-center p-4" style="color: #f39c12; font-size: 22px; font-weight: 600; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                                {{ __('messages.no_ongoing_orders') }}
                                            </div>`;
                                        ongoingLastPage = true;
                                    }
                                    isOngoingLoading = false;
                                    return;
                                }
                
                                ongoingOrders.forEach(order => {
                                    const orderHTML = createOngoingOrderCard(order);
                                    wrapper.insertAdjacentHTML('beforeend', orderHTML);
                                    if (order.id > lastOngoingOrderId) {
                                        lastOngoingOrderId = order.id;
                                    }
                                });
                
                                document.getElementById('ongoing_count').textContent = data.count;
                
                                isOngoingLoading = false;
                                if (data.current_page >= data.total_pages) {
                                    ongoingLastPage = true;
                                }
                            })
                            .catch(error => {
                                console.error('Error loading ongoing orders:', error);
                                removeOngoingLoader();
                                isOngoingLoading = false;
                            });
                    }
                
                    function fetchNewOngoingOrders() {
                        fetch(`/get/only-new-orders-by-status?last_order_id=${lastOngoingOrderId}&status=ongoing`)
                            .then(response => response.json())
                            .then(data => {
                                const newOrders = data.orders || [];
                                const wrapper = document.getElementById('ongoing-orders-wrapper');
                
                                if (newOrders.length === 0) return;
                
                                newOrders.forEach(order => {
                                    const orderHTML = createOngoingOrderCard(order);
                                    const tempDiv = document.createElement('div');
                                    tempDiv.innerHTML = orderHTML;
                                    const orderElement = tempDiv.firstElementChild;
                
                                    orderElement.classList.add('pulse');
                                    wrapper.prepend(orderElement);
                
                                    setTimeout(() => {
                                        orderElement.classList.remove('pulse');
                                    }, 7000);
                
                                    if (order.id > lastOngoingOrderId) {
                                        lastOngoingOrderId = order.id;
                                    }
                                });
                
                                const ongoingCount = document.getElementById('ongoing_count');
                                ongoingCount.textContent = data.count;
                                ongoingCount.classList.add('pulse-red');
                                setTimeout(() => {
                                    ongoingCount.classList.remove('pulse-red');
                                }, 7000);
                            })
                            .catch(error => {
                                console.error('Error fetching new orders:', error);
                            });
                    }
                
                    setInterval(() => {
                        ongoingLastPage = false;
                        fetchNewOngoingOrders();
                    }, 30000);
</script>
                










{{-- <div class="trip-section">
    <div><i class="fas fa-user"></i> {{ __('messages.user') }}:</div>
    <div class="d-flex gap-3 align-items-center">
        <img src="" alt="avatar" class="avatar avatar-45 rounded-pill">
        <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
            <h6 class="m-0" style="font-size: 0.9rem;">${order.userName || '—'}</h6>
            <span>${order.userPhone || '—'}</span>
        </div>
    </div>

    <div><i class="fas fa-user"></i> {{ __('messages.Driver') }}:</div>
    <div class="d-flex gap-3 align-items-center">
        <img src="" alt="avatar" class="avatar avatar-45 rounded-pill">
        <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
            <h6 class="m-0" style="font-size: 0.9rem;">${order.driverName || '—'}</h6>
            <span>${order.driverPhone || '—'}</span>
        </div>
    </div> --}}



















                {{-- <div class="modern-trip-card toggle-card">

                    <!-- العنوان والحالة (الظاهر دائمًا) -->
                    <div class="trip-top card-toggle-header">
                        <div class="trip-id">
                            <i class="fas fa-hashtag"></i>  457
                        </div>
                        <div class="trip-status waiting">
                            <i class="fas fa-clock fa-spin"></i> {{ __('messages.ongoing') }}
                        </div>
                    </div>
                
                    <!-- المسار (ظاهر دائمًا) -->
                    <div class="trip-route card-toggle-header">
                        <div>
                            <i class="fas fa-map-marker-alt text-success"></i>
                            <span>سانا- برامكة</span>
                        </div>
                        <div>
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            <span> المزة - اتوستراد -برج تالا</span>
                        </div>
                @if (null)
                        <!-- وجهات متعددة -->
                        <div class="trip-section">
                            <div><i class="fas fa-route"></i><h4>{{ __('messages.multiple_destinations') }}</h4></div>
                            <ul class="multi-dests">
                                <li>شارع التخصصي</li>
                                <li>دوار سانا عند الكازية بناء مستشفى الملك فيصل</li>
                            </ul>
                        </div>

                 @endif

                
                        <hr class="dashed-separator">
                
                        <!-- بيانات الرحلة -->
                        <div><i class="fas fa-clock"></i> {{ __('messages.time') }}: <strong>02:15 م</strong></div>
                        <div><i class="fas fa-road"></i> {{ __('messages.distance') }}: <strong>12 كم</strong></div>
                        <div><i class="fas fa-car"></i> {{ __('messages.service') }}: <strong>خدمة فاخرة</strong></div>
                        <div><i class="fas fa-credit-card"></i> {{ __('messages.payment') }}: <strong>سيرياتل كاش</strong></div>
                
                        <div class="finance-box">
                            <i class="fas fa-dollar-sign"></i>
                            <div class="label">{{ __('messages.price') }}</div>
                            <div class="value">67000 ل.س</div>
                        </div>
                
                    </div>
                
                    <!-- ✅ التفاصيل (مخفية عند البدء) -->
                    <div class="trip-details" style="display: none;">
                
                        <!-- الخط الفاصل بين القسم الظاهر والمخفي -->
                        <hr class="dashed-separator">
                
                        <!-- المستخدم والخدمة والمكتب -->
                        <div class="trip-section">
                            <div> <i class="fas fa-user"></i> {{ __('messages.user') }}: </div>
                            <div class="d-flex gap-3 align-items-center">
                                <img src="{{ get_default_image($type = 'user') }}" alt="avatar" class="avatar avatar-45 rounded-pill">
                                <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                                    <h6 class="m-0" style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
                                        محمد محمد
                                    </h6>
                                    <span>0933817393</span>
                                </div>
                            </div>
                            <div> <i class="fas fa-user"></i> {{ __('messages.Driver') }}: </div>
                            <div class="d-flex gap-3 align-items-center">
                                <img src="{{ get_default_image($type = 'user') }}" alt="avatar" class="avatar avatar-45 rounded-pill">
                                <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                                    <h6 class="m-0" style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
                                        أحمد أحمد
                                    </h6>
                                    <span>0933817393</span>
                                </div>
                            </div>
                            <div><i class="fas fa-car"></i> {{ __('messages.car_brand') }}: <strong>كيا</strong></div>
                            <div><i class="fas fa-tags"></i> {{ __('messages.car_number') }}: <strong>7822956</strong></div>
                            <div><i class="fas fa-building"></i> {{ __('messages.office') }}: <strong>مكتب المليح</strong></div>
                        </div>
                
                        <!-- المبالغ -->
                        <div class="trip-finance">
                            <div class="finance-box discount">
                                <i class="fas fa-percentage"></i>
                                <div class="label">{{ __('messages.discount') }}</div>
                                <div class="value">5000 ل.س</div>
                            </div>
                
                            <div class="finance-box total">
                                <i class="fas fa-wallet"></i>
                                <div class="label">{{ __('messages.total') }}</div>
                                <div class="value">70000 ل.س</div>
                            </div>
                        </div>
                
                        <!-- العمولات -->
                        <div class="trip-section">
                            <div><i class="fas fa-user-tie"></i> {{ __('messages.driver_commission') }}: <strong>15000 ل.س</strong></div>
                            <div><i class="fas fa-building"></i> {{ __('messages.office_commission') }}: <strong>26000 ل.س</strong></div>
                            <div><i class="fas fa-shield-alt"></i> {{ __('messages.fleet_commission') }}: <strong>7000 ل.س</strong></div>
                        </div>
                
                        <!-- الجزء الخاص بالأزرار داخل الكارد -->
                        <div class="trip-card-footer d-flex justify-content-between align-items-center px-3 py-2 mt-3 border-top" style="background: rgba(255, 255, 255, 0.03); border-style: dashed; border-color: #ccc;">
                            <!-- متابعة على الخريطة -->
                            <a href="{{  route('order.follow.map', ['orderId'=>1]) }}" class="action-btn map-btn" id="follow-map-btn">
                                <i class="fas fa-map-marked-alt"></i>
                                <span>{{ __('messages.follow_map') }}</span>
                            </a>
                
                            <!-- تغيير الحالة -->
                            <button class="action-btn status-btn change-status-btn" id="change-status-btn">
                                <i class="fas fa-random"></i>
                                <span>{{ __('messages.change_status') }}</span>
                            </button>
                        </div>
                
                    </div>
                </div>
                 --}}


            </div>

            <!-- مكتملة -->
            <div class="trip-column">
                <h3>
                    <i class="fa fa-trophy"></i>
                                        {{ __('messages.completed') }}
                      <span id="completed_count" style="margin-right: 30px; font-size: 25px; font-weight: bold; padding-left: 25px; padding-right: 25px;">0</span>
                 </h3>
<div id="completed-orders-wrapper"></div>

<script>
    let completedPage = 1;
    const completedLimit = 7;
    let isCompletedLoading = false;
    let completedLastPage = false;

    document.addEventListener('DOMContentLoaded', function () {
        fetchCompletedOrders(completedPage);
        window.addEventListener('scroll', handleCompletedScroll);
    });

    function handleCompletedScroll() {
        if (completedLastPage || isCompletedLoading) return;

        const scrollPosition = window.innerHeight + window.scrollY;
        const threshold = document.body.offsetHeight - 100;

        if (scrollPosition >= threshold) {
            completedPage++;
            fetchCompletedOrders(completedPage);
        }
    }

    function showCompletedLoader() {
        const wrapper = document.getElementById('completed-orders-wrapper');
        const loader = document.createElement('div');
        loader.id = 'scroll-loader-completed';
        loader.className = 'text-center p-4';
        loader.innerHTML = `<i class="fas fa-spinner fa-spin fa-2x text-warning"></i><p class="mt-2">
                            <div class="text-center p-4" style="color: #f39c12; font-size: 18px; font-weight: 600; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                {{ __('messages.loading') }}</div>
                            </p>`;
        wrapper.appendChild(loader);
    }

    function removeCompletedLoader() {
        const loader = document.getElementById('scroll-loader-completed');
        if (loader) loader.remove();
    }

    function fetchCompletedOrders(page) {
    isCompletedLoading = true;
    showCompletedLoader();

    fetch(`{{ route('orders-by-status') }}?page=${page}&status=completed`)
        .then(response => response.json())
        .then(data => {
            const orders = data.orders || [];
            const wrapper = document.getElementById('completed-orders-wrapper');
            removeCompletedLoader();

            if (orders.length === 0 && page === 1) {
                wrapper.innerHTML = `
                <div class="text-center p-4" style="color: #f39c12; font-size: 22px; font-weight: 600; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                {{ __('messages.no_completed_orders') }}</div>`;
                completedLastPage = true;
                return;
            }

            if (orders.length === 0) {
                completedLastPage = true;
                return;
            }

            orders.forEach(order => {
                const orderHTML = `
                <div class="modern-trip-card toggle-card">
                            <!-- العنوان والحالة -->
                            <div class="trip-top card-toggle-header">
                                <div class="trip-id">
                                    <i class="fas fa-hashtag"></i> ${order.id}
                                </div>
                                <div class="trip-status completed"><i class="fas fa-check-circle"></i> {{ __('messages.completed') }}</div>
                            </div>

                     


                            <!-- المسار -->
                            <div class="trip-route card-toggle-header">
                                <div>
                                    <i class="fas fa-map-marker-alt text-success"></i>
                                    <span>${order.startAddress || '—'}</span>
                                </div>
                                <div>
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    <span>${order.endAddress || '—'}</span>
                                </div>

                                ${order.multiDestnationArray && order.multiDestnationArray.length > 0 ? `
                                    <div class="trip-section">
                                        <div><i class="fas fa-route"></i><h4>{{ __('messages.multiple_destinations') }}</h4></div>
                                        <ul class="multi-dests">
                                            ${order.multiDestnationArray.map(dest => `<li>${dest}</li>`).join('')}
                                        </ul>
                                    </div>
                                ` : ''}

                                <hr class="dashed-separator">

                                <div><i class="fas fa-clock"></i> {{ __('messages.time') }}: <strong>${order.time || '--'}</strong></div>
                                <div><i class="fas fa-road"></i> {{ __('messages.distance') }}: <strong>${order.distance || '--'} كم</strong></div>
                                <div><i class="fas fa-car"></i> {{ __('messages.service') }}: <strong>{{ __('messages.luxury_service') }}</strong></div>
                                <div><i class="fas fa-credit-card"></i> {{ __('messages.payment') }}: <strong>${order.paymentStatus || '--'}</strong></div>

                                <div class="finance-box">
                                    <i class="fas fa-dollar-sign"></i>
                                    <div class="label">{{ __('messages.price') }}</div>
                                    <div class="value">${order.amount.toLocaleString()} ل.س</div>
                                </div>
                            </div>

                            
        <!-- ✅ التفاصيل (مخفية عند البدء) -->
        <div class="trip-details" style="display: none;">
            <hr class="dashed-separator">
            <!-- المستخدم والخدمة والمكتب -->
                        <div class="trip-section">
                            <div><i class="fas fa-user"></i> {{ __('messages.user') }}: </div>
                            <div class="d-flex gap-3 align-items-center">
                                <img src="" alt="avatar" class="avatar avatar-45 rounded-pill">
                                <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                                    <h6 class="m-0" style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
                                        محمد محمد
                                    </h6>
                                    <span>0933817393</span>
                                </div>
                            </div>
                
                            <div><i class="fas fa-user"></i> {{ __('messages.Driver') }}: </div>
                            <div class="d-flex gap-3 align-items-center">
                                <img src="" alt="avatar" class="avatar avatar-45 rounded-pill">
                                <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                                    <h6 class="m-0" style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
                                        أحمد أحمد
                                    </h6>
                                    <span>0933817393</span>
                                </div>
                            </div>    

                <div><i class="fas fa-car"></i> {{ __('messages.car_brand') }}: <strong>كيا</strong></div>
                <div><i class="fas fa-tags"></i> {{ __('messages.car_number') }}: <strong>7822956</strong></div>
                <div><i class="fas fa-building"></i> {{ __('messages.office') }}: <strong>مكتب المليح</strong></div>
            </div>

            <!-- المبالغ -->
            <div class="trip-finance">
                <div class="finance-box discount">
                    <i class="fas fa-percentage"></i>
                    <div class="label">{{ __('messages.discount') }}</div>
                    <div class="value">${(order.discount * 100)}%</div>
                </div>

                <div class="finance-box total">
                    <i class="fas fa-wallet"></i>
                    <div class="label">{{ __('messages.total') }}</div>
                    <div class="value">${order.totalAmount} ل.س</div>
                </div>
            </div>

            <div class="trip-section">
                <div><i class="fas fa-receipt"></i> {{ __('messages.payment_status') }}: <strong>مدفوع</strong></div>
                            <div><i class="fas fa-calendar-alt"></i> {{ __('messages.payment_date') }}: <strong>2025-04-11</strong></div>
                <div><i class="fas fa-user-tie"></i> {{ __('messages.driver_commission') }}: <strong>${order.driverCommissionValue} ل.س</strong></div>
                <div><i class="fas fa-building"></i> {{ __('messages.office_commission') }}: <strong>${order.officeCommissionValue} ل.س</strong></div>
                <div><i class="fas fa-shield-alt"></i> {{ __('messages.fleet_commission') }}: <strong>${order.fleetCommissionValue} ل.س</strong></div>
            </div>

=            <div class="trip-card-footer d-flex justify-content-between align-items-center px-3 py-2 mt-3 border-top" style="background: rgba(255, 255, 255, 0.03); border-style: dashed; border-color: #ccc;">
                <a href="{{ route('booking.show', 1) }}" class="action-btn map-btn" id="follow-map-btn">
        <i class="fas fa-eye"></i>
        <span>{{ __('messages.details') }}</span>
    </a>
                

                <button class="action-btn status-btn change-status-btn" id="change-status-btn">
                    <i class="fas fa-random"></i>
                    <span>{{ __('messages.change_status') }}</span>
                </button>
            </div>
        </div>
    </div>
    `;

                const completed_count = document.getElementById('completed_count');
                 completed_count.textContent = data.count;
                wrapper.insertAdjacentHTML('beforeend', orderHTML);
            });

            isCompletedLoading = false;
            if (data.current_page >= data.total_pages) {
                completedLastPage = true;
            }
        })
        .catch(error => {
            console.error('Error loading completed orders:', error);
            removeCompletedLoader();
            isCompletedLoading = false;
        });
    }

    fetchCompletedOrders(1);

setInterval(() => {
    const wrapper = document.getElementById('completed-orders-wrapper');
    wrapper.innerHTML = ''; 
    completedLastPage = false;
    fetchCompletedOrders(1);
}, 10000);
</script>

                {{-- @for ($i = 0; $i < 7; $i++)


                
                <div class="modern-trip-card toggle-card">
                    <!-- العنوان والحالة (الظاهر دائمًا) -->
                    <div class="trip-top">
                        <div class="trip-id"><i class="fas fa-hashtag"></i> 30{{ $i }}</div>
                        <div class="trip-status completed"><i class="fas fa-check-circle"></i> {{ __('messages.completed') }}</div>
                    </div>
                
                    <!-- المسار (ظاهر دائمًا) -->
                    <div class="trip-route card-toggle-header">
                        <div>
                            <i class="fas fa-map-marker-alt text-success"></i>
                            <span> جسر الرئيس- سانا- برامكة </span>
                        </div>
                        <div>
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            <span>المزة - اتوستراد -برج تالا</span>
                        </div>

                        @if (null)
                        <!-- وجهات متعددة -->
                        <div class="trip-section">
                            <div><i class="fas fa-route"></i><h4>{{ __('messages.multiple_destinations') }}</h4></div>
                            <ul class="multi-dests">
                                <li>شارع التخصصي</li>
                                <li>دوار سانا عند الكازية بناء مستشفى الملك فيصل</li>
                            </ul>
                        </div>

                 @endif
                        <hr class="dashed-separator">
                
                        <!-- بيانات الرحلة -->
                        <div><i class="fas fa-clock"></i> {{ __('messages.time') }}: <strong>02:15 م</strong></div>
                        <div><i class="fas fa-road"></i> {{ __('messages.distance') }}: <strong>12 كم</strong></div>
                        <div><i class="fas fa-car"></i> {{ __('messages.service') }}: <strong>خدمة فاخرة</strong></div>
                        <div><i class="fas fa-credit-card"></i> {{ __('messages.payment') }}: <strong>سيرياتل كاش</strong></div>
                
                        <div class="finance-box">
                            <i class="fas fa-dollar-sign"></i>
                            <div class="label">{{ __('messages.price') }}</div>
                            <div class="value">90000 ل.س</div>
                        </div>
                    </div>
                
                    <!-- ✅ التفاصيل (مخفية عند البدء) -->
                    <div class="trip-details" style="display: none;">
                        <hr class="dashed-separator">
                
                        <!-- المستخدم والخدمة والمكتب -->
                        <div class="trip-section">
                            <div><i class="fas fa-user"></i> {{ __('messages.user') }}: </div>
                            <div class="d-flex gap-3 align-items-center">
                                <img src="{{ get_default_image($type = 'user') }}" alt="avatar" class="avatar avatar-45 rounded-pill">
                                <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                                    <h6 class="m-0" style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
                                        محمد محمد
                                    </h6>
                                    <span>0933817393</span>
                                </div>
                            </div>
                
                            <div><i class="fas fa-user"></i> {{ __('messages.Driver') }}: </div>
                            <div class="d-flex gap-3 align-items-center">
                                <img src="{{ get_default_image($type = 'user') }}" alt="avatar" class="avatar avatar-45 rounded-pill">
                                <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                                    <h6 class="m-0" style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
                                        أحمد أحمد
                                    </h6>
                                    <span>0933817393</span>
                                </div>
                            </div>    
                            <div><i class="fas fa-car"></i> {{ __('messages.car_brand') }}: <strong>كيا</strong></div>
                            <div><i class="fas fa-car"></i> {{ __('messages.car_number') }}: <strong>7822956</strong></div>
                            <div><i class="fas fa-building"></i> {{ __('messages.office') }}: <strong>مكتب المليح</strong></div>
                        </div>
                
                        <div class="trip-finance">
                            <div class="finance-box discount">
                                <i class="fas fa-percentage"></i>
                                <div class="label">{{ __('messages.discount') }}</div>
                                <div class="value">5000 ل.س</div>
                            </div>
                            
                            <div class="finance-box total">
                                <i class="fas fa-wallet"></i>
                                <div class="label">{{ __('messages.total') }}</div>
                                <div class="value">65000 ل.س</div>
                            </div>
                        </div>
                
                        <!-- العمولات -->
                        <div class="trip-section">
                            <div><i class="fas fa-receipt"></i> {{ __('messages.payment_status') }}: <strong>مدفوع</strong></div>
                            <div><i class="fas fa-calendar-alt"></i> {{ __('messages.payment_date') }}: <strong>2025-04-11</strong></div>
                            <div><i class="fas fa-user-tie"></i> {{ __('messages.driver_commission') }}: <strong>15000 ل.س</strong></div>
                            <div><i class="fas fa-building"></i> {{ __('messages.office_commission') }}: <strong>26000 ل.س</strong></div>
                            <div><i class="fas fa-shield-alt"></i> {{ __('messages.fleet_commission') }}: <strong>7000 ل.س</strong></div>
                        </div>
                
                        <!-- الجزء الخاص بالأزرار داخل الكارد -->
                        <div class="trip-card-footer d-flex justify-content-between align-items-center px-3 py-2 mt-3 border-top" style="background: rgba(255, 255, 255, 0.03); border-style: dashed; border-color: #ccc;">
                            <a href="{{ route('booking.show', 1) }}" class="action-btn map-btn" id="follow-map-btn">
                                <i class="fas fa-eye"></i>
                                <span>{{ __('messages.details') }}</span>
                            </a>
                
                            <button class="action-btn status-btn change-status-btn" id="change-status-btn">
                                <i class="fas fa-random"></i>
                                <span>{{ __('messages.change_status') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
                


                @endfor --}}
            </div>
        </div>
    </div>


    
    <script>
    $(document).ready(function () {
    $(document).on('click', '.toggle-card', function (e) {
        if ($(e.target).closest('.action-btn').length === 0) {
            $(this).find('.trip-details').slideToggle(200);
            $(this).toggleClass('active');
        }
    });

    $(document).on('click', '.change-status-btn', function (e) {
        e.stopPropagation();
        if (!$('#status-modal').hasClass('shown')) {
            $('#status-modal').modal('show');
            $('#status-modal').addClass('shown');
        }
    });

    $('#status-modal').on('hidden.bs.modal', function () {
        $(this).removeClass('shown');
    });

    $(document).on('click', '.map-btn', function (e) {
        e.stopPropagation();
    });
});

    </script>
    





    {{-- <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetchCompletedOrders();
        });
    
        function fetchCompletedOrders() {
            fetch('{{ route('orders-by-status') }}')
                .then(response => response.json())
                .then(data => {
                    const completedOrders = data.completed_orders || [];
                    const wrapper = document.getElementById('completed-orders-wrapper');
    
                    if (completedOrders.length === 0) {
                        wrapper.innerHTML = `<div class="text-center p-4 text-muted">{{ __('messages.no_completed_orders') }}</div>`;
                        return;
                    }
    
                    let html = '';
    
                    completedOrders.forEach(order => {
                        html += `
                        <div class="modern-trip-card toggle-card">
                            <div class="trip-top card-toggle-header">
                                <div class="trip-id">
                                    <i class="fas fa-hashtag"></i> ${order.id}
                                </div>
                                <div class="trip-status completed">
                                    <i class="fas fa-check-circle text-success"></i> ${order.status}
                                </div>
                            </div>
    
                            <div class="trip-route card-toggle-header">
                                <div>
                                    <i class="fas fa-map-marker-alt text-success"></i>
                                    <span>${order.startAddress || '—'}</span>
                                </div>
                                <div>
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    <span>${order.endAddress || '—'}</span>
                                </div>
    
                                ${order.multiDestnationArray ? `
                                    <div class="trip-section">
                                        <div><i class="fas fa-route"></i><h4>{{ __('messages.multiple_destinations') }}</h4></div>
                                        <ul class="multi-dests">
                                            ${order.multiDestnationArray.map(dest => `<li>${dest}</li>`).join('')}
                                        </ul>
                                    </div>` : ''
                                }
    
                                <hr class="dashed-separator">
    
                                <div><i class="fas fa-clock"></i> {{ __('messages.time') }}: <strong>${order.time || '--'}</strong></div>
                                <div><i class="fas fa-road"></i> {{ __('messages.distance') }}: <strong>${order.distance} كم</strong></div>
                                <div><i class="fas fa-car"></i> {{ __('messages.service') }}: <strong>{{ __('messages.luxury_service') }}</strong></div>
                                <div><i class="fas fa-credit-card"></i> {{ __('messages.payment') }}: <strong>${order.paymentStatus}</strong></div>
    
                                <div class="finance-box">
                                    <i class="fas fa-dollar-sign"></i>
                                    <div class="label">{{ __('messages.price') }}</div>
                                    <div class="value">${order.amount.toLocaleString()} ل.س</div>
                                </div>
                            </div>
    
                            <div class="trip-details" style="display: none;">
                                <hr class="dashed-separator">
                                <div class="trip-section">
                                    <div><i class="fas fa-user"></i> {{ __('messages.user') }}:</div>
                                    <div class="d-flex gap-3 align-items-center">
                                        <img src="" alt="avatar" class="avatar avatar-45 rounded-pill">
                                        <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                                            <h6 class="m-0" style="font-size: 0.9rem;">—</h6>
                                            <span>—</span>
                                        </div>
                                    </div>
                                    <div><i class="fas fa-building"></i> {{ __('messages.office') }}: <strong>${order.officeId}</strong></div>
                                </div>
    
                                <div class="trip-finance">
                                    <div class="finance-box discount">
                                        <i class="fas fa-percentage"></i>
                                        <div class="label">{{ __('messages.discount') }}</div>
                                        <div class="value">${(order.amount * order.discount).toLocaleString()} ل.س</div>
                                    </div>
                                    <div class="finance-box total">
                                        <i class="fas fa-wallet"></i>
                                        <div class="label">{{ __('messages.total') }}</div>
                                        <div class="value">${order.totalAmount.toLocaleString()} ل.س</div>
                                    </div>
                                </div>
    
                                <div class="trip-card-footer d-flex justify-content-between align-items-center px-3 py-2 mt-3 border-top">
                                    <a href="/order/follow-map/${order.id}" class="action-btn map-btn">
                                        <i class="fas fa-map-marked-alt"></i>
                                        <span>{{ __('messages.follow_on_map') }}</span>
                                    </a>
    
                                    <button class="action-btn status-btn change-status-btn">
                                        <i class="fas fa-random"></i>
                                        <span>{{ __('messages.change_status') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>`;
                    });
    
                    wrapper.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading orders:', error);
                    document.getElementById('completed-orders-wrapper').innerHTML = `<div class="text-danger p-4">{{ __('messages.error_loading_orders') }}</div>`;
                });
        }
    </script>
    
     --}}
    
    





    
</x-master-layout>












