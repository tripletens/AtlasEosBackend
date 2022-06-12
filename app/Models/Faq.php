<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;
    protected $table = "faqs";
    protected $fillable = ['title', 'subtitle', 'description', 'link', 'username','password', 'created_at', 'updated_at', 'deleted_at'];
}
