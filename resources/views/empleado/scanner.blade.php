@extends('layouts.kaiadmin')

@section('title', 'Escáner QR')
@section('page-title', 'Escáner QR Universal')

@section('breadcrumbs')
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="{{ route('empleado.dashboard') }}">Mis Habitaciones</a></li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">Escáner QR</a></li>
@endsection

@push('styles')
<style>
    #qr-reader { border-radius: 8px; overflow: hidden; max-width: 400px; margin: 0 auto; }
    #qr-reader video { border-radius: 8px; }
    .scan-result {
        animation: fadeInUp .3s ease;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .result-card {
        border-left: 4px solid;
        transition: all .2s;
    }
    .result-card.success { border-left-color: #28a745; }
    .result-card.warning { border-left-color: #ffc107; }
    .result-card.danger  { border-left-color: #dc3545; }
    .result-card.info    { border-left-color: #17a2b8; }
</style>
@endpush

@section('content')
<div x-data="globalScanner()" x-init="init()">

    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            {{-- Card escáner --}}
            <div class="card card-round mb-3">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">
                            <i class="fas fa-qrcode me-2 text-primary"></i>Escáner QR
                        </div>
                        <div class="card-tools">
                            <a href="{{ route('empleado.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Volver
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body text-center">
                    <p class="text-muted mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Escanea cualquier QR de prenda y el sistema identificará automáticamente la habitación.
                    </p>

                    <button @click="toggleScanner()"
                            class="btn btn-lg w-100 mb-3"
                            :class="scanning ? 'btn-danger' : 'btn-primary'">
                        <i :class="scanning ? 'fas fa-stop-circle' : 'fas fa-camera'" class="me-2"></i>
                        <span x-show="!scanning">Activar cámara</span>
                        <span x-show="scanning">Detener cámara</span>
                    </button>

                    <div id="qr-reader" x-show="scanning" class="mb-3"></div>

                    <hr>
                    <p class="text-muted small mb-2"><i class="fas fa-keyboard me-1"></i>O ingresa el código manualmente</p>
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

            {{-- Resultados de escaneos --}}
            <template x-for="(r, index) in resultados" :key="index">
                <div class="scan-result mb-2">
                    <div class="card card-round result-card" :class="r.clase">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-shrink-0">
                                    <i :class="r.icono" class="fa-2x"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-1 fw-bold" x-text="r.mensaje"></p>
                                    <template x-if="r.habitacion">
                                        <span class="badge bg-dark me-1">
                                            <i class="fas fa-door-open me-1"></i>
                                            <span x-text="r.habitacion"></span>
                                        </span>
                                    </template>
                                    <template x-if="r.progreso">
                                        <span class="badge bg-primary">
                                            <span x-text="r.progreso.hechos + '/' + r.progreso.total"></span> prendas
                                        </span>
                                    </template>
                                    <template x-if="r.asignacion_id">
                                        <a :href="'/empleado/asignacion/' + r.asignacion_id" class="btn btn-sm btn-outline-primary mt-2 d-block">
                                            <i class="fas fa-eye me-1"></i>Ver habitación
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="resultados.length === 0 && !scanning">
                <div class="text-center text-muted py-4">
                    <i class="fas fa-qrcode fa-3x mb-3 d-block" style="opacity:.3;"></i>
                    <p>Activa la cámara y apunta a un código QR de prenda</p>
                </div>
            </template>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
function globalScanner() {
    return {
        scanning:     false,
        html5QrCode:  null,
        manualCode:   '',
        resultados:   [],
        lastCode:     '',
        lastTime:     0,

        init() {},

        toggleScanner() {
            this.scanning ? this.stopScanner() : this.startScanner();
        },

        startScanner() {
            this.html5QrCode = new Html5Qrcode('qr-reader');
            this.html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    // Evitar escaneos duplicados rápidos (3s cooldown)
                    var now = Date.now();
                    if (decodedText === this.lastCode && (now - this.lastTime) < 3000) return;
                    this.lastCode = decodedText;
                    this.lastTime = now;
                    this.enviarCodigo(decodedText);
                },
                (err) => {}
            ).then(() => {
                this.scanning = true;
            }).catch(err => {
                this.addResult({
                    mensaje: 'No se pudo acceder a la cámara: ' + err,
                    clase: 'danger',
                    icono: 'fas fa-exclamation-triangle text-danger'
                });
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
            if (!codigo || !codigo.trim()) return;
            fetch('{{ route("empleado.escanear.global") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ codigo_qr: codigo.trim() }),
            })
            .then(r => r.json())
            .then(data => {
                var result = {
                    mensaje: data.mensaje || 'Respuesta recibida',
                    habitacion: data.habitacion || null,
                    progreso: data.progreso || null,
                    asignacion_id: data.asignacion_id || null,
                };
                if (data.ok) {
                    if (data.tipo === 'ya_escaneada') {
                        result.clase = 'info';
                        result.icono = 'fas fa-info-circle text-info';
                    } else {
                        result.clase = 'success';
                        result.icono = 'fas fa-check-circle text-success';
                    }
                } else {
                    if (data.tipo === 'extraviada') {
                        result.clase = 'danger';
                        result.icono = 'fas fa-exclamation-triangle text-danger';
                    } else {
                        result.clase = 'warning';
                        result.icono = 'fas fa-exclamation-circle text-warning';
                    }
                }
                this.addResult(result);
            })
            .catch(() => {
                this.addResult({
                    mensaje: 'Error de conexión con el servidor',
                    clase: 'danger',
                    icono: 'fas fa-wifi text-danger'
                });
            });
        },

        addResult(result) {
            this.resultados.unshift(result);
            if (this.resultados.length > 10) this.resultados.pop();
        },
    };
}
</script>
@endpush
