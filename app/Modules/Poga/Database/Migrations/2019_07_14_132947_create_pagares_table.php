<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagaresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'pagares', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('id_inmueble');
                $table->foreign('id_inmueble')->references('id')->on('inmuebles');
                $table->unsignedInteger('id_persona_acreedora')->nullable();
                $table->foreign('id_persona_acreedora')->references('id')->on('personas')->nullable();
                $table->unsignedInteger('id_persona_deudora')->nullable();
                $table->foreign('id_persona_deudora')->references('id')->on('personas')->nullable();
                $table->decimal('monto',10,2);
                $table->unsignedInteger('id_moneda');
                $table->foreign('id_moneda')->references('id')->on('monedas');
                $table->date('fecha_pagare');
                $table->date('fecha_vencimiento')->nullable();
                $table->date('fecha_pago_a_confirmar')->nullable();
                $table->date('fecha_pago_confirmado')->nullable();
                $table->date('fecha_pago_real')->nullable();
                $table->boolean('pagado_fuera_sistema')->default(false);
                $table->unsignedInteger('id_factura')->nullable();
                $table->foreign('id_factura')->references('id')->on('facturas')->nullable();
                $table->enum('enum_estado', ['A_CONFIRMAR_POR_ADMIN','ANULADO','PAGADO','PENDIENTE','TRANSFERIDO']);
                $table->enum('enum_clasificacion_pagare', ['COMISION_INMOBILIARIA','COMISION_POGA','COMISION_RENTA_ADMIN','COMISION_RENTA_PRIM_ADMIN','DEPOSITO_GARANTIA','EXPENSA','MULTA_RENTA','OTRO','RENTA','SALARIO_ADMINISTRADOR','SALARIO_CONSERJE','SOLICITUD','DISTRIBUIDO_EXPENSA']);
                $table->unsignedInteger('id_tabla')->nullable();
                $table->unsignedInteger('id_distribucion_expensa')->nullable();
                $table->foreign('id_distribucion_expensa')->references('id')->on('distribuciones_expensas');
                $table->unsignedInteger('id_tipo_pagare')->nullable();
                $table->foreign('id_tipo_pagare')->references('id')->on('tipos_pagare');
                $table->string('descripcion', 30)->nullable();
                $table->tinyInteger('mes_a_pagar')->nullable();
                $table->string('pagado_con_fondos_de', 15)->nullable();
		$table->unsignedInteger('nro_comprobante')->nullable();
		$table->boolean('revertido')->nullable()->default(0);
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
        Schema::dropIfExists('pagares');
    }
}
