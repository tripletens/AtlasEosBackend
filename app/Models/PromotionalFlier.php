<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionalFlier extends Model
{
    use HasFactory;
    protected $table = "promotional_fliers";
    protected $fillable = ['vendor_id', 'name', 'pdf_url', 'description', 'image_url','status', 'created_at', 'updated_at', 'deleted_at'];
}
