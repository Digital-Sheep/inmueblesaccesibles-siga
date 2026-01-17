<?php

namespace App\Http\Controllers;

use App\Models\ProcesoVenta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    public function generarApartado(ProcesoVenta $procesoVenta)
    {
        // Cargamos los datos necesarios
        $procesoVenta->load(['propiedad', 'interesado', 'vendedor']);

        // Generamos el PDF usando una vista Blade
        $pdf = Pdf::loadView('pdf.contratos.apartado', [
            'proceso' => $procesoVenta,
            'cliente' => $procesoVenta->interesado,
            'propiedad' => $procesoVenta->propiedad,
        ]);

        // Descargamos el archivo con un nombre claro
        return $pdf->stream("Contrato_Apartado_{$procesoVenta->folio_apartado}.pdf");
    }
}
