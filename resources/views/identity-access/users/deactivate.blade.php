@extends('layouts.authenticated')

@section('title', 'Desactivar usuario — Control de Operaciones')

@section('content')
    <h1>Desactivar usuario</h1>

    @if (session('status'))
        <div role="alert">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" novalidate>
        @csrf
        @method('PATCH')
        <div>
            <label for="reason">Motivo de desactivación</label>
            <textarea id="reason" name="reason" rows="3" maxlength="500" required></textarea>
        </div>
        <button type="submit">Desactivar usuario</button>
    </form>
@endsection
