<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramNotes extends Model
{
    use HasFactory;

    protected $table = 'program_notes';

    protected $fillable = [
        'dealer_code',
        'dealer_rep',
        'dealer_uid',
        'vendor_code',
        'vendor_rep',
        'vendor_uid',
        'notes',
        'role',
    ];
}
