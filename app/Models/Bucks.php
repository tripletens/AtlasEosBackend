<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bucks extends Model
{
    use HasFactory;
    protected $table = "show_buck";
    protected $fillable = ['title','vendor_name', 'vendor_code', 'description', 'img_url', 'status', 'created_at', 'updated_at'];
}
