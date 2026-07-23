@extends('layouts.authenticated')

@section('title', 'Desactivar usuario — Control de Operaciones')

@section('content')
    <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Desactivar usuario</h2>

    @if (session('status'))
        <div class="alert alert-success" role="alert" style="margin: var(--space-md) 0;">{{ session('status') }}</div>
    @endif

    <div class="card" style="max-width: 500px;">
        <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" novalidate>
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label class="form-label" for="reason">Motivo de desactivacion</label>
                <textarea id="reason" name="reason" class="form-input" rows="3" maxlength="500" required placeholder="Describa el motivo..."></textarea>
            </div>

            <div style="margin-top: var(--space-md);">
                <x-ui.button variant="danger" type="submit">Desactivar usuario</x-ui.button>
            </div>
        </form>
    </div>
@endsection
