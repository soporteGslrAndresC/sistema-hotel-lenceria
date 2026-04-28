@extends('layouts.kaiadmin')

@section('title', 'Mis Habitaciones')
@section('page-title', 'Mis Habitaciones — ' . now()->format('d/m/Y'))

@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Mis Habitaciones</a></li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="mb-0">
                    <i class="fas fa-user-clock me-2 text-primary"></i>
                    Turno: <span class="badge bg-primary">{{ auth()->user()->turno ?? '—' }}</span>
                </h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('empleado.scanner') }}" class="btn btn-primary btn-round">
                    <i class="fas fa-qrcode me-2"></i>Escanear QR
                </a>
                <small class="text-muted d-none d-md-inline"><i class="far fa-calendar-alt me-1"></i>{{ now()->isoFormat('dddd D [de] MMMM, YYYY') }}</small>
            </div>
        </div>
    </div>

    @if($asignaciones->isEmpty())
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-door-closed fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Sin habitaciones asignadas hoy</h5>
                    <p class="text-muted mb-0">Cuando una habitación pase a <strong>en limpieza</strong> en tu turno, aparecerá aquí automáticamente.</p>
                </div>
            </div>
        </div>
    @else
        @foreach($asignaciones as $a)
            @php
                $p = $a->progreso();
                $pct = $p['total'] > 0 ? intval($p['hechos'] / $p['total'] * 100) : 0;
                $colorBadge = match($a->estado) {
                    'completa'   => 'success',
                    'incompleta' => 'danger',
                    'en_proceso' => 'warning',
                    default      => 'secondary',
                };
                $colorBar = match($a->estado) {
                    'completa'   => 'bg-success',
                    'incompleta' => 'bg-danger',
                    'en_proceso' => 'bg-warning',
                    default      => 'bg-secondary',
                };
                $iconEstado = match($a->estado) {
                    'completa'   => 'fas fa-check-circle text-success',
                    'incompleta' => 'fas fa-exclamation-circle text-danger',
                    'en_proceso' => 'fas fa-spinner text-warning',
                    default      => 'fas fa-clock text-secondary',
                };
            @endphp
            <div class="col-md-6 col-xl-4 mb-4">
                <a href="{{ route('empleado.asignacion.show', $a) }}" class="text-decoration-none">
                    <div class="card card-stats card-round h-100">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-{{ $colorBadge }} bubble-shadow-small">
                                        <i class="fas fa-door-open"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">{{ $a->habitacion->nombre }}</p>
                                        <h4 class="card-title">{{ $a->habitacion->codigo }}</h4>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-2">

                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">Prendas escaneadas</small>
                                <small class="fw-bold">{{ $p['hechos'] }} / {{ $p['total'] }}</small>
                            </div>
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar {{ $colorBar }}" role="progressbar"
                                     style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}"
                                     aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="badge bg-{{ $colorBadge }}">
                                    <i class="{{ $iconEstado }} me-1"></i>
                                    {{ str_replace('_', ' ', strtoupper($a->estado)) }}
                                </span>
                                <small class="text-muted">
                                    <i class="fas fa-chevron-right"></i>
                                </small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    @endif
</div>
@endsection
