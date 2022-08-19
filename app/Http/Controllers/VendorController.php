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

    public function get_vendor_data($code)
    {
        $data = Vendors::where('vendor_code', $code)
            ->get()
            ->first();

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Vendor Data';
        $this->result->data = $data;

        return response()->json($this->result);
    }

    public function get_vendor_note($dealer, $vendor)
    {
        $altas_notes = ProgramNotes::where('dealer_code', $dealer)
            ->where('vendor_code', $vendor)
            ->where('role', '3')
            ->get();
        $res_data = [];
        if ($altas_notes) {
            foreach ($altas_notes as $value) {
                $user = $value->dealer_uid;
                $user_data = Users::where('id', $user)
                    ->get()
                    ->first();

                $data = [
                    'note' => $value->notes,
                    'rep_name' =>
                        $user_data->first_name . ' ' . $user_data->last_name,
                ];

                array_push($res_data, $data);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Vendor Notes';
        $this->result->data = $res_data;

        return response()->json($this->result);
    }

    public function get_atlas_note($dealer, $vendor)
    {
        $altas_notes = ProgramNotes::where('dealer_code', $dealer)
            ->where('vendor_code', $vendor)
            ->where('role', '1')
            ->get();
        $res_data = [];
        if ($altas_notes) {
            foreach ($altas_notes as $value) {
                $user = $value->dealer_uid;
                $user_data = Users::where('id', $user)
                    ->get()
                    ->first();

                $data = [
                    'note' => $value->notes,
                    'rep_name' =>
                        $user_data->first_name . ' ' . $user_data->last_name,
                ];

                array_push($res_data, $data);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Atlas Notes  ';
        $this->result->data = $res_data;

        return response()->json($this->result);
    }

    public function get_privileged_dealers($code)
    {
        $dealers = Users::where('role', '4')->get();
        $access_dealers = [];

        if ($dealers) {
            foreach ($dealers as $value) {
                $privileged_vendors = $value->privileged_vendors;

                if ($privileged_vendors != null) {
                    $expand = explode(',', $privileged_vendors);

                    if (in_array($code, $expand)) {
                        $account_id = $value->account_id;
                        $dealer_data = Dealer::where('dealer_code', $account_id)
                            ->get()
                            ->first();

                        if ($dealer_data) {
                            array_push($access_dealers, $dealer_data);
                        }
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

    public function vendor_single_dashboard_analysis($code)
    {
        $total_sales = Cart::where('vendor', $code)->sum('price');
        $total_orders = Cart::where('vendor', $code)->sum('qty');

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get vendor dashboard analysis';
        $this->result->data->total_sales = $total_sales;
        $this->result->data->total_orders = $total_orders;
        return response()->json($this->result);
    }

    public function vendor_single_dashboard_most_purchaser($code)
    {
        $dealer_data = [];
        $dealer_sales = [];
        $dealers = [];
        $purchasers = [];
        $vend = [];

        $cart_dealer = Cart::where('vendor', $code)->get();
        foreach ($cart_dealer as $value) {
            $dealer_id = $value->dealer;
            if (!in_array($dealer_id, $dealers)) {
                array_push($dealers, $dealer_id);
            }
        }

        for ($i = 0; $i < count($dealers); $i++) {
            $dealer_code = $dealers[$i];
            $total = Cart::where('dealer', $dealer_code)->sum('price');
            $dealer_data = Dealer::where('dealer_code', $dealer_code)
                ->get()
                ->first();

            if ($dealer_data) {
                $data = [
                    'dealer' => $dealer_code,
                    'dealer_name' => is_null($dealer_data->dealer_name)
                        ? null
                        : $dealer_data->dealer_name,
                    'sales' => $total,
                ];

                array_push($purchasers, $data);
            }
        }

        /////// Sorting //////////
        usort($purchasers, function ($a, $b) {
            //Sort the array using a user defined function
            return $a['sales'] > $b['sales'] ? -1 : 1; //Compare the scores
        });

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get vendor single dashboard';
        $this->result->data = $purchasers;

        return response()->json($this->result);
    }

    public function vendor_dashboard_most_purchaser($code, $user)
    {
        $dealer_data = [];
        $dealer_sales = [];
        $dealers = [];
        $purchasers = [];
        $vend = [];

        $selected_user = Users::where('id', $user)
            ->where('vendor_code', $code)
            ->get()
            ->first();

        $privilaged_vendors = $selected_user->privileged_vendors;
        if ($privilaged_vendors != null) {
            $separator = explode(',', $privilaged_vendors);
            $total_sales = 0;
            $total_orders = 0;

            $all_vendor_data = Vendors::all();

            foreach ($all_vendor_data as $value) {
                $vendor_code = $value->vendor_code;
                if (in_array($vendor_code, $separator)) {
                    $cart_dealer = Cart::where('vendor', $vendor_code)->get();
                    foreach ($cart_dealer as $value) {
                        $dealer_id = $value->dealer;
                        if (!in_array($dealer_id, $dealers)) {
                            array_push($dealers, $dealer_id);
                        }
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
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get vendor dashboard';
        $this->result->data = $purchasers;

        return response()->json($this->result);
    }

    public function vendor_dashboard_analysis($code, $user)
    {
        $total_sales = 0;
        $total_orders = 0;
        $selected_user = Users::where('id', $user)
            ->where('vendor_code', $code)
            ->get()
            ->first();

        $privilaged_vendors = $selected_user->privileged_vendors;
        if ($privilaged_vendors != null) {
            $separator = explode(',', $privilaged_vendors);

            $all_vendor_data = Vendors::all();

            foreach ($all_vendor_data as $value) {
                $vendor_code = $value->vendor_code;
                if (in_array($vendor_code, $separator)) {
                    $total_sales += Cart::where('vendor', $vendor_code)->sum(
                        'price'
                    );
                    $total_orders += Cart::where('vendor', $vendor_code)->sum(
                        'qty'
                    );
                }
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get vendor dashboard analysis';
        $this->result->data->total_sales = $total_sales;
        $this->result->data->total_orders = $total_orders;
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
                    $total_atlas_product += $qty;

                    if ($dealer_db) {
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
                            'item_total' =>
                                intval($qty) * floatval($value->price),
                        ];

                        $total_atlas_amount +=
                            intval($qty) * floatval($value->price);

                        array_push($dealer_data, $data);
                    }
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

        $res = $this->sort_according_atlas_id($res_data);

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Sales By Detailed';
        $this->result->data->res = $res;
        // $this->result->data->atlas_id = $atlas_id_data;

        return response()->json($this->result);
    }

    public function sales_by_item_summary($code)
    {
        $vendor_purchases = Cart::where('vendor', $code)
            ->orderBy('atlas_id', 'asc')
            ->get();
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
                'vendor' => $product->vendor_product_code,
                'description' => $product->description,
                'regular' => $product->regular,
                'booking' => $product->booking,
                'total' => $value->price,
            ];

            array_push($res_data, $data);
        }

        $res = $this->sort_according_atlas_id($res_data);

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Purchasers by Dealers';
        $this->result->data = $res;
        return response()->json($this->result);
    }

    public function sort_according_atlas_id($data)
    {
        if (count($data) > 0 && !empty($data)) {
            $ddt = array_map(function ($each) {
                $con = (object) $each;
                $atlas = $con->atlas_id;
                $tem = str_replace('-', '', $atlas);
                $con->temp = $tem;
                return $con;
            }, $data);

            usort($ddt, function ($object1, $object2) {
                $ex1 = explode('-', $object1->atlas_id);
                $ex2 = explode('-', $object2->atlas_id);

                /// return $ex1;

                if (strlen($ex1[0]) < strlen($ex2[0])) {
                    return $object1->temp < $object2->temp;
                } else {
                    return $object1->temp > $object2->temp;
                }
            });

            return $ddt;
        }
    }

    public function view_dealer_summary($user, $dealer, $vendor)
    {
        $dealer_cart = Cart::where('uid', $user)
            ->where('vendor', $vendor)
            ->get();

        $res_data = [];

        $vendor_data = Vendors::where('vendor_code', $vendor)
            ->get()
            ->first();
        $dealer_data = Users::where('id', $user)
            ->get()
            ->first();

        if ($dealer_cart) {
            foreach ($dealer_cart as $value) {
                $atlas_id = $value->atlas_id;
                $pro_data = Products::where('atlas_id', $atlas_id)
                    ->get()
                    ->first();

                $data = [
                    'dealer_rep_name' =>
                        $dealer_data->full_name . ' ' . $dealer_data->last_name,
                    'user_id' => $user,
                    'qty' => $value->qty,
                    'atlas_id' => $atlas_id,
                    'vendor_product_code' => $pro_data->vendor_product_code,
                    'special' => $pro_data->booking,
                    'desc' => $pro_data->description,
                    'total' => $value->price,
                ];

                array_push($res_data, $data);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'View Dealer Summary';
        $this->result->data->summary = $res_data;
        $this->result->data->vendor = $vendor_data;
        $this->result->data->dealer = $dealer_data;

        return response()->json($this->result);
    }

    public function get_purchases_dealers($code)
    {
        $vendor_purchases = Cart::where('vendor', $code)->get();
        $res_data = [];
        $users = [];
        foreach ($vendor_purchases as $value) {
            $user_id = $value->uid;
            $product_id = $value->product_id;

            if (!in_array($user_id, $users)) {
                array_push($users, $user_id);
            }
        }

        foreach ($users as $value) {
            $cart_user = Cart::where('vendor', $code)
                ->where('uid', $value)
                ->get()
                ->first();
            $sum_user_total = Cart::where('vendor', $code)
                ->where('uid', $value)
                ->get()
                ->sum('price');
            $user = Users::where('id', $value)
                ->get()
                ->first();

            if ($user) {
                $data = [
                    'account_id' => $user->account_id,
                    'dealer_name' => $user->company_name,
                    'user' => $user_id,
                    'vendor_code' => $code,
                    'purchaser_name' =>
                        $user->first_name . ' ' . $user->last_name,
                    'amount' => $sum_user_total,
                ];

                array_push($res_data, $data);
            }
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

        foreach ($res_data as $value) {
            $spec_data = $value->spec_data;
            if ($spec_data) {
                $value->spec_data = json_decode($spec_data);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get vendor products';
        $this->result->data = $res_data;
        return response()->json($this->result);
    }

    public function get_vendor_orders($code)
    {
        // $vendor_data = Vendors::where('vendor_code', $code)
        //     ->get()
        //     ->first();

        $vendor_products = Products::where('vendor_code', $code)->get();

        foreach ($vendor_products as $value) {
            $spec_data = $value->spec_data;
            if ($spec_data) {
                $value->spec_data = json_decode($spec_data);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get vendor data';
        // $this->result->data->vendor_data = $res_data;
        $this->result->data = $vendor_products;

        return response()->json($this->result);
    }

    public function get_privileged_vendors($user, $code)
    {
        $selected_user = Users::where('id', $user)
            ->where('vendor_code', $code)
            ->get()
            ->first();

        ///  $privilage_status = false;
        $privilaged_vendors = $selected_user->privileged_vendors;
        $res_data = [];

        $privilage_status = true;
        $separator = explode(',', $privilaged_vendors);
        $all_vendors_data = Vendors::all();

        foreach ($all_vendors_data as $value) {
            $vendor_code = $value->vendor_code;
            if (in_array($vendor_code, $separator)) {
                array_push($res_data, $value);
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
