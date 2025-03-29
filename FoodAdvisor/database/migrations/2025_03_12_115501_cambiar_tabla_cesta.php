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
        Schema::table('Cesta_Productos', function (Blueprint $table) {
            $table->boolean('recomendado')->default(false);
            $table->boolean('comprado')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Cesta_Productos', function (Blueprint $table) {
            $table->dropColumn(['recomendado', 'comprado']);
        });
    }
};
