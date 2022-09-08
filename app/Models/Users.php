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
        'company_code',
        'full_name',
        'role',
        'role_name',
        'privileged_vendors',
        'privileged_dealers',
        'placed_order_date',
        'vendor_code',
        'vendor_name',
        'dealer_name',
        'dealer_code',
        'access_level_first',
        'access_level_second',
        'region',
        'designation',
        'switch_state',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $hidden = ['password', 'remember_token'];
}
