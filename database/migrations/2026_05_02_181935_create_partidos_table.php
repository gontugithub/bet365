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
        Schema::create('partidos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_event')->unique();
            $table->string('equipo_A');
            $table->string('equipo_B');
            $table->string('fase');
            $table->dateTime('fecha_hora_partido');
            $table->integer('goles_equipo_A')->nullable();
            $table->integer('goles_equipo_B')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partidos');
    }
};
