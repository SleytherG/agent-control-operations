@extends('layouts.authenticated')

@section('title', $store->exists ? 'Editar Tienda' : 'Nueva Tienda — Control de Operaciones')

@section('content')
    <h1>{{ $store->exists ? 'Editar Tienda' : 'Nueva Tienda' }}</h1>

    <form method="POST" action="{{ $store->exists ? route('admin.stores.update', $store) : route('admin.stores.store') }}">
        @csrf
        @if($store->exists)
            @method('PATCH')
        @endif

        <div>
            <label for="district_id">Distrito</label>
            <select name="district_id" id="district_id" {{ isset($readonly) && $readonly ? 'disabled' : '' }} required>
                <option value="">Seleccione un distrito</option>
                @foreach($districts as $district)
                    <option value="{{ $district->id }}" {{ old('district_id', $store->district_id) == $district->id ? 'selected' : '' }}>
                        {{ $district->name }} ({{ $district->province?->name }} - {{ $district->province?->region?->name }})
                    </option>
                @endforeach
            </select>
            @error('district_id')<span>{{ $message }}</span>@enderror
        </div>

        <div>
            <label for="code">Código</label>
            <input type="text" name="code" id="code" value="{{ old('code', $store->code) }}" {{ isset($readonly) && $readonly ? 'readonly' : '' }} required maxlength="80">
            @error('code')<span>{{ $message }}</span>@enderror
        </div>

        <div>
            <label for="name">Nombre</label>
            <input type="text" name="name" id="name" value="{{ old('name', $store->name) }}" {{ isset($readonly) && $readonly ? 'readonly' : '' }} required maxlength="200">
            @error('name')<span>{{ $message }}</span>@enderror
        </div>

        <div>
            <label for="address">Dirección</label>
            <input type="text" name="address" id="address" value="{{ old('address', $store->address) }}" {{ isset($readonly) && $readonly ? 'readonly' : '' }} maxlength="500">
            @error('address')<span>{{ $message }}</span>@enderror
        </div>

        @unless(isset($readonly) && $readonly)
            <button type="submit">{{ $store->exists ? 'Actualizar' : 'Crear' }}</button>
        @endunless

        <a href="{{ route('admin.stores.index') }}">Cancelar</a>
    </form>
@endsection
