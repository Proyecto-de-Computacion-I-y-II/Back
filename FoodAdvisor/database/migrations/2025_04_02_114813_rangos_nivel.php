<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Asegúrate de que el nombre de la tabla ('nivelPiramide') sea correcto
        Schema::table('nivel_piramide', function (Blueprint $table) {
            // Añade la columna 'minimo' después de la columna 'Nombre'
            // Usamos decimal para permitir porcentajes con decimales (ej. 15.5).
            // 5 es el total de dígitos, 2 son los decimales. Ajusta si necesitas más precisión.
            // nullable() permite que la columna esté vacía inicialmente.
            // Puedes usar default(0) si prefieres que tengan un valor por defecto.
            $table->decimal('minimo', 5, 2)->nullable();

            // Añade la columna 'maximo' después de la nueva columna 'minimo'
            $table->decimal('maximo', 5, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Este método permite revertir la migración
        Schema::table('nivelPiramide', function (Blueprint $table) {
            // Elimina las columnas si haces un rollback
            $table->dropColumn(['minimo', 'maximo']);
        });
    }
};
