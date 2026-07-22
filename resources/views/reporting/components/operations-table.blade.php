<div class="operations-table-container">
    <h2>Operaciones recientes</h2>

    @if(count($operations) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Agente</th>
                    <th>Tienda</th>
                    <th>Operador</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Fecha Efectiva</th>
                </tr>
            </thead>
            <tbody>
                @foreach($operations as $operation)
                    <tr>
                        <td>{{ $operation->id }}</td>
                        <td>{{ $operation->type_name }}</td>
                        <td>{{ $operation->agent_code }}</td>
                        <td>{{ $operation->store_name ?? '—' }}</td>
                        <td>{{ $operation->username_normalized }}</td>
                        <td>S/ {{ number_format((float) $operation->amount, 2) }}</td>
                        <td>{{ $operation->status === 'ACTIVE' ? 'Activa' : 'Anulada' }}</td>
                        <td>{{ \Carbon\Carbon::parse($operation->effective_at)->setTimezone('America/Lima')->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $operations->links() }}
    @else
        @include('reporting.components.empty-state', ['context' => 'admin'])
    @endif
</div>
