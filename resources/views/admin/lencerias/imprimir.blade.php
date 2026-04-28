<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>QR de prendas — Imprimir</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 12px; }
        .toolbar { margin-bottom: 12px; display: flex; gap: 8px; align-items: center; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
        .card { border: 1px solid #ddd; border-radius: 6px; padding: 8px; text-align: center; page-break-inside: avoid; }
        .card svg { width: 100%; height: auto; max-width: 160px; }
        .meta { font-size: 11px; margin-top: 4px; }
        .meta .codigo { font-family: monospace; word-break: break-all; }
        .meta .tipo { font-weight: bold; text-transform: uppercase; }
        .meta .hab { color: #666; }
        @media print { .toolbar { display: none; } .grid { grid-template-columns: repeat(4, 1fr); } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">🖨 Imprimir</button>
        <span>{{ $lencerias->count() }} etiquetas</span>
        <a href="{{ url()->previous() }}">← Volver</a>
    </div>
    <div class="grid">
        @foreach($lencerias as $l)
            <div class="card">
                {!! $l['svg'] !!}
                <div class="meta">
                    <div class="tipo">{{ $l['tipo'] }}</div>
                    <div class="hab">{{ $l['habitacion'] }}</div>
                    <div class="codigo">{{ $l['codigo_qr'] }}</div>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
