<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $primaryKey = 'ID_Pembayaran';
    protected $table = 'pembayarans';

    protected $fillable = [
        'penyewaan_id',
        'metode_pembayaran',
        'jumlah_bayar',
        'uang_bayar',
        'uang_kembalian',
        'status',
        'kode_pembayaran',
        'catatan',
        'tanggal_bayar',
    ];

    protected $casts = [
        'jumlah_bayar' => 'decimal:2',
        'uang_bayar' => 'decimal:2',
        'uang_kembalian' => 'decimal:2',
        'tanggal_bayar' => 'datetime',
    ];

    // Relation to Penyewaan
    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'penyewaan_id', 'ID_Penyewaan');
    }

    // Helper methods
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function calculateKembalian()
    {
        return max(0, $this->uang_bayar - $this->jumlah_bayar);
    }

    // Generate unique payment code
    public static function generateKodePembayaran()
    {
        do {
            $code = 'PAY' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('kode_pembayaran', $code)->exists());

        return $code;
    }
}
