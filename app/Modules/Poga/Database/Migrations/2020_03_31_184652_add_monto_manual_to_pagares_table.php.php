<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMontoManualToPagaresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pagares', function (Blueprint $table) {
	    $table->decimal('monto_manual',10,2)->nullable()->default(0)->after('monto');
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
            $table->dropColumn('monto_manual');
        });
    }
}
