<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental
            $table->string('nombre')->nullable(); // Columna 'nombre' de tipo string y debe ser única
            $table->string('valor')->nullable(); // Columna 'valor' de tipo string, permitiendo valores nulos
            $table->timestamps(); // Columnas 'created_at' y 'updated_at'
        });

// Insertar configuraciones predeterminadas
DB::table('configuracion')->insert([
    // Productos por página
    [
        'nombre' => 'productos_pagina',
        'valor' => '16',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'nombre' => 'productos_pagina',
        'valor' => '20',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'nombre' => 'productos_pagina',
        'valor' => '24',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'nombre' => 'productos_pagina',
        'valor' => '30',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'nombre' => 'productos_pagina',
        'valor' => '36',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'nombre' => 'productos_pagina',
        'valor' => '48',
        'created_at' => now(),
        'updated_at' => now()
    ],

    // Colores de fondo
    [
        'nombre' => 'color_fondo',
        'valor' => '#FFFFFF',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'nombre' => 'color_fondo',
        'valor' => '#F8F9FA',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'nombre' => 'color_fondo',
        'valor' => '#E9ECEF',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'nombre' => 'color_fondo',
        'valor' => '#DEE2E6',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'nombre' => 'color_fondo',
        'valor' => '#CED4DA',
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'nombre' => 'color_fondo',
        'valor' => '#ADB5BD',
        'created_at' => now(),
        'updated_at' => now()
    ]
]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
