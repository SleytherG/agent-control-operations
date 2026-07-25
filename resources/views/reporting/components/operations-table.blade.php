<div class="card">
    <div class="card-header">
        <h3 class="card-title">Operaciones recientes</h3>
    </div>

    @if($operations->isEmpty())
        <x-ui.empty-state
            icon="&#x1F50D;"
            title="Sin resultados"
            description="No se encontraron operaciones con los filtros aplicados. Intenta ajustar los filtros para ver resultados."
        />
    @else
        <x-ui.data-table
            :headers="[
                ['label' => 'ID'],
                ['label' => 'Tipo'],
                ['label' => 'Agente'],
                ['label' => 'Operador'],
                ['label' => 'Monto', 'align' => 'right'],
                ['label' => 'Estado', 'align' => 'center'],
                ['label' => 'Fecha Efectiva'],
            ]"
            :rows="$operations->map(function($operation) {
                return [
                    ['value' => '#' . $operation->id, 'class' => 'data-mono'],
                    ['value' => $operation->type_name ?? '—'],
                    ['value' => $operation->agent_code ?? $operation->agent_name ?? '—', 'class' => 'data-mono'],
                    ['value' => $operation->username_normalized ?? '—'],
                    ['value' => 'S/ ' . number_format((float) $operation->amount, 2), 'align' => 'right'],
                    ['value' => $operation->status === 'ACTIVE'
                        ? \"<x-ui.badge variant='active'>Activa</x-ui.badge>\"
                        : \"<x-ui.badge variant='annulled'>Anulada</x-ui.badge>\",
                        'align' => 'center'],
                    ['value' => \Carbon\Carbon::parse($operation->effective_at)->setTimezone('America/Lima')->format('Y-m-d H:i')],
                ];
            })->toArray()"
        />
        <x-ui.pagination
            :currentPage="$operations->currentPage()"
            :lastPage="$operations->lastPage()"
            :total="$operations->total()"
            :from="$operations->firstItem() ?? 0"
            :to="$operations->lastItem() ?? 0"
        />
    @endif
</div>
