<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendors;
use App\Models\Users;
use App\Models\Chat;

class VendorController extends Controller
{
    //

    public function __construct()
    {
        //// $this->middleware( 'auth:api', [ 'except' => [ 'login', 'register', 'test' ] ] );
        $this->result = (object) [
            'status' => false,
            'status_code' => 200,
            'message' => null,
            'data' => (object) null,
            'token' => null,
            'debug' => null,
        ];
    }

    public function get_vendor_unread_msg($user)
    {
        $unread_msg_data = Chat::where('chat_to', $user)
            ->where('status', '0')
            ->get()
            ->toArray();

        $data = [];

        if ($unread_msg_data) {
            foreach ($unread_msg_data as $value) {
                $sender = $value['chat_from'];

                $sender_data = Users::where('id', $sender)
                    ->where('role', '4')
                    ->get()
                    ->first();

                $count_notification = Chat::where('chat_from', $sender)
                    ->where('chat_to', $user)
                    ->where('status', '0')
                    ->count();

                $each_data = [
                    'id' => $sender_data->id,
                    'first_name' => $sender_data->first_name,
                    'last_name' => $sender_data->last_name,
                    'full_name' => $sender_data->full_name,
                    'email' => $sender_data->email,
                    'notification' => $count_notification,
                ];

                array_push($data, $each_data);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get vendor unread msg';
        $this->result->data = $data;
        return response()->json($this->result);
    }

    public function get_company_dealer_users($code, $user)
    {
        $dealer = Users::where('account_id', $code)
            ->where('role', '4')
            ->get()
            ->toArray();

        $user_data = Users::where('id', $user)
            ->get()
            ->first();

        $data = [];

        if ($dealer) {
            foreach ($dealer as $value) {
                $phase_one_unique_id =
                    $user_data->id .
                    $user_data->first_name .
                    $value['id'] .
                    $value['first_name'];

                $phase_two_unique_id =
                    $value['id'] .
                    $value['first_name'] .
                    $user_data['id'] .
                    $user_data['first_name'];

                $count_notification = Chat::orWhere(
                    'unique_id',
                    $phase_two_unique_id
                )
                    ->where('status', '0')
                    ->count();

                $each_data = [
                    'id' => $value['id'],
                    'first_name' => $value['first_name'],
                    'last_name' => $value['last_name'],
                    'full_name' => $value['full_name'],
                    'email' => $value['email'],
                    'notification' => $count_notification,
                ];

                array_push($data, $each_data);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get all dealer';
        $this->result->data = $data;
        return response()->json($this->result);
    }

    public function get_distinct_dealers()
    {
        $dealer = Users::select('account_id', 'company_name')
            ->where('role', '4')
            ->distinct('account_id')
            ->orderBy('company_name', 'asc')
            ->get();

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get all dealer';
        $this->result->data = $dealer;
        return response()->json($this->result);
    }

    public function get_vendor_coworkers($code, $user)
    {
        $vendors = Users::where('vendor_code', $code)
            ->where('role', '3')
            ->get()
            ->toArray();

        $user_data = Users::where('id', $user)
            ->get()
            ->first();

        $data = [];

        if ($vendors) {
            foreach ($vendors as $value) {
                $sender = $value['id'];
                $sender_data = Users::where('id', $sender)
                    ->get()
                    ->first();

                $count_notification = Chat::where('chat_from', $sender)
                    ->where('chat_to', $user)
                    ->where('status', '0')
                    ->count();

                // $phase_one_unique_id =
                //     $user_data->id .
                //     $user_data->first_name .
                //     $value['id'] .
                //     $value['first_name'];

                // $phase_two_unique_id =
                //     $value['id'] .
                //     $value['first_name'] .
                //     $user_data['id'] .
                //     $user_data['first_name'];

                // $count_notification = Chat::orWhere(
                //     'unique_id',
                //     $phase_two_unique_id
                // )
                //     ->where('status', '0')
                //     ->count();

                $each_data = [
                    'id' => $sender_data['id'],
                    'first_name' => $value['first_name'],
                    'last_name' => $value['last_name'],
                    'full_name' => $value['full_name'],
                    'email' => $value['email'],
                    'notification' => $count_notification,
                ];

                // $each_data = [
                //     'id' => $sender_data->id,
                //     'first_name' => $sender_data->first_name,
                //     'last_name' => $sender_data->last_name,
                //     'full_name' => $sender_data->full_name,
                //     'email' => $sender_data->email,
                //     'notification' => $count_notification,
                // ];

                array_push($data, $each_data);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get all vendors user coworkers';
        $this->result->data = $data;
        return response()->json($this->result);
    }

    public function get_all_vendors()
    {
        $vendors = Vendors::where('status', '1')->get();
        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get all vendors was successful';
        $this->result->data = $vendors;
        return response()->json($this->result);
    }
}
