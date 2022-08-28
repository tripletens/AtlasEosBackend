<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSettings extends Model
{
    use HasFactory;

    protected $table = 'system_settings';
    public $fillable = ['program_status', 'start_date', 'close_date', 'chart_start_date', 'created_at', 'updated_at'];
}
