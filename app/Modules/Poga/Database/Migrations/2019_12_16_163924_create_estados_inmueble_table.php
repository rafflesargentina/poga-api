<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstadosInmuebleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estados_inmueble', function (Blueprint $table) {
            $table->increments('id');
	    $table->enum('enum_categoria', ['Pintura', 'Puertas', 'Persianas', 'Balancines', 'Baño', 'Ventanas', 'Cocina', 'Rejas', 'Instalaciones eléctricas', 'Jardines', 'Lavadero', 'Canaletas']);
	    $table->string('nombre', 25);
	    $table->enum('enum_estado', ['ACTIVO', 'INACTIVO']);
	    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estados_inmueble');
    }
}
