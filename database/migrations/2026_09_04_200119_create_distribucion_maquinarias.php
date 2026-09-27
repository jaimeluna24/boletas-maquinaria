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
        Schema::create('distribucion_maquinarias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')->nullable()->constrained('solicitudes');
            $table->foreignId('operador_id')->constrained('operadores');
            $table->foreignId('equipo_id')->constrained('equipos');
            $table->foreignId('implemento_id')->nullable()->constrained('implementos');

            $table->unsignedBigInteger('creada_por');
            $table->foreign('creada_por')->references('id')->on('users');

            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->date('fecha')->nullable();

            $table->decimal('horometro_inicial', 8, 2)->nullable();
            $table->decimal('horometro_final', 8, 2)->nullable();

            $table->string('observacion')->nullable();
            $table->string('lugar');
            $table->string('estado')->default('Pendiente');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribucion_maquinarias');
    }
};
