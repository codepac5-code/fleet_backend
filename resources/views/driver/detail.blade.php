<x-master-layout>
    <form action="{{ route('driver.destroy', $driver->id) }}" method="POST" data--submit="driver{{ $driver->id }}">
        @csrf
        @method('DELETE')

        <main class="main-area">
            <div class="main-content">
                <div class="container-fluid">
                    @include('partials._handyman')
                    <div class="card">
                        <div class="card-body p-30">
                            <div class="provider-details-overview mb-30">
                                <div class="provider-details-overview__collect-cash">
                                    <div class="statistics-card statistics-card__collect-cash h-100">
                                        <h3>{{ __('messages.collect_cash') }}</h3>
                                        <a href="{{route('driver.create',$driver->id)}}" class="btn btn--primary text-capitalize btn--lg mw-75">{{ __('messages.collect') }}</a>
                                    </div>
                                </div>

                                {{-- <div class="provider-details-overview__statistics">
                                    <div class="statistics-card statistics-card__style2 statistics-card__pending-withdraw">
                                        <h2>{{ getPriceFormat($driver->walletBalance) ?? 0 }}</h2>
                                        <h3>{{__('messages.pending_withdraw')}}</h3>
                                    </div>

                                    <div class="statistics-card statistics-card__style2 statistics-card__already-withdraw">
                                        <h2>{{ getPriceFormat($driver['providerTotWithdrableAmt']) ?? 0 }}</h2>
                                        <h3>{{__('messages.already_withdraw')}}</h3>
                                    </div>

                                    <div class="statistics-card statistics-card__style2 statistics-card__withdrawable-amount">
                                        <h2>{{ getPriceFormat($driver['providerAlreadyWithdrawAmt']) ?? 0 }}</h2>
                                        <h3>{{__('messages.withdrawble_amount')}}</h3>
                                    </div>
                                    <div class="statistics-card statistics-card__style2 statistics-card__total-earning">
                                        <h2>{{ getPriceFormat($driver['pendWithdrwan']) ?? 0 }}</h2>
                                        <h3>{{__('messages.total_earning')}}</h3>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="information-details-box media flex-column flex-sm-row gap-20">
                                        <img class="avatar-img radius-5" src="./img/1.png" alt="" />
                                        <div class="media-body">
                                            <h2 class="information-details-box__title">
                                                {{ $driver->firstName . ' ' . $driver->lastName }}
                                            </h2>
                                            <ul class="contact-list">
                                                <li>
                                                    <i class="ri-smartphone-line"></i>
                                                    <a href="{{ $driver->phoneNumber }}" class="contact-info-text p-0">
                                                        {{ !empty($driver->phoneNumber) ? $driver->phoneNumber : '-' }}
                                                    </a>
                                                </li>
                                                {{-- <li>
                                                    <i class="ri-mail-line"></i>
                                                    <a href="{{ $driver->email }}" class="contact-info-text p-0">{{ $driver->email }}</a>
                                                </li> --}}
                                                <li>
                                                    <i class="ri-map-2-line"></i>
                                                    <span class="contact-info-text">{{ !empty($driver->address) ? $driver->address : '-' }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </form>
</x-master-layout>
