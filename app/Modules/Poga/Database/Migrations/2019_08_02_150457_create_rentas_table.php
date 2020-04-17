<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRentasTable extends Migration
{
   
    public function up()
    {
        Schema::create(
            'rentas', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('id_renta_padre')->nullable();
                $table->unsignedInteger('id_inmueble');
                $table->foreign('id_inmueble')->references('id')->on('inmuebles');
                $table->unsignedInteger('monto');         
                $table->unsignedInteger('prim_comision_administrador')->nullable()->default(0);
                $table->unsignedInteger('comision_administrador')->nullable()->default(0);;
                $table->unsignedInteger('comision_inmobiliaria')->nullable()->default(0);
                $table->date('fecha_fin');
                $table->date('fecha_inicio');
                $table->boolean('multa')->nullable()->default(0);
                $table->boolean('expensas')->nullable()->default(0);
                $table->boolean('vigente')->nullable()->default(0);
                $table->tinyInteger('dias_multa')->nullable()->default(0);
                $table->decimal('monto_multa_dia',10,2)->nullable()->default(0);
                $table->tinyInteger('dias_notificacion_previa_renovacion')->nullable()->default(60);
                $table->enum('enum_estado', ['ACTIVO','INACTIVO','PENDIENTE','FINALIZADO','PENDIENTE_FINALIZACION']);
                $table->unsignedInteger('id_moneda');
                $table->foreign('id_moneda')->references('id')->on('monedas');
                $table->unsignedInteger('id_inquilino');
                $table->foreign('id_inquilino')->references('id')->on('personas');
                $table->decimal('garantia',10,2)->nullable()->default(0);
                $table->string('motivo_descuento_garantia')->nullable();
                $table->unsignedInteger('dia_mes_pago');
                $table->date('fecha_finalizacion_contrato')->nullable();            
                $table->unsignedInteger('monto_descontado_garantia_finalizacion_contrato')->nullable();
                $table->enum('renovacion', ['AUTOMATICA','MANUAL','NO_RENOVAR'])->nullable()->default('AUTOMATICA');
                $table->timestamps();
            
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rentas');
    }
}
