<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendors;
use App\Models\Users;
use App\Models\Chat;
use App\Models\Products;
use App\Models\Cart;

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

    public function sales_by_item_detailed($code)
    {
        $vendor_purchases = Cart::where('vendor', $code)->get();
        $res_data = [];
        $atlas_id_data = [];

        if ($vendor_purchases) {
            foreach ($vendor_purchases as $value) {
                $user_id = $value->uid;
                $product_id = $value->product_id;
                $atlas_id = $value->atlas_id;
                $vendor_code = $value->vendor_code;
                $pro_data = Products::where('id', $product_id)
                    ->get()
                    ->first();
                if (!in_array($atlas_id, $atlas_id_data)) {
                    array_push($atlas_id_data, $atlas_id);
                }

                $atlas_filter = Cart::where('vendor', $code)
                    ->where('atlas_id', $atlas_id)
                    ->get();

                ///return $atlas_filter;
                $total_atlas_product = 0;

                $dealer_data = [];
                foreach ($atlas_filter as $value) {
                    $qty = $value->qty;
                    $dealer_db = Users::where('id', $user_id)
                        ->get()
                        ->first();
                    $price = $value->price;
                    $total_atlas_product += $price;

                    $data = [
                        'dealer_name' => $dealer_db->company_name,
                        'qty' => $qty,
                        'account_id' => $dealer_db->account_id,
                        'user' =>
                            $dealer_db->first_name .
                            ' ' .
                            $dealer_db->last_name,
                        'total' => $value->price,
                    ];

                    array_push($dealer_data, $data);
                }

                $data = [
                    'vendor' => $code,
                    'description' => $pro_data->description,
                    'overall_total' => $total_atlas_product,
                    'atlas_id' => $atlas_id,
                    'extra_data' => $dealer_data,
                ];

                array_push($res_data, $data);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Sales By Detailed';
        $this->result->data->res = $res_data;
        $this->result->data->atlas_id = $atlas_id_data;

        return response()->json($this->result);
    }

    public function sales_by_item_summary($code)
    {
        $vendor_purchases = Cart::where('vendor', $code)->get();
        $res_data = [];
        foreach ($vendor_purchases as $value) {
            $user_id = $value->uid;
            $product_id = $value->product_id;

            $user = Users::where('id', $user_id)
                ->get()
                ->first();
            $product = Products::where('id', $product_id)
                ->get()
                ->first();

            $data = [
                'qty' => $value->qty,
                'atlas_id' => $value->atlas_id,
                'description' => $product->description,
                'regular' => $product->regular,
                'booking' => $product->booking,
                'total' => $value->price,
            ];

            array_push($res_data, $data);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Purchasers by Dealers';
        $this->result->data = $res_data;
        return response()->json($this->result);
    }

    public function get_purchases_dealers($code)
    {
        $vendor_purchases = Cart::where('vendor', $code)->get();
        $res_data = [];
        foreach ($vendor_purchases as $value) {
            $user_id = $value->uid;
            $product_id = $value->product_id;

            $user = Users::where('id', $user_id)
                ->get()
                ->first();
            $product = Products::where('id', $product_id)
                ->get()
                ->first();

            $data = [
                'account_id' => $user->account_id,
                'dealer_name' => $user->company_name,
                'purchaser_name' => $user->first_name . ' ' . $user->last_name,
                'amount' => $value->price,
            ];

            array_push($res_data, $data);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Purchasers by Dealers';
        $this->result->data = $res_data;
        return response()->json($this->result);
    }

    public function get_vendors_products($code)
    {
        $res_data = Products::where('vendor_code', $code)->get();

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get vendor products';
        $this->result->data = $res_data;
        return response()->json($this->result);
    }

    public function get_privileged_vendors($user, $code)
    {
        $selected_user = Users::where('id', $user)
            ->where('vendor_code', $code)
            ->get()
            ->first();

        $privilaged_vendors = $selected_user->privileged_vendors;
        $separator = explode(',', $privilaged_vendors);
        $res_data = [];
        for ($i = 0; $i < count($separator); $i++) {
            $code = $separator[$i];
            $each = Vendors::where('vendor_code', $code)
                ->get()
                ->first();
            if ($each) {
                array_push($res_data, $each);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get vendor unread msg';
        $this->result->data = $res_data;
        return response()->json($this->result);
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

                if ($sender_data) {
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

                if ($sender_data) {
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

                    array_push($data, $each_data);
                }
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
