<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Users;
use App\Models\Vendors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function product_summary($dealer_id){

        // get all the users under a dealer
        $users = Users::where('account_id',$dealer_id)->where('role','4')->where('account_id', '!=', null);

        $res_data = [];

        $user_account_id = $users->pluck('id');


        if($users){
            $fetch_all_users = $users->get();

            foreach($fetch_all_users as $user){
                // get all the cart items for each user
                $cart_items = Cart::where('uid',$user->id);
                $cart_items_total_price = $cart_items->sum('price');
                $user->total_price = $cart_items_total_price;
                $user->cart =  $cart_items->get();
                // $user->vendor = Vendors::where('vendor_code',$user_cart['vendor'])->get();
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $fetch_all_users;
            $this->result->message = 'Product summary fetched Successfully';
            return response()->json($this->result);
        }

        // $get_all_the_orders_per_user = DB::table('cart')
        //     ->whereIn('uid', $users)
        //     ->where('status', 1)
        //     ->get();



        // return $all_vendors;
        // return $users;
        // get all the items that the dealer have ordered
        // $all_dealer_orders = Cart::where('cart.dealer',$dealer_id)
        //     ->join('vendors','vendors.vendor_code','=','vendor')
        //     ->select('vendors.*','cart.*')
        //     ->orderby('cart.id','desc')->get();

        // return $all_dealer_orders;
        // return $this->get_all_users_with_same_accout_ids($dealer_id);
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

    public function get_all_users_with_same_accout_ids($dealer_id){

        // $account_ids = [];
        // foreach ($users as $user) {
        //     $account_ids['id'] = $user->account_id;
        // }
        // $account_ids = array_unique($account_ids);
        // return $account_ids;
        // return $users;
    }
}
