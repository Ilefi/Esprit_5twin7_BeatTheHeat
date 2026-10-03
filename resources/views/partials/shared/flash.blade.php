{{-- Session flash messages. Uses the classic @component / @slot syntax on purpose (see docs/TEMPLATE.md). --}}
<div class="space-y-3 empty:hidden">
@foreach (['success' => 'Succès', 'error' => 'Une erreur est survenue', 'warning' => 'Attention', 'info' => 'Information'] as $type => $heading)
    @if (session($type))
        @component('components.nt.alert', ['type' => $type === 'error' ? 'danger' : $type, 'dismissible' => true])
            @slot('title')
                {{ $heading }}
            @endslot

            {{ session($type) }}
        @endcomponent
    @endif
@endforeach
</div>
