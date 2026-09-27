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
        Schema::create('implementos', function (Blueprint $table) {
            $table->id();
            $table->string('inventario');
            $table->string('nombre_implemento');
            $table->unsignedBigInteger('tipo_implemento_id');
            $table->foreign('tipo_implemento_id')->references('id')->on('tipo_implementos');
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('implementos');
    }
};
