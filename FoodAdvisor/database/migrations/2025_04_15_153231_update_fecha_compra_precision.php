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
            $table->timestamp('fecha_compra')->change(); // or ->dateTime('fecha_compra', 3)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cesta_compra', function (Blueprint $table) {
            // Revert column back to DATE only (no time)
            $table->date('fecha_compra')->change();
        });
    }
};
