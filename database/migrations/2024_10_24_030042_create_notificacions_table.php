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
        Schema::create('notificacions', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('mensaje');
            $table->enum('estado', ['Pendiente', 'Aceptada', 'Rechazada'])->default('Pendiente');
            $table->timestamps();
            $table->unsignedBigInteger('idusuario')->constrained();
            $table->unsignedBigInteger('idgrupo')->constrained();

            $table->foreign('idgrupo')->references('id')->on('grupos');
            $table->foreign('idusuario')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacions');
    }
};
