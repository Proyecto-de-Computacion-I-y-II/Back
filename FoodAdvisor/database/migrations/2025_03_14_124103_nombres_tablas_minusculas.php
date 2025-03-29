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
        Schema::rename('Usuario', 'usuario');
        Schema::rename('Cesta_compra', 'cesta_compra');
        Schema::rename('Supermercado', 'supermercado');
        Schema::rename('NivelPiramide', 'nivel_piramide');
        Schema::rename('Productos_temp', 'productos_temp');
        Schema::rename('Categoria', 'categoria');
        Schema::rename('Subcategoria', 'subcategoria');
        Schema::rename('Subcategoria2', 'subcategoria2');
        Schema::rename('Producto', 'producto');
        Schema::rename('Cesta_Productos', 'cesta_productos');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('usuario', 'Usuario');
        Schema::rename('cesta_compra', 'Cesta_compra');
        Schema::rename('supermercado', 'Supermercado');
        Schema::rename('nivel_piramide', 'NivelPiramide');
        Schema::rename('productos_temp', 'Productos_temp');
        Schema::rename('categoria', 'Categoria');
        Schema::rename('subcategoria', 'Subcategoria');
        Schema::rename('subcategoria2', 'Subcategoria2');
        Schema::rename('producto', 'Producto');
        Schema::rename('cesta_productos', 'Cesta_Productos');
    }
};
