<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorOrderNotify extends Model
{
    use HasFactory;

    protected $table = 'vendor_order_notify';

    protected $fillable = ['uid', 'vendor', 'status'];
}
