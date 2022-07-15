<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    protected $table = 'reports';
    protected $fillable = [
        'user_id',
        'company_name',
        'subject',
        'description',
        'file_url',
        'dealer_id',
        'vendor_id',
        'role',
        'created_at',
        'ticket_id',
        'deleted_at',
        'status',
        'updated_at',
    ];
}
