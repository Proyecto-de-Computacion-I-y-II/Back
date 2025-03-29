<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('Usuario', function (Blueprint $table) {
            $table->id('ID_user');
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('correo')->unique();
            $table->string('contrasenia');
            $table->string('rol');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('Cesta_compra', function (Blueprint $table) {
            $table->id('ID_cesta');
            $table->foreignId('ID_user')->constrained('Usuario', 'ID_user')->onDelete('cascade');
            $table->date('fecha_compra');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('Supermercado', function (Blueprint $table) {
            $table->id('idSuper');
            $table->string('nombre_supermercado');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('NivelPiramide', function (Blueprint $table) {
            $table->id('idNivel');
            $table->string('Nombre');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('Productos_temp', function (Blueprint $table) {
            $table->id('idTemp');
            $table->string('producto');
            for ($i = 1; $i <= 12; $i++) {
                $table->boolean(date('F', mktime(0, 0, 0, $i, 10)))->default(false);
            }
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('Categoria', function (Blueprint $table) {
            $table->id('idCat');
            $table->string('nombre_categoria');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('Subcategoria', function (Blueprint $table) {
            $table->id('ID_sub');
            $table->foreignId('ID_cat')->constrained('Categoria', 'idCat')->onDelete('cascade');
            $table->string('nombre_subcategoria');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('Subcategoria2', function (Blueprint $table) {
            $table->id('ID_sub2');
            $table->foreignId('ID_sub')->constrained('Subcategoria', 'ID_sub')->onDelete('cascade');
            $table->string('nombre_subsubcategoria');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('Producto', function (Blueprint $table) {
            $table->id('ID_prod');
            $table->foreignId('ID_sub2')->constrained('Subcategoria2', 'ID_sub2')->onDelete('cascade');
            $table->foreignId('idSuper')->constrained('Supermercado', 'idSuper')->onDelete('cascade');
            $table->foreignId('idNivel')->constrained('NivelPiramide', 'idNivel')->onDelete('cascade');
            $table->foreignId('idTemp')->constrained('Productos_temp', 'idTemp')->onDelete('cascade');
            $table->string('nombre');
            $table->decimal('precio', 8, 2);
            $table->string('href')->nullable();
            $table->string('imagen')->nullable();
            $table->decimal('kg', 8, 2)->nullable();
            $table->decimal('l', 8, 2)->nullable();
            $table->integer('ud')->nullable();
            $table->decimal('grasas', 8, 2)->nullable();
            $table->decimal('acidos_grasos', 8, 2)->nullable();
            $table->decimal('fibra', 8, 2)->nullable();
            $table->text('ingredientes')->nullable();
            $table->decimal('hidratos_carbono', 8, 2)->nullable();
            $table->decimal('azucares', 8, 2)->nullable();
            $table->decimal('sal', 8, 2)->nullable();
            $table->decimal('proteinas', 8, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('Cesta_Productos', function (Blueprint $table) {
            $table->foreignId('ID_cesta')->constrained('Cesta_compra', 'ID_cesta')->onDelete('cascade');
            $table->foreignId('ID_prod')->constrained('Producto', 'ID_prod')->onDelete('cascade');
            $table->primary(['ID_cesta', 'ID_prod']);
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('Cesta_Productos');
        Schema::dropIfExists('Producto');
        Schema::dropIfExists('Subcategoria2');
        Schema::dropIfExists('Subcategoria');
        Schema::dropIfExists('Categoria');
        Schema::dropIfExists('Productos_temp');
        Schema::dropIfExists('NivelPiramide');
        Schema::dropIfExists('Supermercado');
        Schema::dropIfExists('Cesta_compra');
        Schema::dropIfExists('Usuario');
    }
};
