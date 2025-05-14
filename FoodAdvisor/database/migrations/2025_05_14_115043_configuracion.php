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
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental
            $table->string('nombre')->unique(); // Columna 'nombre' de tipo string y debe ser única
            $table->string('valor')->nullable(); // Columna 'valor' de tipo string, permitiendo valores nulos
            $table->timestamps(); // Columnas 'created_at' y 'updated_at'
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};