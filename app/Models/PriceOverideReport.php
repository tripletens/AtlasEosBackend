<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceOverideReport extends Model
{
    use HasFactory;

    protected $table = 'price_overide_report';

    protected $fillable = [
        'dealer_code',
        'vendor_code',
        'atlas_id',
        'qty',
        'new_qty',
        'regular',
        'show_price',
        'overide_price',
        'authorised_by',
    ];
}
