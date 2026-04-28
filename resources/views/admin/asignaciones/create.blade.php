@extends('layouts.kaiadmin')
@section('title', 'Asignar habitación')
@section('page-title', 'Asignar habitación')

@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="{{ route('admin.historial.index') }}">Historial</a></li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Nueva asignación</a></li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card card-round">
            <div class="card-header">
                <div class="card-head-row">
                    <div class="card-title">
                        <i class="fas fa-user-plus me-2"></i>Nueva asignación manual
                    </div>
                    <div class="card-tools">
                        <a href="{{ route('admin.historial.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.asignaciones.store') }}">
                    @csrf

                    {{-- Habitación --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium">Habitación <span class="text-danger">*</span></label>
                        <select name="habitacion_id" required
                                class="form-select @error('habitacion_id') is-invalid @enderror">
                            <option value="">— Seleccionar habitación —</option>
                            @foreach($habitaciones as $h)
                                <option value="{{ $h->id }}"
                                    @selected(old('habitacion_id', $preselect) == $h->id)>
                                    {{ $h->codigo }} — {{ $h->nombre }}
                                    ({{ str_replace('_', ' ', $h->estado) }},
                                    {{ $h->lencerias_count ?? $h->lencerias()->count() }} prendas)
                                </option>
                            @endforeach
                        </select>
                        @error('habitacion_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Empleado --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium">Empleado <span class="text-danger">*</span></label>
                        <select name="user_id" id="sel-empleado" required
                                class="form-select @error('user_id') is-invalid @enderror"
                                onchange="syncTurno(this)">
                            <option value="">— Seleccionar empleado —</option>
                            @foreach($empleados as $emp)
                                <option value="{{ $emp->id }}"
                                        data-turno="{{ $emp->turno }}"
                                        @selected(old('user_id') == $emp->id)>
                                    {{ $emp->name }}
                                    <span class="text-muted">({{ $emp->turno }})</span>
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Turno --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium">Turno <span class="text-danger">*</span></label>
                        <select name="turno" id="sel-turno" required
                                class="form-select @error('turno') is-invalid @enderror">
                            @foreach(['mañana','tarde','noche'] as $t)
                                <option value="{{ $t }}" @selected(old('turno') === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Se auto-rellena al elegir un empleado.</div>
                        @error('turno')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fecha --}}
                    <div class="mb-4">
                        <label class="form-label fw-medium">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" required
                               value="{{ old('fecha', today()->toDateString()) }}"
                               class="form-control @error('fecha') is-invalid @enderror">
                        @error('fecha')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i>Crear asignación
                        </button>
                        <a href="{{ route('admin.historial.index') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Info card --}}
        <div class="card card-round border-start border-info border-3 mt-3">
            <div class="card-body py-3">
                <p class="mb-1 text-muted small">
                    <i class="fas fa-info-circle text-info me-2"></i>
                    Si la asignación es para <strong>hoy</strong> y la habitación está <em>Disponible</em>,
                    su estado cambiará automáticamente a <strong>En limpieza</strong>.
                </p>
                <p class="mb-0 text-muted small">
                    <i class="fas fa-info-circle text-info me-2"></i>
                    Si la habitación ya tiene asignación para esa fecha, se mostrará un error.
                </p>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function syncTurno(sel) {
    const opt = sel.options[sel.selectedIndex];
    const turno = opt.dataset.turno;
    if (turno) {
        document.getElementById('sel-turno').value = turno;
    }
}
</script>
@endpush
