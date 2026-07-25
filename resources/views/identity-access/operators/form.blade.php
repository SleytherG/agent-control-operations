@extends('layouts.authenticated')

@section('title', $operator->exists ? 'Editar Operador' : 'Nuevo Operador — Control de Operaciones')

@section('content')
    <div class="form-page form-page--compact">
        <div class="form-page-header">
            <h2 class="admin-title">{{ $operator->exists ? 'Editar Operador' : 'Nuevo Operador' }}</h2>
            <p class="form-page-subtitle">Configure credenciales y datos base del operador con la misma estetica del sistema.</p>
        </div>

        <div class="card form-shell">
            <div class="card-body">
                <form method="POST" action="{{ $operator->exists ? route('admin.users.update', $operator) : route('admin.users.store') }}" class="form-layout form-layout--single">
            @csrf
            @if($operator->exists)
                @method('PATCH')
            @endif

            <x-ui.input
                label="Usuario"
                name="username"
                value="{{ old('username', $operator->exists ? $operator->username_normalized : '') }}"
                :error="$errors->first('username')"
                required="true"
                placeholder="Usuario"
            />

            <x-ui.input
                label="Email"
                name="email"
                type="email"
                value="{{ old('email', $operator->exists ? $operator->email_normalized : '') }}"
                :error="$errors->first('email')"
                required="true"
                placeholder="email@ejemplo.com"
            />

            @unless($operator->exists)
                <x-ui.input
                    label="Contraseña"
                    name="password"
                    type="password"
                    :error="$errors->first('password')"
                    required="true"
                    placeholder="Minimo 8 caracteres"
                />
            @endunless

            <div class="form-actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn--secondary">Cancelar</a>
                <x-ui.button variant="primary" type="submit">
                    {{ $operator->exists ? 'Actualizar' : 'Crear' }}
                </x-ui.button>
            </div>
        </form>
            </div>
        </div>

        @if($operator->exists)
            <section class="card form-shell" data-password-reset aria-labelledby="password-reset-title">
                <div class="card-body">
                    <h2 id="password-reset-title" class="admin-title">Seguridad de acceso</h2>
                    <p class="form-page-subtitle">
                        Restablezca la contraseña solo si confirmó la identidad de
                        <strong>{{ $operator->username_normalized }}</strong>. Sus sesiones activas se revocarán.
                    </p>

                    @if($operator->status->value === 'ACTIVE')
                        <button type="button" class="btn btn--danger" data-password-reset-open>
                            Restablecer contraseña
                        </button>
                    @else
                        <div class="alert alert-warning" role="alert">
                            Active o desbloquee la cuenta antes de restablecer su contraseña.
                        </div>
                    @endif

                    <div class="modal-overlay" data-password-reset-modal hidden role="dialog"
                         aria-modal="true" aria-labelledby="password-reset-dialog-title">
                        <div class="modal">
                            <div class="modal-header">
                                <h3 id="password-reset-dialog-title" class="modal-title">
                                    Restablecer acceso de {{ $operator->username_normalized }}
                                </h3>
                                <button type="button" class="modal-close" data-password-reset-close
                                        aria-label="Cerrar">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div data-password-reset-form-panel>
                                    <p>Esta acción cerrará todas las sesiones del operador.</p>
                                    <form method="POST"
                                          action="{{ route('admin.users.password-reset', $operator) }}"
                                          data-password-reset-form>
                                        @csrf
                                        <x-ui.input
                                            label="Su contraseña actual"
                                            name="admin_password"
                                            type="password"
                                            required="true"
                                            autocomplete="current-password"
                                        />
                                        <x-ui.input
                                            label="Motivo (opcional)"
                                            name="reason"
                                            maxlength="500"
                                        />
                                        <p class="alert alert-danger" role="alert"
                                           data-password-reset-error aria-live="assertive"></p>
                                        <div class="form-actions">
                                            <button type="button" class="btn btn--secondary"
                                                    data-password-reset-close>Cancelar</button>
                                            <button type="submit" class="btn btn--danger">Confirmar restablecimiento</button>
                                        </div>
                                    </form>
                                </div>

                                <div data-password-reset-result hidden>
                                    <p data-password-reset-warning></p>
                                    <p>Vence: <time data-password-reset-expiry></time></p>
                                    <div class="input-group">
                                        <code data-password-reset-secret aria-label="Contraseña temporal"></code>
                                        <button type="button" class="btn btn--primary"
                                                data-password-reset-copy>Copiar</button>
                                    </div>
                                    <p aria-live="polite" data-password-reset-announcement></p>
                                    <p>La contraseña no podrá consultarse después de cerrar este cuadro.</p>
                                    <button type="button" class="btn btn--secondary"
                                            data-password-reset-close>Ya la compartí; cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>
@endsection
