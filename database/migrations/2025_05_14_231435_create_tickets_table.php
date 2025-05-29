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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            $table->foreignId('via_ingreso_id')->constrained('catalogos');
            $table->foreignId('prioridad_id')->constrained('catalogos');
            $table->string('asunto');
            $table->foreignId('tipificacion_id')->constrained('catalogos');
            $table->foreignId('motivo_id')->constrained();
            $table->foreignId('submotivo_id')->constrained();
            $table->foreignId('estatus_id')->constrained('estados');
            $table->json('formulario')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
