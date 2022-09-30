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

    public function get_privileged_dealers($user)
    {
        $res_data = [];

        $selected_user = Users::where('id', $user)
            ->get()
            ->first();

        // $user_vendor_code = $selected_user->vendor_code;
        $privileged_dealers = isset($selected_user->privileged_dealers)
            ? $selected_user->privileged_dealers
            : null;

        if ($privileged_dealers != null) {
            $separator = explode(',', $privileged_dealers);
            if ($separator[1] == '') {
                array_unique($separator);

                $all_dealers_data = Dealer::all();
                foreach ($all_dealers_data as $value) {
                    $dealer_code = $value->dealer_code;

                    if (in_array($dealer_code, $separator)) {
                        array_push($res_data, $value);
                    }
                }
            } else {
                array_unique($separator);

                $all_dealers_data = Dealer::all();
                foreach ($all_dealers_data as $value) {
                    $dealer_code = $value->dealer_code;

                    if (in_array($dealer_code, $separator)) {
                        array_push($res_data, $value);
                    }
                }
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get branch privileged dealers';

        $this->result->data = $res_data;
        return response()->json($this->result);
    }

    public function get_dealers_in_branch($uid)
    {
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
            $get_priviledged_account_ids_array = explode(
                ',',
                $branch_detail->privileged_dealers
            );
        } else {
            $get_priviledged_account_ids_array = [];
        }

        # array to store the result
        $dealer_summary_result = [];

        # get all the orders for each of the priviledged dealers
        foreach ($get_priviledged_account_ids_array as $priviledged_dealer) {
            # get all dealers with the dealer details
            // $dealer_details = Users::where('account_id', $priviledged_dealer)
            //     ->select('id','full_name', 'first_name', 'last_name', 'account_id','last_login')
            //     ->get();

            $user_privileged_dealers_format = str_replace(
                '"',
                '',
                $priviledged_dealer
            );

            $dealer_details = Dealer::where(
                'dealer_code',
                $user_privileged_dealers_format
            )->get();
            # add the dealer info to the result array
            array_push($dealer_summary_result, json_decode($dealer_details));
        }

        $result_arr = [];

        foreach ($dealer_summary_result as $sub_arr) {
            $result_arr = array_merge($result_arr, $sub_arr);
        }
        return $result_arr;
    }

    // public function get_dealer_order_summary($uid)
    // {
    //     $dealer_summary_result = $this->get_dealers_in_branch($uid);

    //     // $dealer_summary_result =
    //     // return $dealer_summary_result;

    //     # get all the dealers with account id orders
    //     if($dealer_summary_result && count($dealer_summary_result) > 0){
    //         foreach($dealer_summary_result as $key => $dealer){
    //             # get dealer orders with id
    //             $dealer_orders_query = Cart::where('uid', $dealer->id);

    //             # get the total price of items ordered by dealer
    //             $dealer_orders_total_sum = $dealer_orders_query->sum('price');

    //             # assign the dealer total price to the dealer
    //             $dealer->total_price = $dealer_orders_total_sum;
    //         }
    //     }

    //     $this->result->status = true;
    //     $this->result->status_code = 200;
    //     $this->result->data = $dealer_summary_result;
    //     $this->result->message = 'Dealers order summary for branch fetched successfully';
    //     return response()->json($this->result);
    // }

    # get all the dealers under a branch with account id
    public function get_dealers_with_account_id_under_branch($uid, $account_id)
    {
        $dealer_summary_result = $this->get_dealers_in_branch($uid);

        return $dealer_summary_result;
        // foreach($dealer_summary_result as $dealer){
        //     # check
        // }
    }

    # get all the dealers under a branch with account id
    public function get_dealers_with_account_id_under_branch_with_orders($uid)
    {
        $dealers = $this->get_dealers_in_branch($uid);

        $vendor_array = [];
        #get all the dealers with account id orders
        if ($dealers && count($dealers) > 0) {
            foreach ($dealers as $key => $dealer) {
                # get dealer orders with id
                $dealer_orders_query = Cart::where('uid', $dealer->id);
                # get the total price of items ordered by dealer
                $dealer_orders_total_sum = $dealer_orders_query->sum('price');
                # assign the dealer total price to the dealer
                $dealer->total_price = $dealer_orders_total_sum;

                // $dealer->orders = $dealer_orders_query->get();

                $dealer->vendors = $dealer_orders_query
                    ->join('vendors', 'vendors.vendor_code', '=', 'cart.vendor')
                    ->select(
                        'vendors.id',
                        'vendors.vendor_name',
                        'vendors.vendor_code'
                    )
                    ->groupBy('vendors.id')
                    ->get();

                foreach ($dealer->vendors as $vendor) {
                    $vendor->orders = Cart::where('uid', $dealer->id)
                        ->where('vendor', $vendor->vendor_code)
                        ->get();
                    $vendor->orders->total_price = Cart::where(
                        'uid',
                        $dealer->id
                    )
                        ->where('vendor', $vendor->vendor_code)
                        ->sum('price');
                }
            }
        }

        $this->result->status = true;
        $this->result->data = $dealers;
        $this->result->status_code = 200;
        $this->result->message =
            'Branch dealers with orders fetched successfully';
        return response()->json($this->result);
    }

    # fetch all the dealers in a branch
    public function branch_dealers($uid)
    {
        // $dealers = $this->get_dealers_in_branch($uid);
        $user_dealers_array = [];
        $user_data = Users::where('id', $uid)
            ->get()
            ->first();

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
            $user_privileged_dealers_array = explode(
                ',',
                $user_privileged_dealers
            );

            foreach (
                $user_privileged_dealers_array
                as $user_privilaged_dealer
            ) {
                $user_privileged_dealers_format = str_replace(
                    '"',
                    '',
                    $user_privilaged_dealer
                );

                $get_priviledged_dealer_details = Dealer::where(
                    'dealer_code',
                    $user_privileged_dealers_format
                )->get();

                if (count($get_priviledged_dealer_details) > 0) {
                    // yay its an array
                    array_push(
                        $user_dealers_array,
                        ...$get_priviledged_dealer_details
                    );
                }
            }
        }

        $this->result->status = true;
        $this->result->data = $user_dealers_array;
        $this->result->status_code = 200;
        $this->result->message = 'Branch dealers fetched successfully';
        return response()->json($this->result);
    }

    public function get_dealer_order_summary($uid)
    {
        $user_dealers_array = [];
        $user_data = Users::where('id', $uid)
            ->get()
            ->first();

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
            $user_privileged_dealers_array = explode(
                ',',
                $user_privileged_dealers
            );

            foreach (
                $user_privileged_dealers_array
                as $user_privilaged_dealer
            ) {
                $user_privileged_dealers_format = str_replace(
                    '"',
                    '',
                    $user_privilaged_dealer
                );

                $get_priviledged_dealer_details = Users::where(
                    'account_id',
                    $user_privileged_dealers_format
                )
                    // ->select('id', 'account_id', 'full_name', 'first_name', 'last_name', 'vendor_name', 'company_name','last_login')
                    ->get();

                if (count($get_priviledged_dealer_details) > 0) {
                    // yay its an array
                    array_push(
                        $user_dealers_array,
                        ...$get_priviledged_dealer_details
                    );
                }
            }
        }

        // return $user_dealers_array;

        $unique_dealers = [];

        foreach ($user_dealers_array as $user_dealer) {
            if (!in_array($user_dealer->account_id, $unique_dealers)) {
                array_push($unique_dealers, (string) $user_dealer->account_id);
            }
        }

        // return $unique_dealers;

        $dealer_info_array = [];

        foreach ($unique_dealers as $unique_user_dealer) {
            $dealer_info = Dealer::where(
                'dealer_code',
                $unique_user_dealer
            )->get();
            $dealer_inner_details = Users::where(
                'account_id',
                $unique_user_dealer
            )
                ->orderby('created_at', 'desc')
                ->get();

            array_push($dealer_info_array, ...$dealer_info);
        }

        // return $dealer_info_array;

        $dealer_user_array = [];

        $lastlogin = [];

        $new_dealers_array = [];

        foreach ($dealer_info_array as $_dealer) {
            $dealer_inner_details = Users::where(
                'account_id',
                $_dealer->dealer_code
            )
                ->select('last_login')
                ->orderby('created_at', 'desc')
                ->get()
                ->pluck('last_login')
                ->toArray();

            $_dealer->login_array = array_values($dealer_inner_details);

            array_push($new_dealers_array, ...$dealer_inner_details);

            $sum_user_total = Cart::where('dealer', $_dealer->dealer_code)
                ->get()
                ->sum('price');

            $_dealer->amount = $sum_user_total;
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Branch purchases fetched successfully';
        $this->result->data = $dealer_info_array;
        return response()->json($this->result);
    }

    # fetch all the dashboard data
    public function branch_dashboard($uid)
    {
        $user_dealers_array = [];
        $user_data = Users::where('id', $uid)
            ->get()
            ->first();

        if (!$user_data) {
            $this->result->status = false;
            $this->result->status_code = 400;
            $this->result->data = [];
            $this->result->message = 'sorry user could not be found';
            return response()->json($this->result);
        }

        // get all the privileged dealers under the person
        $user_privileged_dealers = $user_data->privileged_dealers;

        $user_privilaged_dealer_last_login = 0;

        $total_price = 0;

        $all_dealers_without_orders = [];

        $all_dealers_with_orders = [];

        $all_user_dealers = [];
        
        if ($user_privileged_dealers != null) {

            $user_privileged_dealers_array = explode(',', $user_privileged_dealers);

            foreach ($user_privileged_dealers_array as $user_privilaged_dealer) {
                // $user_privileged_dealers_format = str_replace('"', '', $user_privilaged_dealer);

                // return $user_privilaged_dealer;

                $cart_data_total = Cart::where('dealer', $user_privilaged_dealer)->sum('price');

                $total_price += $cart_data_total;

                // get the dealerships 
                $get_priviledged_dealer_details = Dealer::where('dealer_code', $user_privilaged_dealer)
                    ->get();


                $get_total_user_dealers = Users::where('account_id', $user_privilaged_dealer)->get();

                // return $get_total_user_dealers;

                if (count($get_priviledged_dealer_details) > 0) {
                    // yay its an array

                    $dealer_cart = Cart::where('dealer',$user_privilaged_dealer)->count();
            
                    if($dealer_cart > 0){
                        array_push($all_dealers_without_orders, ...$get_priviledged_dealer_details);
                    }else{
                        array_push($all_dealers_with_orders, ...$get_priviledged_dealer_details);
                    }

                    array_push($user_dealers_array, ...$get_priviledged_dealer_details);
              }

                array_push($all_user_dealers, ...$get_total_user_dealers);
            }
        }


        // return $all_user_dealers;

        $user_privileged_dealers_format = str_replace('\"', '', $user_privilaged_dealer);

        $number_of_dealers = count($user_privileged_dealers_array);

        $last_loggedin_dealer_count = 0;

        $last_not_loggedin_dealer_count = 0;

        $last_login_array = [];

        foreach ($user_dealers_array as $_dealer) {
            $dealer_inner_details = Users::where(
                'account_id',
                $_dealer->dealer_code
            )
                ->select('last_login')
                ->orderby('created_at', 'desc')
                ->get()
                ->pluck('last_login')
                ->toArray();
            array_push($last_login_array, array_values($dealer_inner_details));
        }

        // return $user_dealers_array;

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message =
            'Branch dashboard details fetched successfully';
        $this->result->data->total_sales = $total_price;
        $this->result->data->total_dealers = $number_of_dealers;
        $this->result->data->login_array = $last_login_array;

        $this->result->data->total_logged_in = $last_loggedin_dealer_count;
        $this->result->data->total_not_logged_in = $last_not_loggedin_dealer_count;

        $this->result->data->all_dealers_without_orders = $all_dealers_without_orders;
        $this->result->data->all_dealers_with_orders = $all_dealers_with_orders;
        $this->result->data->all_dealer_users = $all_user_dealers;

        $this->result->data->all_dealers_with_orders_count = count($all_dealers_with_orders);
        $this->result->data->all_dealers_without_orders_count = count($all_dealers_without_orders);
        $this->result->data->all_dealer_users_count = count($all_user_dealers);

        return response()->json($this->result);
    }

    // get dealers that have orders 
    public function branch_dealers_with_orders($uid){
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

        $user_privilaged_dealer_last_login = 0;

        $total_price = 0;

        $all_dealers_with_orders = [];

        $user_dealers_array = [];

        if ($user_privileged_dealers != null) {

            $user_privileged_dealers_array = explode(',', $user_privileged_dealers);

            // return $user_privileged_dealers_array[0];

            foreach ($user_privileged_dealers_array as $user_privilaged_dealer) {
                $user_privileged_dealers_format = str_replace('"', '', $user_privilaged_dealer);

                $get_priviledged_dealer_details = Dealer::where('dealer_code', $user_privileged_dealers_format)
                    ->get();

                    
                if (count($get_priviledged_dealer_details) > 0) {
                    // yay its an array
                    array_push($user_dealers_array, ...$get_priviledged_dealer_details);
                }
            }

            // return $user_dealers_array;

            foreach($user_dealers_array as $_dealer){
                $account_id = $_dealer->dealer_code;
                
                $dealer_cart = Cart::where('dealer',$account_id)->count();
                    
                $cart_data_total = Cart::where('dealer', $account_id)->sum('price');

                $_dealer->total = $cart_data_total;

                if($dealer_cart > 0){
                    array_push($all_dealers_with_orders, $_dealer);
                }
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $all_dealers_with_orders;
            $this->result->message = 'Branch dealers with orders fetched successsfully';
            // $this->result->data->dealers_with_orders_count = count($all_dealers_with_orders);

            return response()->json($this->result);
        }

    }

    // get dealers that dont have orders
    public function branch_dealers_without_orders($uid){
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

        $user_privilaged_dealer_last_login = 0;

        $total_price = 0;

        $all_dealers_without_orders = [];

        $user_dealers_array = [];

        if ($user_privileged_dealers != null) {

            $user_privileged_dealers_array = explode(',', $user_privileged_dealers);

            // return $user_privileged_dealers_array[0];

            foreach ($user_privileged_dealers_array as $user_privilaged_dealer) {
                $user_privileged_dealers_format = str_replace('"', '', $user_privilaged_dealer);

                $get_priviledged_dealer_details = Dealer::where('dealer_code', $user_privileged_dealers_format)
                    ->get();

                    
                if (count($get_priviledged_dealer_details) > 0) {
                    // yay its an array
                    array_push($user_dealers_array, ...$get_priviledged_dealer_details);
                }
            }

            // return $user_dealers_array;

            foreach($user_dealers_array as $_dealer){
                $account_id = $_dealer->dealer_code;
                
                $dealer_cart = Cart::where('dealer',$account_id)->count();
                
                $cart_data_total = Cart::where('dealer', $account_id)->sum('price');

                $_dealer->total = $cart_data_total;

                if($dealer_cart == 0){
                    array_push($all_dealers_without_orders, $_dealer);
                }
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $all_dealers_without_orders;
            $this->result->message = 'Branch dealers without orders fetched successsfully';
            // $this->result->data->dealers_with_orders_count = count($all_dealers_with_orders);

            return response()->json($this->result);
        }

    }
    // public function branch_dashboard($uid){
    //     //
    //     $dealers = $this->get_dealers_in_branch($uid);

    //     // return $dealers;

    //     $total_loggedin = 0;

    //     $total_not_loggedin = 0;

    //     $total_price_array = [];

    //     if($dealers && gettype($dealers) == "array"){

    //         if(count($dealers) > 0){
    //             foreach($dealers as $key => $dealer){
    //                 # get dealer orders with id
    //                 $dealer_orders_query = Cart::where('uid', $dealer->id);

    //                 # get the total price of items ordered by dealer
    //                 $dealer_orders_total_sum = $dealer_orders_query->sum('price');

    //                 # assign the dealer total price to the dealer
    //                 $dealer->total_price = $dealer_orders_total_sum;

    //                 # get all the dealers that have logged in
    //                 if($dealer->last_login !== null){
    //                     $total_loggedin ++;
    //                 }else{
    //                     $total_not_loggedin ++;
    //                 }
    //             }

    //             $total_price_array = array_map(function($item) {
    //                 return $item->total_price;
    //             },$dealers);
    //         }
    //     }

    //     $sum_total_price = array_sum($total_price_array);

    //     // $logged_dealers = Users::where('role', '4')
    //     // ->where('last_login', '!=', null)
    //     // ->count();

    //     // return $total_loggedin;

    //     $this->result->status = true;
    //     $this->result->data->total_dealers = $dealers && gettype($dealers) == 'array' ? count($dealers) : 0;
    //     $this->result->data->total_loggedin = $total_loggedin;
    //     $this->result->data->total_not_loggedin = $total_not_loggedin;
    //     $this->result->data->total_purchase = $sum_total_price;
    //     $this->result->status_code = 200;
    //     $this->result->message = 'Branch dealers fetched successfully';
    //     return response()->json($this->result);
    // }
}
