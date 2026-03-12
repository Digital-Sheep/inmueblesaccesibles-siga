<?php

namespace App\Console\Commands;

use App\Models\Propiedad;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodificarPropiedades extends Command
{
    protected $signature = 'propiedades:geocodificar
                            {--limit=0 : Máximo de propiedades a procesar (0 = todas)}
                            {--dry-run : Solo muestra qué haría sin modificar nada}
                            {--force : Regeocod ifica incluso las que ya tienen coordenadas}';

    protected $description = 'Geocodifica propiedades: primero desde el link de Google Maps, luego con Nominatim';

    private const DELAY_SEGUNDOS = 1;
    private const USER_AGENT = 'SIGA-InmueblesAccesibles/1.0 (contacto@digitalsheep.mx)';

    private int $desdLink      = 0;
    private int $desdNominatim = 0;

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force  = $this->option('force');
        $limit  = (int) $this->option('limit');

        $query = Propiedad::query()
            ->whereNotNull('direccion_completa')
            ->when(! $force, fn($q) => $q->whereNull('latitud'))
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $propiedades = $query->get();
        $total       = $propiedades->count();

        if ($total === 0) {
            $this->info('No hay propiedades pendientes de geocodificar.');
            return self::SUCCESS;
        }

        $this->info("Propiedades a procesar: {$total}");

        if ($dryRun) {
            $this->warn('Modo DRY-RUN activado — no se guardarán cambios.');
        }

        $exitosos = 0;
        $fallidos = 0;
        $omitidos = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($propiedades as $propiedad) {

            // ESTRATEGIA 1: Extraer del link de Google Maps
            $coordenadas = null;
            $fuente      = null;

            if ($propiedad->google_maps_link) {
                $coordenadas = $this->extraerCoordenadaDesdeLink($propiedad->google_maps_link);
                if ($coordenadas) {
                    $fuente = 'link';
                }
            }

            // ESTRATEGIA 2: Geocodificar con Nominatim (fallback)
            if (! $coordenadas) {
                $direccion = $this->construirDireccion($propiedad);

                if (! $direccion) {
                    if ($dryRun) {
                        $this->line("\n  [SKIP] #{$propiedad->id} → Sin datos suficientes");
                    }
                    $omitidos++;
                    $bar->advance();
                    continue;
                }

                if ($dryRun) {
                    $this->line("\n  [DRY] #{$propiedad->id} → Nominatim: \"{$direccion}\"");
                    $exitosos++;
                    $bar->advance();
                    continue;
                }

                $coordenadas = $this->geocodificarNominatim($direccion);
                if ($coordenadas) {
                    $fuente = 'nominatim';
                }

                sleep(self::DELAY_SEGUNDOS);

            } else {
                if ($dryRun) {
                    $this->line("\n  [DRY] #{$propiedad->id} → Desde link: lat={$coordenadas['lat']}, lng={$coordenadas['lng']}");
                    $exitosos++;
                    $bar->advance();
                    continue;
                }
            }

            // Guardar
            if ($coordenadas) {
                $propiedad->update([
                    'latitud'  => $coordenadas['lat'],
                    'longitud' => $coordenadas['lng'],
                ]);

                Log::info('[Geocodificador] Éxito', [
                    'propiedad_id'   => $propiedad->id,
                    'numero_credito' => $propiedad->numero_credito,
                    'fuente'         => $fuente,
                    'lat'            => $coordenadas['lat'],
                    'lng'            => $coordenadas['lng'],
                ]);

                $exitosos++;
                $fuente === 'link' ? $this->desdLink++ : $this->desdNominatim++;

            } else {
                Log::warning('[Geocodificador] Sin resultado', [
                    'propiedad_id'   => $propiedad->id,
                    'numero_credito' => $propiedad->numero_credito,
                ]);
                $fallidos++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Estado', 'Cantidad'],
            [
                [$dryRun ? '🔍 Procesarían'     : '✅ Geocodificadas',    $exitosos],
                ['  📍 Desde link',                                         $dryRun ? '?' : $this->desdLink],
                ['  🌐 Nominatim',                                          $dryRun ? '?' : $this->desdNominatim],
                ['❌ Sin resultado',                                         $fallidos],
                ['⏭️  Sin datos suficientes',                                $omitidos],
                ['📊 Total',                                                 $total],
            ]
        );

        return self::SUCCESS;
    }

    // ================================================================
    // EXTRACCIÓN DESDE LINK DE GOOGLE MAPS
    // ================================================================

    private function extraerCoordenadaDesdeLink(string $link): ?array
    {
        $link = trim($link);

        // Formato @lat,lng
        if (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*)/', $link, $m)) {
            return $this->validarCoordenadas((float) $m[1], (float) $m[2]);
        }

        // Formato ?q=lat,lng
        if (preg_match('/[?&]q=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $link, $m)) {
            return $this->validarCoordenadas((float) $m[1], (float) $m[2]);
        }

        // Formato ?ll=lat,lng
        if (preg_match('/[?&]ll=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $link, $m)) {
            return $this->validarCoordenadas((float) $m[1], (float) $m[2]);
        }

        // Link corto (goo.gl / maps.app.goo.gl)
        if (str_contains($link, 'goo.gl') || str_contains($link, 'maps.app')) {
            return $this->resolverLinkCorto($link);
        }

        return null;
    }

    private function validarCoordenadas(float $lat, float $lng): ?array
    {
        // Rango plausible para México
        if ($lat < 14 || $lat > 33 || $lng < -118 || $lng > -86) {
            return null;
        }
        return ['lat' => $lat, 'lng' => $lng];
    }

    private function resolverLinkCorto(string $link): ?array
    {
        try {
            $response = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->withOptions(['allow_redirects' => ['max' => 10, 'track_redirects' => true]])
                ->get($link);

            $urlFinal = (string) $response->effectiveUri();

            if (! $urlFinal || $urlFinal === $link) {
                return null;
            }

            if (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*)/', $urlFinal, $m)) {
                return $this->validarCoordenadas((float) $m[1], (float) $m[2]);
            }

            if (preg_match('/[?&]q=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $urlFinal, $m)) {
                return $this->validarCoordenadas((float) $m[1], (float) $m[2]);
            }

            return null;

        } catch (\Throwable $e) {
            Log::warning('[Geocodificador] Error resolviendo link corto', [
                'link'  => $link,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    // ================================================================
    // GEOCODIFICACIÓN CON NOMINATIM (fallback)
    // ================================================================

    private function construirDireccion(Propiedad $propiedad): ?string
    {
        // Estrategia 1: Campos estructurados
        if ($propiedad->calle) {
            $partes = [trim("{$propiedad->calle} {$propiedad->numero_exterior}")];
            if ($propiedad->colonia)            $partes[] = $propiedad->colonia;
            if ($propiedad->municipio?->nombre) $partes[] = $propiedad->municipio->nombre;
            if ($propiedad->estado?->nombre)    $partes[] = $propiedad->estado->nombre;
            $partes[] = 'México';
            return implode(', ', array_filter($partes));
        }

        // Estrategia 2: Municipio/estado resueltos
        if ($propiedad->municipio?->nombre && $propiedad->estado?->nombre) {
            $partes = array_filter([
                $propiedad->colonia,
                $propiedad->municipio->nombre,
                $propiedad->estado->nombre,
                'México',
            ]);
            return implode(', ', $partes);
        }

        // Estrategia 3: Dirección completa limpia
        if ($propiedad->direccion_completa) {
            $limpia = $this->limpiarDireccion($propiedad->direccion_completa);
            return $limpia ? "{$limpia}, México" : null;
        }

        return null;
    }

    private function limpiarDireccion(string $dir): ?string
    {
        $limpia = preg_replace('/\b\d{5,}\b/', '', $dir);
        $limpia = preg_replace('/\b[A-Z]{4,6}\b/', '', $limpia);
        $limpia = preg_replace('/\s+/', ' ', $limpia);
        $limpia = trim($limpia);
        return strlen($limpia) >= 10 ? $limpia : null;
    }

    private function geocodificarNominatim(string $direccion): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept'     => 'application/json',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q'            => $direccion,
                'format'       => 'json',
                'limit'        => 1,
                'countrycodes' => 'mx',
            ]);

            if (! $response->ok()) return null;

            $resultados = $response->json();
            if (empty($resultados)) return null;

            return $this->validarCoordenadas(
                (float) $resultados[0]['lat'],
                (float) $resultados[0]['lon'],
            );

        } catch (\Throwable $e) {
            Log::error('[Geocodificador] Error Nominatim', [
                'direccion' => $direccion,
                'error'     => $e->getMessage(),
            ]);
            return null;
        }
    }
}
