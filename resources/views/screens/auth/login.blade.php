@extends('layouts.guest')

@php
    $errorType = null;
    $errorTitle = '';
    $errorMessage = '';
    $isLoading = ($loginState === 'loading');
    $isThrottled = ($loginState === 'throttled');

    switch ($loginState) {
        case 'error':
            $errorType = 'credentials';
            $errorTitle = 'Credenciales incorrectas';
            $errorMessage = 'El usuario o la contraseña no coinciden. Intente de nuevo.';
            break;
        case 'disabled':
            $errorType = 'disabled';
            $errorTitle = 'Usuario desactivado';
            $errorMessage = 'Su cuenta de agente ha sido suspendida. Contacte soporte.';
            break;
        case 'network-error':
            $errorType = 'network';
            $errorTitle = 'Error de conexion';
            $errorMessage = 'No se pudo conectar con el servidor central. Verifique su red.';
            break;
        case 'throttled':
            $errorType = 'throttled';
            $errorTitle = 'Demasiados intentos';
            $errorMessage = 'Ha excedido el limite de intentos. Espere 60 segundos antes de reintentar.';
            break;
    }
@endphp

<div class="login-card">
    <div class="login-header">
        <div class="login-logo" aria-hidden="true">AF</div>
        <h1 class="login-title">AgenteFlow</h1>
        <p class="login-subtitle">Acceso seguro a operaciones</p>
    </div>

    @if($errorType)
    <div class="login-error" role="alert">
        <span class="login-error-icon" aria-hidden="true">&#x26A0;</span>
        <div>
            <div class="login-error-title">{{ $errorTitle }}</div>
            <p class="login-error-message">{{ $errorMessage }}</p>
        </div>
    </div>
    @endif

    <form class="login-form" method="POST" action="#" onsubmit="return false;">
        <x-ui.input
            label="Usuario o Correo"
            name="username"
            type="text"
            placeholder="ID de agente o email"
            required="true"
            :disabled="$isLoading"
        />

        <x-ui.input
            label="Contraseña"
            name="password"
            type="password"
            placeholder="••••••••"
            required="true"
            :disabled="$isLoading"
        />

        <x-ui.button
            variant="primary"
            type="submit"
            size="lg"
            :full="true"
            :loading="$isLoading"
            :disabled="$isThrottled"
            style="margin-top:var(--space-sm);"
        >
            @if($isThrottled) Espere 60s @else Iniciar sesion @endif
        </x-ui.button>
    </form>

    <div class="login-footer">
        <p>Necesita acceso? <a href="#">Contacte al administrador</a></p>
    </div>
</div>
