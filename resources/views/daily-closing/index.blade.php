@extends('layouts.authenticated')

@section('title', 'Cierres Diarios — Control de Operaciones')

@section('content')
<div class="daily-closing">
<div class="admin-page-header">
    <div><h1 class="admin-title">Cierres diarios</h1><p class="admin-subtitle">Consulta y gestión de cierres operativos por agente.</p></div>
    <a href="{{ route('daily-closures.create') }}" class="btn btn--primary">Generar cierre</a>
</div>

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

<form method="GET" action="{{ route('daily-closures.index') }}" class="filter-bar">
    <x-ui.select label="Agente" name="bank_agent_id" :options="$agents->mapWithKeys(fn ($agent) => [$agent->id => $agent->code . ' — ' . ($agent->bank->name ?? 'Sin banco')])->toArray()" :value="request('bank_agent_id')" placeholder="Todos los agentes" />
    <x-ui.input label="Desde" name="date_from" type="date" :value="request('date_from')" />
    <x-ui.input label="Hasta" name="date_to" type="date" :value="request('date_to')" />
    <x-ui.select label="Estado" name="status" :options="['ACTIVO' => 'Activo', 'CONFIRMADO' => 'Confirmado', 'REABIERTO' => 'Reabierto']" :value="request('status')" placeholder="Todos los estados" />
    <div class="filter-bar-actions"><a href="{{ route('daily-closures.index') }}" class="btn btn--secondary">Limpiar</a><x-ui.button type="submit">Filtrar</x-ui.button></div>
</form>

<div class="card"><div class="table-responsive"><table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Agente</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Operaciones</th>
            <th>Monto Bruto</th>
            <th>Neto</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($closures as $closure)
            <tr>
                <td>{{ $closure->id }}</td>
                <td>{{ $closure->bankAgent?->code }} — {{ $closure->bankAgent?->store?->name }}</td>
                <td>{{ $closure->business_date?->format('Y-m-d') }}</td>
                <td><x-ui.badge :variant="match($closure->status) { 'CONFIRMADO' => 'active', 'REABIERTO' => 'pending', default => 'info' }">{{ ucfirst(strtolower($closure->status)) }}</x-ui.badge></td>
                <td>{{ $closure->operation_count }}</td>
                <td class="table-td-right">S/ {{ number_format((float) $closure->gross_amount, 2) }}</td>
                <td class="table-td-right">S/ {{ number_format((float) $closure->net_movement, 2) }}</td>
                <td>
                    <a href="{{ route('daily-closures.show', $closure) }}" class="btn btn--secondary">Ver</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="table-empty"><div class="table-empty-icon" aria-hidden="true">&#x1F4C5;</div>No se encontraron cierres diarios.</td></tr>
        @endforelse
    </tbody>
</table></div>

<x-ui.pagination :current-page="$closures->currentPage()" :last-page="$closures->lastPage()" :total="$closures->total()" :from="$closures->firstItem() ?? 0" :to="$closures->lastItem() ?? 0" />
</div>
</div>
@endsection
