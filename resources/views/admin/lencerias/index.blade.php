@extends('layouts.kaiadmin')
@section('title', 'Lencería')
@section('page-title', 'Lencería')
@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Lencería</a></li>
@endsection
@section('content')
{{-- FILTROS --}}
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
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos los tipos</option>
                    @foreach(['sabana','funda','toalla','almohada','cobija','bata'] as $t)
                        <option value="{{ $t }}" @selected(request('tipo')==$t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos los estados</option>
                    @foreach(['en_habitacion','en_lavanderia','extraviada'] as $e)
                        <option value="{{ $e }}" @selected(request('estado')==$e)>{{ ucfirst(str_replace('_',' ',$e)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-filter me-1"></i>Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card card-round">
    <div class="card-header">
        <div class="card-head-row">
            <div class="card-title"><i class="fas fa-tshirt me-2"></i>Prendas de lencería</div>
            <div class="card-tools d-flex gap-2">
                <a href="{{ route('admin.lencerias.imprimir', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-print me-1"></i>Imprimir QRs
                </a>
                <a href="{{ route('admin.lencerias.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Nueva prenda
                </a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>QR</th><th>Código</th><th>Tipo</th><th>Habitación</th><th>Estado</th><th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($lencerias as $l)
                    <tr>
                        <td class="py-1">
                            <img src="{{ route('admin.lencerias.qr', $l) }}" class="rounded" width="48" height="48" alt="QR">
                        </td>
                        <td><code class="small">{{ $l->codigo_qr }}</code></td>
                        <td class="text-capitalize">{{ $l->tipo }}</td>
                        <td><code>{{ $l->habitacion?->codigo ?? '—' }}</code></td>
                        <td>
                            @if($l->estado==='extraviada')
                                <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Extraviada</span>
                            @elseif($l->estado==='en_lavanderia')
                                <span class="badge bg-info"><i class="fas fa-water me-1"></i>Lavandería</span>
                            @else
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>En habitación</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.lencerias.edit', $l) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.lencerias.destroy', $l) }}" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar prenda {{ $l->codigo_qr }}?')">
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
    <div class="card-footer">
        {{ $lencerias->withQueryString()->links() }}
    </div>
</div>
@endsection
