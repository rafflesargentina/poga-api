<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtraFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'users', function (Blueprint $table) {
                $table->unsignedInteger('id_persona')->nullable();
                $table->unsignedInteger('role_id')->nullable();
                $table->foreign('id_persona')->references('id')->on('personas');
                $table->string('provider')->nullable()->afterColumn('password');
                $table->string('provider_id')->nullable()->afterColumn('password');
                $table->boolean('bloqueado')->default(0)->beforeColumn('created_at');
                $table->string('codigo_validacion')->afterColumn('password');
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
        Schema::table(
            'users', function (Blueprint $table) {
                $table->dropIndex('id_persona');    
                $table->dropColumn('id_persona');
                $table->dropColumn('provider');
                $table->dropColumn('provider_id');
                $table->dropColumn('role_id');
                $table->dropColumn('bloqueado');
                $table->dropColumn('codigo_validacion');
            }
        );
    }
}
