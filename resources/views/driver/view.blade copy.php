<x-master-layout>
    <form action="{{ route('office.destroy', $office->id) }}" method="POST" data--submit="office{{ $office->id }}"> 
        @csrf
        @method('POST')

         <main class="main-area">
            <div class="main-content">
                <div class="container-fluid">
                    @include('partials._office')
                    <div class="card">
                        <div class="card-body p-30">
                            <div class="service-man-list">
                                @foreach($office->drivers as $driver)
                                    <div class="service-man-list__item">
                                        <div class="service-man-list__item_header">
                                            <div class="attach-img-box position-relative">
                                                @php
                                                    $extention = $driver->photo;
                                                @endphp
                                                <img id="profile_image_preview" src="{{ $driver->photo }}" alt="#" 
                                                     class="attachment-image mt-1" 
                                                     style="background-color:{{ $extention == 'svg' ? $office->color : '' }}">

                                                {{-- <a class="text-danger remove-file" href="{{ route('remove.file', ['id' => $driver->id, 'type' => 'profile_image']) }}" 
                                                    data--submit="confirm_form" data--confirmation='true' data--ajax="true" 
                                                    title='{{ __("messages.remove_file_title", ["name" => __("messages.image")]) }}' 
                                                    data-title='{{ __("messages.remove_file_title", ["name" => __("messages.image")]) }}' 
                                                    data-message='{{ __("messages.remove_file_msg") }}'>
                                                    <i class="ri-close-circle-line"></i>
                                                </a> --}}
                                            </div>
                                            <h4 class="service-man-name">{{ $driver->firstName ?? '-' }}</h4>
                                            <a class="service-man-phone" href="tel:{{ $driver->phoneNumber }}">{{ $driver->phoneNumber ?? '-' }}</a>
                                        </div>
                                        <div class="service-man-list__item_body">
                                            {{-- <a class="service-man-mail" href="mailto:{{ $driver->email }}">{{ $driver->email ?? '-' }}</a> --}}
                                            <p class="service-man-address">{{ $driver->address ?? '-' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </form>

    @section('bottom_script')
    @endsection
</x-master-layout>
