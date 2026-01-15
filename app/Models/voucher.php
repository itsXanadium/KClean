<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'points_required',
        'category',
        'voucher_image',
        'actives_at',
        'expired_at',
        'umkm_id',
        'status',
    ];

    public function casts(){
        return[
            'expired_at' =>'datetime'
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
