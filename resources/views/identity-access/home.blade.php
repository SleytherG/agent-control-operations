@extends('layouts.authenticated')

@section('title', 'Inicio — Control de Operaciones')

@section('content')
    <h1>Bienvenido</h1>
    <p>Has iniciado sesión correctamente.</p>
    <p>Tiempo restante: <span id="session-timer"></span></p>
@endsection
