@extends('layouts.authenticated')

@section('title', 'Historial de sesiones — Control de Operaciones')

@section('content')
    <h1>Historial de sesiones</h1>

    <form method="GET" action="{{ route('sessions.index') }}">
        <div>
            <label for="status">Estado</label>
            <select id="status" name="status">
                <option value="">Todos</option>
                <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Activa</option>
                <option value="EXPIRED" {{ request('status') === 'EXPIRED' ? 'selected' : '' }}>Expirada</option>
                <option value="REVOKED" {{ request('status') === 'REVOKED' ? 'selected' : '' }}>Revocada</option>
            </select>
        </div>
        <div>
            <label for="from">Desde</label>
            <input type="date" id="from" name="from" value="{{ request('from') }}">
        </div>
        <div>
            <label for="to">Hasta</label>
            <input type="date" id="to" name="to" value="{{ request('to') }}">
        </div>
        <button type="submit">Filtrar</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID Público</th>
                <th>Usuario</th>
                <th>Estado</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Motivo de fin</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sessions as $session)
                <tr>
                    <td>{{ $session->public_id }}</td>
                    <td>{{ $session->user->username_normalized ?? 'N/A' }}</td>
                    <td>{{ $session->status->value }}</td>
                    <td>{{ $session->started_at?->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $session->ended_at?->format('Y-m-d H:i:s') ?? '—' }}</td>
                    <td>{{ $session->end_reason?->value ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No se encontraron sesiones.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $sessions->appends(request()->query())->links() }}
@endsection
