@extends('layouts.kaiadmin')
@section('title', 'Nueva Habitación')
@section('page-title', 'Nueva Habitación')
@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="{{ route('admin.habitaciones.index') }}">Habitaciones</a></li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Nueva</a></li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-door-open me-2"></i>Nueva habitación</div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.habitaciones.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium">Código <small class="text-muted">(ej: HAB-101)</small></label>
                        <input name="codigo" value="{{ old('codigo','HAB-') }}" required
                               class="form-control font-monospace @error('codigo') is-invalid @enderror">
                        @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nombre</label>
                        <input name="nombre" value="{{ old('nombre') }}" required
                               class="form-control @error('nombre') is-invalid @enderror">
                        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium">Estado inicial</label>
                        <select name="estado" class="form-select">
                            @foreach(['disponible','ocupada','en_limpieza','fuera_servicio'] as $e)
                                <option value="{{ $e }}">{{ ucfirst(str_replace('_',' ',$e)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.habitaciones.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Crear habitación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
