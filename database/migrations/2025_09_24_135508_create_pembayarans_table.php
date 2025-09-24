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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id('ID_Pembayaran');
            $table->unsignedBigInteger('penyewaan_id');
            $table->string('metode_pembayaran'); // 'bca_va', 'ovo', 'gopay', 'dana', 'qris_static', etc.
            $table->decimal('jumlah_bayar', 15, 2); // Total yang harus dibayar
            $table->decimal('uang_bayar', 15, 2); // Uang yang dibayarkan customer
            $table->decimal('uang_kembalian', 15, 2)->default(0); // Kembalian
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('kode_pembayaran')->unique(); // Kode unik pembayaran
            $table->text('catatan')->nullable(); // Catatan tambahan
            $table->timestamp('tanggal_bayar')->nullable(); // Tanggal pembayaran berhasil
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('penyewaan_id')->references('ID_Penyewaan')->on('penyewaans')->onDelete('cascade');
            
            // Index untuk performa
            $table->index(['penyewaan_id', 'status']);
            $table->index('kode_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
