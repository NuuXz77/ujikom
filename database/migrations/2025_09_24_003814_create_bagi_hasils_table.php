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
        Schema::create('bagi_hasils', function (Blueprint $table) {
            $table->id('ID_Bagi_Hasil');
            $table->foreignId('penyewaan_id')->unique()->constrained('penyewaans','ID_Penyewaan')->onDelete('cascade');
            $table->decimal('bagi_hasil_pemilik', 10, 2);
            $table->decimal('bagi_hasil_admin', 10, 2);
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bagi_hasils');
    }
};
