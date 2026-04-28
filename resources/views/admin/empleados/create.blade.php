@extends('layouts.kaiadmin')
@section('title', 'Nuevo Empleado')
@section('page-title', 'Nuevo Empleado')
@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="{{ route('admin.empleados.index') }}">Empleados</a></li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Nuevo</a></li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-user-plus me-2"></i>Nuevo empleado</div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.empleados.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nombre</label>
                        <input name="name" value="{{ old('name') }}" required class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Email</label>
                        <input name="email" type="email" value="{{ old('email') }}" required class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Contraseña</label>
                        <input name="password" type="password" required class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium">Turno</label>
                        <select name="turno" class="form-select">
                            <option value="mañana" @selected(old('turno')==='mañana')>Mañana</option>
                            <option value="tarde" @selected(old('turno')==='tarde')>Tarde</option>
                            <option value="noche" @selected(old('turno')==='noche')>Noche</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.empleados.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Crear empleado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
