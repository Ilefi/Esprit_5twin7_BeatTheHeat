{{-- Drag & drop zone around a real <input type="file"> (keyboard and screen readers use the native input). --}}
@props([
    'name',
    'label' => null,
    'hint' => 'PDF, JPG ou PNG — 5 Mo maximum par fichier.',
    'multiple' => false,
    'accept' => '.pdf,.jpg,.jpeg,.png',
    'required' => false,
    'id' => null,
])

@php
    $key = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id ??= str_replace('.', '_', $key);
    $hasError = $errors->has($key) || $errors->has($key.'.*');
@endphp

<div {{ $attributes->class(['space-y-1.5']) }} x-data="ntFileDrop()">
    @if ($label)
        <span class="nt-label" id="{{ $id }}-label">
            {{ $label }}
            @if ($required)
                <span class="text-danger-strong" aria-hidden="true">*</span>
            @endif
        </span>
    @endif

    <label for="{{ $id }}"
           x-on:dragover.prevent="dragging = true" x-on:dragleave.prevent="dragging = false" x-on:drop.prevent="drop($event)"
           :class="dragging ? 'border-primary bg-primary/6' : 'border-border bg-surface hover:border-primary/50'"
           @class(['flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-6 py-8 text-center transition focus-within:border-primary', 'border-danger' => $hasError])>
        <span class="grid h-12 w-12 place-items-center rounded-full bg-primary/10 text-lg text-primary-strong">
            <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
        </span>
        <span class="text-sm font-semibold">Glissez vos fichiers ici <span class="font-normal text-muted-foreground">ou</span> <span class="text-primary-strong underline">parcourez</span></span>
        <span class="text-xs text-muted-foreground">{{ $hint }}</span>
        <input id="{{ $id }}" x-ref="input" type="file" name="{{ $name }}" accept="{{ $accept }}" class="sr-only"
               @if ($multiple) multiple @endif @required($required) x-on:change="sync()"
               @if ($label) aria-labelledby="{{ $id }}-label" @endif
               @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif>
    </label>

    <ul x-show="files.length" x-cloak class="space-y-1.5" aria-live="polite">
        <template x-for="file in files" :key="file.name">
            <li class="flex items-center gap-3 rounded-lg border bg-surface px-3 py-2 text-sm">
                <i class="fa-solid fa-file-lines text-muted-foreground" aria-hidden="true"></i>
                <span class="flex-1 truncate" x-text="file.name"></span>
                <span class="text-xs text-muted-foreground" x-text="file.size"></span>
            </li>
        </template>
        <li><button type="button" class="nt-link text-xs" x-on:click="clear()">Retirer les fichiers</button></li>
    </ul>

    @if ($hasError)
        <p id="{{ $id }}-error" class="flex items-center gap-1.5 text-sm text-danger-strong">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ $errors->first($key) ?: $errors->first($key.'.*') }}
        </p>
    @endif
</div>
