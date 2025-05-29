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
        Schema::create('jornadas_laborales', function (Blueprint $table) {
            $table->id();
            $table->string('dia')->unique(); // Lunes, Martes, etc.
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->string('creado_por')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jornadas_laborales');
    }
};
