<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('tickets', function (Blueprint $table) {
        $table->unsignedBigInteger('creado_por')->nullable()->after('cliente_id');
        $table->foreign('creado_por')->references('id')->on('users');
    });
}

public function down()
{
    Schema::table('tickets', function (Blueprint $table) {
        $table->dropForeign(['creado_por']);
        $table->dropColumn('creado_por');
    });
}
};
