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
        Schema::create('formulario_campos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formulario_id')->constrained('formularios')->onDelete('cascade');
            $table->string('nombre');
            $table->string('etiqueta');
            $table->string('placeholder')->nullable();
            $table->integer('fila')->default(1);
            $table->string('tipo'); // texto, numérico, correo, etc.
            $table->boolean('requerido')->default(false);
            $table->json('validadores')->nullable(); // ej. {"min":3,"max":50}
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulario_campos');
    }
};
