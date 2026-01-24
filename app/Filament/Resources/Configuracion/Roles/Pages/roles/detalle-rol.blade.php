<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $rol->name }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Creado: {{ $rol->created_at->format('d/M/Y H:i') }} •
                Actualizado: {{ $rol->updated_at->diffForHumans() }}
            </p>
        </div>

        <div class="flex gap-3">
            <x-filament::badge color="info" icon="heroicon-o-user-group">
                {{ $rol->users()->count() }} Usuarios
            </x-filament::badge>

            <x-filament::badge
                :color="$rol->permissions()->count() === 0 ? 'danger' : 'success'"
                icon="heroicon-o-key"
            >
                {{ $rol->permissions()->count() }} Permisos
            </x-filament::badge>
        </div>
    </div>

    {{-- Usuarios con este rol --}}
    @if($rol->users()->count() > 0)
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <x-heroicon-o-user-group class="w-5 h-5" />
                Usuarios con este rol
            </h4>

            <div class="grid grid-cols-2 gap-2">
                @foreach($rol->users as $user)
                    <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <x-heroicon-o-user class="w-4 h-4 text-gray-400" />
                        {{ $user->name }}
                        @if($user->sucursal)
                            <span class="text-xs text-gray-400">({{ $user->sucursal->nombre }})</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Permisos por categoría --}}
    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <x-heroicon-o-key class="w-5 h-5" />
            Permisos Asignados
        </h4>

        @if($rol->permissions()->count() === 0)
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <x-heroicon-o-shield-exclamation class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                <p>Este rol no tiene permisos asignados</p>
            </div>
        @else
            @php
                $categorias = [
                    'Sistema' => ['ver_panel_principal', 'ver_actividad_sistema'],
                    'Navegación' => 'menu_',
                    'Dashboards' => ['dashboard_', 'reportes_'],
                    'Prospectos' => 'prospectos_',
                    'Clientes' => 'clientes_',
                    'Propiedades' => 'propiedades_',
                    'Ventas' => 'ventas_',
                    'Dictámenes' => 'dictamenes_',
                    'Expedientes' => 'expedientes_',
                    'Juicios' => 'juicios_',
                    'Pagos' => 'pagos_',
                    'Compras' => 'compras_',
                    'Contratos' => 'contratos_',
                    'Formalización' => 'formalizacion_',
                    'Cambios' => 'cambios_',
                    'Devoluciones' => 'devoluciones_',
                    'Usuarios' => 'usuarios_',
                    'Roles' => 'roles_',
                    'Catálogos' => 'catalogos_',
                    'Descuentos' => 'autorizar_descuentos_',
                ];

                $permisosPorCategoria = [];

                foreach ($rol->permissions as $permission) {
                    $categorizado = false;

                    foreach ($categorias as $categoria => $prefijos) {
                        $prefijos = is_array($prefijos) ? $prefijos : [$prefijos];

                        foreach ($prefijos as $prefijo) {
                            if (str_starts_with($permission->name, $prefijo)) {
                                $permisosPorCategoria[$categoria][] = $permission->name;
                                $categorizado = true;
                                break 2;
                            }
                        }
                    }

                    if (!$categorizado) {
                        $permisosPorCategoria['Otros'][] = $permission->name;
                    }
                }
            @endphp

            <div class="space-y-4">
                @foreach($permisosPorCategoria as $categoria => $permisos)
                    <div>
                        <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-2">
                            <x-heroicon-o-folder class="w-4 h-4 text-primary-500" />
                            {{ $categoria }} ({{ count($permisos) }})
                        </h5>

                        <div class="grid grid-cols-3 gap-2 ml-6">
                            @foreach($permisos as $permiso)
                                <div class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-check-circle class="w-3 h-3 text-green-500" />
                                    {{ str_replace('_', ' ', $permiso) }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
