{{-- :options = ['value' => 'Label', …] --}}
@props([
    'name',
    'label' => null,
    'options' => [],
    'placeholder' => null,
    'hint' => null,
    'required' => false,
    'value' => null,
    'id' => null,
    'bag' => 'default',
    'srOnlyLabel' => false,
])

@php
    $key = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= str_replace('.', '_', $key);
    $hasError = $errors->getBag($bag)->has($key);
    $current = (string) old($key, $value);
@endphp

<div {{ $attributes->only('class')->class(['space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $id }}" @class(['nt-label', 'sr-only' => $srOnlyLabel])>
            {{ $label }}
            @if ($required)
                <span class="text-danger-strong" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <select id="{{ $id }}" name="{{ $name }}" @required($required)
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @elseif ($hint) aria-describedby="{{ $id }}-hint" @endif
            {{ $attributes->except('class')->class(['nt-input']) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($current === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    @if ($hint && ! $hasError)
        <p id="{{ $id }}-hint" class="text-xs text-muted-foreground">{{ $hint }}</p>
    @endif

    @error($key, $bag)
        <p id="{{ $id }}-error" class="flex items-center gap-1.5 text-sm text-danger-strong">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $message }}
        </p>
    @enderror
</div>
