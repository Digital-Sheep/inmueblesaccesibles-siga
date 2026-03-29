<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatCarpetasJuridicasSeeder extends Seeder
{
    public function run(): void
    {
        $carpetas = [
            [
                'nombre'      => 'Documentos de la administradora',
                'slug'        => 'docs-administradora',
                'descripcion' => 'Documentación recibida de la administradora del crédito.',
                'activo'      => true,
                'orden'       => 1,
            ],
            [
                'nombre'      => 'Documentos de dictamen jurídico',
                'slug'        => 'docs-dictamen-juridico',
                'descripcion' => 'Dictámenes, resoluciones y documentos de análisis jurídico.',
                'activo'      => true,
                'orden'       => 2,
            ],
            [
                'nombre'      => 'Documentos de dictamen registral',
                'slug'        => 'docs-dictamen-registral',
                'descripcion' => 'Certificados del Registro Público de la Propiedad y documentos registrales.',
                'activo'      => true,
                'orden'       => 3,
            ],
            [
                'nombre'      => 'Documentos de contabilidad',
                'slug'        => 'docs-contabilidad',
                'descripcion' => 'Estados de cuenta, fichas de pago y documentación contable.',
                'activo'      => true,
                'orden'       => 4,
            ],
            [
                'nombre'      => 'Documentos del cliente',
                'slug'        => 'docs-cliente',
                'descripcion' => 'Identificaciones, contratos firmados y documentación del cliente.',
                'activo'      => true,
                'orden'       => 5,
            ],
        ];

        foreach ($carpetas as $carpeta) {
            DB::table('cat_carpetas_juridicas')->insertOrIgnore([
                ...$carpeta,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
