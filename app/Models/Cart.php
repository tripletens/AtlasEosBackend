<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart';

    protected $fillable = [
        'uid',
        'dealer',
        'vendor',
        'product_id',
        'atlas_id',
        'qty',
        'price',
        'unit_price',
        'status',
        'desc',
        'pro_img',
        'vendor_img',
        'spec_data',
        'groupings',
        'booking',
        'category',
        'um',
        'xref',
        'type',
    ];
}
