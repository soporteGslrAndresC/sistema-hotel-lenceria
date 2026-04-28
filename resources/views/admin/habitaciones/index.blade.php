@extends('layouts.kaiadmin')
@section('title', 'Habitaciones')
@section('page-title', 'Habitaciones')
@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Habitaciones</a></li>
@endsection
@section('content')
<div class="card card-round">
    <div class="card-header">
        <div class="card-head-row">
            <div class="card-title"><i class="fas fa-door-open me-2"></i>Habitaciones</div>
            <div class="card-tools d-flex gap-2">
                <a href="{{ route('admin.asignaciones.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-user-plus me-1"></i>Asignar habitación
                </a>
                <a href="{{ route('admin.habitaciones.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Nueva habitación
                </a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th><th>Nombre</th><th>Estado</th><th class="text-center">Prendas</th><th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($habitaciones as $h)
                    <tr>
                        <td><code class="fw-bold">{{ $h->codigo }}</code></td>
                        <td>{{ $h->nombre }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.habitaciones.estado', $h) }}" class="d-flex gap-1 align-items-center">
                                @csrf @method('PATCH')
                                <select name="estado" class="form-select form-select-sm" style="width:auto">
                                    @foreach(['disponible','ocupada','en_limpieza','fuera_servicio'] as $e)
                                        <option value="{{ $e }}" @selected($h->estado===$e)>{{ ucfirst(str_replace('_',' ',$e)) }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-outline-secondary" title="Cambiar estado">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $h->lencerias_count }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.asignaciones.create', ['habitacion' => $h->id]) }}"
                               class="btn btn-sm btn-outline-success me-1" title="Asignar a empleado">
                                <i class="fas fa-user-plus"></i>
                            </a>
                            <a href="{{ route('admin.habitaciones.edit', $h) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.habitaciones.destroy', $h) }}" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar habitación {{ $h->codigo }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
