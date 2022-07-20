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
                'products.description',
                'products.img',
                'products.status as product_status',
                'products.description as product_description',
                'products.vendor_code as product_vendor_code',
                'products.vendor_name as products_vendor_name',
                'products.vendor_product_code as product_vendor_product_code',
                'products.xref as product_xref',
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

    public function get_ordered_vendor($code)
    {
        $dealer_cart = Cart::where('dealer', $code)->get();
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
        $vendors = Vendors::all();
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

            // return json_decode($product_array);

            if (count(json_decode($product_array)) > 0 && $product_array) {
                $decode_product_array = json_decode($product_array);

                foreach ($decode_product_array as $product) {
                    // update to the db
                    if (
                        Cart::where('dealer', $dealer)
                        ->where('atlas_id', $product->atlas_id)
                        ->exists()
                    ) {
                        $this->result->status = true;
                        $this->result->status_code = 404;
                        $this->result->message = 'item has been added already';
                        break;
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
            }

            return response()->json($this->result);
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
        $completed_orders = Cart::where('dealer', $account)
            ->where('status', '1')
            ->count();

        $new_products = Products::where('check_new', '1')->count();
        $show_total = Cart::where('dealer', $account)->sum('price');

        $order_remaining = Vendors::count();

        $this->result->status = true;
        $this->result->status_code = 200;

        $this->result->data->completed_orders = $completed_orders;
        $this->result->data->new_products = $new_products;
        $this->result->data->show_total = $show_total;
        $this->result->data->order_remaining = $order_remaining;

        $this->result->message = 'Dealer Dashboard Data';
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
        $fetch_cart_items = Cart::where('dealer', $dealer_id)
            ->orderby('id', 'desc')
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
                "Sorry no quick order items found for user";
            return response()->json($this->result);
        }

        foreach ($fetch_cart_items as $item) {
            $delete_item =  $item->delete();
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
            $delete_item =  $item->delete();
            // $delete_item = $fetch_cart_items->delete();

            if (!$delete_item) {
                $this->result->status = true;
                $this->result->status_code = 400;
                $this->result->message = "Sorry we could not delete the item from the quick order";
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
                "Sorry no quick order items found for dealer";
            return response()->json($this->result);
        }

        foreach ($fetch_cart_items as $item) {
            $delete_item =  $item->delete();
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message =
            'All quick order items for dealer deleted Successfully';
        return response()->json($this->result);
    }

    // fetch all the quick order items by atlas_id and vendor no
    public function fetch_quick_order_items_atlas_id_vendor_no($atlas_id, $vendor_no)
    {
        $fetch_cart_items = QuickOrder::where('atlas_id', $atlas_id)
            ->where('vendor_no', $vendor_no)
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
                'cart.*'
            )
            ->get();

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
}
