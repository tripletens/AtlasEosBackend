<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\BranchAssignDealer;
use App\Models\Dealer;
use App\Models\DealerCart;
use App\Models\Catalogue_Order;
use App\Models\ServiceParts;
use App\Models\CardedProducts;
use App\Models\Cart;
use App\Models\Orders;
use App\Models\Users;
use DB;

class BranchController extends Controller
{
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

    public function get_dealers_in_branch ($uid){
        # get all the dealers attached to the branch
        # 1688  achawayne account on my local

        # get branch details
        $branch_detail = Users::find($uid);

        if (!$branch_detail) {
            $this->result->status = false;
            $this->result->status_code = 400;
            $this->result->message =
                'An error ocurred, branch could not be found.';
            return response()->json($this->result);
        }

        # get all the priviledged dealer account ids
        if ($branch_detail->privileged_dealers !== null) {
            $get_priviledged_account_ids_array = explode(',', $branch_detail->privileged_dealers);
        }
        else {
            $get_priviledged_account_ids_array = [];
        }

        # array to store the result
        $dealer_summary_result = [];

        # get all the orders for each of the priviledged dealers
        foreach ($get_priviledged_account_ids_array as $key => $priviledged_dealer) {
            # get all dealers with the dealer details
            $dealer_details = Users::where('account_id', $priviledged_dealer)
                ->select('id','full_name', 'first_name', 'last_name', 'account_id','last_login')
                ->get();

            # add the dealer info to the result array
            array_push($dealer_summary_result,json_decode($dealer_details));
        }


        $result_arr = array();

        foreach ($dealer_summary_result as $sub_arr){
            $result_arr = array_merge($result_arr,$sub_arr);
        }
        return $result_arr;
    }

    public function get_dealer_order_summary($uid)
    {
        $dealer_summary_result = $this->get_dealers_in_branch($uid);

        // return $dealer_summary_result;

        # get all the dealers with account id orders
        if($dealer_summary_result && count($dealer_summary_result) > 0){
            foreach($dealer_summary_result as $key => $dealer){
                # get dealer orders with id
                $dealer_orders_query = Cart::where('uid', $dealer->id);

                # get the total price of items ordered by dealer
                $dealer_orders_total_sum = $dealer_orders_query->sum('price');

                # assign the dealer total price to the dealer
                $dealer->total_price = $dealer_orders_total_sum;
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $dealer_summary_result;
        $this->result->message = 'Dealers order summary for branch fetched successfully';
        return response()->json($this->result);
    }

    # get all the dealers under a branch with account id
    public function get_dealers_with_account_id_under_branch($uid, $account_id){
        $dealer_summary_result = $this->get_dealers_in_branch($uid);

        return $dealer_summary_result;
        // foreach($dealer_summary_result as $dealer){
        //     # check
        // }
    }

    # get all the dealers under a branch with account id
    public function get_dealers_with_account_id_under_branch_with_orders($uid){
        $dealers = $this->get_dealers_in_branch($uid);

        $vendor_array = [];
        #get all the dealers with account id orders
        if($dealers && count($dealers) > 0){
            foreach($dealers as $key => $dealer){
                # get dealer orders with id
                $dealer_orders_query = Cart::where('uid', $dealer->id);
                # get the total price of items ordered by dealer
                $dealer_orders_total_sum = $dealer_orders_query->sum('price');
                # assign the dealer total price to the dealer
                $dealer->total_price = $dealer_orders_total_sum;

                // $dealer->orders = $dealer_orders_query->get();

                $dealer->vendors = $dealer_orders_query->join('vendors', 'vendors.vendor_code', '=', 'cart.vendor')
                    ->select('vendors.id', 'vendors.vendor_name','vendors.vendor_code')
                    ->groupBy('vendors.id')
                    ->get();

                foreach($dealer->vendors as $vendor){
                    $vendor->orders = Cart::where('uid', $dealer->id)->where('vendor', $vendor->vendor_code)->get();
                    $vendor->orders->total_price = Cart::where('uid', $dealer->id)->where('vendor', $vendor->vendor_code)->sum('price');
                }
            }
        }

        $this->result->status = true;
        $this->result->data = $dealers;
        $this->result->status_code = 200;
        $this->result->message = 'Branch dealers with orders fetched successfully';
        return response()->json($this->result);
    }

    # fetch all the dealers in a branch
    public function branch_dealers($uid){
        // $dealers = $this->get_dealers_in_branch($uid);
        $user_dealers_array = [];
        $user_data = Users::where('id', $uid)->get()->first();

        if (!$user_data) {
            $this->result->status = false;
            $this->result->status_code = 400;
            $this->result->data = [];
            $this->result->message = 'sorry user could not be found';
            return response()->json($this->result);
        }

        // get all the privileged dealers under the person
        $user_privileged_dealers = $user_data->privileged_dealers;

        if ($user_privileged_dealers != null) {

            $user_privileged_dealers_array = explode(',', $user_privileged_dealers);

            foreach ($user_privileged_dealers_array as $user_privilaged_dealer) {
                $user_privileged_dealers_format = str_replace('"', '', $user_privilaged_dealer);

                $get_priviledged_dealer_details = Dealer::where('dealer_code', $user_privileged_dealers_format)->get();

                if (count($get_priviledged_dealer_details) > 0) {
                    // yay its an array
                    array_push($user_dealers_array, ...$get_priviledged_dealer_details);
                }
            }
        }

        $this->result->status = true;
        $this->result->data = $user_dealers_array;
        $this->result->status_code = 200;
        $this->result->message = 'Branch dealers fetched successfully';
        return response()->json($this->result);
    }

    # fetch all the dashboard data
    public function branch_dashboard($uid){
        //
        $dealers = $this->get_dealers_in_branch($uid);

        // return gettype($dealers);

        $total_loggedin = 0;

        $total_not_loggedin = 0;

        $total_price_array = [];

        if($dealers && gettype($dealers) == "array"){

            if(count($dealers) > 0){
                foreach($dealers as $key => $dealer){
                    # get dealer orders with id
                    $dealer_orders_query = Cart::where('uid', $dealer->id);

                    # get the total price of items ordered by dealer
                    $dealer_orders_total_sum = $dealer_orders_query->sum('price');

                    # assign the dealer total price to the dealer
                    $dealer->total_price = $dealer_orders_total_sum;

                    # get all the dealers that have logged in
                    if($dealer->last_login !== null){
                        $total_loggedin ++;
                    }else{
                        $total_not_loggedin ++;
                    }
                }

                $total_price_array = array_map(function($item) {
                    return $item->total_price;
                },$dealers);
            }
        }

        $sum_total_price = array_sum($total_price_array);

        // $logged_dealers = Users::where('role', '4')
        // ->where('last_login', '!=', null)
        // ->count();

        // return $total_loggedin;

        $this->result->status = true;
        $this->result->data->total_dealers = $dealers && gettype($dealers) == 'array' ? count($dealers) : 0;
        $this->result->data->total_loggedin = $total_loggedin;
        $this->result->data->total_not_loggedin = $total_not_loggedin;
        $this->result->data->total_purchase = $sum_total_price;
        $this->result->status_code = 200;
        $this->result->message = 'Branch dealers fetched successfully';
        return response()->json($this->result);
    }
}
