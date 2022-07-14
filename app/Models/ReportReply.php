<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportReply extends Model
{
    use HasFactory;
    protected $table = 'report_reply';
    protected $fillable = ['ticket', 'user', 'reply_msg', 'role', 'replied_by'];
}
