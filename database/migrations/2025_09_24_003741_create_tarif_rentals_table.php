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
        Schema::create('tarif_rentals', function (Blueprint $table) {
            $table->id('ID_Tarif');
            $table->foreignId('motor_id')->constrained('motors','ID_Motor')->onDelete('cascade');
            $table->integer('tarif_harian');
            $table->integer('tarif_mingguan');
            $table->integer('tarif_bulanan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarif_rentals');
    }
};
