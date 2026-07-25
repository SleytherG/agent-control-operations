@extends('layouts.authenticated')

@section('title', 'Operadores — Control de Operaciones')

@section('content')
    <div class="admin-page-header">
        <div>
            <h1 class="admin-title">Operadores</h1>
            <p class="admin-subtitle">Usuarios autorizados para registrar operaciones en el sistema.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn--primary">Nuevo Operador</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success" role="alert" style="margin: var(--space-md) 0;">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert" style="margin: var(--space-md) 0;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="GET" action="{{ route('admin.users.index') }}" class="filter-bar filter-bar--standalone">
        <x-ui.input
            label="Usuario"
            name="username"
            value="{{ request('username') }}"
            placeholder="Filtrar por usuario"
        />
        <x-ui.input
            label="Correo"
            name="email"
            value="{{ request('email') }}"
            placeholder="Filtrar por correo"
        />
        <x-ui.select
            label="Estado"
            name="status"
            :options="['ACTIVE' => 'Activo', 'INACTIVE' => 'Inactivo']"
            :selected="request('status')"
            placeholder="Todos los estados"
        />
        <div class="filter-bar-actions">
            <a href="{{ route('admin.users.index') }}" class="btn btn--secondary">Limpiar</a>
            <x-ui.button variant="secondary" type="submit">Filtrar</x-ui.button>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Usuario</th><th>Email</th><th class="table-th-center">Estado</th><th class="table-th-center">Restablecimiento</th><th class="table-th-center">Acciones</th></tr></thead>
                <tbody>
                    @forelse($operators as $operator)
                        <tr>
                            <td>{{ $operator->username_normalized }}</td><td>{{ $operator->email_normalized }}</td>
                            <td class="table-td-center"><x-ui.badge :variant="$operator->status->value === 'ACTIVE' ? 'active' : 'inactive'">{{ $operator->status->value === 'ACTIVE' ? 'Activo' : 'Inactivo' }}</x-ui.badge></td>
                            <td class="table-td-center">
                                @php
                                    $resetLabels = ['PENDING' => 'Pendiente', 'CONSUMED' => 'Consumido', 'COMPLETED' => 'Completado', 'SUPERSEDED' => 'Reemplazado', 'EXPIRED' => 'Vencido'];
                                @endphp
                                @if($operator->latestPasswordReset)
                                    <span class="badge badge--info">{{ $resetLabels[$operator->latestPasswordReset->status->value] }}</span>
                                @else
                                    <span>Sin restablecimiento</span>
                                @endif
                            </td>
                            <td class="table-td-center">
                                <a href="{{ route('admin.users.edit', $operator) }}" class="btn btn--primary">Editar</a>
                                <a href="{{ route('admin.users.password-resets.index', $operator) }}" class="btn btn--secondary">Auditoría</a>
                                <button type="button" class="btn btn--secondary" onclick="openAssignments({{ $operator->id }}, '{{ $operator->username_normalized }}')">Asignaciones</button>
                                @if($operator->status->value === 'ACTIVE')
                                    <form action="{{ route('admin.users.deactivate-operator', $operator) }}" method="POST" style="display:inline" data-confirm="¿Desactivar este operador?">@csrf @method('DELETE')<button type="submit" class="btn btn--danger">Desactivar</button></form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="table-empty"><div class="table-empty-icon" aria-hidden="true">&#x1F465;</div>No se encontraron operadores.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-ui.pagination
            :currentPage="$operators->currentPage()"
            :lastPage="$operators->lastPage()"
            :total="$operators->total()"
            :from="$operators->firstItem() ?? 0"
            :to="$operators->lastItem() ?? 0"
        />
    </div>

    <div id="assignments-modal" class="modal-overlay" style="display:none;" role="dialog" aria-modal="true" aria-label="Asignaciones">
        <div class="modal" style="max-width: 900px; max-height: 85vh; overflow-y: auto;">
            <div class="modal-header">
                <h2 class="modal-title" id="assignments-modal-title">Asignaciones</h2>
                <button class="modal-close" onclick="closeAssignments()" aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-body" id="assignments-modal-body">
                <p style="text-align:center;padding:var(--space-xl);">Cargando...</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    let currentAssignmentsUserId = null;

    async function openAssignments(userId, username) {
        currentAssignmentsUserId = userId;
        const modal = document.getElementById('assignments-modal');
        const body = document.getElementById('assignments-modal-body');
        const title = document.getElementById('assignments-modal-title');
        title.textContent = 'Asignaciones de ' + username;
        modal.style.display = 'flex';
        loadAssignmentsContent(userId);
    }

    async function loadAssignmentsContent(userId) {
        const body = document.getElementById('assignments-modal-body');
        body.innerHTML = '<p style="text-align:center;padding:var(--space-xl);">Cargando...</p>';

        try {
            const res = await fetch('/admin/users/' + userId + '/assignments', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            });
            if (res.ok) {
                body.innerHTML = await res.text();
            } else {
                body.innerHTML = '<p style="text-align:center;padding:var(--space-xl);color:var(--color-error);">Error al cargar asignaciones.</p>';
            }
        } catch (e) {
            body.innerHTML = '<p style="text-align:center;padding:var(--space-xl);color:var(--color-error);">Error de conexión.</p>';
        }
    }

    function closeAssignments() {
        document.getElementById('assignments-modal').style.display = 'none';
    }

    document.getElementById('assignments-modal')?.addEventListener('click', function(e) {
        if (e.target === this) closeAssignments();
    });

    let pendingForm = null;
    let modalContentBackup = '';

    document.getElementById('assignments-modal-body')?.addEventListener('submit', async function(e) {
        const form = e.target.closest('form');
        if (!form || form.method.toLowerCase() !== 'post') return;
        e.preventDefault();

        const confirmMsg = form.dataset.confirm;
        if (confirmMsg) {
            const body = document.getElementById('assignments-modal-body');
            modalContentBackup = body.innerHTML;
            body.innerHTML = '<div style="text-align:center;padding:var(--space-xl);">' +
                '<p style="margin-bottom:var(--space-md);font-size:var(--font-size-body-md);">' + confirmMsg + '</p>' +
                '<div style="display:flex;gap:var(--space-sm);justify-content:center;">' +
                '<button class="btn btn--danger" id="confirm-yes">Confirmar</button>' +
                '<button class="btn btn--secondary" id="confirm-no">Cancelar</button>' +
                '</div></div>';
            document.getElementById('confirm-yes').onclick = () => submitAssignmentForm(form);
            document.getElementById('confirm-no').onclick = () => { body.innerHTML = modalContentBackup; };
            return;
        }

        await submitAssignmentForm(form);
    });

    async function submitAssignmentForm(form) {
        const body = document.getElementById('assignments-modal-body');
        const formData = new FormData(form);
        const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: formData
            });

            const data = await res.json();

            if (res.ok) {
                body.innerHTML = '<div class="alert alert-success" style="margin-bottom:var(--space-md);text-align:center;">' + data.message + '</div>';
                setTimeout(() => loadAssignmentsContent(currentAssignmentsUserId), 800);
            } else {
                body.innerHTML = '<div class="alert alert-danger" style="margin-bottom:var(--space-md);text-align:center;">' + (data.message || 'Error al procesar.') + '</div>';
                setTimeout(() => loadAssignmentsContent(currentAssignmentsUserId), 1500);
            }
        } catch (e) {
            body.innerHTML = '<div class="alert alert-danger" style="margin-bottom:var(--space-md);text-align:center;">Error de conexión.</div>';
            setTimeout(() => loadAssignmentsContent(currentAssignmentsUserId), 1500);
        }
    }

    document.getElementById('assignments-modal-body')?.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            const body = document.getElementById('assignments-modal-body');
            body.innerHTML = '<p style="text-align:center;padding:var(--space-xl);">Cargando...</p>';
            fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
                .then(res => res.ok ? res.text() : Promise.reject())
                .then(html => { body.innerHTML = html; })
                .catch(() => { body.innerHTML = '<p style="text-align:center;padding:var(--space-xl);color:var(--color-error);">Error al cargar.</p>'; });
        }
    });
    </script>
    @endpush
@endsection
