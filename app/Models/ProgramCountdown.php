<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramCountdown extends Model
{
    use HasFactory;

    protected $table = 'program_countdown';

    protected $fillable = [
        'start_countdown_date',
        'start_countdown_time',
        'end_countdown_time',
        'end_countdown_date',
        'post_med_abbr',
    ];
}
