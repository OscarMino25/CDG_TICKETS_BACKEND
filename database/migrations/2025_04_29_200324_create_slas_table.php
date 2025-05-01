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
    Schema::create('slas', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->integer('tiempo_cliente'); // Tiempo para el cliente en horas
        $table->integer('tiempo_sistema'); // Tiempo SLA del sistema en horas
        $table->timestamps(); // Esto creará los campos created_at y updated_at
    });
}

public function down()
{
    Schema::dropIfExists('slas');
}

};
