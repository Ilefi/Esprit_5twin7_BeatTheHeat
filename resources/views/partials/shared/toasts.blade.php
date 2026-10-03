{{-- Toast stack for client-side feedback: window.dispatchEvent(new CustomEvent('nt-toast', { detail: { message, type } })) --}}
<div x-data="ntToasts()" x-on:nt-toast.window="push($event.detail)"
     class="pointer-events-none fixed inset-x-4 bottom-4 z-[60] flex flex-col items-end gap-2 sm:inset-x-auto sm:right-6"
     aria-live="polite" aria-atomic="false">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition.opacity.duration.200ms
             class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-lg border bg-surface p-4 shadow-lg sm:w-80"
             :class="toast.type === 'danger' ? 'border-danger/40' : 'border-primary/30'" role="status">
            <i class="fa-solid mt-0.5" :class="toast.type === 'danger' ? 'fa-circle-exclamation text-danger' : 'fa-circle-check text-primary'" aria-hidden="true"></i>
            <p class="flex-1 text-sm" x-text="toast.message"></p>
            <button type="button" class="text-muted-foreground hover:text-foreground" x-on:click="remove(toast.id)" aria-label="Fermer la notification">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>
    </template>
</div>
