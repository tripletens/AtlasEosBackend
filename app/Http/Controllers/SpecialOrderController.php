<?php

namespace App\Http\Controllers;

use App\Models\SpecialOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpecialOrderController extends Controller
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

    // add special orders
    public function add_special_orders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'product_array' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            $user_id = $request->input('uid');
            // lets get the items from the array
            $product_array = $request->input('product_array');

            $decode_product_array = json_decode($product_array);

            // `uid`, `quantity`, `vendor_id`, `description`,
            if (count($decode_product_array) > 0) {
                foreach ($decode_product_array as $product) {
                    // insert them to the db
                    // `id`, `uid`, `quantity`, `vendor_id`, `description`,
                    //  `created_at`, `updated_at`, `deleted_at`
                    $add_items = SpecialOrder::create([
                        "uid" => $user_id,
                        "quantity" => $product->quantity,
                        "vendor_id" => $product->vendor_id,
                        "description" => $product->description
                    ]);

                    if (!$add_items) {
                        $this->result->status = false;
                        $this->result->status_code = 422;
                        $this->result->data = $product;
                        $this->result->message = "sorry special order item could not be added";
                    }
                }

                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = "Quick order items added successfully";
                return response()->json($this->result);
            } else {
                $this->result->status = false;
                $this->result->status_code = 422;
                $this->result->message = "please add an item to the product array";
                return response()->json($this->result);
            }
        }
    }

    // edit special orders
    public function edit_special_orders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'product_array' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            $user_id = $request->input('uid');
            // lets get the items from the array
            $product_array = $request->input('product_array');

            $decode_product_array = json_decode($product_array);

            // `uid`, `quantity`, `vendor_id`, `description`,
            if (count($decode_product_array) > 0) {
                foreach ($decode_product_array as $product) {
                    // insert them to the db
                    // `id`, `uid`, `quantity`, `vendor_id`, `description`,
                    //  `created_at`, `updated_at`, `deleted_at`
                    $special_order_id = $product->id;
                    $check_item = SpecialOrder::find($special_order_id);

                    if (!$check_item) {
                        $this->result->status = false;
                        $this->result->status_code = 422;
                        $this->result->data = $product;
                        $this->result->message = "sorry special order item could not be added";
                    }

                    $update_special_order = $check_item->update([
                        "uid" => $user_id,
                        "quantity" => $product->quantity,
                        "vendor_id" => $product->vendor_id,
                        "description" => $product->description
                    ]);
                }

                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = "Quick order items updated successfully";
                return response()->json($this->result);
            } else {
                $this->result->status = false;
                $this->result->status_code = 422;
                $this->result->message = "please add an item to the product array";
                return response()->json($this->result);
            }
        }
    }

    // delete special order by id
    public function delete_special_order($id)
    {
        $check_order = SpecialOrder::find($id);

        // oops we couldnt find the special order
        if (!$check_order) {
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = "sorry special order item could not be found";
        }

        // delete the special order
        $delete_special_order = $check_order->delete();

        // oops we could not delete the order
        if (!$check_order) {
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = "sorry special order item could not be deleted";
        }

        // return success response
        $this->result->status = false;
        $this->result->status_code = 422;
        $this->result->message = "Special order item deleted successfully";
        return response()->json($this->result);
    }

    // fetch special order by uid
    public function fetch_special_order_by_uid($uid)
    {
        $check_special_order = SpecialOrder::where('special_orders.uid', $uid)
            ->join('vendors', 'vendors.id', '=', 'special_orders.vendor_id')
            ->select(
                'vendors.vendor_code as vendor_code',
                'vendors.vendor_name as vendor_name',
                'vendors.role as vendor_role',
                'vendors.role_name as vendor_role_name',
                'vendors.status as vendor_role_name',
                'vendors.created_at as vendor_created_at',
                'vendors.updated_at as vendor_updated_at',
                'special_orders.*'
            )->get();

        // oops we couldnt find the special order
        if (!$check_special_order) {
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = "sorry special order item could not be found";
        }

        // return success response
        $this->result->status = false;
        $this->result->status_code = 422;
        $this->result->data = $check_special_order;
        $this->result->message = "Special order item fetched successfully";
        return response()->json($this->result);
    }
}
