<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarMembers extends Model
{
    use HasFactory;
    protected $table = 'seminar_members';
    protected $fillable = ['seminar_id', 'dealer_id', 'bookmark_status', 'current_seminar_status', 'status', 'created_at', 'updated_at', 'deleted_at'];
}
