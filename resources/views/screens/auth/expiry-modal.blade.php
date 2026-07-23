@extends('layouts.guest')

<x-ui.modal id="expiry-modal" :open="true" wide="true">
    <x-screen.expiry-modal-content
        :expiryState="$expiryState"
        :timerSeconds="30"
    />
    @slot('footer')
        @if($expiryState === 'warning')
            <button class="btn btn--secondary" onclick="window.location.href='/demo/login'">Cerrar sesion</button>
            <button class="btn btn--primary" onclick="window.location.href='/demo/expiry?expiry=renewed'">Continuar sesion</button>
        @elseif($expiryState === 'renewing')
            <button class="btn btn--primary btn--loading" disabled>
                <span class="btn-text">Renovando...</span>
                <span class="btn-spinner" aria-hidden="true"></span>
            </button>
        @else
            <button class="btn btn--primary" onclick="window.location.href='/demo/login'">Ir al inicio de sesion</button>
        @endif
    @endslot
</x-ui.modal>
