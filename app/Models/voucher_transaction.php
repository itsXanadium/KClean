<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class voucher_transaction extends Model
{
  protected $fillable = [
    'umkm_id',
    'user_voucher_id',
    'redeemed_at',
    'user_id',
    'created_at'
  ];

  public function user_voucher(){
    return $this->belongsTo(user_voucher::class);
  }
  public function user(){
    return $this->belongsTo(User::class);
  }
}
