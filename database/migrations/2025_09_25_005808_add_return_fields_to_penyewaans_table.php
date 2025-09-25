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
        Schema::table('penyewaans', function (Blueprint $table) {
            $table->text('catatan_pengembalian')->nullable()->after('harga');
            $table->timestamp('tanggal_pengajuan_pengembalian')->nullable()->after('catatan_pengembalian');
            $table->timestamp('tanggal_pengembalian')->nullable()->after('tanggal_pengajuan_pengembalian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyewaans', function (Blueprint $table) {
            $table->dropColumn(['catatan_pengembalian', 'tanggal_pengajuan_pengembalian', 'tanggal_pengembalian']);
        });
    }
};
