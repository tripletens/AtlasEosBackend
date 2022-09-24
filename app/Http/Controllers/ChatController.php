<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\Users;
use App\Models\ChatHistory;

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

    public function get_chat_history($user, $role)
    {
        $res_data = [];
        $chat_history = ChatHistory::where('owner_uid', $user)
            ->where('role', $role)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        if ($chat_history) {
            foreach ($chat_history as $value) {
                $suser = $value->user;
                $user_data = Users::where('id', $suser)
                    ->get()
                    ->first();

                $data = [
                    'id' => $user_data->id,
                    'first_name' => $user_data->first_name,
                    'last_name' => $user_data->last_name,
                    'email' => $user_data->email,
                ];

                array_push($res_data, $data);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $res_data;

        $this->result->message = 'recent chat history';
        return response()->json($this->result);
    }

    public function testing_chat()
    {
    }

    public function count_unread_msg_role($user)
    {
        $unread_dealer_msg = Chat::where('chat_to', $user)
            ->where('status', '0')
            ->where('role', '4')
            ->count();

        $unread_vendor_msg = Chat::where('chat_to', $user)
            ->where('status', '0')
            ->where('role', '3')
            ->count();

        $unread_admin_msg = Chat::where('chat_to', $user)
            ->where('status', '0')
            ->where('role', '1')
            ->count();

        $unread_branch_msg = Chat::where('chat_to', $user)
            ->where('status', '0')
            ->where('role', '2')
            ->count();

        $unread_sales_rep__msg = Chat::where('chat_to', $user)
            ->where('status', '0')
            ->where('role', '5')
            ->orWhere('role', '6')
            ->count();

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data->dealer = $unread_dealer_msg;
        $this->result->data->vendor = $unread_vendor_msg;
        $this->result->data->admin = $unread_admin_msg;

        $this->result->data->branch = $unread_branch_msg;
        $this->result->data->sales = $unread_sales_rep__msg;

        $this->result->message = 'count unread msg chat based on their role';
        return response()->json($this->result);
    }

    public function count_unread_msg($user)
    {
        $unread_msg = Chat::where('chat_to', $user)
            ->where('status', '0')
            ->count();

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $unread_msg;
        $this->result->message = 'count unread msg chat';
        return response()->json($this->result);
    }

    public function get_user_chat_async($receiver, $sender)
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

        $chat_history = Chat::orWhere('unique_id', $phase_one_unique_id)
            ->orWhere('unique_id', $phase_two_unique_id)
            ->get();

        foreach ($chat_history as $value) {
            $value->from_username =
                $sender_data->first_name . ' ' . $sender_data->last_name;
            $value->to_username =
                $receiver_data->first_name . ' ' . $receiver_data->last_name;

            $value->time_ago = ChatController::timeAgo($value->created_at);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $chat_history;
        $this->result->message = 'all User chat History';
        return response()->json($this->result);
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
        // Chat::where('chat_from', $receiver)
        //     ->where('chat_to', $sender)
        //     ->update([
        //         'status' => '1',
        //     ]);

        Chat::where('unique_id', $phase_two_unique_id)->update([
            'status' => '1',
        ]);

        $chat_history = Chat::orWhere('unique_id', $phase_one_unique_id)
            ->orWhere('unique_id', $phase_two_unique_id)
            ->get();

        foreach ($chat_history as $value) {
            $value->from_username =
                $sender_data->first_name . ' ' . $sender_data->last_name;
            $value->to_username =
                $receiver_data->first_name . ' ' . $receiver_data->last_name;

            $value->time_ago = ChatController::timeAgo($value->created_at);
        }

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
            $role = $request->role;

            $chat_to_data = Users::where('id', $chat_to)
                ->get()
                ->first();

            if (
                ChatHistory::where('owner_uid', $chat_from)
                    ->where('user', $chat_to)
                    ->exists()
            ) {
                ChatHistory::where('owner_uid', $chat_from)
                    ->where('user', $chat_to)
                    ->update([
                        'status' => 1,
                    ]);
            } else {
                ChatHistory::create([
                    'owner_uid' => $chat_from,
                    'user' => $chat_to,
                    'role' => $chat_to_data->role,
                ]);
            }

            $store_chat = Chat::create([
                'chat_from' => $chat_from,
                'chat_to' => $chat_to,
                'msg' => $msg,
                'user' => $chat_user,
                'unique_id' => $unique_id,
                'role' => $role,
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

    //Function definition

    public static function timeAgo($time_ago)
    {
        $time_ago = strtotime($time_ago);
        $cur_time = time();
        $time_elapsed = $cur_time - $time_ago;
        $seconds = $time_elapsed;
        $minutes = round($time_elapsed / 60);
        $hours = round($time_elapsed / 3600);
        $days = round($time_elapsed / 86400);
        $weeks = round($time_elapsed / 604800);
        $months = round($time_elapsed / 2600640);
        $years = round($time_elapsed / 31207680);
        // Seconds
        if ($seconds <= 60) {
            return 'just now';
        }
        //Minutes
        elseif ($minutes <= 60) {
            if ($minutes == 1) {
                return 'one minute ago';
            } else {
                return "$minutes minutes ago";
            }
        }
        //Hours
        elseif ($hours <= 24) {
            if ($hours == 1) {
                return 'an hour ago';
            } else {
                return "$hours hrs ago";
            }
        }
        //Days
        elseif ($days <= 7) {
            if ($days == 1) {
                return 'yesterday';
            } else {
                return "$days days ago";
            }
        }
        //Weeks
        elseif ($weeks <= 4.3) {
            if ($weeks == 1) {
                return 'a week ago';
            } else {
                return "$weeks weeks ago";
            }
        }
        //Months
        elseif ($months <= 12) {
            if ($months == 1) {
                return 'a month ago';
            } else {
                return "$months months ago";
            }
        }
        //Years
        else {
            if ($years == 1) {
                return 'one year ago';
            } else {
                return "$years years ago";
            }
        }
    }
}
