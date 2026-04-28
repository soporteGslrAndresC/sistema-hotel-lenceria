@extends('layouts.kaiadmin')

@section('title', $asignacion->habitacion->codigo)
@section('page-title', $asignacion->habitacion->codigo . ' — ' . $asignacion->habitacion->nombre)

@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="{{ route('empleado.dashboard') }}">Mis Habitaciones</a></li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">{{ $asignacion->habitacion->codigo }}</a></li>
@endsection

@push('styles')
<style>
    #qr-reader { border-radius: 8px; overflow: hidden; }
    .lenceria-row { transition: background .2s; }
    .lenceria-row.escaneado { background-color: #e8f5e9; }
    .lenceria-row.escaneado .badge-estado { background-color: #28a745; }
</style>
@endpush

@section('content')
<div x-data="checklist()" x-init="init()">

    {{-- Alerta de mensaje flotante --}}
    <template x-if="msg">
        <div :class="msgClass" class="alert alert-dismissible fade show mb-3" role="alert" x-text="msg">
            <button type="button" class="btn-close" @click="msg=''"></button>
        </div>
    </template>

    <div class="row">

        {{-- ====== Col izquierda: escáner + manual ====== --}}
        <div class="col-md-5 mb-4">

            {{-- Card estado habitación --}}
            <div class="card card-round mb-3">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">
                            <i class="fas fa-door-open me-2"></i>{{ $asignacion->habitacion->codigo }}
                        </div>
                        <div class="card-tools">
                            <span class="badge
                                @if($asignacion->estado === 'completa') bg-success
                                @elseif($asignacion->estado === 'incompleta') bg-danger
                                @elseif($asignacion->estado === 'en_proceso') bg-warning
                                @else bg-secondary @endif">
                                {{ str_replace('_', ' ', strtoupper($asignacion->estado)) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-1"><i class="fas fa-hotel me-1"></i>{{ $asignacion->habitacion->nombre }}</p>
                    <p class="text-muted mb-1"><i class="fas fa-clock me-1"></i>Turno: <strong>{{ $asignacion->turno }}</strong></p>
                    @if($asignacion->iniciada_at)
                        <p class="text-muted mb-0"><i class="fas fa-play-circle me-1"></i>Inicio: {{ $asignacion->iniciada_at->format('H:i') }}</p>
                    @endif
                </div>
            </div>

            {{-- Card escáner --}}
            <div class="card card-round mb-3">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-qrcode me-2"></i>Escanear prenda</div>
                </div>
                <div class="card-body">
                    <button @click="toggleScanner()"
                            :disabled="completada"
                            class="btn w-100 mb-3"
                            :class="scanning ? 'btn-danger' : 'btn-primary'">
                        <i :class="scanning ? 'fas fa-stop-circle' : 'fas fa-camera'" class="me-2"></i>
                        <span x-show="!scanning">Activar cámara QR</span>
                        <span x-show="scanning">Detener cámara</span>
                    </button>

                    <div id="qr-reader" x-show="scanning" class="mb-3"></div>

                    <hr>
                    <p class="text-muted small mb-2"><i class="fas fa-keyboard me-1"></i>Ingreso manual</p>
                    <div class="input-group">
                        <input type="text" x-model="manualCode"
                               class="form-control"
                               placeholder="LEN-XXXXXXXXXX"
                               @keyup.enter="enviarCodigo(manualCode); manualCode=''">
                        <button class="btn btn-secondary"
                                @click="enviarCodigo(manualCode); manualCode=''">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Botón cerrar habitación --}}
            @if(!in_array($asignacion->estado, ['completa','incompleta']))
            <form method="POST" action="{{ route('empleado.asignacion.completar', $asignacion) }}"
                  onsubmit="return confirm('¿Cerrar esta habitación? Prendas faltantes quedarán registradas.')">
                @csrf
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-check-circle me-2"></i>Cerrar habitación
                </button>
            </form>
            @else
            <a href="{{ route('empleado.dashboard') }}" class="btn btn-outline-secondary w-100">
                <i class="fas fa-arrow-left me-2"></i>Volver al dashboard
            </a>
            @endif

        </div>

        {{-- ====== Col derecha: progreso + checklist ====== --}}
        <div class="col-md-7">

            {{-- Resumen por tipo --}}
            <div class="card card-round mb-3">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-chart-bar me-2"></i>Progreso por tipo</div>
                    <div class="card-tools">
                        <span class="fw-bold" x-text="hechosTotal + ' / ' + totalChecklist"></span>
                        <span class="text-muted ms-1">prendas</span>
                    </div>
                </div>
                <div class="card-body pb-2">
                    @foreach($conteos as $tipo => $c)
                    <div class="mb-3" id="tipo-bloque-{{ $tipo }}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-capitalize fw-medium">{{ $tipo }}</span>
                            <span class="text-muted small">
                                <span x-text="conteoTipo['{{ $tipo }}'] ? conteoTipo['{{ $tipo }}'].hechos : {{ $c['hechos'] }}"></span>
                                / {{ $c['total'] }}
                            </span>
                        </div>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar bg-primary" role="progressbar"
                                 :style="'width:' + (conteoTipo['{{ $tipo }}'] ? Math.round(conteoTipo['{{ $tipo }}'].hechos / {{ $c['total'] }} * 100) : {{ $c['total'] ? intval($c['hechos']/$c['total']*100) : 0 }}) + '%'"
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Lista checklist --}}
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-list-check me-2"></i>Checklist de prendas</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Tipo</th>
                                    <th>Código QR</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($asignacion->checklist as $item)
                                <tr class="lenceria-row" :class="checkItems[{{ $item->id }}]?.escaneado ? 'escaneado' : ''" id="row-{{ $item->id }}">
                                    <td class="text-muted small">{{ $loop->iteration }}</td>
                                    <td class="text-capitalize">{{ $item->lenceria?->tipo ?? '—' }}</td>
                                    <td><code class="small">{{ $item->lenceria?->codigo_qr ?? '—' }}</code></td>
                                    <td class="text-center">
                                        <span class="badge badge-estado"
                                              :class="checkItems[{{ $item->id }}]?.escaneado ? 'bg-success' : 'bg-secondary'">
                                            <i :class="checkItems[{{ $item->id }}]?.escaneado ? 'fas fa-check' : 'fas fa-times'" class="me-1"></i>
                                            <span x-text="checkItems[{{ $item->id }}]?.escaneado ? 'OK' : 'Pendiente'"></span>
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle me-2"></i>No hay prendas asignadas a esta habitación.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
function checklist() {
    return {
        scanning:      false,
        html5QrCode:   null,
        manualCode:    '',
        msg:           '',
        msgClass:      '',
        completada:    {{ in_array($asignacion->estado, ['completa','incompleta']) ? 'true' : 'false' }},
        checkItems:    @json($checkItemsData),
        conteoTipo:    @json(collect($conteos)->map(fn($c) => ['hechos' => $c['hechos'], 'total' => $c['total']])),
        totalChecklist: {{ $asignacion->checklist->count() }},

        get hechosTotal() {
            return Object.values(this.checkItems).filter(i => i.escaneado).length;
        },

        init() {
            // Sin acción de init adicional por ahora
        },

        toggleScanner() {
            if (this.scanning) {
                this.stopScanner();
            } else {
                this.startScanner();
            }
        },

        startScanner() {
            this.html5QrCode = new Html5Qrcode('qr-reader');
            this.html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    this.enviarCodigo(decodedText);
                },
                (err) => {}
            ).then(() => {
                this.scanning = true;
            }).catch(err => {
                this.flash('No se pudo acceder a la cámara: ' + err, 'alert-danger');
            });
        },

        stopScanner() {
            if (this.html5QrCode) {
                this.html5QrCode.stop().then(() => {
                    this.html5QrCode.clear();
                    this.scanning = false;
                });
            }
        },

        enviarCodigo(codigo) {
            if (!codigo.trim()) return;
            fetch('{{ route("empleado.asignacion.escanear", $asignacion) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ codigo_qr: codigo.trim() }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    const id = data.checklist_item_id;
                    if (id && this.checkItems[id] !== undefined && !this.checkItems[id].escaneado) {
                        this.checkItems[id].escaneado = true;
                        const tipo = this.checkItems[id].tipo;
                        if (tipo && this.conteoTipo[tipo] !== undefined) {
                            this.conteoTipo[tipo].hechos++;
                        }
                    }
                    this.flash(data.mensaje ?? '✓ Prenda escaneada', 'alert-success');
                    if (data.completada) {
                        this.completada = true;
                        this.stopScanner();
                    }
                } else {
                    this.flash(data.mensaje ?? 'Error al procesar', 'alert-warning');
                }
            })
            .catch(() => this.flash('Error de conexión', 'alert-danger'));
        },

        flash(mensaje, clase) {
            this.msg      = mensaje;
            this.msgClass = clase;
            setTimeout(() => { this.msg = ''; }, 4000);
        },
    };
}
</script>
@endpush
