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
        'ID_Penyewaan',
        'bagi_hasil_pemilik',
        'bagi_hasil_admin',
        'settled_at',
    ];

    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'ID_Penyewaan', 'ID_Penyewaan');
    }
}