@extends('layouts.kaiadmin')
@section('title', 'Empleados')
@section('page-title', 'Empleados')
@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Empleados</a></li>
@endsection
@section('content')
<div class="card card-round">
    <div class="card-header">
        <div class="card-head-row">
            <div class="card-title"><i class="fas fa-users me-2"></i>Lista de empleados</div>
            <div class="card-tools">
                <a href="{{ route('admin.empleados.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Nuevo empleado
                </a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th><th>Email</th><th>Turno</th><th>Estado</th><th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($empleados as $e)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-2">
                                    <span class="avatar-title rounded-circle bg-primary text-white">{{ strtoupper(substr($e->name,0,1)) }}</span>
                                </div>
                                {{ $e->name }}
                            </div>
                        </td>
                        <td class="text-muted">{{ $e->email }}</td>
                        <td><span class="badge bg-info text-capitalize">{{ $e->turno }}</span></td>
                        <td>
                            @if($e->activo)
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Activo</span>
                            @else
                                <span class="badge bg-secondary"><i class="fas fa-times me-1"></i>Inactivo</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.empleados.edit', $e) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.empleados.destroy', $e) }}" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar a {{ $e->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Sin empleados registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
