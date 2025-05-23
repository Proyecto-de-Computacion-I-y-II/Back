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
        Schema::table('usuario', function (Blueprint $table) {
            $table->integer('codigo_verificacion')->nullable()->after('contrasenia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //quitar la columna codigo_verificacion
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropColumn('codigo_verificacion');
        });
    }
};
