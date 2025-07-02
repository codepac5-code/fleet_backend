<form action="" method="POST" enctype="multipart/form-data">
    @csrf
    {{-- {{ route('settings.social.save') }} --}}
    <div class="row">
        @php
            $socials = [
                'facebook' => 'Facebook',
                'twitter' => 'Twitter',
                'instagram' => 'Instagram',
                'linkedin' => 'LinkedIn',
                'youtube' => 'YouTube',
                'tiktok' => 'TikTok'
            ];
        @endphp

        @foreach($socials as $key => $label)
            <div class="form-group col-md-6">
                <label for="{{ $key }}" class="form-control-label">{{ $label }}</label>
                <input type="url" 
                       name="social[{{ $key }}]" 
                       class="form-control" 
                       id="{{ $key }}" 
                       placeholder="https://{{ strtolower($key) }}.com/yourpage"
                       value="{{ old('social.'.$key, $settings['social'][$key] ?? '') }}">
            </div>
        @endforeach
    </div>

    <div class="text-end mt-3">
        <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
    </div>
</form>
