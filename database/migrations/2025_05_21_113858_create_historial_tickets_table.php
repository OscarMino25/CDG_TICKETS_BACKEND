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
        Schema::create('historial_tickets', function (Blueprint $table) {
            $table->id();
    
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->enum('tipo', ['creación', 'cambio de estado', 'solución']);
    
            $table->foreignId('estado_anterior_id')->nullable()->constrained('estados')->nullOnDelete();
            $table->foreignId('estado_actual_id')->constrained('estados')->onDelete('restrict');
    
            $table->foreignId('usuario_anterior_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('usuario_actual_id')->nullable()->constrained('users')->nullOnDelete();
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_tickets');
    }
};
