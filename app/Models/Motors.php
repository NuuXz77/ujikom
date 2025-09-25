<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motors extends Model
{
    use HasFactory;

    protected $primaryKey = 'ID_Motor';

    protected $fillable = [
        'owner_id',
        'merk',
        'tipe_cc',
        'no_plat',
        'status',
        'photo',
        'dokumen_kepemilikan',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tarif()
    {
        return $this->hasOne(TarifRental::class, 'motor_id', 'ID_Motor');
    }

    public function penyewaan()
    {
        return $this->hasMany(Penyewaan::class, 'motor_id', 'ID_Motor');
    }
}
