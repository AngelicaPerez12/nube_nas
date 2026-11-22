<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('carpeta')->unique(); // Ej: grupo_contabilidad
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('grupo_id')->nullable()->constrained('grupos')->nullOnDelete();
            $table->string('permiso')->default('lectura'); // 'lectura' o 'escritura'
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('grupo_id');
            $table->dropColumn('permiso');
        });
        Schema::dropIfExists('grupos');
    }
};