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
        Schema::create('tiempo_perdido_detalles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_tiempo_perdido');
            $table->unsignedBigInteger('distribucion_maquinaria_id');
            $table->foreign('distribucion_maquinaria_id')->references('id')->on('distribucion_maquinarias');
            // $table->unsignedBigInteger('tiempo_perdido_id')->nullable();
            // $table->foreign('tiempo_perdido_id')->references('id')->on('tiempo_perdidos');
            $table->string('observacion')->nullable();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiempo_perdido_detalles');
    }
};
