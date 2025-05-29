<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('matrices_atencion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submotivo_id'); // para saber a qué submotivo pertenece
            $table->unsignedBigInteger('estado_id');     // estado del salto
            $table->unsignedBigInteger('grupo_asignacion_id'); // grupo asignado
            $table->unsignedBigInteger('grupo_visualizacion_id'); // grupo que puede visualizar
            $table->timestamps();
        
            // Claves foráneas
            $table->foreign('submotivo_id')->references('id')->on('submotivos')->onDelete('cascade');
            $table->foreign('estado_id')->references('id')->on('estados');
            $table->foreign('grupo_asignacion_id')->references('id')->on('grupos');
            $table->foreign('grupo_visualizacion_id')->references('id')->on('grupos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matrices_atencion');
    }
};
