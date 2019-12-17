<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstadoInmuebleRentaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estado_inmueble_renta', function (Blueprint $table) {
            $table->increments('id');
	    $table->unsignedInteger('id_renta');
	    $table->foreign('id_renta')->references('id')->on('rentas');
	    $table->unsignedInteger('id_estado_inmueble');
	    $table->foreign('id_estado_inmueble')->references('id')->on('estados_inmueble');
	    $table->tinyInteger('cantidad')->nullable()->default(0);
	    $table->boolean('reparar')->nullable()->default(0);
	    $table->string('foto')->nullable();
	    $table->enum('enum_estado', ['ACTIVO','INACTIVO']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estado_inmueble_renta');
    }
}
