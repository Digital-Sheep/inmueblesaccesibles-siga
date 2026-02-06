<?php

namespace App\Services;

use App\Models\AprobacionPrecio;
use App\Models\CatEtapaProcesal;
use App\Models\CatTabuladorCosto;
use App\Models\Cotizacion;
use App\Models\Propiedad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CotizadorService
{
    /**
     * Calcular cotización completa de una propiedad
     *
     * @param Propiedad $propiedad
     * @param string $tamano
     * @param int $etapaProcesalId
     * @param float $porcentajeDescuento
     * @param string|null $notas
     * @return Cotizacion
     */
    public function calcular(
        Propiedad $propiedad,
        string $tamano,
        int $etapaProcesalId,
        float $porcentajeDescuento,
        ?string $notas = null
    ): Cotizacion {

        DB::beginTransaction();

        try {
            // 1. Obtener costos del tabulador
            $tabulador = CatTabuladorCosto::getCostosPorTamano($tamano);
            if (!$tabulador) {
                throw new \Exception("No se encontraron costos para el tamaño: {$tamano}");
            }

            // 2. Obtener porcentaje de inversión de la etapa procesal
            $etapaProcesal = CatEtapaProcesal::find($etapaProcesalId);
            if (!$etapaProcesal || !$etapaProcesal->aplica_para_cotizacion) {
                throw new \Exception("Etapa procesal inválida para cotización");
            }

            $porcentajeInversion = $etapaProcesal->porcentaje_inversion;

            // 3. Precio base (normalmente precio_lista)
            $precioBase = $propiedad->precio_lista ?? $propiedad->avaluo_banco;
            if (!$precioBase) {
                throw new \Exception("La propiedad no tiene precio base definido");
            }

            // 4. CALCULAR COSTOS
            $costoRemodelacion = $tabulador->costo_remodelacion;
            $costoLuz = $tabulador->costo_luz;
            $costoAgua = $tabulador->costo_agua;
            $costoPredial = $tabulador->costo_predial;
            $costoGastosJuridicos = $tabulador->costo_gastos_juridicos;

            $totalCostos = $costoRemodelacion + $costoLuz + $costoAgua +
                $costoPredial + $costoGastosJuridicos;

            // 5. CALCULAR INCREMENTO POR INVERSIÓN
            $montoInversion = $precioBase * ($porcentajeInversion / 100);

            // 6. CALCULAR PRECIO SIN REMODELACIÓN
            // Base + Costos sin remodelación + Inversión
            $costosSinRemodelacion = $costoLuz + $costoAgua + $costoPredial + $costoGastosJuridicos;
            $precioSinRemodelacion = $precioBase + $costosSinRemodelacion + $montoInversion;

            // 7. CALCULAR PRECIO VENTA SUGERIDO
            // Base + TODOS los costos + Inversión
            $precioVentaSugerido = $precioBase + $totalCostos + $montoInversion;

            // 8. CALCULAR PRECIO CON DESCUENTO
            $precioVentaConDescuento = $precioVentaSugerido * (1 - ($porcentajeDescuento / 100));

            // 9. CALCULAR PORCENTAJE DE UTILIDAD
            // Utilidad = (Precio con desc - Costo total) / Precio con desc * 100
            $costoTotal = $precioBase + $totalCostos;
            $utilidadConDescuento = $precioVentaConDescuento - $costoTotal;
            $porcentajeUtilidad = ($utilidadConDescuento / $precioVentaConDescuento) * 100;

            // 10. Obtener versión
            $ultimaVersion = Cotizacion::where('propiedad_id', $propiedad->id)
                ->max('version') ?? 0;
            $nuevaVersion = $ultimaVersion + 1;

            // 11. CREAR COTIZACIÓN
            $cotizacion = Cotizacion::create([
                'propiedad_id' => $propiedad->id,
                'version' => $nuevaVersion,
                'activa' => true,
                'precio_base' => $precioBase,
                'tamano_propiedad' => $tamano,
                'etapa_procesal_id' => $etapaProcesalId,

                // Costos desglosados
                'costo_remodelacion' => $costoRemodelacion,
                'costo_luz' => $costoLuz,
                'costo_agua' => $costoAgua,
                'costo_predial' => $costoPredial,
                'costo_gastos_juridicos' => $costoGastosJuridicos,
                'total_costos' => $totalCostos,

                // Inversión
                'porcentaje_inversion' => $porcentajeInversion,
                'monto_inversion' => $montoInversion,

                // Resultados
                'precio_sin_remodelacion' => $precioSinRemodelacion,
                'precio_venta_sugerido' => $precioVentaSugerido,
                'porcentaje_descuento' => $porcentajeDescuento,
                'precio_venta_con_descuento' => $precioVentaConDescuento,
                'porcentaje_utilidad' => round($porcentajeUtilidad, 2),

                'calculada_por_id' => Auth::id(),
                'notas' => $notas,
            ]);

            // 12. Desactivar cotizaciones anteriores
            $cotizacion->desactivarAnteriores();

            // 13. Actualizar propiedad
            $propiedad->update([
                'tamano_propiedad' => $tamano,
                'etapa_procesal_id' => $etapaProcesalId,
                'precio_calculado' => true,
            ]);

            // 14. Sincronizar precios con propiedad
            $cotizacion->sincronizarConPropiedad();

            // 15. Crear aprobaciones pendientes
            $this->crearAprobaciones($cotizacion);

            DB::commit();

            return $cotizacion;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Crear registros de aprobación (Comercial y Contabilidad)
     */
    protected function crearAprobaciones(Cotizacion $cotizacion): void
    {
        $tiposAprobacion = ['COMERCIAL', 'CONTABILIDAD'];

        foreach ($tiposAprobacion as $tipo) {
            AprobacionPrecio::create([
                'propiedad_id' => $cotizacion->propiedad_id,
                'cotizacion_id' => $cotizacion->id,
                'precio_propuesto' => $cotizacion->precio_venta_sugerido,
                'tipo_aprobador' => $tipo,
                'estatus' => 'PENDIENTE',
            ]);
        }
    }

    /**
     * Recalcular cotización con nuevo descuento
     * (sin crear nueva versión)
     */
    public function recalcularDescuento(
        Cotizacion $cotizacion,
        float $nuevoPorcentajeDescuento
    ): Cotizacion {

        // Recalcular precio con descuento
        $precioVentaConDescuento = $cotizacion->precio_venta_sugerido *
            (1 - ($nuevoPorcentajeDescuento / 100));

        // Recalcular utilidad
        $costoTotal = $cotizacion->precio_base + $cotizacion->total_costos;
        $utilidadConDescuento = $precioVentaConDescuento - $costoTotal;
        $porcentajeUtilidad = ($utilidadConDescuento / $precioVentaConDescuento) * 100;

        // Actualizar cotización
        $cotizacion->update([
            'porcentaje_descuento' => $nuevoPorcentajeDescuento,
            'precio_venta_con_descuento' => $precioVentaConDescuento,
            'porcentaje_utilidad' => round($porcentajeUtilidad, 2),
        ]);

        // Sincronizar con propiedad
        $cotizacion->sincronizarConPropiedad();

        return $cotizacion->fresh();
    }

    /**
     * Obtener desglose completo de la cotización
     */
    public function getDesglose(Cotizacion $cotizacion): array
    {
        return [
            'precio_base' => $cotizacion->precio_base,
            'costos' => [
                'remodelacion' => $cotizacion->costo_remodelacion,
                'luz' => $cotizacion->costo_luz,
                'agua' => $cotizacion->costo_agua,
                'predial' => $cotizacion->costo_predial,
                'gastos_juridicos' => $cotizacion->costo_gastos_juridicos,
                'total' => $cotizacion->total_costos,
            ],
            'inversion' => [
                'porcentaje' => $cotizacion->porcentaje_inversion,
                'monto' => $cotizacion->monto_inversion,
            ],
            'precios' => [
                'sin_remodelacion' => $cotizacion->precio_sin_remodelacion,
                'venta_sugerido' => $cotizacion->precio_venta_sugerido,
                'descuento_porcentaje' => $cotizacion->porcentaje_descuento,
                'con_descuento' => $cotizacion->precio_venta_con_descuento,
                'utilidad_porcentaje' => $cotizacion->porcentaje_utilidad,
            ],
        ];
    }
}
