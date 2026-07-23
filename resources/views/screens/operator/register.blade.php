@extends('layouts.authenticated')

@section('content')
<div class="registration-page">
    <div style="margin-bottom:var(--space-lg);">
        <h2 class="admin-title" style="margin-bottom:var(--space-xs);">Registro Rapido</h2>
        <p class="admin-subtitle">Ingrese los detalles de la transaccion de caja.</p>
</div>
@endsection


    <x-screen.operation-form
        :banks="$banks"
        :types="$types"
    />
</div>
