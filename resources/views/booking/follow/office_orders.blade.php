<x-master-layout>
 
    <main class="main-area">
        <div class="main-content">
            <div class="container-fluid">
                @include('partials._office')
                <div class="tab-content">
                    {{-- <div class="tab-pane fade show active" id="review-tab-pane"> --}}



    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <ul class="nav nav-tabs nav-fill custom-tabs-nav" id="customTabs">
                    {{-- <li class="nav-item">
                        <a class="nav-link active custom-tabs-link custom-tabs-hover" data-tab="pending">
                            <i class="fa fa-hourglass-half custom-tabs-icon"></i> 
                            <span class="custom-tabs-text">{{ __('messages.pending') }}</span>
                        </a>
                    </li> --}}
                    <li class="nav-item">
                        <a class="nav-link active custom-tabs-link custom-tabs-hover" data-tab="ongoing">
                            <i class="fa fa-clock custom-tabs-icon"></i>
                            <span class="custom-tabs-text">{{ __('messages.ongoing') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link custom-tabs-link custom-tabs-hover" data-tab="completed">
                            <i class="fa fa-check-circle custom-tabs-icon"></i>
                            <span class="custom-tabs-text">{{ __('messages.order_history') }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <div class="tab-content">
                        {{-- <div class="tab-pane show active" id="pending">

                            <h4 class="custom-tabs-title">
                                <i class="fa-solid fa-spinner animated-icon pulse-animation pending-icon"></i> 
                                🚧{{ __('messages.pending_orders') }}
                                <span id="counterElement" style="float: right; margin-right: 60px; font-size: 70px; font-weight: bold;">0</span>
                            </h4>
                            <p class="completed-orders-text">{{ __('messages.pending_orders_desc') }}</p>
                            

                            @include('booking.follow.pending-index')
                        </div> --}}
            
                        <div class="tab-pane" id="ongoing">
                            <h4 class="custom-tabs-title">
                                <i class="fa-solid fa-truck-fast animated-icon rotate-animation ongoing-icon"></i> ⏳ {{ __('messages.ongoing_orders') }}
                                <span id="counterElement2" style="float: right; margin-right: 60px; font-size: 70px; font-weight: bold;">0</span>

                            </h4>
                            <p class="completed-orders-text">{{ __('messages.ongoing_orders_desc')  }}</p>
                            @include('booking.follow.ongoing-index')
                        </div>
            
                        <div class="tab-pane" id="completed">
                            <h4 class="custom-tabs-title">
                                <i class="fa-solid fa-circle-check animated-icon blink-animation completed-icon"></i> 
                                
                                ✅ {{ __('messages.order_history') }}
                                <span id="counterElement3" style="float: right; margin-right: 60px; font-size: 70px; font-weight: bold;">0</span>

                            </h4>
                            <p class="completed-orders-text">{{ __('messages.completed_orders_desc') }}</p>
                            @include('booking.follow.completed-index' , ['officeId' => ($office != null )? $office->id : null])
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $(".nav-link").on("click", function () {
            let targetTab = $(this).data("tab");
            let targetContent = $("#" + targetTab).find(".tab-content-data");

            $(".nav-link").removeClass("active");
            $(".tab-pane").removeClass("show active");

            $(this).addClass("active");
            $("#" + targetTab).addClass("show active");
            
            targetContent.removeClass("d-none");
        });

        $(".nav-link.active").click();
    });
</script>


{{-- @if(auth()->user()->hasAnyRole(['office']))  --}}
<script> 

  socket.on('offices:follow-order', (data) => {
    console.log('follow_order event...');
    switch(data.table_name){

        // case 'new-order-pending' : 
        // counterElement.textContent = data.order_count;
        // window.renderedDataTable1.ajax.reload(null, false);
        // break;

        case 'new-order-ongoing' : 
        counterElement2.textContent = data.order_count;
        window.renderedDataTable2.ajax.reload(null, false);
        break;
    
        case 'new-order-completed' : 
        counterElement3.textContent = data.order_count;
        window.renderedDataTable3.ajax.reload(null, false);
        break;

    }

    });
</script>
{{-- @endif --}}


@if(auth()->user()->hasAnyRole(['super-admin'])) 
<script> 

  socket.on('admins:follow_order', (data) => {
    console.log('follow_order event...');
    switch(data.table_name){

        // case 'new-order-pending' : 
        // counterElement.textContent = data.order_count;
        // window.renderedDataTable1.ajax.reload(null, false);
        // break;

        case 'new-order-ongoing' : 
        counterElement2.textContent = data.order_count;
        window.renderedDataTable2.ajax.reload(null, false);
        break;
    
        case 'new-order-completed' : 
        counterElement3.textContent = data.order_count;
        window.renderedDataTable3.ajax.reload(null, false);
        break;

    }
    });
</script>
@endif


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const counterElement = document.getElementById("counterElement");

        function highlightCounter() {
            counterElement.style.transition = "all 0.08s ease"; 
            counterElement.style.color = "red";
            counterElement.style.transform = "scale(1.2)"; 

            setTimeout(() => {
                counterElement.style.color = ""; 
                counterElement.style.transform = "scale(1)"; 
            }, 3000);
        }
        const observer = new MutationObserver(() => {
            highlightCounter();
        });

        observer.observe(counterElement, { childList: true, characterData: true, subtree: true });


        ////////////

        function highlightCounter2() {
            counterElement2.style.transition = "all 0.08s ease"; 
            counterElement2.style.color = "red";
            counterElement2.style.transform = "scale(1.2)"; 

            setTimeout(() => {
                counterElement2.style.color = ""; 
                counterElement2.style.transform = "scale(1)"; 
            }, 3000);
        }
        const observer2 = new MutationObserver(() => {
            highlightCounter2();
        });

        observer2.observe(counterElement2, { childList: true, characterData: true, subtree: true });

        /////////

        function highlightCounter3() {
            counterElement3.style.transition = "all 0.08s ease"; 
            counterElement3.style.color = "red";
            counterElement3.style.transform = "scale(1.2)"; 

            setTimeout(() => {
                counterElement3.style.color = ""; 
                counterElement3.style.transform = "scale(1)"; 
            }, 3000);
        }
        const observer3 = new MutationObserver(() => {
            highlightCounter3();
        });

        observer3.observe(counterElement3, { childList: true, characterData: true, subtree: true });
 
    
    });


</script>

 </div>
                </div>
            </div>
        </div>
    </main>
</x-master-layout>

