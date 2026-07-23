@extends('layouts.authenticated')

@section('title', $title ?? 'Registrar Operacion — AgenteFlow')

@section('content')
    <div class="registration-page">
        <div style="margin-bottom:var(--space-lg);">
            <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Registro Rapido</h2>
            <p class="admin-subtitle">Ingrese los detalles de la transaccion de caja.</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger" role="alert" style="margin-bottom:var(--space-md);">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if($assignments->isEmpty())
            <x-ui.empty-state
                icon="&#x1F3E6;"
                title="Sin agentes asignados"
                description="No tienes agentes bancarios asignados. Contacta al administrador para que te asigne un agente."
            />
        @else
            <x-screen.operation-form
                :assignments="$assignments"
                :types="$types"
                :idempotencyKey="$idempotencyKey"
            />
        @endif
    </div>
@endsection
