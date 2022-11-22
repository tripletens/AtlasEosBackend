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
use App\Models\SystemSettings;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Models\ProgramCountdown;

use App\Models\VendorOrderNotify;
use App\Models\SpecialOrder;
use App\Models\ProductModel;

set_time_limit(250000000000000000);

class WebViewController extends Controller
{
    //

    public function __construct()
    {
        // set timeout limit
        set_time_limit(2500000000000000);

        $this->result = (object) [
            'status' => false,
            'status_code' => 200,
            'message' => null,
            'data' => (object) null,
            'token' => null,
            'debug' => null,
        ];
    }

    public function generate_vendor_view_summary(
        $dealer,
        $vendor,
        $lang,
        $create_time
    ) {
        $over_all_total = 0;

        $dealer_cart = Cart::where('vendor', $vendor)
            ->where('dealer', $dealer)
            ->get();

        $res_data = [];

        $vendor_data = Vendors::where('vendor_code', $vendor)
            ->get()
            ->first();
        $dealer_data = Users::where('account_id', $dealer)
            ->get()
            ->first();

        if ($dealer_cart) {
            foreach ($dealer_cart as $value) {
                $atlas_id = $value->atlas_id;
                $pro_data = Products::where('atlas_id', $atlas_id)
                    ->get()
                    ->first();

                $over_all_total += floatval($value->price);

                $data = [
                    // 'dealer_rep_name' =>
                    //     $dealer_data->full_name . ' ' . $dealer_data->last_name,
                    'qty' => $value->qty,
                    'atlas_id' => $atlas_id,
                    'vendor_product_code' => isset(
                        $pro_data->vendor_product_code
                    )
                        ? $pro_data->vendor_product_code
                        : null,
                    'special' => isset($pro_data->booking)
                        ? $pro_data->booking
                        : null,
                    'desc' => isset($pro_data->description)
                        ? $pro_data->description
                        : null,
                    'total' => $value->price,
                ];

                array_push($res_data, $data);
            }
        }

        $pdf_data = [
            'data' => $res_data,
            'dealer' => $dealer_data ? $dealer_data : null,
            'vendor' => $vendor_data ? $vendor_data : null,
            'grand_total' => $over_all_total,
            'lang' => $lang,
            'printed_at' => $create_time,
        ];

        $pdf = PDF::loadView('vendor-purchasers-summary', $pdf_data);
        return $pdf->stream('vendor-purchasers-summary.pdf');
    }

    public function generate_vendor_purchasers_summary(
        $user,
        $dealer,
        $vendor,
        $lang,
        $create_time
    ) {
        $dealer_cart = Cart::where('uid', $user)
            ->where('vendor', $vendor)
            ->get();

        $res_data = [];

        $over_all_total = 0;

        $vendor_data = Vendors::where('vendor_code', $vendor)
            ->get()
            ->first();
        $dealer_data = Users::where('id', $user)
            ->get()
            ->first();

        if ($dealer_cart) {
            foreach ($dealer_cart as $value) {
                $atlas_id = $value->atlas_id;
                $pro_data = Products::where('atlas_id', $atlas_id)
                    ->get()
                    ->first();

                $over_all_total += floatval($value->price);

                $data = [
                    'dealer_rep_name' =>
                        $dealer_data->full_name . ' ' . $dealer_data->last_name,
                    'user_id' => $user,
                    'qty' => $value->qty,
                    'atlas_id' => $atlas_id,
                    'vendor_product_code' => isset(
                        $pro_data->vendor_product_code
                    )
                        ? $pro_data->vendor_product_code
                        : null,
                    'special' => isset($pro_data->booking)
                        ? $pro_data->booking
                        : null,
                    'desc' => isset($pro_data->description)
                        ? $pro_data->description
                        : null,
                    'total' => $value->price,
                ];

                array_push($res_data, $data);
            }
        }

        $pdf_data = [
            'data' => $res_data,
            'dealer' => $dealer_data ? $dealer_data : null,
            'vendor' => $vendor_data ? $vendor_data : null,
            'grand_total' => $over_all_total,
            'lang' => $lang,
            'printed_at' => $create_time,
        ];

        $pdf = PDF::loadView('vendor-purchasers-summary', $pdf_data);
        return $pdf->stream('vendor-purchasers-summary.pdf');
    }

    public function generate_sales_rep_purchasers_pdf($user)
    {
        $dealership_codes = [];
        $res_data = [];
        $selected_user = Users::where('id', $user)
            ->get()
            ->first();

        $privilaged_dealers = $selected_user->privileged_dealers;
        if ($privilaged_dealers != null) {
            $separator = explode(',', $privilaged_dealers);

            foreach ($separator as $value) {
                if (!in_array($value, $dealership_codes)) {
                    array_push($dealership_codes, $value);
                }
            }

            foreach ($dealership_codes as $value) {
                $value = trim($value);
                $vendor_purchases = Cart::where('dealer', $value)->get();

                if (count($vendor_purchases) > 0) {
                    foreach ($vendor_purchases as $cart_data) {
                        $user_id = $cart_data->uid;
                        $user_data = Users::where('id', $user_id)
                            ->get()
                            ->first();

                        $sum_user_total = Cart::where('uid', $user_id)
                            ->get()
                            ->sum('price');

                        if ($user_data) {
                            $data = [
                                'account_id' => $user_data->account_id,
                                'dealer_name' => $user_data->company_name,
                                'user' => $user_id,
                                'purchaser_name' =>
                                    $user_data->first_name .
                                    ' ' .
                                    $user_data->last_name,
                                'amount' => $sum_user_total,
                            ];

                            array_push($res_data, $data);
                        }
                    }
                }
            }

            /////// Sorting //////////
            usort($res_data, function ($a, $b) {
                //Sort the array using a user defined function
                return $a['amount'] > $b['amount'] ? -1 : 1; //Compare the scores
            });

            $res_data = array_map(
                'unserialize',
                array_unique(array_map('serialize', $res_data))
            );

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Purchasers by Dealers';
            $this->result->data = $res_data;
            return response()->json($this->result);
        }
    }

    public function generate_sales_summary_pdf($code, $lang, $create_time)
    {
        $vendor_data = Vendors::where('vendor_code', $code)
            ->get()
            ->first();

        $vendor_purchases = Cart::where('vendor', $code)
            ->orderBy('xref', 'asc')
            ->get();
        $res_data = [];
        $atlas_id_checker = [];

        $over_all_total = 0;

        if ($vendor_purchases) {
            foreach ($vendor_purchases as $value) {
                $atlas_id = $value->atlas_id;
                if (!in_array($atlas_id, $atlas_id_checker)) {
                    array_push($atlas_id_checker, $atlas_id);
                }
            }
        }

        foreach ($atlas_id_checker as $value) {
            $item_cart = Cart::where('vendor', $code)
                ->where('atlas_id', $value)
                ->get();

            $total_qty = 0;
            $total_price = 0;

            if ($item_cart) {
                foreach ($item_cart as $kvalue) {
                    $total_qty += intval($kvalue->qty);
                    $total_price += intval($kvalue->price);
                }

                $product = Products::where('atlas_id', $value)
                    ->get()
                    ->first();

                $data = [
                    'pro_id' => isset($product->id) ? $product->id : null,
                    'qty' => $total_qty,
                    'atlas_id' => isset($product->atlas_id)
                        ? $product->atlas_id
                        : null,
                    'vendor' => isset($product->vendor_product_code)
                        ? $product->vendor_product_code
                        : null,
                    'description' => isset($product->description)
                        ? $product->description
                        : null,
                    'regular' => isset($product->regular)
                        ? $product->regular
                        : null,
                    'booking' => isset($product->booking)
                        ? $product->booking
                        : null,
                    'total' => $total_price,
                ];

                $data = (object) $data;

                $over_all_total += $total_price;

                array_push($res_data, $data);
            }
        }

        $res = $this->sort_according_atlas_id($res_data);

        $pdf_data = [
            'data' => $res,
            'vendor' => $vendor_data ? $vendor_data : null,
            'grand_total' => $over_all_total,
            'lang' => $lang,
            'printed_at' => $create_time,
        ];

        /////  return $pdf_data;

        $pdf = PDF::loadView('vendor-summary-sales', $pdf_data);
        return $pdf->stream('vendor-summary-sales.pdf');
        // return $pdf->download('dealership.pdf');
    }

    public function generate_special_order_pdf($dealer, $lang, $current_time)
    {
        // $check_special_order_exists = SpecialOrder::where(
        //     'dealer_id',
        //     $dealer
        // )->get();

        $check_special_order = DB::table('special_orders')
            ->join(
                'vendors',
                'vendors.vendor_code',
                '=',
                'special_orders.vendor_code'
            )
            ->join('users', 'users.id', '=', 'special_orders.uid')
            ->where('special_orders.dealer_id', $dealer)
            ->select('vendors.*', 'special_orders.*', 'users.full_name')
            ->get();

        $dealer_ship = Dealer::where('dealer_code', $dealer)
            ->get()
            ->first();

        foreach ($check_special_order as $value) {
            $uid = $value->uid;
            $user = Users::where('id', $uid)
                ->get()
                ->first();

            $check_special_order->full_name = isset($user->full_name)
                ? $user->full_name
                : null;
        }

        ////// return $check_special_order;

        $pdf_data = [
            'data' => $check_special_order,
            'dealer' => $dealer_ship ? $dealer_ship : null,
            'lang' => $lang,
            'printed_at' => $current_time,
        ];

        $d_name = isset($dealer_ship->dealer_name)
            ? $dealer_ship->dealer_name
            : null;
        $d_code = isset($dealer_ship->dealer_code)
            ? $dealer_ship->dealer_code
            : null;
        $filename = $d_name . $d_code . 'special-order';

        $pdf = PDF::loadView('special-orders-pdf', $pdf_data);
        return $pdf->stream($filename . '.pdf');
        // return $pdf->download('dealership.pdf');
    }

    public function translateToLocal($languagecode, $text)
    {
        if ($languagecode == 'en') {
            //no conversion in case of english to english
            return $text;
        }

        $tr = new GoogleTranslate(); // Translates to 'en' from auto-detected language by default
        $tr->setSource('en'); // Translate from English
        $tr->setSource(); // Detect language automatically
        $tr->setTarget('fr'); // Translate to Georgian
        if ($text != null && $text != '') {
            return $tr->translate($text);
        } else {
            return $text;
        }
    }

    public function generate_pdf($dealer, $lang, $current_time)
    {
        $data = [
            'title' => 'Welcome to Tutsmake.com',
            'date' => date('m/d/Y'),
        ];

        $vendors = [];
        $res_data = [];
        $grand_total = 0;

        $dealer_data = Cart::where('dealer', $dealer)->get();
        $vendor_data = Vendors::where('dealer', $dealer)->get();

        $dealer_ship = Dealer::where('dealer_code', $dealer)
            ->get()
            ->first();

        foreach ($vendor_data as $value) {
            $vendor_code = $value->vendor_code;
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
                ->orderBy('xref', 'asc')
                ->get();

            $total = 0;

            foreach ($cart_data as $value) {
                $total += $value->price;
                $atlas_id = $value->atlas_id;
                $pro_data = Products::where('atlas_id', $atlas_id)
                    ->get()
                    ->first();

                if (isset($pro_data->description)) {
                    $value->description = $this->translateToLocal(
                        $lang,
                        isset($pro_data->description)
                            ? $pro_data->description
                            : 'null'
                    );
                }

                $value->vendor_product_code = $this->translateToLocal(
                    $lang,
                    isset($pro_data->vendor_product_code)
                        ? $pro_data->vendor_product_code
                        : null
                );
            }

            $data = [
                'vendor_code' => isset($vendor_data->vendor_code)
                    ? $vendor_data->vendor_code
                    : null,
                'vendor_name' => isset($vendor_data->vendor_name)
                    ? $vendor_data->vendor_name
                    : null,
                'total' => floatval($total),
                'data' => $cart_data,
            ];

            $grand_total += $total;

            array_push($res_data, $data);
        }

        usort($res_data, function ($object1, $object2) {
            // $ex1 = explode('-', $object1->atlas_id);
            // $ex2 = explode('-', $object2->atlas_id);

            // if ($ex1[0] > $ex2[0]) {
            //     return true;
            // } else {
            //     return false;
            // }
            return $object1['vendor_name'] > $object2['vendor_name'];
        });

        $pdf_data = [
            'data' => $res_data,
            'dealer' => $dealer_ship ? $dealer_ship : null,
            'grand_total' => $grand_total,
            'lang' => $lang,
            'printed_at' => $current_time,
        ];

        $d_name = isset($dealer_ship->dealer_name)
            ? $dealer_ship->dealer_name
            : null;
        $d_code = isset($dealer_ship->dealer_code)
            ? $dealer_ship->dealer_code
            : null;
        $filename = $d_name . $d_code;

        /////  return $pdf_data;

        $pdf = PDF::loadView('dealership-pdf', $pdf_data);
        return $pdf->stream($filename . '.pdf');
        // return $pdf->download('dealership.pdf');
    }
}
