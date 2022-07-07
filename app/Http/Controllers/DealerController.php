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
                        ]);

                        if (!$save) {
                            $this->result->status = false;
                            $this->result->status_code = 500;
                            $this->result->data = $product;
                            $this->result->message = 'sorry we could not save this item to cart';
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
            "%{$search}%"
        )->exists();
        $product = Products::where('atlas_id', 'LIKE', "%{$search}%")->exists();

        switch (true) {
            case $vendor:
                $item = Vendors::where('vendor_code', 'LIKE', "%{$search}%")
                    ->get()
                    ->first();

                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'get products with atlas id';
                $this->result->data = $item;
                break;

            case $product:
                $item = Products::where('atlas_id', 'LIKE', "%{$search}%")
                    ->get()
                    ->first();

                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'get products with atlas id';
                $this->result->data = $item;
                break;

            default:
                $this->result->status = false;
                $this->result->status_code = 404;
                $this->result->message = 'not found';

                break;
        }

        return response()->json($this->result);
    }

    public function create_report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required',
            'description' => 'required',
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
            if ($request->hasFile('photo')) {
                $filenameWithExt = $request
                    ->file('photo')
                    ->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request
                    ->file('photo')
                    ->getClientOriginalExtension();
                $fileNameToStore = Str::slug($filename, '_', $language = 'en') . '_' . time() . '.' . $extension;
                $filepath =
                    env('APP_URL') .
                    Storage::url(
                        $request
                            ->file('photo')
                            ->storeAs('public/reports', $fileNameToStore)
                    );
            }

            $subject = $request->input('subject');
            $description = $request->input('description');
            $dealer_id = $request->input('dealer_id');
            $vendor_id = $request->input('vendor_id');
            $role = $request->input('role');

            // subject, description , file_url , ticket_id, created_at, deleted_at, updated_at

            // return [$subject,$description,$request->hasFile('photo'),$filepath];
            $create_report = Report::create([
                'subject' => $subject ? $subject : null,
                'description' => $description ? $description : null,
                'file_url' => $request->hasFile('photo') ? $filepath : null,
                'vendor_id' => $vendor_id ? $vendor_id : null,
                'dealer_id' => $dealer_id ? $dealer_id : null,
                'role' => $role ? $role : null,
                'ticket_id' => Str::random(8)
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
}
