@props([
    'name',
    'label',
    'description' => null,
    'value' => '1',
    'checked' => false,
    'id' => null,
    'required' => false,
])

@php
    $key = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= str_replace('.', '_', $key).(str_ends_with($name, '[]') ? '_'.\Illuminate\Support\Str::slug((string) $value, '_') : '');
    $old = old($key);
    $isChecked = is_array($old) ? in_array((string) $value, array_map('strval', $old), true) : ($old !== null ? (string) $old === (string) $value : $checked);
@endphp

<div {{ $attributes->only('class')->class(['space-y-1']) }}>
    <label for="{{ $id }}" class="flex cursor-pointer items-start gap-3">
        <input id="{{ $id }}" type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked($isChecked) @required($required)
               @error($key) aria-invalid="true" @enderror
               {{ $attributes->except('class')->class(['nt-checkbox mt-0.5 shrink-0']) }}>
        <span>
            <span class="text-sm font-medium">{{ $label }}</span>
            @if ($description)
                <span class="block text-xs text-muted-foreground">{{ $description }}</span>
            @endif
        </span>
    </label>
    @error($key)
        <p class="flex items-center gap-1.5 text-sm text-danger-strong">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $message }}
        </p>
    @enderror
</div>
