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
        Schema::create('penyewaans', function (Blueprint $table) {
            $table->id('ID_Penyewaan');
            $table->foreignId('penyewa_id')->constrained('users','ID_User')->onDelete('cascade');
            $table->foreignId('motor_id')->constrained('motors','ID_Motor')->onDelete('cascade');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('tipe_durasi'); // e.g., 'daily', 'weekly', 'monthly'
            $table->string('status'); // e.g., 'pending', 'active', 'completed', 'canceled'
            $table->decimal('harga', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyewaans');
    }
};
