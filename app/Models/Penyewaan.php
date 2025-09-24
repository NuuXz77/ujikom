<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penyewaan extends Model
{
    use HasFactory;

    protected $primaryKey = 'ID_Penyewaan';
    protected $table = 'penyewaans';

    protected $fillable = [
        'penyewa_id',
        'motor_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'tipe_durasi',
        'status',
        'harga',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'penyewa_id', 'ID_User');
    }

    public function motor()
    {
        return $this->belongsTo(Motors::class, 'motor_id', 'ID_Motor');
    }

    public function transaksi()
    {
        return $this->hasOne(Transaksi::class, 'ID_Penyewaan', 'ID_Penyewaan');
    }

    public function bagiHasil()
    {
        return $this->hasOne(BagiHasil::class, 'ID_Penyewaan', 'ID_Penyewaan');
    }
}