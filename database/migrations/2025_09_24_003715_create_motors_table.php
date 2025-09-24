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
        Schema::create('motors', function (Blueprint $table) {
            $table->id('ID_Motor');
            $table->foreignId('owner_id')->constrained('users','ID_User')->onDelete('cascade');
            $table->string('merk');
            $table->string('tipe_cc'); // '100cc', '125cc', '150cc'.
            $table->string('no_plat')->unique();
            $table->string('status'); // 'disewa', 'tersedia', 'perawatan'
            $table->string('photo')->nullable();
            $table->string('dokumen_kepemilikan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motors');
    }
};
