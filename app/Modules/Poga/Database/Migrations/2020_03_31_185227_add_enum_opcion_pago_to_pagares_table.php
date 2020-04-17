<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnumOpcionPagoToPagaresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pagares', function (Blueprint $table) {
	    $table->enum('enum_opcion_pago', ['TOTAL','MINIMO','MANUAL'])->nullable()->default('TOTAL')->before('monto');
	});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pagares', function (Blueprint $table) {
            $table->dropColumn('enum_opcion_pago'); 
        });
    }
}
