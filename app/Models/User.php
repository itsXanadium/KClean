<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Providers;
use App\rolepermission;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_qr',
        'profile_qr_path',
        'trash_transaction_qr',
        'transaction_qr_path',
        'no_telp',
        // 'otp',
        // 'otp_expires_at',
        // 'otp_verified_at'
    ];

    public function voucher(){
        return $this->hasMany(Voucher::class);
    }
    public function trash_transaction(){
        return $this->hasMany(trash_transaction::class);
    }
    public function user_voucher(){
        return $this->hasMany(user_voucher::class);
    }
    public function voucher_transaction(){
        return $this->hasMany(voucher_transaction::class);
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        // 'otp',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // 'otp_expires_at' => 'datetime',
            // 'otp_verified_at' => 'datetime',
        ];
    }
    protected $cast = [
        'role' => rolepermission::class,
    ];
}
