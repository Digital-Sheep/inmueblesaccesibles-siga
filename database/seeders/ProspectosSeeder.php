<?php

namespace Database\Seeders;

use App\Models\Prospecto;
use App\Models\User;
use App\Models\CatSucursal;
use App\Models\Interaccion;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProspectosSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👥 Generando Prospectos y Actividad para Asesores...');

        $faker = Faker::create('es_MX'); // Nombres latinos reales

        // 1. Obtenemos todas las sucursales activas
        $sucursales = CatSucursal::all();

        foreach ($sucursales as $sucursal) {

            // 2. Buscamos asesores SOLO de esta sucursal (para mantener integridad de datos)
            // Usamos el Scope de Spatie o filtramos por rol si usas tu propio método
            $asesores = User::role('SVT_Asesor')
                ->where('sucursal_id', $sucursal->id)
                ->get();

            if ($asesores->isEmpty()) {
                continue; // Si no hay asesores en esta sucursal, saltamos
            }

            foreach ($asesores as $asesor) {
                // Generamos entre 5 y 10 prospectos por cada asesor
                $cantidad = rand(5, 10);

                for ($i = 0; $i < $cantidad; $i++) {

                    // Definimos un estatus aleatorio
                    $estatusPosibles = ['NUEVO', 'CONTACTADO', 'CONTACTADO', 'CITA', 'DESCARTADO'];
                    $estatus = $estatusPosibles[array_rand($estatusPosibles)];

                    // Definimos origen
                    $origen = $faker->randomElement(['FACEBOOK', 'WEB', 'REFERIDO', 'WALK_IN', 'CALL']);

                    // Crear el Prospecto
                    $prospecto = Prospecto::create([
                        'nombre_completo' => $faker->name,
                        'email' => $faker->unique()->safeEmail,
                        'celular' => $faker->phoneNumber, // Formato variable
                        'origen' => $origen,
                        'estatus' => $estatus,
                        'motivo_descarte' => ($estatus === 'DESCARTADO') ? 'Ya compró con la competencia' : null,
                        'sucursal_id' => $sucursal->id,
                        'usuario_responsable_id' => $asesor->id,
                        'created_at' => $faker->dateTimeBetween('-1 month', 'now'),
                    ]);

                    // 3. GENERAR INTERACCIONES (TIMELINE)
                    // Si el prospecto no es NUEVO, debe tener historia
                    if ($estatus !== 'NUEVO') {
                        $this->crearHistorialFalso($prospecto, $faker, $asesor->id);
                    }
                }
            }
        }

        $this->command->info('✅ Prospectos generados exitosamente.');
    }

    /**
     * Genera interacciones fake para poblar el Timeline
     */
    private function crearHistorialFalso($prospecto, $faker, $userId)
    {
        // 1. Siempre hay un primer contacto
        Interaccion::create([
            'entidad_type' => Prospecto::class,
            'entidad_id' => $prospecto->id,
            'titulo' => 'Primer contacto',
            'tipo' => 'LLAMADA',
            'estatus' => 'COMPLETADA',
            'resultado' => 'CONTACTADO',
            'comentario' => 'El cliente pide informes sobre casas de 2 recámaras.',
            'fecha_programada' => $prospecto->created_at,
            'fecha_realizada' => $prospecto->created_at->addHour(), // 1 hora después de crearse
            'usuario_id' => $userId,
        ]);

        // 2. Si tiene cita o está avanzado, agregamos más cosas
        if (in_array($prospecto->estatus, ['CITA', 'APARTADO', 'CLIENTE'])) {
            Interaccion::create([
                'entidad_type' => Prospecto::class,
                'entidad_id' => $prospecto->id,
                'titulo' => 'Envío de ficha técnica',
                'tipo' => 'WHATSAPP',
                'estatus' => 'COMPLETADA',
                'resultado' => 'CONTACTADO',
                'comentario' => 'Se envió PDF con opciones en zona norte.',
                'fecha_programada' => $prospecto->created_at->addDay(),
                'fecha_realizada' => $prospecto->created_at->addDay(),
                'usuario_id' => $userId,
            ]);
        }

        // 3. Si es CITA, agendamos la cita
        if ($prospecto->estatus === 'CITA') {
            Interaccion::create([
                'entidad_type' => Prospecto::class,
                'entidad_id' => $prospecto->id,
                'titulo' => 'Visita a Desarrollo',
                'tipo' => 'VISITA_PROPIEDAD',
                'estatus' => 'PENDIENTE', // <--- Para que salga en la Agenda futura
                'fecha_programada' => now()->addDays(rand(1, 5)), // Cita en el futuro
                'usuario_id' => $userId,
            ]);
        }
    }
}
