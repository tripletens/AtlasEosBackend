<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dealer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Products;
use Illuminate\Support\Facades\Storage;
use App\Models\DealerCart;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubmitOrderMail;
use App\Mail\DeleteOrderMail;
use App\Models\Orders;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Barryvdh\DomPDF\Facade as PDF;

use App\Models\Promotional_ads;
use App\Models\Catalogue_Order;
use App\Models\Category;
use App\Models\AtlasLoginLog;

use App\Models\CardedProducts;
use App\Models\ServiceParts;
use App\Models\Cart;
use App\Models\Faq;
use App\Models\Report;
use App\Models\Vendors;
use App\Models\Users;
use App\Models\Chat;
use App\Models\QuickOrder;
use App\Models\User;
use App\Models\ReportReply;
use App\Models\ProgramNotes;

use App\Models\DealerQuickOrder;
use App\Models\PromotionalFlier;

class DealerController extends Controller
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

    public function login()
    {
        echo 'login page setup';
    }

    public function generate_pdf($dealer)
    {
        $data = [
            'title' => 'Welcome to Tutsmake.com',
            'date' => date('m/d/Y'),
        ];

        $vendors = [];
        $res_data = [];
        $grand_total = 0;

        $dealer_data = Cart::where('dealer', $dealer)->get();
        $dealer_ship = Dealer::where('dealer_code', $dealer)
            ->get()
            ->first();

        foreach ($dealer_data as $value) {
            $vendor_code = $value->vendor;
            if (!\in_array($vendor_code, $vendors)) {
                array_push($vendors, $vendor_code);
            }
        }

        foreach ($vendors as $value) {
            $vendor_data = Vendors::where('vendor_code', $value)
                ->get()
                ->first();
            $cart_data = Cart::where('vendor', $value)
                ->where('dealer', $dealer)
                ->get();

            $total = 0;

            foreach ($cart_data as $value) {
                $total += $value->price;
                $atlas_id = $value->atlas_id;
                $pro_data = Products::where('atlas_id', $atlas_id)
                    ->get()
                    ->first();

                $value->description = $pro_data->description;
                $value->vendor_product_code = $pro_data->vendor_product_code;
            }

            $data = [
                'vendor_code' => $vendor_data->vendor_code,
                'vendor_name' => $vendor_data->vendor_name,
                'total' => floatval($total),
                'data' => $cart_data,
            ];

            $grand_total += $total;

            array_push($res_data, $data);
        }

        //return $res_data;

        $pdf_data = [
            'data' => $res_data,
            'dealer' => $dealer_ship,
            'grand_total' => $grand_total,
        ];

        $pdf = PDF::loadView('dealership-pdf', $pdf_data);
        return $pdf->stream('dealership.pdf');
        // return $pdf->download('dealership.pdf');
    }

    public function get_vendor_item($vendor, $atlas)
    {
        $item = Products::where('vendor', $vendor)
            ->where('atlas_id', $atlas)
            ->get()
            ->first();
        $current = Products::where('vendor', $vendor)
            ->where('atlas_id', $atlas)
            ->get();
        $assorted_status = false;
        $assorted_data = [];

        foreach ($current as $value) {
            $value->spec_data = json_decode($value->spec_data);
        }

        $check_assorted = $item->grouping != null ? true : false;

        if ($check_assorted) {
            $assorted_status = true;
            $assorted_data = Products::where(
                'grouping',
                $item->grouping
            )->get();

            foreach ($assorted_data as $value) {
                $value->spec_data = json_decode($value->spec_data);
            }
        }

        // if ($item) {
        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data->assorted_state = $assorted_status;
        $this->result->data->item = $current;
        $this->result->data->assorted_data = $assorted_data;
        $this->result->message = 'get user vendor item';
        // }

        return response()->json($this->result);
    }

    public function save_edited_user_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'dealer' => 'required',
            'product_array' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $uid = $request->uid;
            $dealer = $request->dealer;
            $vendor = $request->vendor;
            $newly_added = 0;

            // lets get the items from the array
            $product_array = $request->input('product_array');
            if (count(json_decode($product_array)) > 0 && $product_array) {
                $decode_product_array = json_decode($product_array);

                foreach ($decode_product_array as $product) {
                    // update to the db

                    if (
                        Cart::where('dealer', $product->dealer)
                            ->where('atlas_id', $product->atlas_id)
                            ->exists()
                    ) {
                        Cart::where('dealer', $product->dealer)
                            ->where('atlas_id', $product->atlas_id)
                            ->update([
                                'unit_price' => $product->unit_price,
                                'price' => $product->price,
                                'qty' => $product->qty,
                            ]);
                    } else {
                    }
                }
            }

            $order = Cart::where('vendor', $vendor)
                ->where('dealer', $dealer)
                ->get();

            $res_data = [];

            if ($order) {
                foreach ($order as $value) {
                    $atlas_id = $value->atlas_id;
                    $product_data = Products::where('atlas_id', $atlas_id)
                        ->get()
                        ->first();

                    $data = [
                        'id' => $product_data->id,
                        'desc' => $product_data->description,
                        'spec_data' => $product_data->spec_data
                            ? json_decode($product_data->spec_data)
                            : null,
                        'grouping' => $product_data->grouping,
                        'vendor' => $product_data->vendor,
                        'atlas_id' => $product_data->atlas_id,
                        'regular' => $product_data->regular,
                        'booking' => $product_data->booking,
                        'price' => $value->price,
                        'unit_price' => $value->unit_price,
                        'qty' => $value->qty,
                    ];

                    array_push($res_data, $data);
                }
            }

            $this->result->status = true;
            $this->result->data = $res_data;
            $this->result->status_code = 200;
            $this->result->message = 'item Added';

            return response()->json($this->result);
        }
    }

    public function remove_dealer_order_item(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'dealer' => 'required',
            'product_array' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $uid = $request->uid;
            $atlas_id = $request->atlasId;
            $dealer = $request->dealer;
            $vendor = $request->vendor;
            $newly_added = 0;

            if (
                Cart::where('dealer', $dealer)
                    ->where('atlas_id', $atlas_id)
                    ->exists()
            ) {
                Cart::where('dealer', $dealer)
                    ->where('atlas_id', $atlas_id)
                    ->delete();
            }

            // lets get the items from the array
            $product_array = $request->input('product_array');
            if (count(json_decode($product_array)) > 0 && $product_array) {
                $decode_product_array = json_decode($product_array);

                foreach ($decode_product_array as $product) {
                    // update to the db

                    if (
                        Cart::where('dealer', $product->dealer)
                            ->where('atlas_id', $product->atlas_id)
                            ->exists()
                    ) {
                        Cart::where('dealer', $product->dealer)
                            ->where('atlas_id', $product->atlas_id)
                            ->update([
                                'unit_price' => $product->unit_price,
                                'price' => $product->price,
                                'qty' => $product->qty,
                            ]);
                    } else {
                    }
                }
            }

            $order = Cart::where('vendor', $vendor)
                ->where('dealer', $dealer)
                ->get();

            $res_data = [];

            if ($order) {
                foreach ($order as $value) {
                    $atlas_id = $value->atlas_id;
                    $product_data = Products::where('atlas_id', $atlas_id)
                        ->get()
                        ->first();

                    $data = [
                        'id' => $product_data->id,
                        'desc' => $product_data->description,
                        'spec_data' => $product_data->spec_data
                            ? json_decode($product_data->spec_data)
                            : null,
                        'grouping' => $product_data->grouping,
                        'vendor' => $product_data->vendor,
                        'atlas_id' => $product_data->atlas_id,
                        'regular' => $product_data->regular,
                        'booking' => $product_data->booking,
                        'price' => $value->price,
                        'unit_price' => $value->unit_price,
                        'qty' => $value->qty,
                    ];

                    array_push($res_data, $data);
                }
            }

            $this->result->status = true;
            // $this->result->data->order = $order;
            $this->result->data = $res_data;

            $this->result->status_code = 200;
            $this->result->message = 'item Removed';

            return response()->json($this->result);
        }
    }

    public function get_user_vendor_order($dealer, $vendor)
    {
        $order = Cart::where('vendor', $vendor)
            ->where('dealer', $dealer)
            ->orderBy('atlas_id', 'asc')
            ->get();

        $res_data = [];

        if ($order) {
            foreach ($order as $value) {
                $atlas_id = $value->atlas_id;
                $product_data = Products::where('atlas_id', $atlas_id)
                    ->get()
                    ->first();

                $data = [
                    'id' => $product_data->id,
                    'desc' => $product_data->description,
                    'spec_data' => $product_data->spec_data
                        ? json_decode($product_data->spec_data)
                        : null,
                    'grouping' => $product_data->grouping,
                    'vendor' => $product_data->vendor,
                    'atlas_id' => $product_data->atlas_id,
                    'regular' => $product_data->regular,
                    'booking' => $product_data->booking,
                    'price' => $value->price,
                    'unit_price' => $value->unit_price,
                    'qty' => $value->qty,
                ];

                array_push($res_data, $data);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $res_data;
        $this->result->message = 'get user vendor order';
        return response()->json($this->result);
    }

    // move item to the cart from the quick order
    public function move_dealer_quick_order_to_cart(Request $request)
    {
        // validation
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            // 'dealer' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // fetch all items by the uid
            $uid = $request->input('uid');
            $dealer = $request->input('dealer');
            $fetch_all_items_by_uid = DealerQuickOrder::where(
                'uid',
                $uid
            )->get();

            if (!$fetch_all_items_by_uid) {
                $this->result->status = true;
                $this->result->status_code = 400;
                $this->result->message =
                    "An Error Ocurred, we couldn't fetch dealer's quick order";
                return response()->json($this->result);
            }

            if (count($fetch_all_items_by_uid) == 0) {
                $this->result->status = true;
                $this->result->status_code = 402;
                $this->result->message =
                    'Sorry no item in the quick order for this  user';
                return response()->json($this->result);
            }

            foreach ($fetch_all_items_by_uid as $item) {
                $atlas_id = $item->atlas_id;
                $vendor = $item->vendor;
                $dealer = $item->dealer;
                $product_id = $item->product_id;
                $qty = $item->qty;
                $price = $item->price;
                $unit_price = $item->unit_price;
                $status = $item->status;
                $groupings = $item->groupings;

                if (
                    Cart::where('dealer', $dealer)
                        ->where('atlas_id', $atlas_id)
                        ->exists()
                ) {
                    $this->result->status = true;
                    $this->result->status_code = 404;
                    $this->result->message = 'item has been added already';
                    break;
                } else {
                    $save = Cart::create([
                        'uid' => $uid,
                        'atlas_id' => $atlas_id,
                        'dealer' => $dealer,
                        'groupings' => $groupings,
                        'vendor' => $vendor,
                        'product_id' => $product_id,
                        'qty' => $qty,
                        'price' => $price,
                        'unit_price' => $unit_price,
                        'status' => $status,
                    ]);

                    if (!$save) {
                        $this->result->status = false;
                        $this->result->status_code = 422;
                        $this->result->data = $item;
                        $this->result->message =
                            'sorry we could not save this item to cart';
                    }
                }
                // delete item from quick order
                $delete_quick_order_item = $item->delete();

                if (!$delete_quick_order_item) {
                    $this->result->status = false;
                    $this->result->status_code = 422;
                    $this->result->data = $item;
                    $this->result->message =
                        'sorry we could not delete quick order item';
                }
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'item moved to cart from quick order';

            return response()->json($this->result);
        }
    }

    public function remove_all_quick_order($user)
    {
        if (DealerQuickOrder::where('uid', $user)->exists()) {
            $delete = DealerQuickOrder::where('uid', $user)->delete();
            if ($delete) {
                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'item removal was successful ';
            } else {
                $this->result->status = false;
                $this->result->status_code = 404;
                $this->result->message = 'item removal was not successful';
            }
        } else {
            $this->result->status = false;
            $this->result->status_code = 404;
            $this->result->message = 'no item found';
        }

        return response()->json($this->result);
    }

    public function delete_quick_order_item($user, $atlas_id)
    {
        if (
            DealerQuickOrder::where('uid', $user)
                ->where('atlas_id', $atlas_id)
                ->exists()
        ) {
            $delete = DealerQuickOrder::where('uid', $user)
                ->where('atlas_id', $atlas_id)
                ->delete();
            if ($delete) {
                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'item removal was successful ';
            } else {
                $this->result->status = false;
                $this->result->status_code = 404;
                $this->result->message = 'item removal was not successful';
            }

            return response()->json($this->result);
        }
    }

    public function get_item_grouping($group)
    {
        $product_data = Products::where('grouping', $group)->get();
        foreach ($product_data as $value) {
            $value->spec_data = json_decode($value->spec_data);
        }

        $this->result->status = true;
        $this->result->data = $product_data;
        $this->result->message = 'Grouped Products';
        return response()->json($this->result);
    }

    public function get_dealer_quick_orders($dealer, $uid)
    {
        $quick_orders = DealerQuickOrder::where('uid', $uid)
            ->where('dealer', $dealer)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($quick_orders as $value) {
            $altas_id = $value->atlas_id;
            $product_data = Products::where('atlas_id', $altas_id)
                ->get()
                ->first();

            $value->desc = $product_data->description;
            $value->booking = $product_data->booking;
            $value->regular = $product_data->regular;

            $value->spec_data = json_decode($product_data->spec_data);
        }

        $this->result->status = true;
        $this->result->data = $quick_orders;
        $this->result->message = 'dealer quick orders';
        return response()->json($this->result);
    }

    // adds item to the quick order table
    public function submit_assorted_quick_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'dealer' => 'required',
            'product_array' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $uid = $request->uid;
            $atlas_id = $request->atlas_id;
            $dealer = $request->dealer;
            $vendor = $request->vendor;
            $existing_already_in_order = '';
            $newly_added = 0;
            $existing_already_in_quick_order = '';
            $current_vendor = '';
            $submitted_status = false;

            // lets get the items from the array
            $product_array = $request->input('product_array');
            if (count(json_decode($product_array)) > 0 && $product_array) {
                $decode_product_array = json_decode($product_array);

                foreach ($decode_product_array as $product) {
                    // update to the db

                    if (
                        Cart::where('dealer', $dealer)
                            ->where('atlas_id', $product->atlas_id)
                            ->exists()
                    ) {
                        $existing_already_in_order .= $product->atlas_id . ', ';
                        $current_vendor = $product->vendor_id;
                    } else {
                        if (
                            DealerQuickOrder::where('dealer', $dealer)
                                ->where('atlas_id', $product->atlas_id)
                                ->exists()
                        ) {
                            $existing_already_in_quick_order .=
                                $product->atlas_id . ', ';
                        } else {
                            $submitted_status = true;
                            $save = QuickOrder::create([
                                'uid' => $uid,
                                'atlas_id' => $product->atlas_id,
                                'dealer' => $dealer,
                                'grouping' => $product->groupings,
                                'vendor' => $product->vendor_id,
                                'product_id' => $product->product_id,
                                'qty' => $product->qty,
                                'price' => $product->price,
                                'unit_price' => $product->unit_price,
                                'vendor_no' => $product->vendor_no,
                                'type' => $product->type,
                            ]);

                            $newly_added++;
                            $this->result->status = true;
                            $this->result->status_code = 200;
                            $this->result->message = 'item Submitted';
                        }
                    }

                    $this->result->status = true;
                    $this->result->data->existing_already_in_order = $existing_already_in_order;
                    $this->result->data->newly_added = $newly_added;
                    $this->result->data->existing_already_in_quick_order = $existing_already_in_quick_order;
                    $this->result->data->current_vendor = $current_vendor;

                    $this->result->data->submitted_status = $submitted_status;

                    $this->result->status_code = 200;
                    $this->result->message = 'item Added';
                }
            }

            return response()->json($this->result);
        }
    }

    // adds item to the quick order table
    public function submit_quick_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'dealer' => 'required',
            'product_array' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $uid = $request->uid;
            $atlas_id = $request->atlas_id;
            $dealer = $request->dealer;
            $vendor = $request->vendor;

            $existing_already_in_order = '';
            $newly_added = 0;
            $existing_already_in_quick_order = '';
            $existing_status = false;
            $existing_quick_order_status = false;
            $current_vendor = '';
            $submitted_status = false;

            // lets get the items from the array
            $product_array = $request->input('product_array');
            if (count(json_decode($product_array)) > 0 && $product_array) {
                $decode_product_array = json_decode($product_array);

                foreach ($decode_product_array as $product) {
                    // update to the db

                    if (
                        Cart::where('dealer', $dealer)
                            ->where('atlas_id', $product->atlas_id)
                            ->exists()
                    ) {
                        $existing_already_in_order .= $product->atlas_id . ', ';
                        $existing_status = true;
                        $current_vendor = $product->vendor_id;
                    } else {
                        if (
                            DealerQuickOrder::where('dealer', $dealer)
                                ->where('atlas_id', $product->atlas_id)
                                ->exists()
                        ) {
                            $existing_already_in_quick_order .=
                                $product->atlas_id . ', ';
                            $existing_quick_order_status = true;
                        } else {
                            $submitted_status = true;
                            $save = QuickOrder::create([
                                'uid' => $uid,
                                'atlas_id' => $product->atlas_id,
                                'dealer' => $dealer,
                                'groupings' => $product->groupings,
                                'vendor' => $product->vendor_id,
                                'product_id' => $product->product_id,
                                'qty' => $product->qty,
                                'price' => $product->price,
                                'unit_price' => $product->unit_price,
                                'vendor_no' => $product->vendor_no,
                                'type' => $product->type,
                            ]);

                            $newly_added++;
                        }
                    }
                }
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'item Added to cart';
            $this->result->data->existing_status = $existing_status;
            $this->result->data->current_vendor = $current_vendor;
            $this->result->data->existing_quick_order_status = $existing_quick_order_status;
            $this->result->data->existing_already_in_order = $existing_already_in_order;
            $this->result->data->newly_added = $newly_added;
            $this->result->data->existing_already_in_quick_order = $existing_already_in_quick_order;

            $this->result->data->submitted_status = $submitted_status;

            return response()->json($this->result);
        }
    }

    public function get_fetch_by_vendor_atlas($code)
    {
        $filtered_item = Products::orWhere('atlas_id', $code)
            ->orWhere('vendor_product_code', $code)
            ->get()
            ->first();

        $data = [];

        if ($filtered_item) {
            $filtered_item->spec_data = json_decode($filtered_item->spec_data);
            $vendor = $filtered_item->vendor_code;
            $vendor_data = Vendors::where('vendor_code', $vendor)
                ->get()
                ->first();
            if ($vendor_data) {
                $filtered_item->vendor_data = $vendor_data;
            }

            array_push($data, $filtered_item);

            $check_assorted = $filtered_item->grouping != null ? true : false;
            $this->result->data->assorted = $check_assorted;
        } else {
            $filtered_item = [];
        }

        $this->result->status = true;
        $this->result->data->filtered_data = $data;
        $this->result->message = 'filtered data';
        return response()->json($this->result);
    }

    public function save_item_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'dealer' => 'required',
            'product_array' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $uid = $request->uid;
            $atlas_id = $request->atlas_id;
            $dealer = $request->dealer;
            $vendor = $request->vendor;

            // lets get the items from the array
            $product_array = $request->input('product_array');

            $item_already_added = 0;
            $item_added = 0;
            $item_details = '';
            $current_vendor = '';
            $submitted_status = false;

            if (count(json_decode($product_array)) > 0 && $product_array) {
                $decode_product_array = json_decode($product_array);

                foreach ($decode_product_array as $product) {
                    // update to the db

                    if (
                        Cart::where('dealer', $dealer)
                            ->where('atlas_id', $product->atlas_id)
                            ->exists()
                    ) {
                        $item_already_added += 1;
                        $item_details .= $product->atlas_id . ',';
                        // break;
                        /// break;
                        // $this->result->status = true;
                        // $this->result->status_code = 404;
                        // $this->result->message = 'item has been added already';
                    } else {
                        $current_vendor = $product->vendor_id;
                        $submitted_status = true;
                        $save = Cart::create([
                            'uid' => $uid,
                            'atlas_id' => $product->atlas_id,
                            'dealer' => $dealer,
                            'groupings' => $product->groupings,
                            'vendor' => $product->vendor_id,
                            'product_id' => $product->product_id,
                            'qty' => $product->qty,
                            'price' => $product->price,
                            'unit_price' => $product->unit_price,
                            'type' => $product->type,
                        ]);

                        if ($save) {
                            $item_added += 1;
                        }
                    }
                }
            }

            $vendors_chat = [];

            if ($current_vendor != '') {
                $vendors = Users::where('vendor_code', $current_vendor)->get();

                if ($vendors) {
                    foreach ($vendors as $value) {
                        $id = $value->id;
                        $first_name = $value->first_name;
                        $chat_id = $id . $first_name;
                        array_push($vendors_chat, $chat_id);
                    }
                }
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'item Added to cart';
            $this->result->data->item_added = $item_added;
            $this->result->data->item_already_added = $item_already_added;

            $this->result->data->item_details = $item_details;
            $this->result->data->submitted_status = $submitted_status;
            $this->result->data->current_vendor = $current_vendor;

            $this->result->data->chat_data = $vendors_chat;

            return response()->json($this->result);
            // return $array_check;
        }
    }

    public function get_report_reply($ticket)
    {
        $selected = ReportReply::where('ticket', $ticket)->get();

        $res_data = [];
        if ($selected) {
            foreach ($selected as $value) {
                $user = $value->user;
                $user_data = Users::where('id', $user)
                    ->get()
                    ->first();

                if ($user_data) {
                    $data = [
                        'first_name' => $user_data->first_name,
                        'last_name' => $user_data->last_name,
                        'role' => $value->role,
                        'msg' => $value->reply_msg,
                        'replied_by' => $value->replied_by,
                        'ticket' => $ticket,
                        'status' => $value->status,
                        'created_at' => $value->created_at,
                    ];

                    array_push($res_data, $data);
                }
            }
        }

        $this->result->status = true;
        $this->result->data = $res_data;
        $this->result->message = 'Report Replies';
        return response()->json($this->result);
    }

    public function save_dealer_reply_problem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'replyMsg' => 'required',
            'userId' => 'required',
            'role' => 'required',
            'ticket' => 'required',
            'replier' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $replyMsg = $request->replyMsg;
            $userId = $request->userId;
            $role = $request->role;
            $ticket = $request->ticket;
            $replier = $request->replier;

            $save_reply = ReportReply::create([
                'user' => $userId,
                'reply_msg' => $replyMsg,
                'role' => $role,
                'ticket' => $ticket,
                'replied_by' => $replier,
            ]);

            if (!$save_reply) {
                $this->result->status = false;
                $this->result->status_code = 422;
                $this->result->message =
                    'Sorry File could not be uploaded. Try again later.';
                return response()->json($this->result);
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Admin User Added Successfully';

            return response()->json($this->result);
        }
    }

    public function get_first_ticket($ticket)
    {
        $selected = Report::where('ticket_id', $ticket)
            ->get()
            ->first();

        $user_id = $selected->user_id;
        $user_data = Users::where('id', $user_id)
            ->get()
            ->first();

        $selected->first_name = $user_data->first_name;
        $selected->last_name = $user_data->last_name;

        $this->result->status = true;
        $this->result->data = $selected;
        $this->result->message = 'Program Count Down Set Successfully';
        return response()->json($this->result);
    }

    public function get_problem_dealer($ticket)
    {
        $selected = Report::where('ticket_id', $ticket)->get();

        $res_data = [];
        if ($selected) {
            // post with the same slug already exists
            // Report::where('ticket_id', $ticket)->update([
            //     'status' => 2,
            // ]);
            foreach ($selected as $value) {
                $user_id = $value->user_id;
                $user_data = Users::where('id', $user_id)
                    ->get()
                    ->first();

                $data = [
                    'first_name' => $user_data->first_name,
                    'last_name' => $user_data->last_name,
                    'subject' => $value->subject,
                    'description' => $value->description,
                    'file_url' => $value->file_url,
                    'role' => $value->role,
                    'created_at' => $value->created_at,
                ];

                array_push($res_data, $data);
            }

            array_shift($res_data);
        }

        $this->result->status = true;
        $this->result->data = $res_data;
        $this->result->message = 'Program Count Down Set Successfully';
        return response()->json($this->result);
    }

    public function delete_item_cart($dealer, $vendor)
    {
        $data = Cart::where('cart.dealer', $dealer)->where(
            'cart.vendor',
            $vendor
        );

        // return $data->get();

        $check_data = $data->exists();
        $fetch_users_data = $data
            ->join('users', 'users.id', '=', 'cart.uid')
            ->join('products', 'products.id', '=', 'cart.product_id')
            ->select(
                'products.img as product_img',
                'products.status as product_status',
                'products.description as product_description',
                'products.vendor_code as product_vendor_code',
                'products.vendor_name as products_vendor_name',
                'products.vendor_product_code as product_vendor_product_code',
                'products.xref as product_xref',
                'products.vendor as product_vendor',
                'products.id as product_id',
                'products.atlas_id as product_atlas_id',
                'products.vendor_logo as product_vendor_logo',
                'products.um as product_um',
                'products.regular as product_regular',
                'products.booking as product_booking',
                'products.special as product_special',
                'products.cond as product_cond',
                'products.type as product_type',
                'products.grouping as product_grouping',
                'products.full_desc as product_full_desc',
                'products.spec_data as product_spec_data',
                'products.check_new as product_check_new',
                'products.short_note as product_short_note',
                'products.short_note_url as product_short_note_url',
                'products.created_at as product_created_at',
                'products.updated_at as product_updated_at',
                'cart.*'
            )
            ->get();
        if ($check_data) {
            $delete = Cart::where('dealer', $dealer)
                ->where('vendor', $vendor)
                ->delete();
            if (!$delete) {
                $this->result->status = false;
                $this->result->status_code = 500;
                $this->result->message =
                    'sorry we could not delete this item to cart';
            } else {
                // get the dealer details
                $dealer = User::where('role', 4)
                    ->where('id', $dealer)
                    ->get()
                    ->first();

                // Mail::to($dealer->email)->send(
                //     new DeleteOrderMail($fetch_users_data)
                // );

                $this->result->status = true;
                $this->result->data = $fetch_users_data;
                $this->result->status_code = 200;
                $this->result->message = 'Item deleted successfully';
            }
        } else {
            $this->result->status = false;
            $this->result->status_code = 404;
            $this->result->message = 'vendor items not found';
        }

        return response()->json($this->result);
    }

    public function delete_item_cart_atlas_id_dealer_id($dealer, $atlas_id)
    {
        $data = Cart::where('cart.dealer', $dealer)->where(
            'cart.atlas_id',
            $atlas_id
        );

        // return $data->get();

        $check_data = $data->exists();
        $fetch_users_data = $data
            ->join('users', 'users.id', '=', 'cart.uid')
            ->join('products', 'products.id', '=', 'cart.product_id')
            ->select(
                'products.img as product_img',
                'products.status as product_status',
                'products.description as product_description',
                'products.vendor_code as product_vendor_code',
                'products.vendor_name as products_vendor_name',
                'products.vendor_product_code as product_vendor_product_code',
                'products.xref as product_xref',
                'products.vendor as product_vendor',
                'products.id as product_id',
                'products.atlas_id as product_atlas_id',
                'products.vendor_logo as product_vendor_logo',
                'products.um as product_um',
                'products.regular as product_regular',
                'products.booking as product_booking',
                'products.special as product_special',
                'products.cond as product_cond',
                'products.type as product_type',
                'products.grouping as product_grouping',
                'products.full_desc as product_full_desc',
                'products.spec_data as product_spec_data',
                'products.check_new as product_check_new',
                'products.short_note as product_short_note',
                'products.short_note_url as product_short_note_url',
                'products.created_at as product_created_at',
                'products.updated_at as product_updated_at',
                'cart.*'
            )
            ->get();
        if ($check_data) {
            $delete = Cart::where('dealer', $dealer)
                ->where('atlas_id', $atlas_id)
                ->delete();
            if (!$delete) {
                $this->result->status = false;
                $this->result->status_code = 500;
                $this->result->message =
                    'sorry we could not delete this item to cart';
            } else {
                // get the dealer details
                $dealer = User::where('role', 4)
                    ->where('id', $dealer)
                    ->get()
                    ->first();

                // Mail::to($dealer->email)->send(
                //     new DeleteOrderMail($fetch_users_data)
                // );

                $this->result->status = true;
                $this->result->data = $fetch_users_data;
                $this->result->status_code = 200;
                $this->result->message = 'Item deleted successfully';
            }
        } else {
            $this->result->status = false;
            $this->result->status_code = 404;
            $this->result->message = 'vendor items not found';
        }

        return response()->json($this->result);
    }

    // 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
    public function get_ordered_vendor($code)
    {
        $dealer_cart = Cart::where('dealer', $code)->get();
        $dealer_details = User::where('role', 4)
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

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $res_data;
        $this->result->message = 'ordered vendors';

        return response()->json($this->result);
    }

    public function edit_dealer_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'dealer' => 'required',
            'product_array' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $uid = $request->uid;
            $dealer = $request->dealer;

            // lets get the items from the array
            $product_array = $request->input('product_array');

            if (count(json_decode($product_array)) > 0 && $product_array) {
                $decode_product_array = json_decode($product_array);

                foreach ($decode_product_array as $product) {
                    // update to the db

                    $update = Cart::where('uid', $uid)
                        ->where('dealer', $dealer)
                        ->where('atlas_id', $product->atlas_id)
                        ->update([
                            'qty' => $product->qty,
                            'price' => $product->price,
                            'unit_price' => $product->unit_price,
                        ]);

                    if (!$update) {
                        $this->result->status = false;
                        $this->result->status_code = 500;
                        $this->result->message =
                            'sorry we could not update this item to cart';
                    }
                }
            }
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'item Updated to cart';
            // }

            return response()->json($this->result);
        }
    }

    public function get_editable_orders_by_vendor($code)
    {
        $cart = Cart::where('vendor', $code)->get();

        if ($cart) {
            foreach ($cart as $value) {
                $pro_id = $value->product_id;
                $product_data = Products::where('id', $pro_id)
                    ->get()
                    ->first();
                $value->description = $product_data->description;
                $value->booking = $product_data->booking;
                $value->vendor_product_code =
                    $product_data->vendor_product_code;
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $cart;
        $this->result->message = 'Editable order by vendor';

        return response()->json($this->result);
    }

    public function get_dealer_unread_msg($user)
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
                    ->where('role', '3')
                    ->get()
                    ->first();

                if ($sender_data) {
                    $count_notification = Chat::where('chat_from', $sender)
                        ->where('chat_to', $user)
                        ->where('status', '0')
                        ->count();

                    $each_data = [
                        'id' => $sender_data->id,
                        'vendor_name' => $sender_data->vendor_name,
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
        $this->result->message = 'get dealer unread msg';
        $this->result->data = $data;
        return response()->json($this->result);
    }

    public function get_company_vendor_users($code, $user)
    {
        $vendor = Users::where('vendor_code', $code)
            ->where('role', '3')
            ->get()
            ->toArray();

        $user_data = Users::where('id', $user)
            ->get()
            ->first();

        $data = [];

        if ($vendor) {
            foreach ($vendor as $value) {
                $sender = $value['id'];
                $sender_data = Users::where('id', $sender)
                    ->get()
                    ->first();

                $count_notification = Chat::where('chat_from', $sender)
                    ->where('chat_to', $user)
                    ->where('status', '0')
                    ->count();

                $each_data = [
                    'id' => $sender_data['id'],
                    'first_name' => $value['first_name'],
                    'last_name' => $value['last_name'],
                    'full_name' => $value['full_name'],
                    'email' => $value['email'],
                    'notification' => $count_notification,
                ];

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

                // $each_data = [
                //     'id' => $value['id'],
                //     'first_name' => $value['first_name'],
                //     'last_name' => $value['last_name'],
                //     'full_name' => $value['full_name'],
                //     'email' => $value['email'],
                //     'notification' => $count_notification,
                // ];

                array_push($data, $each_data);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get all dealer';
        $this->result->data = $data;
        return response()->json($this->result);
    }

    public function get_vendor()
    {
        // $vendors = Vendors::join('promotional_fliers', 'promotional_fliers.vendor_id', '=', 'vendors.vendor_code')
        //     // ->join('products', 'products.id', '=', 'cart.product_id');
        //     ->select('vendors.*', 'promotional_fliers.*')->get();

        $vendors = Vendors::all();

        // foreach($vendors as $vendor){
        //     $vendor->promotional_flier = PromotionalFlier::where('vendor_id', $vendor->vendor_code)->get();
        // }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $vendors;
        $this->result->message = 'all Vendors';

        return response()->json($this->result);
    }

    public function get_dealer_coworkers($code, $user)
    {
        $dealers = Users::where('account_id', $code)
            ->get()
            ->toArray();

        $user_data = Users::where('id', $user)
            ->get()
            ->first();

        $data = [];

        if ($dealers) {
            foreach ($dealers as $value) {
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
        $this->result->message = 'get all dealers user coworkers';
        $this->result->data = $data;
        return response()->json($this->result);
    }

    public function add_item_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'dealer' => 'required',
            'product_array' => 'required',
        ]);

        // $product = array([
        //     'product_id' => $product_id,
        //     'qty' => $quantity,
        //     'price' => $price,
        //     'unit_price' => $unit_price,
        // ], );

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $uid = $request->uid;
            $atlas_id = $request->atlas_id;
            $dealer = $request->dealer;
            $vendor = $request->vendor;

            // $product_id = $request->product_id;
            // $qty = $request->qty;
            // $price = $request->price;
            // $unit_price = $request->unit_price;

            // lets get the items from the array
            $product_array = $request->input('product_array');

            // return gettype(json_decode($product_array));
            // return json_decode($product_array);

            if (count(json_decode($product_array)) > 0 && $product_array) {
                $decode_product_array = json_decode($product_array);

                // return count($decode_product_array);

                $array_check = [];

                foreach ($decode_product_array as $product) {
                    // update to the db
                    array_push(
                        $array_check,
                        Cart::where('dealer', $dealer)
                            ->where('atlas_id', $product->atlas_id)
                            ->exists()
                    );
                    if (
                        Cart::where('dealer', $dealer)
                            ->where('atlas_id', $product->atlas_id)
                            ->exists()
                    ) {
                        $this->result->status = true;
                        $this->result->status_code = 404;
                        $this->result->message = 'item has been added already';
                        return response()->json($this->result);
                        // break;
                        /// break;
                        // $this->result->status = true;
                        // $this->result->status_code = 404;
                        // $this->result->message = 'item has been added already';
                    } else {
                        $save = Cart::create([
                            'uid' => $uid,
                            'atlas_id' => $product->atlas_id,
                            'dealer' => $dealer,
                            'groupings' => $product->groupings,
                            'vendor' => $product->vendor_id,
                            'product_id' => $product->product_id,
                            'qty' => $product->qty,
                            'price' => $product->price,
                            'unit_price' => $product->unit_price,
                            'type' => $product->type,
                        ]);

                        if (!$save) {
                            $this->result->status = false;
                            $this->result->status_code = 500;
                            $this->result->data = $product;
                            $this->result->message =
                                'sorry we could not save this item to cart';
                        }

                        $this->result->status = true;
                        $this->result->status_code = 200;
                        $this->result->message = 'item Added to cart';
                    }
                }

                return response()->json($this->result);
                // return $array_check;
            }
        }
    }

    public function quick_order_filter_atlasid($id)
    {
        if (Products::where('atlas_id', $id)->exists()) {
            $item = Products::where('atlas_id', $id)
                ->get()
                ->first();

            $vendor_code = $item->vendor_code;
            $vendor_data = Vendors::where('vendor_code', $vendor_code)
                ->get()
                ->first();

            $item->vendor_name = $vendor_data->vendor_name;

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'get products with atlas id';
            $this->result->data = $item;
        } else {
            $this->result->status = false;
            $this->result->status_code = 404;
            $this->result->message = 'product not found';
        }

        return response()->json($this->result);
    }

    public function dealer_get_vendor_products($code)
    {
        if (
            Products::where('vendor', $code)
                ->where('status', '1')
                ->exists()
        ) {
            $vendor_products = Products::where('vendor', $code)
                ->where('status', '1')
                ->get();

            foreach ($vendor_products as $value) {
                $value->spec_data = json_decode($value->spec_data);
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $vendor_products;
            $this->result->message = 'all Vendor Products Data';
        } else {
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = [];
            $this->result->message = 'no product found';
        }

        return response()->json($this->result);
    }

    public function get_vendor_products($code)
    {
        if (
            Products::where('vendor', $code)
                ->where('status', '1')
                ->exists()
        ) {
            $vendor_products = Products::where('vendor', $code)
                ->where('status', '1')
                ->get();
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $vendor_products;
            $this->result->message = 'all Vendor Products Data';
        } else {
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = [];
            $this->result->message = 'no product found';
        }

        return response()->json($this->result);
    }

    public function dealer_dashboard($account)
    {
        // completed orders are the list of vendors that you have ordered from

        // fetch all vendors
        $all_vendors = Vendors::all();

        $fetch_all_vendor_codes = $all_vendors->pluck('vendor_code')->toArray();

        // fetch all the orders
        $completed_orders_vendors = Cart::where('dealer', $account)
            ->where('status', 1)
            ->groupBy('vendor')
            ->pluck('vendor')
            ->toArray();

        $all_uncompleted_orders_vendors = DB::table('vendors')
            ->whereNotIn('vendor_code', $completed_orders_vendors)
            ->where('status', 1)
            ->pluck('vendor_code')
            ->toArray();

        // return count($all_uncompleted_orders_vendors) . " => completed => " . count($completed_orders_vendors);

        // group them by vendor
        $completed_orders = Cart::where('dealer', $account)
            ->where('status', '1')
            ->count();

        $new_products = Products::where('check_new', '1')->count();
        $show_total = Cart::where('dealer', $account)
            ->where('status', 1)
            ->sum('price');

        $order_remaining = Vendors::count();

        $this->result->status = true;
        $this->result->status_code = 200;

        $this->result->data->completed_orders = count(
            $completed_orders_vendors
        );
        $this->result->data->new_products = $new_products;
        $this->result->data->show_total = $show_total;
        $this->result->data->order_remaining = count(
            $all_uncompleted_orders_vendors
        );
        $this->result->message = 'Dealer Dashboard Data';

        return response()->json($this->result);
    }

    // talks about the vendors that dealer has not ordered from
    public function fetch_orders_remaining($account)
    {
        $all_vendors = Vendors::all();

        if (!$all_vendors) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch all the vendors";
            return response()->json($this->result);
        }

        $completed_orders_vendors = Cart::where('dealer', $account)
            ->where('status', 1)
            ->groupBy('vendor')
            ->pluck('vendor')
            ->toArray();

        $all_uncompleted_orders_vendors = DB::table('vendors')
            ->whereNotIn('vendor_code', $completed_orders_vendors)
            ->where('status', 1)
            ->get();

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data->count = count($all_uncompleted_orders_vendors);
        $this->result->data->order_remaining = $all_uncompleted_orders_vendors;
        $this->result->message = 'Orders Remaining fetched successfully';
        return response()->json($this->result);
    }

    // fetch all the vendors that have new products
    public function fetch_vendors_new_products()
    {
        // fetch all the vendors
        $all_vendors = Vendors::all();

        if (!$all_vendors) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch all the vendors";
            return response()->json($this->result);
        }

        $fetch_all_vendor_codes = $all_vendors->pluck('vendor_code')->toArray();

        // fetch all the new products
        $fetch_new_products = Products::where('check_new', '1')
            ->groupby('products.vendor_code')
            ->pluck('vendor_code')
            ->toArray();
        // ->join('vendors', 'vendors.vendor_code', '=', 'products.vendor_code')
        // ->groupby('vendors.vendor_code')
        // ->select('vendors.*')->get();
        // return $fetch_new_products;

        $all_vendors_with_new_products = DB::table('vendors')
            ->whereIn('vendor_code', $fetch_new_products)
            ->where('status', 1)
            ->get();

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data->count = count($all_vendors_with_new_products);
        $this->result->data->vendors = $all_vendors_with_new_products;
        $this->result->message =
            'Vendors with new products fetched successfully';
        return response()->json($this->result);
    }

    // fetch the sum of order price per dealer per day
    public function fetch_all_orders_per_day($account)
    {
        // fetch all the orders
        $all_orders = Cart::where('dealer',$account)->where('status', '1');
        if(!$all_orders){
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message = "An Error Ocurred, we couldn't fetch all the orders";
            return response()->json($this->result);
        }

        // get all the order dates using group by
        $all_orders_dates = $all_orders->groupBy('created_at')->pluck('created_at')->toArray();
        // format date to be able to compare
        $all_orders_dates = array_map(function ($date) {
            return Carbon::parse($date)->format('Y-m-d');
        }, $all_orders_dates);

        // get all orders per day sum
        $all_orders_per_day = $all_orders->groupBy('created_at')->select(DB::raw('sum(price) as total_price'))->get();

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data->order_count = count($all_orders_per_day);
        $this->result->data->dates = $all_orders_dates;
        $this->result->data->orders = $all_orders_per_day;
        $this->result->message = 'All orders per day fetched successfully';
        return response()->json($this->result);
    }

    public function universal_search($search)
    {
        $vendor = Vendors::where(
            'vendor_code',
            'LIKE',
            '%' . $search . '%'
        )->get();

        $product = Products::where(
            'atlas_id',
            'LIKE',
            '%' . $search . '%'
        )->get();

        $search_result = [
            'products' => count($product) > 0 ? $product : null,
            'vendor' => count($vendor) > 0 ? $vendor : null,
        ];

        // switch (true) {
        //     case $vendor:
        //         $item = Vendors::where('vendor_code', 'LIKE', "%{$search}%")
        //             ->get()
        //             ->first();
        //         break;
        //     case $product:
        //         $item = Products::where('atlas_id', 'LIKE', "%{$search}%")
        //             ->get()
        //             ->first();
        //         break;
        //     default:
        //         $this->result->status = false;
        //         $this->result->status_code = 404;
        //         $this->result->message = 'not found';
        //         break;
        // }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'search products by vendor code or atlas_id';
        $this->result->data = $search_result;
        return response()->json($this->result);
    }

    public function create_report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required',
            'description' => 'required',
            // 'company_name' => 'required',
            'user_id' => 'required',
            // 'photo' => ['mimes:pdf,docx,jpeg,jpg']
            // 'photo' => 'mimes:image/jpeg,image/png,image/svg+xml,application/xml',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            if ($request->hasFile('file')) {
                $filenameWithExt = $request
                    ->file('file')
                    ->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request
                    ->file('file')
                    ->getClientOriginalExtension();
                $fileNameToStore =
                    Str::slug($filename, '_', $language = 'en') .
                    '_' .
                    time() .
                    '.' .
                    $extension;
                $filepath =
                    env('APP_URL') .
                    Storage::url(
                        $request
                            ->file('file')
                            ->storeAs('public/reports', $fileNameToStore)
                    );
            }

            $subject = $request->input('subject');
            $description = $request->input('description');
            $dealer_id = $request->input('dealer_id');
            $vendor_id = $request->input('vendor_id');
            $role = $request->input('role');
            $company_name = $request->input('company_name');
            $user_id = $request->input('user_id');
            //company_name, role,vendor_id, subject, description , file_url , ticket_id, created_at, deleted_at, updated_at

            $create_report = Report::create([
                'subject' => $subject ? $subject : null,
                'description' => $description ? $description : null,
                'file_url' => $request->hasFile('file') ? $filepath : null,
                'vendor_id' => $vendor_id ? $vendor_id : null,
                'dealer_id' => $dealer_id ? $dealer_id : null,
                'role' => $role ? $role : null,
                'ticket_id' => Str::random(8),
                'company_name' => $company_name ? $company_name : null,
                'user_id' => $user_id ? $user_id : null,
            ]);

            if (!$create_report) {
                $this->result->status = true;
                $this->result->status_code = 400;
                $this->result->message =
                    'An Error Ocurred, Vendor Addition failed';
                return response()->json($this->result);
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Report Created Successfully';
            return response()->json($this->result);
        }
    }

    public function dealer_faq()
    {
        $fetch_faqs = Faq::orderBy('id', 'desc')
            ->where('status', 1)
            ->where('role', '4')
            ->get();

        if (!$fetch_faqs) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch all the faqs";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_faqs;
        $this->result->message = 'Dealer FAQs fetched Successfully';
        return response()->json($this->result);
    }

    public function fetch_all_faqs()
    {
        $fetch_faqs = Faq::orderBy('id', 'desc')
            ->where('status', 1)
            ->get();

        if (!$fetch_faqs) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch all the faqs";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_faqs;
        $this->result->message = 'FAQs fetched Successfully';
        return response()->json($this->result);
    }

    // fetch all cart items
    public function fetch_all_cart_items($dealer_id)
    {
        // return $dealer_id;
        $fetch_cart_items = Cart::where('dealer', $dealer_id)
            ->join('vendors', 'vendors.vendor_code', '=', 'cart.vendor')
            ->join('products', 'products.id', '=', 'cart.product_id')
            ->join('users', 'users.id', '=', 'cart.uid')
            ->select(
                'vendors.vendor_code as vendor_code',
                'vendors.vendor_name as vendor_name',
                'vendors.role as vendor_role',
                'vendors.role_name as vendor_role_name',
                'vendors.status as vendor_role_name',
                'vendors.created_at as vendor_created_at',
                'vendors.updated_at as vendor_updated_at',
                'products.img as product_img',
                'products.status as product_status',
                'products.description as product_description',
                'products.vendor_code as product_vendor_code',
                'products.vendor_name as products_vendor_name',
                'products.vendor_product_code as product_vendor_product_code',
                'products.xref as product_xref',
                'products.vendor as product_vendor',
                'products.id as product_id',
                'products.atlas_id as product_atlas_id',
                'products.vendor_logo as product_vendor_logo',
                'products.um as product_um',
                'products.regular as product_regular',
                'products.booking as product_booking',
                'products.special as product_special',
                'products.cond as product_cond',
                'products.type as product_type',
                'products.grouping as product_grouping',
                'products.full_desc as product_full_desc',
                'products.spec_data as product_spec_data',
                'products.check_new as product_check_new',
                'products.short_note as product_short_note',
                'products.short_note_url as product_short_note_url',
                'products.created_at as product_created_at',
                'products.updated_at as product_updated_at',
                'cart.*',
                'users.full_name as dealer_full_name',
                'users.email as dealer_email',
                'users.role as dealer_role',
                'users.role_name as dealer_role_name',
                'users.privileged_vendors as dealer_privileged_vendors',
                'users.username as dealer_username',
                'users.account_id as dealer_account_id',
                'users.phone as dealer_phone',
                'users.regular as dealer_regular',
                'users.order_status as dealer_order_status',
                'users.location as dealer_location',
                'users.company_name as dealer_company_name',
                'users.last_login as dealer_last_login',
                'users.login_device as dealer_login_device',
                'users.place_order_date as dealer_place_order_date'
            )
            ->orderby('cart.id', 'desc')
            ->get();

        if (!$fetch_cart_items) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch dealer's cart items";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_cart_items;
        $this->result->message =
            'All cart items for dealer fetched Successfully';
        return response()->json($this->result);
    }

    // adds item to the quick order table
    public function add_quick_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'dealer' => 'required',
            'product_array' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $uid = $request->uid;
            $atlas_id = $request->atlas_id;

            $dealer = $request->dealer;
            $vendor = $request->vendor;

            // lets get the items from the array
            $product_array = $request->input('product_array');

            // return json_decode($product_array);

            if (count(json_decode($product_array)) > 0 && $product_array) {
                $decode_product_array = json_decode($product_array);

                foreach ($decode_product_array as $product) {
                    // update to the db
                    if (
                        QuickOrder::where('dealer', $dealer)
                            ->where('atlas_id', $product->atlas_id)
                            ->exists()
                    ) {
                        $this->result->status = true;
                        $this->result->status_code = 404;
                        $this->result->message = 'item has been added already';
                        break;
                    } else {
                        $save = QuickOrder::create([
                            'uid' => $uid,
                            'atlas_id' => $product->atlas_id,
                            'dealer' => $dealer,
                            'groupings' => $product->groupings,
                            'vendor' => $product->vendor_id,
                            'product_id' => $product->product_id,
                            'qty' => $product->qty,
                            'price' => $product->price,
                            'unit_price' => $product->unit_price,
                            'vendor_no' => $product->vendor_no,
                            'type' => $product->type,
                        ]);

                        if (!$save) {
                            $this->result->status = false;
                            $this->result->status_code = 500;
                            $this->result->data = $product;
                            $this->result->message =
                                'sorry we could not save this item to quick order';
                        }
                    }
                }
            }
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'item Added to cart';
            // }

            return response()->json($this->result);
        }
    }

    // move item to the cart from the quick order
    public function move_quick_order_to_cart(Request $request)
    {
        // validation
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            // 'dealer' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // fetch all items by the uid
            $uid = $request->input('uid');
            $dealer = $request->input('dealer');

            $fetch_all_items_by_uid = QuickOrder::where('uid', $uid)->get();

            if (!$fetch_all_items_by_uid) {
                $this->result->status = true;
                $this->result->status_code = 400;
                $this->result->message =
                    "An Error Ocurred, we couldn't fetch dealer's quick order";
                return response()->json($this->result);
            }

            if (count($fetch_all_items_by_uid) == 0) {
                $this->result->status = true;
                $this->result->status_code = 402;
                $this->result->message =
                    'Sorry no item in the quick order for this  user';
                return response()->json($this->result);
            }

            // return $fetch_all_items_by_uid;
            // `id`, `uid`, `dealer`, `vendor`, `atlas_id`, `product_id`,
            //  `qty`, `price`, `unit_price`, `status`,

            // {
            //     "atlas_id":"12323","vendor_id":"2121",
            //     "groupings":"",
            //     "product_id":"43",
            //     "qty":"56","price":"789.79",
            //     "unit_price":"78"
            // }
            foreach ($fetch_all_items_by_uid as $item) {
                $atlas_id = $item->atlas_id;
                $vendor = $item->vendor;
                $dealer = $item->dealer;
                $product_id = $item->product_id;
                $qty = $item->qty;
                $price = $item->price;
                $unit_price = $item->unit_price;
                $status = $item->status;
                $groupings = $item->groupings;

                if (
                    Cart::where('dealer', $dealer)
                        ->where('atlas_id', $atlas_id)
                        ->exists()
                ) {
                    $this->result->status = true;
                    $this->result->status_code = 404;
                    $this->result->message = 'item has been added already';
                    break;
                } else {
                    $save = Cart::create([
                        'uid' => $uid,
                        'atlas_id' => $atlas_id,
                        'dealer' => $dealer,
                        'groupings' => $groupings,
                        'vendor' => $vendor,
                        'product_id' => $product_id,
                        'qty' => $qty,
                        'price' => trim($price),
                        'unit_price' => trim($unit_price),
                        'status' => $status,
                    ]);

                    if (!$save) {
                        $this->result->status = false;
                        $this->result->status_code = 422;
                        $this->result->data = $item;
                        $this->result->message =
                            'sorry we could not save this item to cart';
                    }
                }
                // delete item from quick order
                $delete_quick_order_item = $item->delete();

                if (!$delete_quick_order_item) {
                    $this->result->status = false;
                    $this->result->status_code = 422;
                    $this->result->data = $item;
                    $this->result->message =
                        'sorry we could not delete quick order item';
                }
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'item moved to cart from quick order';

            return response()->json($this->result);
        }
    }

    // fetch all the quick order items by dealer id
    public function fetch_quick_order_items_dealer_id($dealer_id)
    {
        $fetch_cart_items = QuickOrder::where('dealer', $dealer_id)
            ->orderby('id', 'desc')
            ->get();

        if (!$fetch_cart_items) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch dealer's quick order items";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_cart_items;
        $this->result->message =
            'All quick order items for dealer fetched Successfully';
        return response()->json($this->result);
    }

    // fetch all the quick order by user_id
    public function fetch_quick_order_items_user_id($user_id)
    {
        $fetch_cart_items = QuickOrder::where('uid', $user_id)
            ->join('products', 'products.id', '=', 'quick_order.product_id')
            ->select(
                'products.*',
                'quick_order.id as quick_order_id',
                'quick_order.uid as quick_order_uid',
                'quick_order.dealer as quick_order_dealer',
                'quick_order.vendor as quick_order_vendor',
                'quick_order.atlas_id as quick_order_atlas_id',
                'quick_order.product_id as quick_order_product_id',
                'quick_order.groupings as quick_order_groupings',
                'quick_order.qty as quick_order_qty',
                'quick_order.price as quick_order_price',
                'quick_order.unit_price as quick_order_unit_price',
                'quick_order.status as quick_order_status',
                'quick_order.created_at as quick_order_created_at',
                'quick_order.updated_at as quick_order_updated_at',
                'quick_order.deleted_at as quick_order_deleted_at'
            )
            ->orderby('id', 'desc')
            ->get();

        if (!$fetch_cart_items) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch dealer's quick order items by user id";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_cart_items;
        $this->result->message =
            'All quick order items for user fetched Successfully';
        return response()->json($this->result);
    }

    // delete quick order items by user_id
    public function delete_quick_order_items_user_id($user_id)
    {
        $fetch_cart_items = QuickOrder::where('uid', $user_id)
            ->orderby('id', 'desc')
            ->get();

        if (!$fetch_cart_items) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch dealer's quick order items by user id";
            return response()->json($this->result);
        }

        if (count($fetch_cart_items) == 0) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                'Sorry no quick order items found for user';
            return response()->json($this->result);
        }

        foreach ($fetch_cart_items as $item) {
            $delete_item = $item->delete();
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message =
            'All quick order items for user deleted Successfully';
        return response()->json($this->result);
    }

    // delete quick order items by atlas_id
    public function delete_quick_order_items_atlas_id($user_id, $atlas_id)
    {
        $fetch_cart_items = QuickOrder::where('atlas_id', $atlas_id)
            ->where('uid', $user_id)
            ->get();

        // return $fetch_cart_items;
        if (!$fetch_cart_items) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch dealer's quick order items by atlas id";
            return response()->json($this->result);
        }

        foreach ($fetch_cart_items as $item) {
            $delete_item = $item->delete();
            // $delete_item = $fetch_cart_items->delete();

            if (!$delete_item) {
                $this->result->status = true;
                $this->result->status_code = 400;
                $this->result->message =
                    'Sorry we could not delete the item from the quick order';
                return response()->json($this->result);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message =
            'All quick order items for user deleted Successfully';
        return response()->json($this->result);
    }

    // delete_quick_order_items_dealer_id
    public function delete_quick_order_items_dealer_id($dealer_id)
    {
        $fetch_cart_items = QuickOrder::where('dealer', $dealer_id)
            ->orderby('id', 'desc')
            ->get();

        if (!$fetch_cart_items) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch dealer's quick order items by user id";
            return response()->json($this->result);
        }

        if (count($fetch_cart_items) == 0) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                'Sorry no quick order items found for dealer';
            return response()->json($this->result);
        }

        foreach ($fetch_cart_items as $item) {
            $delete_item = $item->delete();
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message =
            'All quick order items for dealer deleted Successfully';
        return response()->json($this->result);
    }

    // fetch all the quick order items by atlas_id and vendor no

    public function fetch_quick_order_items_atlas_id_vendor_no(
        $atlas_id,
        $vendor_no
    ) {
        // return $atlas_id .  " => " . $vendor_no;
        $fetch_cart_items = QuickOrder::where('quick_order.atlas_id', $atlas_id)
            ->where('quick_order.vendor_no', $vendor_no)
            ->join('vendors', 'vendors.vendor_code', '=', 'quick_order.vendor')
            ->join('products', 'products.id', '=', 'quick_order.product_id')
            ->select(
                'vendors.vendor_code as vendor_code',
                'vendors.vendor_name as vendor_name',
                'vendors.role as vendor_role',
                'vendors.role_name as vendor_role_name',
                'vendors.status as vendor_role_name',
                'vendors.created_at as vendor_created_at',
                'vendors.updated_at as vendor_updated_at',
                'products.img as product_img',
                'products.status as product_status',
                'products.description as product_description',
                'products.vendor_code as product_vendor_code',
                'products.vendor_name as products_vendor_name',
                'products.vendor_product_code as product_vendor_product_code',
                'products.xref as product_xref',
                'products.vendor as product_vendor',
                'products.id as product_id',
                'products.atlas_id as product_atlas_id',
                'products.vendor_logo as product_vendor_logo',
                'products.um as product_um',
                'products.regular as product_regular',
                'products.booking as product_booking',
                'products.special as product_special',
                'products.cond as product_cond',
                'products.type as product_type',
                'products.grouping as product_grouping',
                'products.full_desc as product_full_desc',
                'products.spec_data as product_spec_data',
                'products.check_new as product_check_new',
                'products.short_note as product_short_note',
                'products.short_note_url as product_short_note_url',
                'products.created_at as product_created_at',
                'products.updated_at as product_updated_at',
                'quick_order.*'
            )
            ->get();

        // return $fetch_cart_items;
        if (!$fetch_cart_items) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch dealer's quick order items by atlas id";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_cart_items;
        $this->result->message =
            'All quick order items with atlas id and vendor no fetched successfully';
        return response()->json($this->result);
    }

    // fetch all the cart order items by account and vendor id
    public function fetch_order_items_atlas_id_vendor_id($dealer_id, $vendor_id)
    {
        $fetch_cart_items = Cart::where('cart.dealer', $dealer_id)
            ->where('cart.vendor', $vendor_id)
            ->join('vendors', 'vendors.vendor_code', '=', 'cart.vendor')
            ->join('products', 'products.id', '=', 'cart.product_id')
            ->select(
                'vendors.vendor_code as vendor_code',
                'vendors.vendor_name as vendor_name',
                'vendors.role as vendor_role',
                'vendors.role_name as vendor_role_name',
                'vendors.status as vendor_role_name',
                'vendors.created_at as vendor_created_at',
                'vendors.updated_at as vendor_updated_at',
                'products.img as product_img',
                'products.status as product_status',
                'products.description as product_description',
                'products.vendor_code as product_vendor_code',
                'products.vendor_name as products_vendor_name',
                'products.vendor_product_code as product_vendor_product_code',
                'products.xref as product_xref',
                'products.vendor as product_vendor',
                'products.id as product_id',
                'products.atlas_id as product_atlas_id',
                'products.vendor_logo as product_vendor_logo',
                'products.um as product_um',
                'products.regular as product_regular',
                'products.booking as product_booking',
                'products.special as product_special',
                'products.cond as product_cond',
                'products.type as product_type',
                'products.grouping as product_grouping',
                'products.full_desc as product_full_desc',
                'products.spec_data as product_spec_data',
                'products.check_new as product_check_new',
                'products.short_note as product_short_note',
                'products.short_note_url as product_short_note_url',
                'products.created_at as product_created_at',
                'products.updated_at as product_updated_at',
                'cart.*'
            )
            ->orderby('cart.atlas_id', 'desc')
            ->get();

        foreach ($fetch_cart_items as $value) {
            $value->product_spec_data = json_decode($value->product_spec_data);
        }

        // return $fetch_cart_items;
        if (!$fetch_cart_items) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch dealer's order items by atlas id / vendor id";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_cart_items;
        $this->result->message =
            'All dealer\'s order items with atlas id and vendor id fetched successfully';
        return response()->json($this->result);
    }

    // delete quick order items by atlas_id
    public function delete_order_items_atlas_id_user_id($atlas_id, $user_id)
    {
        // return $user_id . " => " . $atlas_id;

        $fetch_cart_items = Cart::where('atlas_id', $atlas_id)
            ->where('uid', $user_id)
            ->get();

        if (!$fetch_cart_items) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch dealer's order items by atlas id and user id";
            return response()->json($this->result);
        }

        foreach ($fetch_cart_items as $item) {
            $delete_item = $item->delete();
            // $delete_item = $fetch_cart_items->delete();

            if (!$delete_item) {
                $this->result->status = true;
                $this->result->status_code = 400;

                $this->result->data = $item;
                $this->result->message =
                    'Sorry we could not delete the item from the order';
                return response()->json($this->result);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message =
            'All order items for user deleted Successfully';
        return response()->json($this->result);
    }
}
