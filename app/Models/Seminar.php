<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    use HasFactory;
    protected $table = 'seminars';
    protected $fillable = [
        'topic',
        'vendor_name',
        'vendor_id',
        'seminar_date',
        'start_time',
        'stop_time',
        'link',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
        'completed_seminar_link'
    ];
}
