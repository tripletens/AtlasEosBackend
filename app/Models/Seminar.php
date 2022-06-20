<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    use HasFactory;
    protected $table = 'seminars';
    protected $fillable = [
        'seminar_name',
        'vendor_name',
        'vendor_id',
        'seminar_date',
        'seminar_time',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
