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
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('finalizacion')->nullable();
            $table->string('descripcion',300);
            $table->enum('categoria', ['Trabajo', 'Estudios', 'Gimnasio', 'Dieta', 'Ocio', 'Viajes', 'Otro']);
            $table->enum('prioridad', ['Baja', 'Media', 'Alta']);
            $table->boolean('asignacion')->default(false);
            $table->enum('estado', ['Pendiente', 'Completada'])->default('Pendiente');
            $table->unsignedBigInteger('idusuario')->nullable();
            $table->unsignedBigInteger('idgrupo')->nullable();

            $table->foreign('idgrupo')->references('id')->on('grupos');
            $table->foreign('idusuario')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
