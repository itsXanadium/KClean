<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class user_voucher extends Model
{
    protected $fillable = [
        'user_id',
        'voucher_id',
        'status',
        'used_at',
        'actives_at',
        'expired_at',
        'voucher_qr',
        'user_voucher_qr_path'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function voucher(){
        return $this->belongsTo(Voucher::class);
    }
    public function vouchertransaction(){
        return $this->hasMany(voucher_transaction::class);
    }
}
