@extends('layouts.kaiadmin')
@section('title', 'Historial')
@section('page-title', 'Historial de Checklists')
@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Historial</a></li>
@endsection
@section('content')
{{-- Filtros --}}
<div class="card card-round mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
                <select name="habitacion_id" class="form-select form-select-sm">
                    <option value="">Todas las habitaciones</option>
                    @foreach($habitaciones as $h)
                        <option value="{{ $h->id }}" @selected(request('habitacion_id')==$h->id)>{{ $h->codigo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Todos los empleados</option>
                    @foreach($empleados as $e)
                        <option value="{{ $e->id }}" @selected(request('user_id')==$e->id)>{{ $e->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="fecha" value="{{ request('fecha') }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-search me-1"></i>Buscar</button>
            </div>
        </form>
    </div>
</div>

<div class="card card-round">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-history me-2"></i>Asignaciones</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th><th>Habitación</th><th>Empleado</th><th>Turno</th>
                        <th>Estado</th><th class="text-center">Progreso</th><th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($asignaciones as $a)
                    @php $p = $a->progreso(); @endphp
                    <tr class="{{ $a->tiene_faltantes ? 'table-warning' : '' }}">
                        <td>{{ $a->fecha->format('d/m/Y') }}</td>
                        <td><code>{{ $a->habitacion?->codigo }}</code></td>
                        <td>{{ $a->empleado?->name }}</td>
                        <td><span class="badge bg-info text-capitalize">{{ $a->turno }}</span></td>
                        <td>
                            <span class="badge
                                @if($a->estado==='completa') bg-success
                                @elseif($a->estado==='incompleta') bg-danger
                                @elseif($a->estado==='en_proceso') bg-warning text-dark
                                @else bg-secondary @endif">
                                {{ str_replace('_',' ', strtoupper($a->estado)) }}
                            </span>
                        </td>
                        <td class="text-center">
                            {{ $p['hechos'] }}/{{ $p['total'] }}
                            @if($a->tiene_faltantes)
                                <i class="fas fa-exclamation-triangle text-danger ms-1" title="Tiene faltantes"></i>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.historial.show', $a) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $asignaciones->withQueryString()->links() }}
    </div>
</div>
@endsection
