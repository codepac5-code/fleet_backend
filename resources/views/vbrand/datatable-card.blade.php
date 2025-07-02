{{-- <a href="{{ route('service.list', ['subcategory_id'=>$data->id]) }}" > --}} 

    <div class="row justify-content-center my-4">
    <div class="col-md-6 text-center">
        <div class="border p-2 d-flex justify-content-center align-items-center" 
            style="height: 80px; width: 170px; max-width: 500px; margin: 0 auto;">
            <img id="imagePreview" 
                src="{{ asset($vbrand->image ?  $vbrand->image :  get_default_image('vbrand'))  }}" 
                alt="Preview" 
                style="height: 100%; width: 100%; object-fit: contain;">
        </div>
    </div>
</div>
{{-- </a> --}}
