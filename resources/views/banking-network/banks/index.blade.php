@extends('layouts.authenticated')

@section('title', 'Bancos — Control de Operaciones')

@section('content')
    <h1>Bancos</h1>

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

    <a href="{{ route('admin.banks.create') }}">Nuevo Banco</a>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($banks as $bank)
                <tr>
                    <td>{{ $bank->code }}</td>
                    <td>{{ $bank->name }}</td>
                    <td>{{ $bank->is_active ? 'Activo' : 'Inactivo' }}</td>
                    <td>
                        <a href="{{ route('admin.banks.update', $bank) }}" onclick="event.preventDefault(); document.getElementById('edit-bank-{{ $bank->id }}').submit();">Editar</a>
                        <form id="edit-bank-{{ $bank->id }}" action="{{ route('admin.banks.update', $bank) }}" method="POST" style="display:none;">
                            @csrf
                            @method('PATCH')
                        </form>
                        @if($bank->is_active)
                            <form action="{{ route('admin.banks.deactivate', $bank) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Desactivar este banco?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Desactivar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No se encontraron bancos.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $banks->links() }}
@endsection
