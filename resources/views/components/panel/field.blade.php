@props([
    'label' => null,
    'name',
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'options' => null,
    'suggestions' => null,
    'full' => false,
])

<div class="p-field {{ $full ? 'p-field--full' : '' }}">
    @if($label)
        <label for="{{ $name }}">{{ $label }}@if($required)<span class="p-req">*</span>@endif</label>
    @endif

    @if($type === 'textarea')
        <textarea name="{{ $name }}" id="{{ $name }}" placeholder="{{ $placeholder }}" @if($required) required @endif>{{ old($name, $value) }}</textarea>
    @elseif($type === 'select')
        <select name="{{ $name }}" id="{{ $name }}" @if($required) required @endif>
            @foreach(($options ?? []) as $val => $text)
                <option value="{{ $val }}" @selected((string) old($name, $value) === (string) $val)>{{ $text }}</option>
            @endforeach
        </select>
    @else
        {{-- `suggestions` keeps the field free text (legacy values still save) while
             offering the managed catalog as a datalist. --}}
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}"
            @if($suggestions) list="{{ $name }}-options" @endif @if($required) required @endif>
        @if($suggestions)
            <datalist id="{{ $name }}-options">
                @foreach($suggestions as $suggestion)<option value="{{ $suggestion }}"></option>@endforeach
            </datalist>
        @endif
    @endif

    @error($name)<small class="p-field__error">{{ $message }}</small>@enderror
</div>
