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
        Schema::table('historial_tickets', function (Blueprint $table) {
            $table->string('tipo', 20)->change(); // Ajusta a un tamaño suficiente
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial_tickets', function (Blueprint $table) {
            $table->string('tipo', 10)->change(); // o el valor original
        });
    }
};
