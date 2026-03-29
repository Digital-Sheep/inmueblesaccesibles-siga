<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cat_carpetas_juridicas', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 150)
                ->comment('Nombre visible en el tab, ej: "Documentos de la administradora"');

            $table->string('slug', 100)->unique()
                ->comment('Identificador de path en disco, ej: "docs-administradora". NO modificar si ya hay archivos.');

            $table->text('descripcion')->nullable();

            $table->boolean('activo')->default(true)
                ->comment('Las carpetas inactivas no aparecen en nuevos tabs pero conservan sus archivos');

            $table->unsignedSmallInteger('orden')->default(0)
                ->comment('Orden de aparición de izquierda a derecha en los tabs');

            $table->timestamps();

            // Índices
            $table->index(['activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_carpetas_juridicas');
    }
};
