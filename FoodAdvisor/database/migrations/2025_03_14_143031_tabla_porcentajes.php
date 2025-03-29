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
        Schema::table('cesta_compra', function (Blueprint $table) {
            $table->dropColumn([
                'base_piramide',
                'hidratos_carbono',
                'frutas_verduras',
                'carnes_rojas',
                'azucar_grasas_sal',
                'lacteos_proteinas',
            ]);
        });

        Schema::create('porcentajes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ID_cesta');
            $table->unsignedBigInteger('idNivel');
            $table->decimal('porcentaje', 5, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('ID_cesta')->references('ID_cesta')->on('cesta_compra');
            $table->foreign('idNivel')->references('idNivel')->on('nivel_piramide');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cesta_compra', function (Blueprint $table) {
            $table->float('base_piramide')->nullable()->default(null);
            $table->float('hidratos_carbono')->nullable()->default(null);
            $table->float('frutas_verduras')->nullable()->default(null);
            $table->float('carnes_rojas')->nullable()->default(null);
            $table->float('azucar_grasas_sal')->nullable()->default(null);
            $table->float('lacteos_proteinas')->nullable()->default(null);
        });

        Schema::dropIfExists('porcentajes');
    }
};
