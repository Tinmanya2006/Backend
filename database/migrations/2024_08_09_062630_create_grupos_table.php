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
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre',45);
            $table->enum('admin', ['usuario', 'administrador'])->default('administrador');
            $table->string('descripcion',200);
            $table->timestamps();
            $table->string('logo')->nullable();
            $table->unsignedBigInteger('idusuario')->notnullable();

            $table->foreign('idusuario')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
