<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Users extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'location',
        'phone',
        'account_id',
        'status',
        'password_show',
        'username',
        'last_login',
        'company_name',
        'full_name',
        'role',
        'role_name',
        'privileged_vendors',
        'placed_order_date',
        'vendor',
        'vendor_name',
        'dealer',
    ];

    protected $hidden = ['password'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
