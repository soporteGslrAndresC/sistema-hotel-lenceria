@extends('layouts.kaiadmin')
@section('title', 'Editar Prenda')
@section('page-title', 'Editar Prenda')
@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="{{ route('admin.lencerias.index') }}">Lencería</a></li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Editar</a></li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        {{-- QR Preview --}}
        <div class="card card-round mb-3">
            <div class="card-body d-flex align-items-center gap-3">
                <img src="{{ route('admin.lencerias.qr', $lenceria) }}" class="rounded" width="100" height="100" alt="QR">
                <div>
                    <div class="text-muted small">Código QR</div>
                    <code class="fw-bold">{{ $lenceria->codigo_qr }}</code>
                </div>
            </div>
        </div>

        <div class="card card-round">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-tshirt me-2"></i>Editar prenda</div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.lencerias.update', $lenceria) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-medium">Tipo</label>
                        <select name="tipo" class="form-select">
                            @foreach(['sabana','funda','toalla','almohada','cobija','bata'] as $t)
                                <option value="{{ $t }}" @selected(old('tipo', $lenceria->tipo)===$t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Habitación</label>
                        <select name="habitacion_id" class="form-select">
                            @foreach($habitaciones as $h)
                                <option value="{{ $h->id }}" @selected(old('habitacion_id', $lenceria->habitacion_id)==$h->id)>
                                    {{ $h->codigo }} — {{ $h->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium">Estado</label>
                        <select name="estado" class="form-select">
                            @foreach(['en_habitacion','en_lavanderia','extraviada'] as $e)
                                <option value="{{ $e }}" @selected(old('estado', $lenceria->estado)===$e)>{{ ucfirst(str_replace('_',' ',$e)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.lencerias.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
