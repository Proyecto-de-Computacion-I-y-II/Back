<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('Usuario', function (Blueprint $table) {
            // Modifica el tipo de la columna a VARCHAR
            $table->string('rol')->change();
        });

        // Añade la restricción CHECK separadamente
        DB::statement('ALTER TABLE "Usuario" ADD CONSTRAINT rol_check CHECK (rol IN (\'admin\', \'comprador\'))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Elimina la restricción CHECK
        DB::statement('ALTER TABLE "Usuario" DROP CONSTRAINT IF EXISTS rol_check');

        Schema::table('Usuario', function (Blueprint $table) {
            // Revertir el tipo de la columna a string
            $table->string('rol')->change();
        });
    }
};
