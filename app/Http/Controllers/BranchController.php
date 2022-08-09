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
            $this->result->status = true;
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
                ->select('id','full_name', 'first_name', 'last_name', 'account_id')
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

        #get all the dealers with account id orders
        if($dealers && count($dealers) > 0){
            foreach($dealers as $key => $dealer){
                # get dealer orders with id
                $dealer_orders_query = Cart::where('uid', $dealer->id);
                # get the total price of items ordered by dealer
                $dealer_orders_total_sum = $dealer_orders_query->sum('price');
                # assign the dealer total price to the dealer
                $dealer->total_price = $dealer_orders_total_sum;
            }
        }
        
        return $dealers;
    }

    # fetch all the dealers in a branch
    public function branch_dealers($uid){
        $dealers = $this->get_dealers_in_branch($uid);

        $this->result->status = true;
        $this->result->data = $dealers;
        $this->result->status_code = 200;
        $this->result->message = 'Branch dealers fetched successfully';
        return response()->json($this->result);
    }
}
