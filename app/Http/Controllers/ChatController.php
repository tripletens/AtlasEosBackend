<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\Users;

use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
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

    public function testing_chat()
    {
    }

    public function get_user_chat($receiver, $sender)
    {
        $sender_data = Users::where('id', $sender)
            ->get()
            ->first();
        $receiver_data = Users::where('id', $receiver)
            ->get()
            ->first();

        $phase_one_unique_id =
            $sender_data->id .
            $sender_data->first_name .
            $receiver_data->id .
            $receiver_data->first_name;
        $phase_two_unique_id =
            $receiver_data->id .
            $receiver_data->first_name .
            $sender_data->id .
            $sender_data->first_name;

        // orWhere('unique_id', $phase_one_unique_id)
        Chat::where('chat_from', $sender)
            ->where('chat_to', $receiver)
            ->update([
                'status' => '1',
            ]);

        $chat_history = Chat::orWhere('unique_id', $phase_one_unique_id)
            ->orWhere('unique_id', $phase_two_unique_id)
            ->get();

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $chat_history;
        $this->result->message = 'all User chat History';
        return response()->json($this->result);
    }

    public function store_chat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chatFrom' => 'required',
            'chatTo' => 'required',
            'msg' => 'required',
            'chatUser' => 'required',
            'uniqueId' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $chat_from = $request->chatFrom;
            $chat_to = $request->chatTo;
            $msg = $request->msg;
            $chat_user = $request->chatUser;
            $unique_id = $request->uniqueId;

            $store_chat = Chat::create([
                'chat_from' => $chat_from,
                'chat_to' => $chat_to,
                'msg' => $msg,
                'user' => $chat_user,
                'unique_id' => $unique_id,
            ]);

            if (!$store_chat) {
                $this->result->status = false;
                $this->result->status_code = 422;
                $this->result->message =
                    'Sorry Chat Cant be stored. Try again later.';
                return response()->json($this->result);
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Chat Stored Successfully';

            return response()->json($this->result);
        }
    }
}
