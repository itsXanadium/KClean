<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class trash_transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'trash_transaction_id',
        'trash_type',
        'trash_weight',
        'points',
        'user_id',
        'petugas_id'
    ]; 
    public function user(){
        return $this->belongsTo(User::class);
    }
}
