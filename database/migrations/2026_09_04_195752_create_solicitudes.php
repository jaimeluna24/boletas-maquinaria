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
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_solicitud');
            $table->string('descripcion');
            $table->string('sector');
            $table->date('fecha_solicitud');
            $table->string('tipo_equipo');
            $table->unsignedBigInteger('actividad_id');
            $table->foreign('actividad_id')->references('id')->on('actividades');
            $table->unsignedBigInteger('solicitante');
            $table->foreign('solicitante')->references('id')->on('users');
            $table->string('estado')->default('Pendiente');
            $table->string('observacion')->nullable();
            $table->string('contestada_por')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
