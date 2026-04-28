@extends('layouts.kaiadmin')
@section('title', 'Nueva Prenda')
@section('page-title', 'Nueva Prenda de Lencería')
@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="{{ route('admin.lencerias.index') }}">Lencería</a></li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Nueva</a></li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-round">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-tshirt me-2"></i>Nueva prenda</div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.lencerias.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium">Tipo de prenda</label>
                        <select name="tipo" class="form-select">
                            @foreach(['sabana','funda','toalla','almohada','cobija','bata'] as $t)
                                <option value="{{ $t }}" @selected(old('tipo')===$t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Habitación</label>
                        <select name="habitacion_id" class="form-select">
                            @foreach($habitaciones as $h)
                                <option value="{{ $h->id }}" @selected(old('habitacion_id')==$h->id)>{{ $h->codigo }} — {{ $h->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Estado inicial</label>
                        <select name="estado" class="form-select">
                            @foreach(['en_habitacion','en_lavanderia','extraviada'] as $e)
                                <option value="{{ $e }}">{{ ucfirst(str_replace('_',' ',$e)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium">
                            Cantidad
                            <small class="text-muted">(genera N prendas con QR único)</small>
                        </label>
                        <input type="number" name="cantidad" value="{{ old('cantidad', 1) }}"
                               min="1" max="50" class="form-control @error('cantidad') is-invalid @enderror">
                        @error('cantidad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.lencerias.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Crear prendas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
