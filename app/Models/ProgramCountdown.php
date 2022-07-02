<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramCountdown extends Model
{
    use HasFactory;

    protected $table = 'program_countdown';

    protected $fillable = ['countdown_date', 'countdown_time', 'post_med_abbr'];
}
