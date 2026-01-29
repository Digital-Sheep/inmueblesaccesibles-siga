<?php

namespace App\Services;

use App\Models\Cartera;
use App\Models\CatEstado;
use App\Models\CatMunicipio;
use App\Models\Propiedad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportadorCarteras
{
    protected int $procesados = 0;
    protected int $duplicados = 0;
    protected array $errores = [];

    /**
     * Importa propiedades desde un CSV de una cartera
     */
    public function importar(Cartera $cartera): array
    {
        // Validar archivo
        if (!Storage::exists($cartera->archivo_path)) {
            throw new \Exception('Archivo no encontrado');
        }

        $path = Storage::path($cartera->archivo_path);
        $file = fopen($path, 'r');

        $header = fgetcsv($file);

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($file)) !== false) {
                $this->procesarFila($row, $cartera);
            }

            $cartera->update(['estatus' => 'PROCESADA']);

            DB::commit();
            fclose($file);

            return [
                'success' => true,
                'procesados' => $this->procesados,
                'duplicados' => $this->duplicados,
                'errores' => $this->errores,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            if (is_resource($file)) {
                fclose($file);
            }

            throw $e;
        }
    }

    /**
     * Procesa una fila del CSV
     */
    protected function procesarFila(array $row, Cartera $cartera): void
    {
        // 0: Codigo cartera, 1: Crédito, 2: Estado, 3: Municipio, 4: Fraccionamiento
        // 5: Dirección, 6: Segunda dir, 7: Número exterior, 8: Número interior, 9: CP, 10: Etapa Jud, 11: 2da Etapa, 12: Fecha
        // 13: Tipo Viv, 14: M2 Const, 15: Tipo Inm, 16: Avalúo, 17: Precio Lista, 18: Cofinavit, 19: Nombre Acreditado

        // Validar fila mínima
        if (count($row) < 2 || empty($row[1])) {
            return;
        }

        // Expandir array a 20 columnas
        $row = array_pad($row, 20, '');

        // Convertir encoding
        $row = array_map(function ($text) {
            return mb_convert_encoding(
                $text,
                'UTF-8',
                mb_detect_encoding($text, 'UTF-8, ISO-8859-1, Windows-1252', true) ?: 'Windows-1252'
            );
        }, $row);

        $numeroCredito = trim($row[1]);

        // Validar duplicados
        if ($this->existePropiedad($numeroCredito, $cartera->id)) {
            $this->duplicados++;
            return;
        }

        $estado = CatEstado::where('nombre', 'LIKE', '%' . trim($row[2]) . '%')->first();
        $municipio = CatMunicipio::where('nombre', 'LIKE', '%' . trim($row[3]) . '%')->first();

        $estadoBorrador = trim($row[2]);
        $municipioBorrador = trim($row[3]);

        $fraccionamiento = trim($row[4]);
        $calle = trim($row[5]);
        $direccion = trim($row[5]) . ' ' . trim($row[7]) . ' ' . trim($row[8]) . ' ' . trim($row[6]);
        $numeroExterior = trim($row[7]) ?: null;
        $numeroInterior = trim($row[8]) ?: null;
        $codigoPostal = is_numeric(trim($row[9])) ? trim($row[9]) : null;

        $etapaJudicial = trim($row[10]);
        $segundaEtapa = trim($row[11]);
        $fechaCorte = $this->parsearFecha($row[12]);

        $tipoVivienda = trim($row[13]);
        $m2Construccion = $this->limpiarNumero($row[14]);
        $tipoInmueble = trim($row[15]);

        $avaluo = $this->limpiarNumero($row[16]);
        $precio = $this->limpiarNumero($row[17]);
        $cofinavit = $this->limpiarNumero($row[18]);

        $nombreAcreditado = !empty($row[19]) ? trim($row[19]) : null;

        // Crear propiedad
        Propiedad::create([
            'cartera_id' => $cartera->id,
            'administradora_id' => $cartera->administradora_id,
            'sucursal_id' => $cartera->sucursal_id,
            'numero_credito' => $numeroCredito,

            // Ubicación
            'estado_id' => $estado?->id,
            'municipio_id' => $municipio?->id,
            'estado_borrador' => $estadoBorrador,
            'municipio_borrador' => $municipioBorrador,

            'fraccionamiento' => $fraccionamiento,
            'direccion_completa' => $direccion,
            'calle' => $calle,
            'numero_exterior' => $numeroExterior,
            'numero_interior' => $numeroInterior,
            'codigo_postal' => $codigoPostal,

            // Datos Legales
            'etapa_judicial_reportada' => $etapaJudicial . ' - ' . $segundaEtapa,
            'fecha_corte_judicial' => $fechaCorte,

            // Características
            'tipo_vivienda' => $tipoVivienda,
            'construccion_m2' => $m2Construccion,
            'tipo_inmueble' => $tipoInmueble,

            // Valores
            'avaluo_banco' => $avaluo,
            'precio_lista' => $precio,
            'cofinavit_monto' => $cofinavit,

            // Dato sensible
            'nombre_acreditado' => $nombreAcreditado,

            'estatus_comercial' => 'BORRADOR',
            'created_by' => Auth::id(),
        ]);

        $this->procesados++;
    }

    /**
     * Verifica si una propiedad ya existe
     */
    protected function existePropiedad(string $numeroCredito, int $carteraId): bool
    {
        return Propiedad::where('numero_credito', $numeroCredito)
            ->where('cartera_id', $carteraId)
            ->exists();
    }

    /**
     * Limpia números (quita $, comas, etc)
     */
    protected function limpiarNumero(string $valor): float
    {
        $limpio = preg_replace('/[^0-9.]/', '', $valor);
        return is_numeric($limpio) ? (float) $limpio : 0;
    }

    /**
     * Parsea fechas en formato Excel
     */
    protected function parsearFecha(?string $fecha): ?string
    {
        if (empty($fecha)) {
            return null;
        }

        try {
            return date('Y-m-d', strtotime(str_replace('/', '-', $fecha)));
        } catch (\Exception $e) {
            return null;
        }
    }
}
