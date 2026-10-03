@props([
    'name',
    'label' => null,
    'type' => 'text',
    'hint' => null,
    'required' => false,
    'value' => null,
    'id' => null,
    'bag' => 'default',
    'icon' => null,
    'srOnlyLabel' => false,
])

@php
    $key = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= str_replace('.', '_', $key);
    $hasError = $errors->getBag($bag)->has($key);
    $current = $type === 'password' ? null : old($key, $value);
    $describedBy = $hasError ? $id.'-error' : ($hint ? $id.'-hint' : null);
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

    <div class="relative">
        @if ($icon)
            <i class="fa-solid {{ $icon }} pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-sm text-muted-foreground" aria-hidden="true"></i>
        @endif
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
               @if (! is_null($current)) value="{{ $current }}" @endif
               @required($required)
               @if ($hasError) aria-invalid="true" @endif
               @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
               {{ $attributes->except('class')->class(['nt-input', 'ps-10' => $icon]) }}>
    </div>

    @if ($hint && ! $hasError)
        <p id="{{ $id }}-hint" class="text-xs text-muted-foreground">{{ $hint }}</p>
    @endif

    @error($key, $bag)
        <p id="{{ $id }}-error" class="flex items-center gap-1.5 text-sm text-danger-strong">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $message }}
        </p>
    @enderror
</div>
