<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BagiHasil extends Model
{
    use HasFactory;

    protected $primaryKey = 'ID_Bagi_Hasil';
    protected $table = 'bagi_hasils';

    protected $fillable = [
        'penyewaan_id',
        'bagi_hasil_pemilik',
        'bagi_hasil_admin',
        'settled_at',
    ];

    // Tambahkan cast untuk mengkonversi settled_at menjadi Carbon instance
    protected $casts = [
        'settled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'bagi_hasil_pemilik' => 'decimal:2',
        'bagi_hasil_admin' => 'decimal:2',
    ];

    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'penyewaan_id', 'ID_Penyewaan');
    }
}