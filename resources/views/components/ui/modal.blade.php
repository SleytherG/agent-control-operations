@props(['id' => 'modal', 'title' => null, 'open' => false, 'wide' => false])

<div class="modal-overlay" id="{{ $id }}-overlay" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title" {{ $open ? '' : 'hidden' }}>
    <div class="modal {{ $wide ? 'modal--wide' : '' }}">
        @if($title)
        <div class="modal-header">
            <h2 class="modal-title" id="{{ $id }}-title">{{ $title }}</h2>
            <button class="modal-close" data-modal-close="{{ $id }}" aria-label="Cerrar">&times;</button>
        </div>
        @endif
        <div class="modal-body">
            {{ $slot }}
        </div>
        @if(isset($footer))
        <div class="modal-footer">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>
