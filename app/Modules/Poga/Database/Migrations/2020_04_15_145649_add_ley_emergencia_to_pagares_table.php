<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLeyEmergenciaToPagaresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pagares', function (Blueprint $table) {
            $table->boolean('ley_emergencia')->nullable()->default(0)->before('created_at');
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
            $table->dropColumn('ley_emergencia');
        });
    }
}
