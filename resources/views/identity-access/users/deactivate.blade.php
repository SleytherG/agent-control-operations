@extends('layouts.authenticated')

@section('title', 'Desactivar usuario — Control de Operaciones')

@section('content')
    <div class="form-page form-page--compact">
    <div class="form-page-header">
        <h2 class="admin-title">Desactivar usuario</h2>
        <p class="form-page-subtitle">Registre un motivo de auditoria antes de inhabilitar al usuario.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success" role="alert" style="margin: var(--space-md) 0;">{{ session('status') }}</div>
    @endif

    <div class="card form-shell">
        <div class="card-body">
        <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" novalidate class="form-layout form-layout--single">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label class="form-label" for="reason">Motivo de desactivacion</label>
                <textarea id="reason" name="reason" class="form-input" rows="3" maxlength="500" required placeholder="Describa el motivo..."></textarea>
            </div>

            <div class="form-actions">
                <x-ui.button variant="danger" type="submit">Desactivar usuario</x-ui.button>
            </div>
        </form>
        </div>
    </div>
    </div>
@endsection
