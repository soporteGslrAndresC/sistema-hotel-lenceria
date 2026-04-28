<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use App\Models\Lenceria;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{
    /**
     * Devuelve el SVG del QR de una prenda (inline en views).
     */
    public function svg(Lenceria $lenceria)
    {
        $svg = QrCode::format('svg')->size(180)->margin(1)->generate($lenceria->codigo_qr);
        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }

    /**
     * Vista imprimible con todos los QRs de prendas.
     * Filtros: ?habitacion_id=&tipo=
     */
    public function imprimir(Request $request)
    {
        $query = Lenceria::with('habitacion')->orderBy('habitacion_id')->orderBy('tipo');

        if ($id = $request->get('habitacion_id')) {
            $query->where('habitacion_id', $id);
        }
        if ($tipo = $request->get('tipo')) {
            $query->where('tipo', $tipo);
        }

        $lencerias = $query->get()->map(function ($l) {
            return [
                'codigo_qr' => $l->codigo_qr,
                'tipo' => $l->tipo,
                'habitacion' => optional($l->habitacion)->codigo,
                'svg' => QrCode::format('svg')->size(160)->margin(1)->generate($l->codigo_qr),
            ];
        });

        $habitaciones = Habitacion::orderBy('codigo')->get();

        return view('admin.lencerias.imprimir', compact('lencerias', 'habitaciones'));
    }
}
