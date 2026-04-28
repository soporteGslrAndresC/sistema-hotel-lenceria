@extends('layouts.kaiadmin')
@section('title', 'Editar Empleado')
@section('page-title', 'Editar Empleado')
@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="{{ route('admin.empleados.index') }}">Empleados</a></li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Editar</a></li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-user-edit me-2"></i>Editar: {{ $empleado->name }}</div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.empleados.update', $empleado) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nombre</label>
                        <input name="name" value="{{ old('name', $empleado->name) }}" required class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Email</label>
                        <input name="email" type="email" value="{{ old('email', $empleado->email) }}" required class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nueva contraseña <small class="text-muted">(dejar vacía para no cambiar)</small></label>
                        <input name="password" type="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Turno</label>
                        <select name="turno" class="form-select">
                            @foreach(['mañana','tarde','noche'] as $t)
                                <option value="{{ $t }}" @selected(old('turno', $empleado->turno)===$t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="activo" value="1"
                                   id="activoCheck" @checked(old('activo', $empleado->activo))>
                            <label class="form-check-label" for="activoCheck">Empleado activo</label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.empleados.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
