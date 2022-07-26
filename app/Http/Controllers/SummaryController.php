<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;

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
        echo $this->get_all_users_with_same_accout_ids($dealer_id);
    }

    public function get_all_users_with_same_accout_ids($dealer_id){
        $users = Users::where('id',$dealer_id)->where('role','4')->where('account_id', '!=', null)->get();
        $account_ids = [];
        foreach ($users as $user) {
            $account_ids[] = $user->account_id;
        }
        $account_ids = array_unique($account_ids);
        return $account_ids;
    }
}
