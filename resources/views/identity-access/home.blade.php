@extends('layouts.authenticated')

@section('title', $title ?? 'AgenteFlow - Inicio')

@section('content')
<div class="welcome-page">
    <div class="welcome-card">
        <div class="welcome-icon" aria-hidden="true">&#x1F3E6;</div>
        <h1 class="welcome-title">Bienvenido, {{ $user?->username_normalized ?? $user?->email_normalized ?? 'Usuario' }}</h1>
        <p class="welcome-subtitle">Has iniciado sesión correctamente.</p>

        <div class="welcome-actions">
            <a href="{{ route(($role ?? 'operator') === 'admin' ? 'admin.dashboard' : 'dashboard.operator') }}" class="btn btn--primary">Ir al Dashboard</a>
        </div>
    </div>
</div>
@endsection
