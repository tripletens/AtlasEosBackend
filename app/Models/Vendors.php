<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendors extends Model
{
    use HasFactory;

    protected $table = 'vendors';

    protected $fillable = ['vendor_id', 'vendor_name', 'role', 'role_name'];
}
