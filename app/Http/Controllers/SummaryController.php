<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use App\Models\Users;
use App\Models\Vendors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Products;

class SummaryController extends Controller
{
    //
    public function __construct()
    {
        // $this->middleware( 'auth:api');
        $this->result = (object) [
            'status' => false,
            'status_code' => 200,
            'message' => null,
            'data' => (object) null,
            'token' => null,
            'debug' => null,
        ];
    }

    public function view_dealer_purchaser_summary($uid, $dealer, $vendor)
    {
        $cart_data = Cart::where('uid', $uid)
            ->where('dealer', $dealer)
            ->where('vendor', $vendor)
            ->get();

        $vendor_data = Vendors::where('vendor_code', $vendor)
            ->get()
            ->first();

        foreach ($cart_data as $value) {
            $pro_id = $value->product_id;

            $pro_data = Products::where('id', $pro_id)
                ->get()
                ->first();
            $value->description = $pro_data->description;
        }

        $data = [
            'vendor_name' => $vendor_data->vendor_name,
            'vendor_code' => $vendor_data->vendor_code,
            'orders' => $cart_data,
        ];

        $this->result->status = true;
        $this->result->data = $data;
        $this->result->status_code = 200;
        $this->result->message = 'View Dealer Purchasers Summary successfully';
        return response()->json($this->result);
    }

    public function get_dealers_purchasers_summary($uid, $account_id)
    {
        $user_data = Users::where('id', $uid)
            ->get()
            ->first();

        $db_acc_id = $user_data->account_id;

        // if ($db_acc_id == $account_id) {

        $uid_arr = [];

        $cart_dealer = Cart::where('dealer', $account_id)->get();
        foreach ($cart_dealer as $value) {
            $uid = $value->uid;

            if (!in_array($uid, $uid_arr)) {
                array_push($uid_arr, $uid);
            }
        }

        $user_level_data = [];

        foreach ($uid_arr as $value) {
            $car = Cart::select('vendor')
                ->where('uid', $value)
                ->where('dealer', $account_id)
                ->distinct('vendor')
                ->get();

            $curr_user = Users::where('id', $value)
                ->get()
                ->first();

            $cart_user_level = [];

            foreach ($car as $ass) {
                $vendor = $ass->vendor;

                $total = Cart::where('uid', $value)
                    ->where('dealer', $account_id)
                    ->where('vendor', $vendor)
                    ->sum('price');

                $vendor_data = Vendors::where('vendor_code', $vendor)
                    ->get()
                    ->first();

                $vendor_levels = [
                    'vendor_name' => $vendor_data->vendor_name,
                    'vendor_code' => $vendor_data->vendor_code,
                    'total' => $total,
                ];

                array_push($cart_user_level, $vendor_levels);
            }

            $outer_user_level = [
                'full_name' => $curr_user->full_name,
                'uid' => $curr_user->id,
                'account_id' => $curr_user->account_id,
                'order_data' => $cart_user_level,
            ];

            array_push($user_level_data, $outer_user_level);
        }

        // return $user_level_data;
        // } else {
        //     /////// Supper Dealer
        // }

        $this->result->status = true;
        $this->result->data = $user_level_data;
        $this->result->status_code = 200;
        $this->result->message = 'Dealer Purchasers Summary successfully';
        return response()->json($this->result);
    }

    public function product_summary($account_id)
    {
        // get the dealer details then use it to get the account id
        // $get_user_details = Users::find($uid);

        // if (!$get_user_details) {
        //     $this->result->status = true;
        //     $this->result->status_code = 400;
        //     $this->result->message =
        //         'An error ocurred, User doesnt exist.';
        //     return response()->json($this->result);
        // }

        // return $get_user_details;
        // $dealer_id = $get_user_details->account_id;

        // if ($dealer_id) {
        // get all the users under a dealer
        $users = Users::where('account_id', $account_id)
            ->where('role', '4')
            ->where('account_id', '!=', null);

        $res_data = [];

        $user_account_id = $users->pluck('id');

        $group = [];

        if ($users->count() > 0) {
            // $fetch_all_users = $users->pluck('id')->toArray();
            $fetch_all_users = $users
                ->select(
                    'id',
                    'account_id',
                    'first_name',
                    'last_name',
                    'full_name'
                )
                ->get();

            // return $fetch_all_users;

            foreach ($fetch_all_users as $user) {
                // get all the cart items for each user
                $cart_items = Cart::where('dealer', $user->account_id);
                $user->cart = $cart_items->get();
                $user->vendors = $cart_items
                    ->join('vendors', 'vendor_code', '=', 'cart.vendor')
                    ->join('products', 'cart.product_id', '=', 'products.id')
                    ->select(
                        'vendors.*',
                        'products.*',
                        'cart.price',
                        'cart.uid'
                    )
                    ->distinct('vendors.vendor_code')
                    ->get();
                $user->all_vendors = $user->vendors->groupBy('vendor_code');
                // $user->total_price = $cart_items->sum('price');
                // $user->vendors = $group;
            }

            foreach ($user->vendors as $vendor) {
                $vendor->total_price = $vendor->sum('price');
                $sum = $vendor->sum('price');
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $fetch_all_users;
            $this->result->message = 'Product summary fetched Successfully';
            return response()->json($this->result);
        } else {
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->data = null;
            $this->result->message = "Sorry User isn't a dealer";
            return response()->json($this->result, 422);
        }
    }

    public function get_ordered_vendor($code)
    {
        $dealer_cart = Cart::where('dealer', $code)->get();
        $dealer_details = Users::where('role', 4)
            ->where('id', $code)
            ->get()
            ->first();

        $vendor_code = [];

        if ($dealer_cart) {
            foreach ($dealer_cart as $value) {
                $vendor = $value->vendor;
                if (!in_array($vendor, $vendor_code)) {
                    array_push($vendor_code, $vendor);
                }
            }
        }

        $res_data = [];

        for ($i = 0; $i < count($vendor_code); $i++) {
            $vendor = $vendor_code[$i];

            $vendor_data = Vendors::where('vendor_code', $vendor)
                ->get()
                ->first();
            if ($vendor_data) {
                $vendor_data->dealer = $dealer_details;
                array_push($res_data, $vendor_data);
            }
        }
        return $res_data;
    }

    public function get_all_users_with_same_accout_ids($dealer_id)
    {
        // $account_ids = [];
        // foreach ($users as $user) {
        //     $account_ids['id'] = $user->account_id;
        // }
        // $account_ids = array_unique($account_ids);
        // return $account_ids;
        // return $users;
    }

    public function get_dealers_with_orders($uid)
    {
        $dealer = Users::where('id', $uid)->first();
        $vendor_array = [];
        #get all the dealers with account id orders

        $dealer_account_id = $dealer->account_id;
        # get dealer orders with id
        $dealer_orders_query = Cart::where('uid', $uid)->where(
            'dealer',
            $dealer_account_id
        );
        # get the total price of items ordered by dealer
        $dealer_orders_total_sum = $dealer_orders_query->sum('price');
        # assign the dealer total price to the dealer
        $dealer['total_price'] = $dealer_orders_total_sum;

        // $dealer->orders = $dealer_orders_query->get();

        $dealer['vendors'] = $dealer_orders_query
            ->join('vendors', 'vendors.vendor_code', '=', 'cart.vendor')
            ->select('vendors.id', 'vendors.vendor_name', 'vendors.vendor_code')
            ->groupBy('vendors.id')
            ->get();

        // $users = Users::where('account_id', $dealer_id)
        //         ->where('role', '4')->where('account_id', '!=', null);

        // $user->vendors = $cart_items->join('vendors', 'vendor_code', '=', 'cart.vendor')
        //     ->join('products', 'cart.product_id', '=', 'id')
        //     ->select('vendors.*','products.*','cart.price', 'cart.uid')
        //     ->distinct('vendors.vendor_code')
        //     ->get();

        // $user->all_vendors = $user->vendors->groupBy('vendor_code');

        foreach ($dealer['vendors'] as $vendor) {
            $vendor->orders = Cart::where('cart.uid', $dealer->id)
                ->where('cart.vendor', $vendor->vendor_code)
                ->join('products', 'products.id', '=', 'cart.product_id')
                ->get();
            $vendor->orders->total_price = Cart::where('cart.uid', $dealer->id)
                ->where('cart.vendor', $vendor->vendor_code)
                ->sum('cart.price');
        }

        $this->result->status = true;
        $this->result->data = $dealer;
        $this->result->status_code = 200;
        $this->result->message = 'Dealer orders fetched successfully';
        return response()->json($this->result);
    }
}
