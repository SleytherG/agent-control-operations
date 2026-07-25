@extends('layouts.authenticated')

@section('title', 'Auditoría de restablecimientos')

@section('content')
    @php
        $labels = [
            'password_reset.issued' => 'Emitido',
            'password_reset.sessions_revoked' => 'Sesiones revocadas',
            'password_reset.consumed' => 'Consumido',
            'password_reset.completed' => 'Completado',
            'password_reset.superseded' => 'Reemplazado',
            'password_reset.expired' => 'Vencido',
        ];
    @endphp
    <div class="admin-page-header">
        <div>
            <h1 class="admin-title">Auditoría de contraseña</h1>
            <p class="admin-subtitle">{{ $operator->username_normalized }} — eventos sin secretos.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn--secondary">Volver</a>
    </div>

    <form method="GET" class="filter-bar filter-bar--standalone">
        <x-ui.select label="Estado" name="status"
            :options="collect($statuses)->mapWithKeys(fn($status) => [$status->value => ucfirst(strtolower($status->value))])->all()"
            :selected="request('status')" placeholder="Todos" />
        <x-ui.input label="Desde" name="from" type="date" value="{{ request('from') }}" />
        <x-ui.input label="Hasta" name="to" type="date" value="{{ request('to') }}" />
        <div class="filter-bar-actions">
            <a href="{{ route('admin.users.password-resets.index', $operator) }}" class="btn btn--secondary">Limpiar</a>
            <button type="submit" class="btn btn--primary">Filtrar</button>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>Fecha</th><th>Acción</th><th>Actor</th><th>Resultado</th><th>Motivo</th></tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>{{ $event->occurred_at->timezone('America/Lima')->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $labels[$event->action] ?? $event->action }}</td>
                            <td>{{ $event->actor?->username_normalized ?? 'Sistema' }}</td>
                            <td>{{ data_get($event->after_values, 'status', 'Registrado') }}</td>
                            <td>{{ $event->reason ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="table-empty">No hay eventos de restablecimiento.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-ui.pagination
            :currentPage="$events->currentPage()"
            :lastPage="$events->lastPage()"
            :total="$events->total()"
            :from="$events->firstItem() ?? 0"
            :to="$events->lastItem() ?? 0"
        />
    </div>
@endsection
