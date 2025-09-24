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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id('ID_Transaksi');
            $table->foreignId('penyewaan_id')->unique()->constrained('penyewaans','ID_Penyewaan')->onDelete('cascade');
            $table->string('metode_pembayaran'); // e.g., 'credit_card', 'cash'
            $table->string('status'); // e.g., 'paid', 'unpaid', 'failed'
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
