@extends('layouts.authenticated')

@section('title', 'Mis Agentes — Control de Operaciones')

@section('content')
    <h1>Mis Agentes Asignados</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Terminal</th>
                <th>Tienda</th>
                <th>Banco</th>
                <th>Asignado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $assignment)
                <tr>
                    <td>{{ $assignment->bankAgent->code }}</td>
                    <td>{{ $assignment->bankAgent->terminal_code }}</td>
                    <td>{{ $assignment->bankAgent->store?->name }}</td>
                    <td>{{ $assignment->bankAgent->bank?->name }}</td>
                    <td>{{ $assignment->assigned_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No tienes agentes asignados activos.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $assignments->links() }}
@endsection
