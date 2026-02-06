<?php

namespace Database\Seeders;

use App\Models\CatEtapaProcesal;
use Illuminate\Database\Seeder;

class EtapasProcesalesSeeder extends Seeder
{
    public function run(): void
    {
        // MAPEO DE LAS 20 ETAPAS DEL COTIZADOR
        // Formato: [nombre_en_sistema_actual => [fase, porcentaje, orden_cotizacion]]

        $etapasParaCotizacion = [
            // ===== FASE 1: 35% (Etapas 1-9) =====
            'Presentación de Demanda / Apersonamiento' => ['FASE_1', 35.00, 1],
            'Admisión de Demanda' => ['FASE_1', 35.00, 2], // NUEVA
            'Emplazamiento' => ['FASE_1', 35.00, 3],
            'Contestación de Demanda' => ['FASE_1', 35.00, 4],
            'Réplica y Dúplica' => ['FASE_1', 35.00, 5], // NUEVA
            'Ofrecimiento de Pruebas' => ['FASE_1', 35.00, 6],
            'Desahogo de Pruebas' => ['FASE_1', 35.00, 7],
            'Alegatos' => ['FASE_1', 35.00, 8],
            'Sentencia Definitiva' => ['FASE_1', 35.00, 9], // Sentencia del precio principal

            // ===== FASE 2: 20% (Etapas 10-15) =====
            'Liquidación de Sentencia' => ['FASE_2', 20.00, 10], // NUEVA
            'Requerimiento de Pago' => ['FASE_2', 20.00, 11], // NUEVA
            'Embargo de Bienes' => ['FASE_2', 20.00, 12], // NUEVA
            'Avalúos' => ['FASE_2', 20.00, 13],
            'Señalamiento de Remate' => ['FASE_2', 20.00, 14], // NUEVA
            'Remate (Almonedas)' => ['FASE_2', 20.00, 15],

            // ===== FASE 3: 15% (Etapas 16-20) =====
            'Aprobación del Remate' => ['FASE_3', 15.00, 16], // NUEVA
            'Adjudicación' => ['FASE_3', 15.00, 17],
            'Expedición de Testimonio' => ['FASE_3', 15.00, 18], // NUEVA
            'Escrituración' => ['FASE_3', 15.00, 19],
            'Inscripción en Registro Público' => ['FASE_3', 15.00, 20], // NUEVA
        ];

        // ETAPAS ADICIONALES QUE YA TIENES (no aplican para cotización)
        $etapasAdicionales = [
            'Apelación / Amparo' => ['orden' => 81, 'dias' => 30],
            'Ejecución de Sentencia' => ['orden' => 91, 'dias' => 20],
            'Toma de Posesión / Desalojo' => ['orden' => 141, 'dias' => 15],
            'Entrega al Cliente' => ['orden' => 151, 'dias' => 5],
        ];

        // 1. ACTUALIZAR/CREAR ETAPAS PARA COTIZACIÓN
        foreach ($etapasParaCotizacion as $nombre => $config) {
            [$fase, $porcentaje, $ordenCotizacion] = $config;

            CatEtapaProcesal::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'orden' => $ordenCotizacion * 10, // Para mantener espacios
                    'dias_termino_legal' => 15, // Default
                    'tipo_juicio_id' => null, // Genérica
                    'fase_cotizacion' => $fase,
                    'porcentaje_inversion' => $porcentaje,
                    'aplica_para_cotizacion' => true,
                    'activo' => true,
                ]
            );
        }

        // 2. MANTENER ETAPAS ADICIONALES (sin datos de cotización)
        foreach ($etapasAdicionales as $nombre => $config) {
            CatEtapaProcesal::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'orden' => $config['orden'],
                    'dias_termino_legal' => $config['dias'],
                    'tipo_juicio_id' => null,
                    'fase_cotizacion' => null,
                    'porcentaje_inversion' => null,
                    'aplica_para_cotizacion' => false,
                    'activo' => true,
                ]
            );
        }

        $this->command->info('✅ Etapas procesales actualizadas: ' . (count($etapasParaCotizacion) + count($etapasAdicionales)) . ' etapas');
        $this->command->info('📊 Etapas para cotización: ' . count($etapasParaCotizacion));
        $this->command->info('⚖️ Etapas jurídicas adicionales: ' . count($etapasAdicionales));
    }
}
