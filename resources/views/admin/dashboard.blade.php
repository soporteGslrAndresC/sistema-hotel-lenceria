@extends('layouts.kaiadmin')

@section('title', 'Tablero en vivo')
@section('page-title', 'Tablero en tiempo real')

@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Tablero</a></li>
@endsection

@push('styles')
<style>
    .card-stats .icon-big { width: 55px; height: 55px; line-height: 55px; font-size: 1.5rem; }
    .room-card { transition: transform .15s, box-shadow .15s; cursor: default; }
    .room-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.12) !important; }
    .progress-thin { height: 5px; border-radius: 3px; }
    .skew-shadow { position: relative; overflow: hidden; }
    .skew-shadow::after { content: ''; position: absolute; top: -30px; right: -30px;
        width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,.08); }
    .turno-badge { font-size: .65rem; letter-spacing: .05em; text-transform: uppercase; }
</style>
@endpush

@section('content')
<div x-data="adminDashboard()" x-init="init()">

    {{-- ── Alertas tiempo real ── --}}
    <template x-for="(a, idx) in alertas" :key="idx">
        <div class="alert alert-dismissible fade show mb-2"
             :class="a.tipo === 'extraviada' ? 'alert-danger' : a.tipo === 'faltantes' ? 'alert-warning' : 'alert-info'">
            <i class="fas me-2" :class="a.tipo === 'faltantes' ? 'fa-exclamation-triangle' : 'fa-bell'"></i>
            <strong class="text-uppercase" x-text="a.tipo"></strong> — <span x-text="a.mensaje"></span>
            <small class="text-muted ms-2" x-text="a.hora"></small>
            <button type="button" class="btn-close" @click="alertas.splice(idx,1)"></button>
        </div>
    </template>

    {{-- ══════════════════════════════════════════════════════════════════════
         ROW 1 · KPI cards (habitaciones)
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-success card-round">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center"><i class="fas fa-door-open"></i></div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Disponibles</p>
                                <h4 class="card-title">{{ $stats['disponibles'] }} <small class="text-white-50 fs-6">/ {{ $stats['total'] }}</small></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-warning card-round">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center"><i class="fas fa-broom"></i></div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">En limpieza</p>
                                <h4 class="card-title">{{ $stats['en_limpieza'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-primary card-round">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center"><i class="fas fa-check-double"></i></div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Completadas hoy</p>
                                <h4 class="card-title">{{ $stats['completadas'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-secondary card-round">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center"><i class="fas fa-users"></i></div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Empleados activos</p>
                                <h4 class="card-title">{{ $stats['empleados'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         ROW 2 · secondary KPIs + summary cards
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-danger bubble-shadow-small">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Incompletas hoy</p>
                                <h4 class="card-title">{{ $stats['incompletas'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-search-minus"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Prendas extraviadas</p>
                                <h4 class="card-title">{{ $stats['extraviadas'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-tshirt"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Total prendas</p>
                                <h4 class="card-title">{{ $stats['prendas'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                <i class="fas fa-bed"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Ocupadas</p>
                                <h4 class="card-title">{{ $stats['ocupadas'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         ROW 3 · Rendimiento por empleado (hoy)
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="row mb-2">
        <div class="col-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">
                            <i class="fas fa-chart-bar me-2"></i>Rendimiento por empleado — {{ now()->isoFormat('dddd D [de] MMMM') }}
                        </div>
                        <div class="card-tools">
                            <a href="{{ route('admin.historial.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-history me-1"></i>Ver historial
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($empleadoStats->isEmpty())
                        <p class="text-muted text-center py-3 mb-0"><i class="fas fa-info-circle me-1"></i>Sin asignaciones registradas hoy.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Empleado</th>
                                    <th>Turno</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center"><span class="badge bg-success">Completas</span></th>
                                    <th class="text-center"><span class="badge bg-warning text-dark">En proceso</span></th>
                                    <th class="text-center"><span class="badge bg-danger">Incompletas</span></th>
                                    <th class="text-center"><span class="badge bg-secondary">Pendientes</span></th>
                                    <th style="min-width:140px;">Avance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($empleadoStats as $e)
                                @php
                                    $barColor = $e['pct'] >= 80 ? 'bg-success' : ($e['pct'] >= 40 ? 'bg-warning' : 'bg-danger');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-sm">
                                                <span class="avatar-title rounded-circle bg-primary text-white fw-bold">
                                                    {{ strtoupper(substr($e['name'], 0, 1)) }}
                                                </span>
                                            </div>
                                            <span class="fw-semibold">{{ $e['name'] }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge turno-badge
                                            @if($e['turno'] === 'mañana') bg-info
                                            @elseif($e['turno'] === 'tarde') bg-warning text-dark
                                            @else bg-secondary @endif">
                                            {{ $e['turno'] }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold">{{ $e['total'] }}</td>
                                    <td class="text-center">
                                        <span class="fw-bold text-success">{{ $e['completas'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-warning">{{ $e['enProceso'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-danger">{{ $e['incompletas'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-secondary">{{ $e['pendientes'] }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1 progress-thin">
                                                <div class="progress-bar {{ $barColor }}" style="width: {{ $e['pct'] }}%"></div>
                                            </div>
                                            <small class="fw-bold" style="min-width:32px;">{{ $e['pct'] }}%</small>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         ROW 4 · Grid de habitaciones en tiempo real
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="card card-round">
        <div class="card-header">
            <div class="card-head-row">
                <div class="card-title">
                    <i class="fas fa-th me-2"></i>Estado de habitaciones en vivo
                </div>
                <div class="card-tools d-flex align-items-center gap-3">
                    <a href="{{ route('admin.asignaciones.create') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-user-plus me-1"></i>Asignar habitación
                    </a>
                    <small><span class="badge bg-success me-1">&nbsp;</span>Libre</small>
                    <small><span class="badge bg-warning me-1 text-dark">&nbsp;</span>Limpieza</small>
                    <small><span class="badge bg-danger me-1">&nbsp;</span>Faltantes</small>
                    <small><span class="badge bg-secondary me-1">&nbsp;</span>Ocupada</small>
                    <small class="text-muted"><i class="fas fa-circle text-success me-1" style="font-size:.6rem;"></i>En vivo</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <template x-for="h in habitaciones" :key="h.id">
                    <div class="col-6 col-md-4 col-lg-3 col-xl-2 mb-3">
                        <div class="card card-stats card-round room-card h-100 mb-0"
                             :style="'border-top: 3px solid ' + borderColor(h)">
                            <div class="card-body p-3">
                                {{-- Icon + código --}}
                                <div class="row align-items-center mb-2">
                                    <div class="col-icon">
                                        <div class="icon-big text-center bubble-shadow-small"
                                             :class="iconClass(h)">
                                            <i class="fas fa-door-open"></i>
                                        </div>
                                    </div>
                                    <div class="col col-stats ms-2">
                                        <div class="numbers">
                                            <p class="card-category mb-0" style="font-size:.7rem;" x-text="h.nombre"></p>
                                            <h6 class="card-title mb-0 fw-bold" x-text="h.codigo"></h6>
                                        </div>
                                    </div>
                                </div>
                                {{-- Estado badge --}}
                                <span class="badge w-100 mb-2" :class="badgeClass(h)" x-text="estadoLabel(h.estado)"></span>
                                {{-- Progreso prendas --}}
                                <div x-show="h.totalChecklist > 0">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="text-muted" style="font-size:.7rem;">Prendas</small>
                                        <small class="fw-bold" style="font-size:.7rem;">
                                            <span x-text="h.hechosChecklist"></span>/<span x-text="h.totalChecklist"></span>
                                        </small>
                                    </div>
                                    <div class="progress progress-thin mb-2">
                                        <div class="progress-bar" :class="progressBarClass(h)"
                                             :style="'width:' + pct(h) + '%'"></div>
                                    </div>
                                </div>
                                {{-- Empleado --}}
                                <div x-show="h.empleado">
                                    <small class="text-muted" style="font-size:.68rem;">
                                        <i class="fas fa-user me-1"></i><span x-text="h.empleado"></span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>{{-- /x-data --}}
@endsection

@push('scripts')
<script>
function adminDashboard() {
    return {
        habitaciones: @json($habitaciones),
        alertas: [],

        init() {
            if (window.Echo) {
                window.Echo.private('admin.habitaciones')
                    .listen('.habitacion.actualizada', (e) => {
                        const idx = this.habitaciones.findIndex(h => h.id === e.habitacionId);
                        const data = {
                            id: e.habitacionId, codigo: e.codigo, nombre: e.nombre,
                            estado: e.estado, totalChecklist: e.totalChecklist,
                            hechosChecklist: e.hechosChecklist,
                            tieneFaltantes: e.tieneFaltantes, empleado: e.empleado,
                        };
                        if (idx >= 0) this.habitaciones.splice(idx, 1, data);
                        else this.habitaciones.push(data);
                    })
                    .listen('.checklist.actualizado', (e) => {
                        const idx = this.habitaciones.findIndex(h => h.id === e.habitacionId);
                        if (idx >= 0) {
                            this.habitaciones[idx].hechosChecklist = e.hechos;
                            this.habitaciones[idx].totalChecklist   = e.total;
                        }
                    });
                window.Echo.private('admin.alertas')
                    .listen('.alerta', (e) => {
                        this.alertas.unshift({ tipo: e.tipo, mensaje: e.mensaje, hora: new Date().toLocaleTimeString() });
                    });
            }
        },

        pct(h) {
            return h.totalChecklist ? Math.round(h.hechosChecklist / h.totalChecklist * 100) : 0;
        },
        borderColor(h) {
            if (h.tieneFaltantes)          return '#dc3545';
            if (h.estado === 'en_limpieza') return '#ffc107';
            if (h.estado === 'ocupada')     return '#6c757d';
            if (h.estado === 'fuera_servicio') return '#e74c3c';
            return '#28a745';
        },
        iconClass(h) {
            if (h.tieneFaltantes)          return 'icon-danger';
            if (h.estado === 'en_limpieza') return 'icon-warning';
            if (h.estado === 'ocupada')     return 'icon-secondary';
            if (h.estado === 'fuera_servicio') return 'icon-danger';
            return 'icon-success';
        },
        badgeClass(h) {
            if (h.tieneFaltantes)          return 'bg-danger';
            if (h.estado === 'en_limpieza') return 'bg-warning text-dark';
            if (h.estado === 'ocupada')     return 'bg-secondary';
            if (h.estado === 'fuera_servicio') return 'bg-dark';
            return 'bg-success';
        },
        progressBarClass(h) {
            const p = this.pct(h);
            return p >= 80 ? 'bg-success' : p >= 40 ? 'bg-warning' : 'bg-danger';
        },
        estadoLabel(estado) {
            return { disponible:'Libre', ocupada:'Ocupada', en_limpieza:'En limpieza',
                     fuera_servicio:'F. Servicio' }[estado] ?? estado;
        },
    };
}
</script>
@endpush
