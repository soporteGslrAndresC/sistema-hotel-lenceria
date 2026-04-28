@extends('layouts.kaiadmin')
@section('title', 'Detalle Asignación')
@section('page-title', 'Detalle: ' . $asignacion->habitacion->codigo . ' — ' . $asignacion->fecha->format('d/m/Y'))
@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="{{ route('admin.historial.index') }}">Historial</a></li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Detalle</a></li>
@endsection
@section('content')
<div class="row">
    <div class="col-12 mb-3 d-flex gap-2">
        <a href="{{ route('admin.historial.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Volver al historial
        </a>
        <form method="POST" action="{{ route('admin.asignaciones.destroy', $asignacion) }}"
              onsubmit="return confirm('¿Eliminar esta asignación? Se borrarán también los ítems del checklist.')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm">
                <i class="fas fa-trash me-1"></i>Eliminar asignación
            </button>
        </form>
    </div>

    {{-- Info card --}}
    <div class="col-md-4 mb-3">
        <div class="card card-round h-100">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-info-circle me-2"></i>Información</div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Habitación</dt>
                    <dd class="col-7"><code>{{ $asignacion->habitacion->codigo }}</code></dd>

                    <dt class="col-5 text-muted">Empleado</dt>
                    <dd class="col-7">{{ $asignacion->empleado?->name }}</dd>

                    <dt class="col-5 text-muted">Turno</dt>
                    <dd class="col-7"><span class="badge bg-info text-capitalize">{{ $asignacion->turno }}</span></dd>

                    <dt class="col-5 text-muted">Estado</dt>
                    <dd class="col-7">
                        <span class="badge
                            @if($asignacion->estado==='completa') bg-success
                            @elseif($asignacion->estado==='incompleta') bg-danger
                            @else bg-secondary @endif">
                            {{ str_replace('_',' ', strtoupper($asignacion->estado)) }}
                        </span>
                    </dd>

                    <dt class="col-5 text-muted">Iniciada</dt>
                    <dd class="col-7">{{ $asignacion->iniciada_at?->format('H:i') ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Completada</dt>
                    <dd class="col-7">{{ $asignacion->completada_at?->format('H:i') ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Faltantes</dt>
                    <dd class="col-7">
                        @if($asignacion->tiene_faltantes)
                            <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Sí</span>
                        @else
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>No</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Checklist table --}}
    <div class="col-md-8 mb-3">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-list-check me-2"></i>Checklist de prendas</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Tipo</th><th>Código QR</th><th class="text-center">Escaneado</th><th>Hora</th></tr>
                        </thead>
                        <tbody>
                        @foreach($asignacion->checklist as $i)
                            <tr class="{{ !$i->escaneado ? 'table-danger' : '' }}">
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td class="text-capitalize">{{ $i->lenceria?->tipo ?? '—' }}</td>
                                <td><code class="small">{{ $i->lenceria?->codigo_qr ?? '—' }}</code></td>
                                <td class="text-center">
                                    @if($i->escaneado)
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-times-circle text-danger"></i>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $i->escaneado_at?->format('H:i:s') ?? '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
