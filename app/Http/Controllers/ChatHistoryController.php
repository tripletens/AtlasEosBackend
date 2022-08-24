<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatHistory;

class ChatHistoryController extends Controller
{
    //

    public function __construct()
    {
        // set timeout limit
        set_time_limit(25000000);
        $this->result = (object) [
            'status' => false,
            'status_code' => 200,
            'message' => null,
            'data' => (object) null,
            'token' => null,
            'debug' => null,
        ];
    }

    public function get_user_chat_history()
    {
    }
}
