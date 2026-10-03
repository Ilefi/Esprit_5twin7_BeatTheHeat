{{-- With :maxlength, shows a live character counter. --}}
@props([
    'name',
    'label' => null,
    'hint' => null,
    'required' => false,
    'value' => null,
    'id' => null,
    'rows' => 4,
    'maxlength' => null,
    'bag' => 'default',
])

@php
    $key = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= str_replace('.', '_', $key);
    $hasError = $errors->getBag($bag)->has($key);
    $current = (string) old($key, $value);
@endphp

<div {{ $attributes->only('class')->class(['space-y-1.5']) }} @if ($maxlength) x-data="{ count: {{ mb_strlen($current) }} }" @endif>
    @if ($label)
        <label for="{{ $id }}" class="nt-label">
            {{ $label }}
            @if ($required)
                <span class="text-danger-strong" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @required($required)
              @if ($maxlength) maxlength="{{ $maxlength }}" x-on:input="count = $el.value.length" @endif
              @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @elseif ($hint) aria-describedby="{{ $id }}-hint" @endif
              {{ $attributes->except('class')->class(['nt-input resize-y']) }}>{{ $current }}</textarea>

    <div class="flex items-start justify-between gap-3">
        <div>
            @if ($hint && ! $hasError)
                <p id="{{ $id }}-hint" class="text-xs text-muted-foreground">{{ $hint }}</p>
            @endif
            @error($key, $bag)
                <p id="{{ $id }}-error" class="flex items-center gap-1.5 text-sm text-danger-strong">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $message }}
                </p>
            @enderror
        </div>
        @if ($maxlength)
            <p class="shrink-0 text-xs tabular-nums text-muted-foreground" aria-live="polite">
                <span x-text="count">{{ mb_strlen($current) }}</span> / {{ $maxlength }}
            </p>
        @endif
    </div>
</div>
