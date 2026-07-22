@extends('layouts.authenticated')

@section('title', $region->name . ' — Provincias — Control de Operaciones')

@section('content')
    <h1>{{ $region->name }}</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <a href="{{ route('admin.regions.index') }}">Volver a Regiones</a>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($provinces as $province)
                <tr>
                    <td>{{ $province->name }}</td>
                    <td>{{ $province->is_active ? 'Activo' : 'Inactivo' }}</td>
                    <td>
                        <a href="{{ route('admin.provinces.districts.index', $province) }}">Ver Distritos</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">No se encontraron provincias.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $provinces->links() }}
@endsection
