<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $guard = 'api';

    protected $table = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'password_clear',
        'designation',
        'role_name',
        'account_access',
        'region_ab',
        'first_level_access',
        'second_level_access',
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
