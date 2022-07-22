<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpecialOrder extends Model
{
    use HasFactory, SoftDeletes;

    public $fillable = ['id', 'uid', 'quantity', 'vendor_id', 'description', 'created_at', 'updated_at', 'deleted_at','dealer_id','vendor_code','vendor_no'];
}
