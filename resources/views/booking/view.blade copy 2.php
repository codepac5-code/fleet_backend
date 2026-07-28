<x-master-layout>
    <head>

   <!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;600;700&display=swap" rel="stylesheet">


<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">



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















.swal2-popup {
    font-family: 'Tajawal', sans-serif;
    border-radius: 1rem;
}
.status-btn-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    border: 1px solid #eee;
    border-radius: 8px;
    cursor: pointer;
    margin-bottom: 10px;
    transition: 0.2s ease;
}
.status-btn-option:hover {
    background-color: #ffcc00;
    color: black;
    font-weight: bold;
}
.status-btn-option i {
    font-size: 1.2rem;
    width: 24px;
    text-align: center;
}




        </style>
    </head>
<script>
    function decreaseCount(elementId) {
    const element = document.getElementById(elementId);
    let currentCount = parseInt(element.textContent, 10);

    if (currentCount > 0) {
        element.textContent = currentCount - 1;
        return currentCount;
    }

    return currentCount;

    }

    function deletePendingOrder(orderId) {
                        // const elementId = `pending-order-${orderId}`;
    const element = document.getElementById('pending-order-'+orderId);

    if (element) {
        element.remove();
        let count =  decreaseCount('pending_count');
        if(count == 0 ){
            const wrapper = document.getElementById('pending-orders-wrapper');
            wrapper.innerHTML = `<div class="text-center p-4" style="color: #f39c12; font-size: 22px; font-weight: 600; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                {{ __('messages.no_pending_orders') }}</div>`;
         }
        }
    }


</script>
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
            return ` <div class="modern-trip-card toggle-card" id="pending-order-${order.id}">
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

            fetch(`{{ route('orders-by-status') }}?status=Pending&page=${page}`)
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
  fetch(`/get/only-new-orders-by-status?last_order_id=${lastPendingOrderId}&status=Pending`)
    .then(response => response.json())
    .then(data => {
      const newOrders = data.orders || [];
      const canceled_order_Ids = data.canceled_order_Ids || [];
      const wrapper = document.getElementById('pending-orders-wrapper');
      removeLoaderPending();

      if (canceled_order_Ids.length > 0) {

        canceled_order_Ids.forEach(orderId => {
            deletePendingOrder(orderId);
        });
    }
      if (newOrders.length === 0) {
        return;
      }





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
    }, 10000);

</script>
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





                    function createOngoingOrderCard(order , url) {
                return `
                <div class="modern-trip-card toggle-card" id="ongoing-order-${order.id}">
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
                        <div><i class="fas fa-road"></i> {{ __('messages.distance') }}: <strong>${order.distance || '--'}</strong></div>
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
                                <img src="${order.user.photo || '/path/to/default-avatar.png'}" alt="avatar" class="avatar avatar-45 rounded-pill">
                                <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                                    <h6 class="m-0" style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
                                        ${order.user.firstName + ' ' + order.user.lastName}
                                    </h6>
                                    <span>${order.user.phoneNumber}</span>
                                </div>
                            </div>

                            ${order.driver ? `
                                <div><i class="fas fa-user"></i> {{ __('messages.Driver') }}: </div>
                                <div class="d-flex gap-3 align-items-center">
                                    <img src="${order.driver.photo || '/path/to/default-avatar.png'}" alt="avatar" class="avatar avatar-45 rounded-pill">
                                    <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                                        <h6 class="m-0" style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
                                            ${order.driver.firstName + ' ' + order.driver.lastName}
                                        </h6>
                                        <span>${order.driver.phoneNumber}</span>
                                    </div>
                                </div>
                            ` : ''}

                            <div><i class="fas fa-car"></i> {{ __('messages.car_brand') }}: <strong>${order.driver?.vehicle?.vehicleBrand || '—'}</strong></div>
                            <div><i class="fas fa-tags"></i> {{ __('messages.car_number') }}: <strong>${order.driver?.vehicle?.plate || '—'}</strong></div>

                            ${order.withOffice ? `
                                <div><i class="fas fa-building"></i> {{ __('messages.office') }}: <strong>مكتب المليح</strong></div>
                            ` : '<div><i class="fas fa-building"></i> {{ __('messages.office') }}: <strong>{{ __('messages.fleet') }}</strong></div>'}
                        </div>

                        <div class="trip-finance">
                            <div class="finance-box discount">
                                <i class="fas fa-percentage"></i>
                                <div class="label">{{ __('messages.discount') }}</div>
                                <div class="value">${(order.discount * 100) || 0}%</div>
                            </div>

                            <div class="finance-box total">
                                <i class="fas fa-wallet"></i>
                                <div class="label">{{ __('messages.total') }}</div>
                                <div class="value">${order.totalAmount}</div>
                            </div>
                        </div>

                        <div class="trip-section">
                            <div><i class="fas fa-user-tie"></i> {{ __('messages.driver_commission') }}: <strong>${order.driverCommissionValue || 0}</strong></div>
                            <div><i class="fas fa-building"></i> {{ __('messages.office_commission') }}: <strong>${order.officeCommissionValue || 0}</strong></div>
                            <div><i class="fas fa-shield-alt"></i> {{ __('messages.fleet_commission') }}: <strong>${order.fleetCommissionValue || 0}</strong></div>
                        </div>
                        <div class="trip-card-footer d-flex justify-content-between align-items-center px-3 py-2 mt-3 border-top" style="background: rgba(255, 255, 255, 0.03); border-style: dashed; border-color: #ccc;">
                        <button type="button"
                    class="action-btn map-btn"
                    id="follow-map-btn-${order.id }"
                    onclick="followOrderOnMap(${order.id })">
                <i class="fas fa-map-marked-alt"></i>
                <span>{{ __('messages.follow_map') }}</span>
            </button>


                            <button id="change-status-btn-${order.id}"
                                class="action-btn status-btn"
                                data-order-id="${order.id }">
                                <i class="fas fa-random"></i>
                                <span>{{ __('messages.change_status') }}</span>
                            </button>


                        </div>
                    </div>
                </div>
                `;
            }

            
    function followOrderOnMap(orderId) {
        const url = `{{ route('order.follow.map', ['orderId' => '__ORDER_ID__']) }}`.replace('__ORDER_ID__', orderId);
        window.location.href = url;
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
                                    const url = '/order-on-map/'+order.id;
                                    const orderHTML = createOngoingOrderCard(order , url);
                                    deletePendingOrder(order.id);
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
                                    deletePendingOrder(order.id);
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
                    }, 10000);
</script>

            </div>

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
                     let lastCompletedOrderId = 0;

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
                         loader.innerHTML = `
                             <i class="fas fa-spinner fa-spin fa-2x text-warning"></i>
                             <p class="mt-2" style="color: #f39c12; font-size: 18px; font-weight: 600; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                 {{ __('messages.loading') }}
                             </p>
                         `;
                         wrapper.appendChild(loader);
                     }

                     function removeCompletedLoader() {
                         const loader = document.getElementById('scroll-loader-completed');
                         if (loader) loader.remove();
                     }



                    function deleteOngoingOrder(orderId) {
                        // const elementId = `pending-order-${orderId}`;
                        const element = document.getElementById('ongoing-order-'+orderId);

                        if (element) {
                            element.remove();
                            decreaseCount('ongoing_count');
                         }
                     }

                     function createCompletedOrderCard(order) {
                         return `
                         <div class="modern-trip-card toggle-card">
                             <div class="trip-top card-toggle-header">
                                 <div class="trip-id">
                                     <i class="fas fa-hashtag"></i> ${order.id}
                                 </div>
                                 <div class="trip-status completed"><i class="fas fa-check-circle"></i> {{ __('messages.completed') }}</div>
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
                                 <div><i class="fas fa-road"></i> {{ __('messages.distance') }}: <strong>${order.distance || '--'} كم</strong></div>
                                 <div><i class="fas fa-car"></i> {{ __('messages.service') }}: <strong>${order.subService.name || '--'}</strong></div>
                                 <div><i class="fas fa-credit-card"></i> {{ __('messages.payment') }}: <strong>${order.paymentStatus || '--'}</strong></div>

                                 <div class="finance-box">
                                     <i class="fas fa-dollar-sign"></i>
                                     <div class="label">{{ __('messages.price') }}</div>
                                     <div class="value">${order.amount.toLocaleString()} ل.س</div>
                                 </div>
                             </div>

                             <div class="trip-details" style="display: none;">
                                 <hr class="dashed-separator">
                                 <div class="trip-section">
                                     <div><i class="fas fa-user"></i> {{ __('messages.user') }}: </div>
                                     <div class="d-flex gap-3 align-items-center">
                                         <img src="${order.user.photo || ''}" alt="avatar" class="avatar avatar-45 rounded-pill">
                                         <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                                             <h6 class="m-0" style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
                                                 ${order.user.firstName} ${order.user.lastName}
                                             </h6>
                                             <span>${order.user.phoneNumber}</span>
                                         </div>
                                     </div>

                                     <div><i class="fas fa-user"></i> {{ __('messages.Driver') }}: </div>
                                     <div class="d-flex gap-3 align-items-center">
                                         <img src="${order.driver.photo || ''}" alt="avatar" class="avatar avatar-45 rounded-pill">
                                         <div class="d-flex flex-column text-start" style="font-family: 'Tajawal', sans-serif;">
                                             <h6 class="m-0" style="font-size: 0.9rem; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);">
                                                 ${order.driver.firstName} ${order.driver.lastName}
                                             </h6>
                                             <span>${order.driver.phoneNumber || '—'}</span>
                                         </div>
                                     </div>

                                     <div><i class="fas fa-car"></i> {{ __('messages.car_brand') }}: <strong>${order.driver.vehicle.vehicleBrand || '—'}</strong></div>
                                     <div><i class="fas fa-tags"></i> {{ __('messages.car_number') }}: <strong>${order.driver.vehicle.plate || '—'}</strong></div>
                                     <div><i class="fas fa-building"></i> {{ __('messages.office') }}: <strong>${order.withOffice ? 'مكتب المليح' : 'لا يوجد مكتب'}</strong></div>
                                 </div>

                                 <div class="trip-finance">
                                     <div class="finance-box discount">
                                         <i class="fas fa-percentage"></i>
                                         <div class="label">{{ __('messages.discount') }}</div>
                                         <div class="value">${(order.discount ? (order.discount * 100) : 0)}%</div>
                                     </div>

                                     <div class="finance-box total">
                                         <i class="fas fa-wallet"></i>
                                         <div class="label">{{ __('messages.total') }}</div>
                                         <div class="value">${order.totalAmount} ل.س</div>
                                     </div>
                                 </div>

                                 <div class="trip-section">
                                     <div><i class="fas fa-receipt"></i> {{ __('messages.payment_status') }}: <strong>${order.paymentStatus || '—'}</strong></div>
                                     <div><i class="fas fa-calendar-alt"></i> {{ __('messages.payment_date') }}: <strong>${order.PaymentDatetime || '—'}</strong></div>
                                     <div><i class="fas fa-user-tie"></i> {{ __('messages.driver_commission') }}: <strong>${order.driverCommissionValue || '0'} ل.س</strong></div>
                                     <div><i class="fas fa-building"></i> {{ __('messages.office_commission') }}: <strong>${order.officeCommissionValue || '0'} ل.س</strong></div>
                                     <div><i class="fas fa-shield-alt"></i> {{ __('messages.fleet_commission') }}: <strong>${order.fleetCommissionValue || '0'} ل.س</strong></div>
                                 </div>

                                 <div class="trip-card-footer d-flex justify-content-between align-items-center px-3 py-2 mt-3 border-top" style="background: rgba(255, 255, 255, 0.03); border-style: dashed; border-color: #ccc;">
                                    <button type="button"
                                        class="action-btn map-btn"
                                        style="width: 100%;"
                                        onclick="goToBookingDetails(${order.id })">
                                        <i class="fas fa-eye"></i>
                                        <span>{{ __('messages.details') }}</span>
                                    </button>
                                 </div>
                             </div>
                         </div> `;
                     }

             function goToBookingDetails(orderId) {
                    const routeTemplate = "{{ route('booking.show', ['id' => '__ORDER_ID__']) }}";
                    const finalUrl = routeTemplate.replace('__ORDER_ID__', orderId);
                    window.location.href = finalUrl;
            }


                     function fetchCompletedOrders(page) {
                         isCompletedLoading = true;
                         showCompletedLoader();

                         fetch(`{{ route('orders-by-status') }}?status=Completed&page=${page}`)
                             .then(response => response.json())
                             .then(data => {
                                 const completedOrders = data.orders || [];
                                 const wrapper = document.getElementById('completed-orders-wrapper');
                                 removeCompletedLoader();

                                 if (completedOrders.length === 0) {
                                     if (page === 1) {
                                         wrapper.innerHTML = `
                                             <div class="text-center p-4" style="color: #f39c12; font-size: 22px; font-weight: 600; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                                 {{ __('messages.no_completed_orders') }}
                                             </div>`;
                                         completedLastPage = true;
                                     }
                                     isCompletedLoading = false;
                                     return;
                                 }

                                 completedOrders.forEach(order => {
                                     const orderHTML = createCompletedOrderCard(order);
                                     wrapper.insertAdjacentHTML('beforeend', orderHTML);
                                     if (order.id > lastCompletedOrderId) {
                                         lastCompletedOrderId = order.id;
                                     }
                                 });

                                 document.getElementById('completed_count').textContent = data.count;

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

                     function fetchNewCompletedOrders() {
                         fetch(`/get/only-new-orders-by-status?last_order_id=${lastCompletedOrderId}&status=Completed`)
                             .then(response => response.json())
                             .then(data => {
                                 const newOrders = data.orders || [];
                                 const wrapper = document.getElementById('completed-orders-wrapper');

                                 if (newOrders.length === 0) return;

                                 newOrders.forEach(order => {
                                     const orderHTML = createCompletedOrderCard(order);
                                     deleteOngoingOrder(order.id);
                                     deletePendingOrder(order.id);

                                     const tempDiv = document.createElement('div');
                                     tempDiv.innerHTML = orderHTML;
                                     const orderElement = tempDiv.firstElementChild;

                                     orderElement.classList.add('pulse');
                                     wrapper.prepend(orderElement);

                                     setTimeout(() => {
                                         orderElement.classList.remove('pulse');
                                     }, 7000);

                                     if (order.id > lastCompletedOrderId) {
                                         lastCompletedOrderId = order.id;
                                     }
                                 });

                                 const completedCount = document.getElementById('completed_count');
                                 completedCount.textContent = data.count;
                                 completedCount.classList.add('pulse-red');
                                 setTimeout(() => {
                                     completedCount.classList.remove('pulse-red');
                                 }, 7000);
                             })
                             .catch(error => {
                                 console.error('Error fetching new orders:', error);
                             });
                     }

                     setInterval(() => {
                         completedLastPage = false;
                         fetchNewCompletedOrders();
                     }, 10000);
                 </script>










            </div>
        </div>
    </div>









<!-- Font: Tajawal -->
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;600;700&display=swap" rel="stylesheet">


<style>
    /* SweetAlert2 custom theme */
    .swal2-popup {
        font-family: 'Tajawal', sans-serif;
        border-radius: 1rem !important;
    }
    .swal2-confirm.btn {
        background-color: #ffcc00 !important;
        color: #000 !important;
        font-weight: bold;
        border: none;
        font-family: 'Tajawal', sans-serif;
    }
    .swal2-cancel.btn {
        font-family: 'Tajawal', sans-serif;
    }
</style>




    </script>











    <script>
document.addEventListener("DOMContentLoaded", function () {
    const wrapper = document.getElementById('ongoing-orders-wrapper');

    wrapper.addEventListener('click', function (e) {
        const btn = e.target.closest("[id^='change-status-btn-']");
        if (!btn) return;

        const orderId = btn.getAttribute("data-order-id");
        let selectedStatus = null;

        Swal.fire({
            title: 'اختر الحالة الجديدة',
            html: `
                <div id="custom-status-options" style="display:flex; flex-direction:column; gap:10px; margin-bottom:15px;">
                    <div class="status-btn-option" data-status="completed_payment"><i class="fas fa-check-circle text-success"></i> مكتمل مع دفع</div>
                    <div class="status-btn-option" data-status="completed"><i class="fas fa-check-double text-info"></i> مكتمل بدون دفع</div>
                    <div class="status-btn-option" data-status="hold"><i class="fas fa-pause-circle text-warning"></i> معلق</div>
                    <div class="status-btn-option" data-status="cancel"><i class="fas fa-times-circle text-danger"></i> ملغى</div>
                </div>
                <button id="confirm-status-btn" class="swal2-confirm swal2-styled" style="background-color: #ffcc00; display: none;">تأكيد الحالة</button>
            `,
            showCancelButton: true,
            cancelButtonText: 'إلغاء',
            showConfirmButton: false,
            didOpen: () => {
                const options = document.querySelectorAll('.status-btn-option');
                const confirmBtn = document.getElementById('confirm-status-btn');

                options.forEach(option => {
                    option.style.cursor = 'pointer';
                    option.style.padding = '8px 12px';
                    option.style.border = '1px solid #eee';
                    option.style.borderRadius = '8px';
                    option.style.background = '#fff';
                    option.style.fontFamily = 'Tajawal, sans-serif';
                    option.style.transition = 'background 0.3s';

                    option.addEventListener('mouseenter', () => option.style.background = '#fff8cc');
                    option.addEventListener('mouseleave', () => {
                        if (option.getAttribute('data-status') !== selectedStatus)
                            option.style.background = '#fff';
                    });

                    option.addEventListener('click', () => {
                        selectedStatus = option.getAttribute('data-status');

                        options.forEach(opt => opt.style.background = '#fff');
                        option.style.background = '#ffec99';

                        confirmBtn.style.display = 'inline-block';
                    });
                });

                confirmBtn.addEventListener('click', () => {
                    if (!selectedStatus) return;

                    Swal.close();
                    handleStatusSelection(orderId, selectedStatus);
                });
            }
        });
    });

    function handleStatusSelection(orderId, status) {
        if (status === 'hold' || status === 'cancel') {
            Swal.fire({
                title: `سبب ${status === 'hold' ? 'التعليق' : 'الإلغاء'}`,
                input: 'textarea',
                inputPlaceholder: 'يرجى إدخال السبب...',
                inputAttributes: { dir: 'rtl' },
                inputValidator: value => !value && 'السبب مطلوب',
                showCancelButton: true,
                confirmButtonText: 'تأكيد',
                cancelButtonText: 'إلغاء'
            }).then(reasonResult => {
                if (reasonResult.isConfirmed) {
                    sendStatusChange(orderId, status, reasonResult.value);
                }
            });
        } else {
            sendStatusChange(orderId, status);
        }
    }

    function sendStatusChange(orderId, status, reason = null) {
        Swal.fire({
            title: 'جاري تحديث الحالة...',
            html: '<div style="font-size:16px; margin-top:10px;">يرجى الانتظار</div>',
            didOpen: () => Swal.showLoading(),
            allowOutsideClick: false,
            showConfirmButton: false
        });

        fetch('/change-order-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ orderId: orderId, status: status, reason: reason })
        })
        .then(res => res.json())
        .then(data => {
            Swal.fire({
                icon: data.success ? 'success' : 'error',
                title: data.success ? 'تم التحديث' : 'فشل التحديث',
                text: data.message || (data.success ? 'تم تغيير الحالة بنجاح.' : 'حدث خطأ أثناء التحديث.')
            });

            deleteOngoingOrder(orderId);
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'فشل الاتصال',
                text: 'حدث خطأ في الاتصال بالخادم. حاول لاحقًا.'
            });
        });
    }
});

        </script>











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


</x-master-layout>












