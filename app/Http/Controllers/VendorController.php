<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Vendors;
use App\Models\Users;
use App\Models\Chat;
use App\Models\Products;
use App\Models\Cart;
use App\Models\Dealer;
use App\Models\Faq;
use App\Models\ProgramNotes;

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

    public function get_privileged_dealers($code)
    {
        $dealers = Users::where('role', '4')->get();

        $access_dealers = [];

        if ($dealers) {
            foreach ($dealers as $value) {
                $privileged_vendors = $value->privileged_vendors;

                if ($privileged_vendors != '') {
                    $expand = explode(',', $privileged_vendors);

                    if (\in_array($code, $expand)) {
                        $account_id = $value->account_id;
                        $dealer_data = Dealer::where('dealer_code', $account_id)
                            ->get()
                            ->first();

                        array_push($access_dealers, $dealer_data);
                    }
                }
            }
        }

        $access_dealers = array_map(
            'unserialize',
            array_unique(array_map('serialize', $access_dealers))
        );

        $filter_array = [];

        foreach ($access_dealers as $value) {
            array_push($filter_array, $value);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'privileged dealers ';
        $this->result->data = $filter_array;

        return response()->json($this->result);
    }

    public function save_atlas_notes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendorCode' => 'required',
            'vendorRepName' => 'required',
            'vendorUid' => 'required',
            'dealerCode' => 'required',
            'dealerRepName' => 'required',
            'dealerUid' => 'required',
            'notes' => 'required',
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $vendor_code = $request->vendorCode;
            $vendor_repname = $request->vendorRepName;
            $role = $request->role;
            $notes = $request->notes;
            $dealer_code = $request->dealerCode;
            $dealer_rep = $request->dealerRepName;

            $dealer_uid = $request->dealerUid;
            $vendor_uid = $request->vendorUid;

            $save_note = ProgramNotes::create([
                'vendor_code' => $vendor_code,
                'vendor_rep' => $vendor_repname,
                'role' => $role,
                'dealer_code' => $dealer_code,
                'dealer_rep' => $dealer_rep,
                'notes' => $notes,
                'dealer_uid' => $dealer_uid,
                'vendor_uid' => $vendor_uid,
            ]);

            if (!$save_note) {
                $this->result->status = false;
                $this->result->status_code = 422;
                $this->result->message =
                    'Sorry Note was not saved. Try again later.';
                return response()->json($this->result);
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Atlas Note saved Successfully';

            return response()->json($this->result);
        }
    }

    public function save_vendor_notes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendorCode' => 'required',
            'vendorRepName' => 'required',
            'vendorUid' => 'required',
            'dealerCode' => 'required',
            'dealerRepName' => 'required',
            'dealerUid' => 'required',
            'notes' => 'required',
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $vendor_code = $request->vendorCode;
            $vendor_repname = $request->vendorRepName;
            $role = $request->role;
            $notes = $request->notes;
            $dealer_code = $request->dealerCode;
            $dealer_rep = $request->dealerRepName;

            $dealer_uid = $request->dealerUid;
            $vendor_uid = $request->vendorUid;

            $save_note = ProgramNotes::create([
                'vendor_code' => $vendor_code,
                'vendor_rep' => $vendor_repname,
                'role' => $role,
                'dealer_code' => $dealer_code,
                'dealer_rep' => $dealer_rep,
                'notes' => $notes,
                'dealer_uid' => $dealer_uid,
                'vendor_uid' => $vendor_uid,
            ]);

            if (!$save_note) {
                $this->result->status = false;
                $this->result->status_code = 422;
                $this->result->message =
                    'Sorry Note was not saved. Try again later.';
                return response()->json($this->result);
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Vendor Note saved Successfully';

            return response()->json($this->result);
        }
    }

    public function get_vendor_faq()
    {
        $faq = Faq::where('role', '3')
            ->where('status', '1')
            ->get();

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get vendor faq';
        $this->result->data = $faq;

        return response()->json($this->result);
    }

    public function vendor_dashboard($code, $user)
    {
        $dealer_data = [];
        $dealer_sales = [];
        $dealers = [];
        $purchasers = [];

        $selected_user = Users::where('id', $user)
            ->where('vendor_code', $code)
            ->get()
            ->first();

        $privilaged_vendors = $selected_user->privileged_vendors;
        $separator = explode(',', $privilaged_vendors);
        $total_sales = 0;
        for ($i = 0; $i < count($separator); $i++) {
            $code = $separator[$i];
            $total_sales += Cart::where('vendor', $code)->sum('price');

            $cart_dealer = Cart::where('vendor', $code)->get();
            foreach ($cart_dealer as $value) {
                $dealer_id = $value->dealer;
                if (!in_array($dealer_id, $dealers)) {
                    array_push($dealers, $dealer_id);
                }
            }
        }

        for ($i = 0; $i < count($dealers); $i++) {
            $dealer_code = $dealers[$i];
            $total = Cart::where('dealer', $dealer_code)->sum('price');
            $dealer_data = Dealer::where('dealer_code', $dealer_code)
                ->get()
                ->first();

            $data = [
                'dealer' => $dealer_code,
                'dealer_name' => $dealer_data->dealer_name,
                'sales' => $total,
            ];

            array_push($purchasers, $data);
        }

        /////// Sorting //////////
        usort($purchasers, function ($a, $b) {
            //Sort the array using a user defined function
            return $a['sales'] > $b['sales'] ? -1 : 1; //Compare the scores
        });

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get vendor dashboard';
        // $this->result->data = $res_data;
        $this->result->data->purchasers = $purchasers;
        $this->result->data->total_sales = $total_sales;
        $this->result->data->dealers = $dealers;
        $this->result->data->orders_received = count($dealers);

        return response()->json($this->result);
    }

    public function sales_by_item_detailed($code)
    {
        $vendor_purchases = Cart::where('vendor', $code)
            ->orderBy('atlas_id', 'asc')
            ->get();
        $res_data = [];
        $atlas_id_data = [];
        $unique_array = [];

        if ($vendor_purchases) {
            $user_id = '';

            foreach ($vendor_purchases as $value) {
                $atlas_id = $value->atlas_id;
                $user_id = $value->uid;

                if (!in_array($atlas_id, $unique_array)) {
                    array_push($unique_array, $atlas_id);
                }
            }

            sort($unique_array);

            for ($i = 0; $i < count($unique_array); $i++) {
                $each_id = $unique_array[$i];

                $atlas_filter = Cart::where('vendor', $code)
                    ->where('atlas_id', $each_id)
                    ->get();

                $pro_data = Products::where('atlas_id', $each_id)
                    ->get()
                    ->first();

                $total_atlas_product = 0;
                $total_atlas_amount = 0;

                $dealer_data = [];
                foreach ($atlas_filter as $value) {
                    $qty = $value->qty;
                    $dealer_db = Users::where('id', $user_id)
                        ->get()
                        ->first();

                    $price = $value->price;
                    //  $total_atlas_amount += $price;
                    $total_atlas_product += $qty;

                    $data = [
                        'atlas_id' => $value->atlas_id,
                        'dealer_name' => $dealer_db->company_name,
                        'qty' => $qty,
                        'account_id' => $dealer_db->account_id,
                        'user' =>
                            $dealer_db->first_name .
                            ' ' .
                            $dealer_db->last_name,
                        'total' => $value->price,
                        'item_total' => intval($qty) * floatval($value->price),
                    ];

                    $total_atlas_amount +=
                        intval($qty) * floatval($value->price);

                    array_push($dealer_data, $data);
                }

                $data = [
                    'vendor' => $code,
                    'description' => $pro_data->description,
                    'overall_total' => $total_atlas_amount,
                    'qty_total' => $total_atlas_product,
                    'atlas_id' => $each_id,
                    'extra_data' => $dealer_data,
                ];

                array_push($res_data, $data);
            }

            // return $res_data;
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Sales By Detailed';
        $this->result->data->res = $res_data;
        // $this->result->data->atlas_id = $atlas_id_data;

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
                'vendor' => $product->vendor_code,
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
            ->distinct('chat_from')
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

        $data = array_map(
            'unserialize',
            array_unique(array_map('serialize', $data))
        );

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
