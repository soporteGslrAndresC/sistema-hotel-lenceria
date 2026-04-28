@extends('layouts.kaiadmin')
@section('title', 'Editar Habitación')
@section('page-title', 'Editar Habitación')
@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="{{ route('admin.habitaciones.index') }}">Habitaciones</a></li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Editar</a></li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-door-open me-2"></i>Editar: {{ $habitacion->codigo }}</div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.habitaciones.update', $habitacion) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-medium">Código</label>
                        <input name="codigo" value="{{ old('codigo', $habitacion->codigo) }}" required
                               class="form-control font-monospace @error('codigo') is-invalid @enderror">
                        @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nombre</label>
                        <input name="nombre" value="{{ old('nombre', $habitacion->nombre) }}" required
                               class="form-control @error('nombre') is-invalid @enderror">
                        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium">Estado</label>
                        <select name="estado" class="form-select">
                            @foreach(['disponible','ocupada','en_limpieza','fuera_servicio'] as $e)
                                <option value="{{ $e }}" @selected(old('estado', $habitacion->estado)===$e)>{{ ucfirst(str_replace('_',' ',$e)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.habitaciones.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
