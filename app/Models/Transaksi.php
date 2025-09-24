<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $primaryKey = 'ID_Transaksi';
    protected $table = 'transaksis';

    protected $fillable = [
        'penyewaan_id',
        'metode_pembayaran',
        'status',
        'tanggal',
        'jumlah',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'jumlah' => 'decimal:2',
    ];

    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'penyewaan_id', 'ID_Penyewaan');
    }
}