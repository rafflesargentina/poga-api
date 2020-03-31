<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCotizacionToPagaresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pagares', function (Blueprint $table) {
            $table->decimal('cotizacion',5,2)->nullable()->default(0)->after('monto');
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
            $table->dropColumn('cotizacion');
        });
    }
}
