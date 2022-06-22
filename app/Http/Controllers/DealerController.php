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

    public function add_item_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'dealer' => 'required',
            'vendor' => 'required',
            'atlas_id' => 'required',
            'product_id' => 'required',
            'qty' => 'required',
            'price' => 'required',
            'unit_price' => 'required',
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
            $product_id = $request->product_id;
            $qty = $request->qty;
            $price = $request->price;
            $unit_price = $request->unit_price;

            if (
                Cart::where('dealer', $dealer)
                    ->where('atlas_id', $atlas_id)
                    ->exists()
            ) {
                $this->result->status = true;
                $this->result->status_code = 404;
                $this->result->message = 'item has been added already';
            } else {
                // update to the db
                $save = Cart::create([
                    'uid' => $uid,
                    'atlas_id' => $atlas_id,
                    'dealer' => $dealer,
                    'vendor' => $vendor,
                    'product_id' => $product_id,
                    'qty' => $qty,
                    'price' => $price,
                    'unit_price' => $unit_price,
                ]);

                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'item Added to cart';
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
            'photo' => 'mimes:pdf,doc,docx,xls,jpg,jpeg,png,gif',
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
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
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

            // subject, description , file_url , ticket_id, created_at, deleted_at, updated_at
            $create_report = Report::create([
                'subject' => $subject ? $subject : null,
                'description' => $description ? $description : null,
                'file_url' => $request->hasFile('file') ? $filepath : null,
                'ticket_id' => Str::random(8),
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
