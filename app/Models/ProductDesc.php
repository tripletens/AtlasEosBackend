<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDesc extends Model
{
    use HasFactory;

    public $table = 'product_desc';

    protected $fillable = ['atlas_id', 'xref', 'description'];
}
