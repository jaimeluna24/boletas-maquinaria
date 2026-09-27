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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();

            // Título y Mensaje
            $table->string('titulo');
            $table->text('mensaje');

            // Alcance: 'usuario' (1 persona), 'rol' (varios/grupo), 'todos' (sistema completo)
            $table->enum('tipo_destinatario', ['usuario', 'rol', 'todos'])->default('usuario');
            $table->string('rol_destino')->nullable(); // Ej: 'supervisor', 'operador' (si tipo es 'rol')

            // Polimorfismo para vincular la solicitud u otro recurso (Opcional pero muy útil)
            $table->nullableMorphs('notificable'); // Crea notificable_type y notificable_id

            // Enlace o ruta a la que redirige al hacer clic
            $table->string('url')->nullable();

            // Emisor del evento
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
