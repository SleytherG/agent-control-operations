<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — Control de Operaciones</title>
</head>
<body>
    <main>
        <h1>Iniciar sesión</h1>
        @if ($errors->any())
            <div role="alert" class="error-message">
                {{ $errors->first() }}
            </div>
        @endif
        <form method="POST" action="{{ route('login.store') }}" novalidate>
            @csrf
            <div>
                <label for="identifier">Usuario o correo</label>
                <input type="text" id="identifier" name="identifier"
                       value="{{ old('identifier') }}" required autocomplete="username"
                       maxlength="254">
            </div>
            <div>
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password"
                       required autocomplete="current-password">
            </div>
            <button type="submit">Ingresar</button>
        </form>
    </main>
</body>
</html>
