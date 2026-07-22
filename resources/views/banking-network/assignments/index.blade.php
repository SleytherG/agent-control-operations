@extends('layouts.authenticated')

@section('title', 'Asignaciones de ' . $user->username_normalized . ' — Control de Operaciones')

@section('content')
    <h1>Asignaciones de {{ $user->username_normalized }}</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <a href="{{ route('admin.users.index') }}">Volver a Operadores</a>

    <form method="POST" action="{{ route('admin.users.assignments.store', $user) }}">
        @csrf
        <div>
            <label for="bank_agent_id">Asignar Agente</label>
            <select name="bank_agent_id" id="bank_agent_id" required>
                <option value="">Seleccione un agente</option>
                @foreach(\App\Modules\BankingNetwork\Models\BankAgent::with(['store', 'bank'])->where('organization_id', auth()->user()->organization_id)->where('is_active', true)->orderBy('code')->get() as $agent)
                    <option value="{{ $agent->id }}">{{ $agent->code }} — {{ $agent->store?->name }} / {{ $agent->bank?->name }}</option>
                @endforeach
            </select>
            <button type="submit">Asignar</button>
        </div>
        @error('bank_agent_id')<span>{{ $message }}</span>@enderror
    </form>

    <table>
        <thead>
            <tr>
                <th>Agente</th>
                <th>Tienda</th>
                <th>Banco</th>
                <th>Asignado</th>
                <th>Desasignado</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $assignment)
                <tr>
                    <td>{{ $assignment->bankAgent->code }}</td>
                    <td>{{ $assignment->bankAgent->store?->name }}</td>
                    <td>{{ $assignment->bankAgent->bank?->name }}</td>
                    <td>{{ $assignment->assigned_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $assignment->unassigned_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td>{{ $assignment->is_active ? 'Activo' : 'Inactivo' }}</td>
                    <td>
                        @if($assignment->is_active)
                            <form action="{{ route('admin.assignments.destroy', $assignment) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Desasignar este operador?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Desasignar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No se encontraron asignaciones.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $assignments->links() }}
@endsection
