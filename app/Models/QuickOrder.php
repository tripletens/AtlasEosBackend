<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuickOrder extends Model
{
    use HasFactory, SoftDeletes;
    // set the table
    protected $table = "quick_order";
    protected $fillable = ['id', 'uid', 'dealer', 'vendor', 'atlas_id', 'product_id', 'qty','price','unit_price', 'status', 'created_at', 'updated_at','deleted_at'];
}
