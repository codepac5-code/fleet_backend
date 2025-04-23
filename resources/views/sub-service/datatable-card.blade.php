<div class="d-flex gap-3 align-items-center">
    <div class="d-flex align-items-center gap-3">
        <div class="border p-2 d-flex justify-content-center align-items-center" 
            style="height: 60px; width: 105px; border-radius: 10px; overflow: hidden;">
            <img id="imagePreview" 
                src="{{ $subservice->image ? $subservice->image : get_default_image('service') }}" 
                alt="Preview" 
                style="height: 100%; width: 100%; object-fit: cover;">
        </div>    <div class="text-start">
    {{-- <h6 class="m-0">{{ $data->title ?? '--' }} </h6>
    <span>{{ $data->description ?? '--' }}</span> --}}
    </div>
</div>

