<div id="assignments-modal-content">
    <h3 style="margin-bottom: var(--space-xs);">Asignaciones de {{ $user->username_normalized }}</h3>
    <p style="color: var(--color-on-surface-variant); margin-bottom: var(--space-md);">Agentes asignados al operador.</p>

    @if($errors->any())
        <div class="alert alert-danger" role="alert" style="margin-bottom: var(--space-md);">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="card" style="margin-bottom: var(--space-md); padding: var(--space-md);">
        <h4 style="margin-bottom: var(--space-sm); font-weight: var(--font-weight-bold);">Asignar Agente</h4>
        <form method="POST" action="{{ route('admin.users.assignments.store', $user) }}">
            @csrf

            <x-ui.select
                label="Agente"
                name="agent_id"
                :options="$availableAgents->pluck('name', 'id')->toArray()"
                required="true"
                :error="$errors->first('agent_id')"
                placeholder="Seleccione un agente"
            />

            <div style="margin-top: var(--space-sm);">
                <x-ui.button variant="primary" type="submit">Asignar</x-ui.button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive"><table class="data-table">
            <thead><tr><th>Agente</th><th>Ciudad</th><th>Asignado</th><th>Desasignado</th><th class="table-th-center">Estado</th><th class="table-th-center">Acciones</th></tr></thead>
            <tbody>
                @forelse($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->agent->code }} — {{ $assignment->agent->name }}</td>
                        <td>{{ $assignment->agent->city }}</td>
                        <td>{{ $assignment->starts_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $assignment->ends_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="table-td-center">
                            <x-ui.badge :variant="$assignment->is_active ? 'active' : 'inactive'">{{ $assignment->is_active ? 'Activo' : 'Inactivo' }}</x-ui.badge>
                        </td>
                        <td class="table-td-center">
                            @if($assignment->is_active)
                                <form action="{{ route('admin.assignments.destroy', $assignment) }}" method="POST" style="display:inline;" data-confirm="¿Desasignar este operador?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn--danger btn--sm">Desasignar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="table-empty"><div class="table-empty-icon" aria-hidden="true">&#x1F4CB;</div>No se encontraron asignaciones.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <x-ui.pagination
            :currentPage="$assignments->currentPage()"
            :lastPage="$assignments->lastPage()"
            :total="$assignments->total()"
            :from="$assignments->firstItem() ?? 0"
            :to="$assignments->lastItem() ?? 0"
        />
    </div>
</div>
