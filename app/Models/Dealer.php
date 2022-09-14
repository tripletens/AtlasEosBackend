<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Dealer extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'dealers';

    protected $fillable = [
        'dealer_name',
        'dealer_code',
        'role_id',
        'role_name',
        'location',
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
