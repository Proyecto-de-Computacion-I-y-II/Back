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
        Schema::table('Cesta_compra', function (Blueprint $table) {
            $table->float('base_piramide', 3)->nullable();
            $table->float('hidratos_carbono', 3)->nullable();
            $table->float('frutas_verduras', 3)->nullable();
            $table->float('carnes_rojas', 3)->nullable();
            $table->float('azucar_grasas_sal', 3)->nullable();
            $table->float('lacteos_proteinas', 3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Cesta_compra', function (Blueprint $table) {
            $table->dropColumn([
                'base_piramide',
                'hidratos_carbono',
                'frutas_verduras',
                'carnes_rojas',
                'azucar_grasas_sal',
                'lacteos_proteinas',
            ]);
        });
    }
};
