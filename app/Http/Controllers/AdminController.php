<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Admin;
use App\Models\Dealer;
use App\Models\Users;
use App\Models\Vendors;
use App\Models\Faq;
use App\Models\Seminar;

// use Maatwebsite\Excel\Facades\Excel;
// use App\Imports\ProductsImport;
// use App\Http\Helpers;
use App\Models\Products;
// use App\Models\Category;
// use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Storage;
// use App\Models\Branch;
use App\Models\PromotionalFlier;
use App\Models\Cart;
use App\Models\Chat;
use App\Models\Report;
use App\Models\User;
use App\Models\ProgramCountdown;
use App\Models\ReportReply;
use App\Models\ProgramNotes;

use App\Models\PriceOverideReport;
use App\Models\SpecialOrder;
use App\Models\UserStatus;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App;

use DateTime;
// use App\Models\Catalogue_Order;
// use Illuminate\Support\Facades\Mail;
// use App\Mail\SendDealerDetailsMail;

// use App\Models\DealerCart;
// use App\Models\ServiceParts;
// use App\Models\CardedProducts;
// use App\Models\PromotionalCategory;

// use Barryvdh\DomPDF\Facade as PDF;
set_time_limit(25000000);

class AdminController extends Controller
{
    public function __construct()
    {
        // set timeout limit
        set_time_limit(25000000);
        $this->result = (object) [
            'status' => false,
            'status_code' => 200,
            'message' => null,
            'data' => (object) null,
            'token' => null,
            'debug' => null,
        ];
    }

    ///// Permission Role Access
    // admin == 1
    // branch manager == 2
    // vendor == 3
    // dealer == 4
    // inside sales == 5
    // outside == 6

    public function view_dealer_summary($code)
    {
        $vendors = [];
        $res_data = [];
        $grand_total = 0;

        $dealer_data = Cart::where('dealer', $code)->get();
        $dealer_ship = Dealer::where('dealer_code', $code)
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
                ->where('dealer', $code)
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

        $pdf_data = [
            'data' => $res_data,
            'dealer' => $dealer_ship,
            'grand_total' => $grand_total,
        ];

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $pdf_data;
        $this->result->message = 'View Dealer Summary';
        return response()->json($this->result);
    }

    public function dealer_single_summary($code)
    {
        $dealers = Dealer::where('dealer_code', $code)->get();
        $dealer_count = Dealer::count();
        $total_sales = 0;
        $res_data = [];

        if ($dealers) {
            foreach ($dealers as $value) {
                $dealer_code = $value->dealer_code;
                $dealer_name = $value->dealer_name;
                $dealer_sales = Cart::where('dealer', $dealer_code)->sum(
                    'price'
                );
                $total_sales += Cart::where('dealer', $dealer_code)->sum(
                    'price'
                );

                $data = [
                    'dealer_name' => $dealer_name,
                    'dealer_code' => $dealer_code,
                    'sales' => $dealer_sales,
                ];

                array_push($res_data, $data);
            }
        }

        /////// Sorting //////////
        usort($res_data, function ($a, $b) {
            //Sort the array using a user defined function
            return $a['sales'] > $b['sales'] ? -1 : 1; //Compare the scores
        });

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $res_data;
        $this->result->message = 'Dealer Single Summary';
        return response()->json($this->result);
    }

    public function get_users_status()
    {
        $users_status = UserStatus::all();
        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'All vendor users status';
        $this->result->data = $users_status;
        return response()->json($this->result);
    }

    public function activate_all_vendors()
    {
        Users::where('role', '3')->update(['status' => 1]);
        $users_status = UserStatus::where('role', '3')->update(['status' => 1]);

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'All vendor users has been activated';
        $this->result->data = $users_status;

        return response()->json($this->result);
    }

    public function deactivate_all_vendors()
    {
        Users::where('role', '3')->update(['status' => 0]);
        $users_status = UserStatus::where('role', '3')->update(['status' => 0]);

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'All vendor users has been deactivated';
        return response()->json($this->result);
    }

    public function activate_all_dealers()
    {
        Users::where('role', '4')->update(['status' => 1]);
        $users_status = UserStatus::where('role', '4')->update(['status' => 1]);

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'All Dealer users has been activated';
        return response()->json($this->result);
    }

    public function deactivate_all_dealers()
    {
        Users::where('role', '4')->update(['status' => 0]);
        $users_status = UserStatus::where('role', '4')->update(['status' => 0]);

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'All Dealer users has been deactivated';
        return response()->json($this->result);
    }

    public function vendor_summary($code)
    {
        $vendor_purchases = Cart::where('vendor', $code)->get();
        $res_data = [];
        $users = [];
        foreach ($vendor_purchases as $value) {
            $user_id = $value->uid;
            $product_id = $value->product_id;

            if (!in_array($user_id, $users)) {
                array_push($users, $user_id);
            }
        }

        foreach ($users as $value) {
            $cart_user = Cart::where('vendor', $code)
                ->where('uid', $value)
                ->get()
                ->first();
            $sum_user_total = Cart::where('vendor', $code)
                ->where('uid', $value)
                ->get()
                ->sum('price');
            $user = Users::where('id', $value)
                ->get()
                ->first();

            $data = [
                'account_id' => $user->account_id,
                'dealer_name' => $user->company_name,
                'user' => $user_id,
                'vendor_code' => $code,
                'purchaser_name' => $user->first_name . ' ' . $user->last_name,
                'amount' => $sum_user_total,
            ];

            array_push($res_data, $data);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Purchasers by Dealers';
        $this->result->data = $res_data;
        return response()->json($this->result);
    }

    public function dealer_summary()
    {
        $dealers = Dealer::all();
        $dealer_count = Dealer::count();
        $total_sales = 0;
        $res_data = [];
        if ($dealers) {
            foreach ($dealers as $value) {
                $dealer_code = $value->dealer_code;
                $dealer_name = $value->dealer_name;
                $dealer_sales = Cart::where('dealer', $dealer_code)->sum(
                    'price'
                );
                $total_sales += Cart::where('dealer', $dealer_code)->sum(
                    'price'
                );

                $data = [
                    'dealer_name' => $dealer_name,
                    'dealer_code' => $dealer_code,
                    'sales' => $dealer_sales,
                ];

                array_push($res_data, $data);
            }
        }

        /////// Sorting //////////
        usort($res_data, function ($a, $b) {
            //Sort the array using a user defined function
            return $a['sales'] > $b['sales'] ? -1 : 1; //Compare the scores
        });

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $res_data;
        $this->result->message = 'Dealer Summary';
        return response()->json($this->result);
    }

    public function get_vendor_products($code)
    {
        if (Products::where('vendor', $code)->exists()) {
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

    public function get_all_vendor()
    {
        $vendors = Vendors::all();

        $this->result->status = true;
        $this->result->data = $vendors;
        $this->result->message = 'All Vendor';
        return response()->json($this->result);
    }

    public function get_special_orders()
    {
        $orders = SpecialOrder::orderBy('created_at', 'desc')->get();

        $res_data = [];

        foreach ($orders as $value) {
            $vendor = $value->vendor_code;
            $user = $value->uid;

            $vendor_data = Vendors::where('vendor_code', $vendor)
                ->get()
                ->first();
            $user_data = Users::where('id', $user)
                ->get()
                ->first();

            $data = [
                'qty' => $value->quantity,
                'description' => $value->description,
                'vendor_name' => $vendor_data->vendor_name
                    ? $vendor_data->vendor_name
                    : null,
                'account' => $user_data->account_id,
                'dealer_name' => $user_data->company_name,
                'rep_name' =>
                    $user_data->first_name . ' ' . $user_data->last_name,
                'vendor_no' => null,
            ];

            array_push($res_data, $data);
        }

        $this->result->status = true;
        $this->result->data = $res_data;
        $this->result->message = 'Price overide report';
        return response()->json($this->result);
    }

    public function get_price_overide_report()
    {
        $all_report = PriceOverideReport::all();
        $res_data = [];

        if ($all_report) {
            foreach ($all_report as $value) {
                $user_id = $value->authorised_by;
                $user_data = Users::where('id', $user_id)
                    ->get()
                    ->first();
                $dealer_code = $value->dealer_code;
                $vendor_code = $value->vendor_code;
                $vendor_data = Vendors::where('vendor_code', $vendor_code)
                    ->get()
                    ->first();
                $atlas_id = $value->atlas_id;
                $product_data = Products::where('atlas_id', $atlas_id)
                    ->get()
                    ->first();
                $qty = $value->qty;
                $new_qty = $value->new_qty;
                $regular = $value->regular;
                $show = $value->show_price;
                $overide_price = $value->overide_price;

                $data = [
                    'dealer_code' => $dealer_code,
                    'vendor_name' => $vendor_data->vendor_name,
                    'atlas_id' => $atlas_id,
                    'vendor_product_code' => $product_data->vendor_product_code,
                    'qty' => $qty,
                    'new_qty' => $new_qty,
                    'regular' => $regular,
                    'show' => $show,
                    'overide_price' => $overide_price,
                    'authorized_by' =>
                        $user_data->first_name . ' ' . $user_data->last_name,
                ];

                array_push($res_data, $data);
            }
        }

        $this->result->status = true;
        $this->result->data = $res_data;
        $this->result->message = 'Price overide report';
        return response()->json($this->result);
    }

    public function save_price_override(Request $request)
    {
        $dealer_code = $request->dealerCode;
        $atlas_code = $request->atlasCode;
        $new_qty = $request->newQty;
        $new_price = $request->newPrice;

        $current_cart_data = Cart::where('dealer', $dealer_code)
            ->where('atlas_id', $atlas_code)
            ->get()
            ->first();

        $old_qty = $current_cart_data->qty;
        $old_price = $current_cart_data->price;
        $unit_price = $current_cart_data->unit_price;
        $vendor = $current_cart_data->vendor;
        $authorised_by = $request->authorizer;
        $product_data = Products::where('atlas_id', $atlas_code)
            ->get()
            ->first();

        PriceOverideReport::create([
            'dealer_code' => $dealer_code,
            'vendor_code' => $vendor,
            'atlas_id' => $atlas_code,
            'qty' => $old_qty,
            'new_qty' => $new_qty != '' ? $new_qty : $old_qty,
            'regular' => $product_data->regular,
            'show_price' => $old_price,
            'overide_price' => $new_price,
            'authorised_by' => $authorised_by,
        ]);

        if ($new_price != '') {
            $update = Cart::where('dealer', $dealer_code)
                ->where('atlas_id', $atlas_code)
                ->update([
                    'price' => $new_price,
                ]);
        }

        if ($new_qty != '') {
            $update = Cart::where('dealer', $dealer_code)
                ->where('atlas_id', $atlas_code)
                ->update([
                    'qty' => $new_qty,
                ]);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Price override was Successfully';
        return response()->json($this->result);
    }

    public function get_price_override_item($dealer, $atlas_id)
    {
        if (
            Cart::where('dealer', $dealer)
                ->where('atlas_id', $atlas_id)
                ->exists()
        ) {
            $cart_data = Cart::where('dealer', $dealer)
                ->where('atlas_id', $atlas_id)
                ->get()
                ->first();

            $pro_data = Products::where('atlas_id', $atlas_id)
                ->get()
                ->first();

            $data = [
                'qty' => $cart_data->qty,
                'atlas_id' => $atlas_id,
                'vendor' => $pro_data->vendor_code,
                'description' => $pro_data->description,
                'price' => $cart_data->price,
                'dealer' => $dealer,
            ];

            $this->result->status = true;
            $this->result->data = $data;
            $this->result->message = 'Selected Cart Item';
        } else {
            $this->result->status = true;
            $this->result->data = [];
            $this->result->message = 'no Item found';
        }

        return response()->json($this->result);
    }

    public function get_atlas_notes()
    {
        $notes = ProgramNotes::where('role', '1')
            ->orderBy('id', 'desc')
            ->get();

        $res_data = [];

        if ($notes) {
            foreach ($notes as $value) {
                $dealer = $value->dealer_uid;
                $vendor = $value->vendor_uid;

                $dealer_data = Users::where('id', $dealer)
                    ->get()
                    ->first();

                $vendor_data = Users::where('id', $vendor)
                    ->get()
                    ->first();

                $value->dealership_name = $dealer_data->company_name;
                $value->vendorship_name = $vendor_data->company_name;
                // $value->dealer_rep =
                // $dealer_data->first_name . ' ' . $dealer_data->last_name;

                ///array_push($res_data, $vendor_data);
            }
        }

        $this->result->status = true;
        $this->result->data = $notes;
        $this->result->message = 'Atlas Notes';
        return response()->json($this->result);
    }

    public function get_vendor_notes()
    {
        $notes = ProgramNotes::where('role', '3')
            ->orderBy('id', 'desc')
            ->get();

        $res_data = [];

        if ($notes) {
            foreach ($notes as $value) {
                $dealer = $value->dealer_uid;
                $vendor = $value->vendor_uid;

                $dealer_data = Users::where('id', $dealer)
                    ->get()
                    ->first();

                $vendor_data = Users::where('id', $vendor)
                    ->get()
                    ->first();

                $value->dealership_name = $dealer_data->company_name;
                $value->vendorship_name = $vendor_data->company_name;
                // $value->dealer_rep =
                // $dealer_data->first_name . ' ' . $dealer_data->last_name;

                ///array_push($res_data, $vendor_data);
            }
        }

        $this->result->status = true;
        $this->result->data = $notes;
        $this->result->message = 'Vendor Notes';
        return response()->json($this->result);
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

    public function save_admin_reply_problem(Request $request)
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
            $this->result->message = 'report replied Successfully';

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

    public function edit_seminar(Request $request)
    {
        // process the request
        $id = $request->id;
        $seminarDate = $request->seminarDate;
        $startTime = $request->startTime;
        $stopTime = $request->stopTime;

        $topic = $request->topic;
        $vendorCode = $request->vendorCode;
        $vendorName = $request->vendorName;
        $link = $request->link;

        if ($seminarDate != '') {
            $update = Seminar::where('id', $id)->update([
                'seminar_date' => $seminarDate,
            ]);
        }

        if ($startTime != '') {
            $update = Seminar::where('id', $id)->update([
                'start_time' => $startTime,
            ]);
        }

        if ($stopTime != '') {
            $update = Seminar::where('id', $id)->update([
                'stop_time' => $stopTime,
            ]);
        }

        if ($topic != '') {
            $update = Seminar::where('id', $id)->update([
                'topic' => $topic,
            ]);
        }

        if ($vendorCode != '') {
            $update = Seminar::where('id', $id)->update([
                'vendor_id' => $vendorCode,
            ]);
        }

        if ($vendorName != '') {
            $update = Seminar::where('id', $id)->update([
                'vendor_name' => $vendorName,
            ]);
        }

        if ($link != '') {
            $update = Seminar::where('id', $id)->update([
                'link' => $link,
            ]);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Seminar Updated Successfully';
        return response()->json($this->result);
    }

    public function get_seminar_by_id($id)
    {
        $seminar = Seminar::find($id);
        if (!$seminar) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch the seminar";
        } else {
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $seminar;
            $this->result->message = 'Seminar fetched Successfully';
        }

        return response()->json($this->result);
    }

    public function get_current_promotional_flier($id)
    {
        $one_promotional_flier = PromotionalFlier::find($id);

        if ($one_promotional_flier) {
            $vendor_code = $one_promotional_flier->vendor_id;
            $vendor_data = Vendors::where('vendor_code', $vendor_code)
                ->get()
                ->first();
            if ($vendor_data) {
                $one_promotional_flier->vendor_name = $vendor_data->vendor_name;
            }
        }

        if (!$one_promotional_flier) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch the promotional fliers";
            return response()->json($this->result);
        } else {
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $one_promotional_flier;
            $this->result->message = 'Promotional Flier fetched Successfully';
            return response()->json($this->result);
        }
    }

    public function get_all_promotional_flyer()
    {
        $promotional = PromotionalFlier::all();

        $res_data = [];

        foreach ($promotional as $value) {
            $vendor_code = $value->vendor_id;
            $vendor_data = Vendors::where('vendor_code', $vendor_code)
                ->get()
                ->first();

            if ($vendor_data) {
                $data = [
                    'id' => $value->id,
                    'name' => $value->name,
                    'vendor_code' => $value->vendor_id,
                    'pdf_url' => $value->pdf_url,
                    'description' => $value->description,
                    'status' => $value->status,
                    'vendor_name' => $vendor_data->vendor_name,
                ];

                array_push($res_data, $data);
            }
        }

        $this->result->status = true;
        $this->result->data = $res_data;
        $this->result->message = 'All Promotional fliers';
        return response()->json($this->result);
    }

    public function admin_reply_report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required',
            'role' => 'required',
            'user' => 'required',
            'ticket' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $description = $request->description;
            $role = $request->role;
            $user = $request->user;
            $ticket = $request->ticket;

            $save = Report::create([
                'description' => $description,
                'role' => $role,
                'company_name' => 'Atlas',
                'user_id' => $user,
                'ticket_id' => $ticket,
            ]);

            if (!$save) {
                $this->result->status = false;
                $this->result->status_code = 422;
                $this->result->message =
                    'Sorry pro could not be uploaded. Try again later.';
                return response()->json($this->result);
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Message sent successfully';

            return response()->json($this->result);
        }
    }

    public function get_report_problem($ticket)
    {
        $selected = Report::where('ticket_id', $ticket)->get();

        $res_data = [];
        if ($selected) {
            // post with the same slug already exists
            Report::where('ticket_id', $ticket)->update([
                'status' => 2,
            ]);
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
        }

        $this->result->status = true;
        $this->result->data = $res_data;
        $this->result->message = 'Program Count Down Set Successfully';
        return response()->json($this->result);
    }

    public function get_countdown()
    {
        $active_countdown = ProgramCountdown::where('status', '1')
            ->get()
            ->first();

        $start_time = $active_countdown->start_countdown_time;
        $start_date = $active_countdown->start_countdown_date;

        $start_timer = Carbon::createFromFormat(
            'Y-m-d H:s:i',
            $start_date . ' ' . $start_time . ':00'
        );

        $end_date = $active_countdown->end_countdown_date;
        $end_time = $active_countdown->end_countdown_time;

        $end_timer = Carbon::createFromFormat(
            'Y-m-d H:s:i',
            $end_date . ' ' . $end_time . ':00'
        );

        $inital_end_timer = Carbon::parse($end_date . ' ' . $end_time, 'UTC');

        $inital_start_timer = Carbon::parse(
            $start_date . ' ' . $start_time,
            'UTC'
        );

        $now = Carbon::now();

        $first = strtotime($start_timer);
        $second = strtotime($end_timer);

        $secondsLeft = $second - $first;
        $days = floor(($secondsLeft / 60) * 60 * 24);
        $hours = floor((($secondsLeft - $days * 60 * 60 * 24) / 60) * 60);

        // if ($now->lt($start_timer)) {
        //     $this->result->data->years = 0;
        //     $this->result->data->months = 0;
        //     $this->result->data->weeks = 0;
        //     $this->result->data->days = 0;
        //     $this->result->data->hours = 0;
        //     $this->result->data->minutes = 0;
        //     $this->result->data->seconds = 0;
        // } else {

        // $years = $end_timer->diffInYears($start_timer);
        // $months = $end_timer->diffInMonths($start_timer);
        // $weeks = $end_timer->diffInWeeks($start_timer);
        // $days = $end_timer->diffInDays($start_timer);
        // $hours = $end_timer->diffInHours($start_timer);
        // $minutes = $end_timer->diffInMinutes($start_timer);
        // $seconds = $end_timer->diffInSeconds($start_timer);

        // $human = $end_timer->diffForHumans($start_timer);

        // $this->result->data->years = $years;
        // $this->result->data->months = $months;
        // $this->result->data->weeks = $weeks;
        // $this->result->data->days = $days;
        // $this->result->data->hours = $hours;
        // $this->result->data->minutes = $minutes;
        // $this->result->data->seconds = $seconds;

        $this->result->data->start_timer_timestamp = strtotime($start_timer);

        $this->result->data->end_timer_timestamp = strtotime($end_timer);

        $this->result->data->start_timer = $start_timer;
        $this->result->data->end_timer = $end_timer;

        $this->result->data->start_date = $start_date;
        $this->result->data->start_time = $start_time;

        $this->result->data->end_date = $end_date;
        $this->result->data->end_time = $end_time;

        $this->result->data->inital_end_timer = $inital_end_timer;

        $this->result->data->real_start_timer = $inital_start_timer;

        // echo $gameStart;
        //    }

        // $this->result->data->starter = $start_timer;
        // $this->result->data->ender = $end_timer;

        $this->result->status = true;
        $this->result->message = 'Program Count Down Set Successfully';
        return response()->json($this->result);
    }

    public function save_countdown(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'countdownEndDate' => 'required',
            'countdownEndTime' => 'required',
            'countdownStartDate' => 'required',
            'countdownStartTime' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $countdownStartTime = $request->countdownStartTime;
            $countdownStartDate = $request->countdownStartDate;
            $countdownEndTime = $request->countdownEndTime;
            $countdownEndDate = $request->countdownEndDate;

            ProgramCountdown::where('status', '1')->update([
                'status' => '0',
            ]);

            $save_countdown = ProgramCountdown::create([
                'start_countdown_date' => $countdownStartDate,
                'start_countdown_time' => $countdownStartTime,

                'end_countdown_date' => $countdownEndDate,
                'end_countdown_time' => $countdownEndTime,

                // 'post_med_abbr' => $post_med_abbr,
            ]);

            if (!$save_countdown) {
                $this->result->status = false;
                $this->result->status_code = 422;
                $this->result->message =
                    'Sorry pro could not be uploaded. Try again later.';
                return response()->json($this->result);
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Program Count Down Set Successfully';

            return response()->json($this->result);
        }
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
                    ->where('role', '4')
                    ->get()
                    ->first();

                if ($sender_data) {
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

    public function get_vendor_unread_msg($user)
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
        $this->result->message = 'get vendor unread msg';
        $this->result->data = $data;
        return response()->json($this->result);
    }

    public function get_all_admin_users($user)
    {
        $admin_users = Users::where('role', '1')
            ->get()
            ->toArray();

        $user_data = Users::where('id', $user)
            ->get()
            ->first();

        $data = [];

        if ($admin_users) {
            foreach ($admin_users as $value) {
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

                array_push($data, $each_data);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $data;
        $this->result->message = 'All Admin Users Data';
        return response()->json($this->result);
    }

    public function get_all_users()
    {
        $all_users = Users::orderBy('first_name', 'asc')->get();

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $all_users;
        $this->result->message = 'All Users Data';
        return response()->json($this->result);
    }

    public function save_chat_id(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'chatId' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $user = $request->id;
            $chat_id = $request->chatId;

            $chat = Users::where('id', $user)
                ->where('chat_id', '!=', null)
                ->get();

            if (!$chat) {
                // post with the same slug already exists
                $update = Users::where('id', $user)->update([
                    'chat_id' => $chat_id,
                ]);

                $this->result->data = $chat;
                return response()->json($this->result);
            } else {
                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'Not Saved';
                return response()->json($this->result);
            }
        }
    }

    public function register_admin_users(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullName' => 'required',
            'designation' => 'required',
            'role' => 'required',
            'accountAccess' => 'required',
            'region' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $accountAccess = $request->accountAccess;
            $full_name = $request->fullName;
            $designation = $request->designation;
            $role = $request->role;
            $region = $request->region;
            $email = $request->email;
            $password = $request->password;

            if (strtolower($role) == '1') {
                $role_name = 'admin';
            }

            if (strtolower($role) == '2') {
                $role_name = 'branch manager';
            }

            if (strtolower($role) == '5') {
                $role_name = 'inside sales';
            }

            if (strtolower($role) == '6') {
                $role_name = 'outside sales';
            }

            if (Users::where('email', $email)->exists()) {
                // post with the same slug already exists
            } else {
                $save_admin = Users::create([
                    'first_name' => $full_name,
                    ////'last_name' => $last_name,
                    'full_name' => $full_name,
                    'designation' => $designation,
                    'email' => $email,
                    'role_name' => $role_name,
                    'role' => $role,
                    'access_level_first' => $accountAccess,
                    'password' => bcrypt($password),
                    'password_show' => $password,
                    'region' => $region,
                ]);

                if (!$save_admin) {
                    $this->result->status = false;
                    $this->result->status_code = 422;
                    $this->result->message =
                        'Sorry File could not be uploaded. Try again later.';
                    return response()->json($this->result);
                }

                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'Admin User Added Successfully';
            }

            return response()->json($this->result);
        }
    }

    public function deactivate_admin($id)
    {
        if (Users::where('id', $id)->exists()) {
            $update = Users::where('id', $id)->update([
                'status' => '0',
            ]);

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Admin User deactivated with id';
        } else {
            $this->result->status = false;
            $this->result->status_code = 404;
            $this->result->message = 'User not found';
        }

        return response()->json($this->result);
    }

    public function get_all_seminar()
    {
        $seminar = Seminar::orderBy('id', 'desc')->get();
        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $seminar;
        $this->result->message = 'All Seminar Fetched Successfully';
        return response()->json($this->result);
    }

    public function check_status($seminar_date, $start_time, $stop_time)
    {
        echo $seminar_date . ' => ' . $start_time . ' => ' . $stop_time;
    }

    public function create_seminar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required',
            'vendorName' => 'required',
            'vendorCode' => 'required',
            'seminarDate' => 'required',
            'startTime' => 'required',
            'stopTime' => 'required',
            'link' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $topic = $request->topic;
            $vendor_name = $request->vendorName;
            $vendor_id = $request->vendorCode;
            $seminar_date = $request->seminarDate;
            $start_time = $request->startTime;
            $stop_time = $request->stopTime;
            $link = $request->link;

            // return $this->check_status($seminar_date,$start_time,$stop_time);

            // update to the db
            $save = Seminar::create([
                'topic' => $topic,
                'vendor_name' => $vendor_name,
                'vendor_id' => $vendor_id,
                'seminar_date' => $seminar_date,
                'start_time' => $start_time,
                'stop_time' => $stop_time,
                'link' => $link,
                'status' => '1', // 1 means scheduled, 2 means ongoing, 3 means completed
            ]);

            if ($save) {
                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'Seminar Added Successfully';
                return response()->json($this->result);
            } else {
                $this->result->status = true;
                $this->result->status_code = 404;
                $this->result->message =
                    'An Error Ocurred, Seminar Uploading failed';
                return response()->json($this->result);
            }
        }
    }

    public function edit_faq(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $id = $request->id;
            $title = $request->title;
            $subtitle = $request->subtitle;
            $description = $request->description;
            $link = $request->link;
            $role = $request->role;

            // update to the db
            $update = Faq::where('id', $id)->update([
                'title' => $title,
                'subtitle' => $subtitle,
                'description' => $description,
                'link' => $link,
                'role' => $role,
            ]);

            if ($update) {
                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'Faq Updated Successfully';
                return response()->json($this->result);
            } else {
                $this->result->status = true;
                $this->result->status_code = 404;
                $this->result->message =
                    'An Error Ocurred, Products Update failed';
                return response()->json($this->result);
            }
        }
    }

    public function get_faq_id($id)
    {
        if (Faq::where('id', $id)->exists()) {
            $faq = Faq::where('id', $id)
                ->get()
                ->first();

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $faq;

            $this->result->message = 'Faq acquired with id';
        } else {
            $this->result->status = false;
            $this->result->status_code = 404;
            $this->result->message = 'Faq not found';
        }

        return response()->json($this->result);
    }

    public function deactivate_faq($id)
    {
        if (Faq::where('id', $id)->exists()) {
            $update = Faq::where('id', $id)->update([
                'status' => '0',
            ]);

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Faq deactivated with id';
        } else {
            $this->result->status = false;
            $this->result->status_code = 404;
            $this->result->message = 'Faq not found';
        }

        return response()->json($this->result);
    }

    public function create_faq(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'subtitle' => 'required',
            'description' => 'required',
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            $title = $request->input('title');
            $subtitle = $request->input('subtitle');
            $description = $request->input('description');
            $link = $request->input('link');
            $role = $request->input('role');

            $createfaq = Faq::create([
                'title' => $title,
                'subtitle' => $subtitle,
                'description' => $description,
                'link' => $link,
                'role' => $role,
            ]);

            if (!$createfaq) {
                $this->result->status = true;
                $this->result->status_code = 400;
                $this->result->message =
                    'An Error Ocurred, Faq Addition failed';
                return response()->json($this->result);
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'FAQ Created Successfully';
            return response()->json($this->result);
        }
    }

    public function get_all_admins()
    {
        $all_admin = Users::orWhere('role', '1')
            ->orWhere('role', '2')
            ->orWhere('role', '5')
            ->orWhere('role', '6')
            ->get();

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $all_admin;
        $this->result->message = 'Admin All Admin Data';
        return response()->json($this->result);
    }

    public function upload_admin_csv(Request $request)
    {
        $csv = $request->file('csv');
        if ($csv == null) {
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = 'Please upload dealer in csv format';
            return response()->json($this->result);
        }

        if ($csv->getSize() > 0) {
            $file = fopen($_FILES['csv']['tmp_name'], 'r');
            $csv_data = [];
            while (($col = fgetcsv($file, 1000, ',')) !== false) {
                $csv_data[] = $col;
            }
            array_shift($csv_data);
            // remove the first row of the csv

            foreach ($csv_data as $key => $value) {
                # code...
                $name = $value[0];
                ///  $desgination = $value[1];
                $email = $value[2];
                $access_level_first = $value[4];
                $access_level_second = $value[5];
                $password = bcrypt($value[6]);
                $password_show = $value[6];
                $region = $value[7];
                $extra_name = explode(' ', $name);
                $first_name = $extra_name[0];
                // $last_name = is_empty($extra_name[1]) ? '' : $extra_name[1];
                $role = 0;
                $role_name = $value[3];

                if (strtolower($role_name) == 'admin') {
                    $role = 1;
                }

                if (strtolower($role_name) == 'branch manager') {
                    $role = 2;
                }

                if (strtolower($role_name) == 'vendor') {
                    $role = 3;
                }

                if (strtolower($role_name) == 'dealer') {
                    $role = 4;
                }
                if (strtolower($role_name) == 'inside sales') {
                    $role = 5;
                }

                if (strtolower($role_name) == 'outside sales') {
                    $role = 6;
                }

                if (Users::where('email', $email)->exists()) {
                    // post with the same slug already exists
                } else {
                    $save_admin = Users::create([
                        'first_name' => $first_name,
                        ////'last_name' => $last_name,
                        'full_name' => $name,
                        'designation' => $role_name,
                        'email' => $email,
                        'role_name' => $role_name,
                        'role' => $role,
                        'access_level_first' => $access_level_first,
                        'access_level_second' => $access_level_second,
                        'password' => $password,
                        'password_show' => $password_show,
                        'region' => $region,
                    ]);

                    if (!$save_admin) {
                        $this->result->status = false;
                        $this->result->status_code = 422;
                        $this->result->message =
                            'Sorry File could not be uploaded. Try again later.';
                        return response()->json($this->result);
                    }
                }
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Admin Users uploaded successfully';
        return response()->json($this->result);
        fclose($file);
    }

    public function most_sales_vendors_admin_dashboard()
    {
        /////////// Most sales By Vendor /////////
        $most_sales_vendor = [];
        $all_vendors_data = Vendors::all();

        if ($all_vendors_data) {
            foreach ($all_vendors_data as $value) {
                $vendor_code = $value->vendor_code;
                $vendor_name = $value->vendor_name;
                $total_sales = Cart::where('vendor', $vendor_code)->sum(
                    'price'
                );

                $data = [
                    'vendor_name' => $vendor_name,
                    'vendor_code' => $vendor_code,
                    'vendor_sales' => $total_sales,
                    'trend' => '0%',
                ];

                array_push($most_sales_vendor, $data);
            }
        }

        /// 0903 164 6427

        /////// Sorting //////////
        usort($most_sales_vendor, function ($a, $b) {
            //Sort the array using a user defined function
            return $a['vendor_sales'] > $b['vendor_sales'] ? -1 : 1; //Compare the scores
        });

        $most_sales_vendor = array_slice($most_sales_vendor, 0, 6);

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $most_sales_vendor;

        $this->result->message = 'Mosr Sales Vendors Dashboard Data';
        return response()->json($this->result);
    }

    public function most_sales_dealer_admin_dashboard()
    {
        ///// Most orders Dealers ///////
        $most_sale_dealer = [];
        $all_dealer_users = Users::where('role', '4')->get();

        if ($all_dealer_users) {
            foreach ($all_dealer_users as $value) {
                $user_id = $value->id;
                $total_sales = Cart::where('uid', $user_id)->sum('price');
                $dealer_code = $value->account_id;
                $dealer_data = Dealer::where('dealer_code', $dealer_code)
                    ->get()
                    ->first();

                $data = [
                    'account_id' => $value->account_id,
                    'dealer' => $dealer_data,
                    'total_sales' => $total_sales,
                    'trend' => '0%',
                ];

                array_push($most_sale_dealer, $data);
            }
        }

        /////// Sorting //////////
        usort($most_sale_dealer, function ($a, $b) {
            //Sort the array using a user defined function
            return $a['total_sales'] > $b['total_sales'] ? -1 : 1; //Compare the scores
        });

        $most_sale_dealer = array_slice($most_sale_dealer, 0, 6);

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $most_sale_dealer;

        $this->result->message = 'Most Sales Dealers Admin Dashboard Data';
        return response()->json($this->result);
    }

    public function admin_dashboard_analysis()
    {
        $total_vendors = Users::where('role', '3')->count();
        $total_dealers = Users::where('role', '4')->count();
        $total_products = Products::count();
        $total_order = Cart::where('status', '1')->count();

        $logged_vendors = Users::where('role', '3')
            ->where('last_login', '!=', null)
            ->count();

        $logged_dealers = Users::where('role', '4')
            ->where('last_login', '!=', null)
            ->count();

        $logged_admin = Users::orWhere('role', '1')
            ->orWhere('role', '5')
            ->orWhere('role', '2')
            ->orWhere('role', '6')
            ->where('last_login', '!=', null)
            ->count();

        $cart_data = Cart::all();
        $cart_total = 0;
        $total_item_ordered = 0;

        if ($cart_data) {
            foreach ($cart_data as $value) {
                $price = $value->price;
                $qty = $value->qty;
                $cart_total += floatval($price);
                $total_item_ordered += intval($qty);
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data->total_logged_vendors = $logged_vendors;
        $this->result->data->total_logged_admin = $logged_admin;
        $this->result->data->total_logged_dealers = $logged_dealers;

        $this->result->data->total_vendors = $total_vendors;
        $this->result->data->total_dealers = $total_dealers;
        $this->result->data->total_products = $total_products;
        $this->result->data->total_amount = $cart_total;
        $this->result->data->total_item_ordered = $total_item_ordered;

        $this->result->message = 'Analysis Admin Dashboard Data';
        return response()->json($this->result);
    }

    public function add_product(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendorAccount' => 'required',
            'atlasId' => 'required',
            'vendorItemId' => 'required',
            'description' => 'required',
            'regular' => 'required',
            'special' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $atlasId = $request->atlasId;
            $vendorAccount = $request->vendorAccount;
            $regular = $request->regular;
            $special = $request->special;
            $vendorItemId = $request->vendorItemId;
            $description = $request->description;

            if (Products::where('atlas_id', $atlasId)->exists()) {
                $this->result->status = false;
                $this->result->status_code = 200;
                $this->result->message =
                    'product with atlas id ' .
                    $atlasId .
                    ' has been add already';
            } else {
                $save_product = Products::create([
                    'atlas_id' => $atlasId,
                    'description' => $description,
                    'status' => '1',
                    'vendor_code' => $vendorAccount,
                    'vendor' => $vendorAccount,
                    'vendor_product_code' => $vendorItemId,
                    'booking' => $regular,
                    'special' => $special,
                ]);

                if (!$save_product) {
                    $this->result->status = false;
                    $this->result->status_code = 422;
                    $this->result->message =
                        'Sorry File could not be uploaded. Try again later.';
                    return response()->json($this->result);
                }

                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'Products registered successfully';
                return response()->json($this->result);
            }
        }
    }

    public function deactivate_product($id)
    {
        if (Products::where('id', $id)->exists()) {
            $update = Products::where('id', $id)->update([
                'status' => '0',
            ]);

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'product deactivated with id';
        } else {
            $this->result->status = false;
            $this->result->status_code = 404;
            $this->result->message = 'product not found';
        }

        return response()->json($this->result);
    }

    public function edit_product(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'atlasId' => 'required',
            'desc' => 'required',
            'regular' => 'required',
            'vendor' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $atlasId = $request->atlasId;
            $desc = $request->desc;
            $regular = $request->regular;
            $special = $request->special;
            $vendor = $request->vendor;

            // update to the db
            $update = Products::where('atlas_id', $atlasId)->update([
                'atlas_id' => $atlasId,
                'description' => $desc,
                'booking' => $regular,
                'vendor' => $vendor,
            ]);

            if ($special != null) {
                Products::where('atlas_id', $atlasId)->update([
                    'special' => $special != null ? $special : '',
                ]);
            }

            if ($update) {
                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'Products Updated Successfully';
                return response()->json($this->result);
            } else {
                $this->result->status = true;
                $this->result->status_code = 404;
                $this->result->message =
                    'An Error Ocurred, Products Update failed';
                return response()->json($this->result);
            }
        }
    }

    public function get_product_by_atlas_id($id)
    {
        if (Products::where('atlas_id', $id)->exists()) {
            $item = Products::where('atlas_id', $id)
                ->get()
                ->first();

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

    public function get_product($id)
    {
        $product = Products::where('id', $id)
            ->get()
            ->first();
        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get products was successful';
        $this->result->data = $product;
        return response()->json($this->result);
    }

    // public function deactivate_product($id)
    // {
    //     // update to the db
    //     $update = Products::where('id', $id)->update([
    //         'status' => '0',
    //     ]);

    //     if ($update) {
    //         $this->result->status = true;
    //         $this->result->status_code = 200;
    //         $this->result->message = 'Product Deactivated Successfully';
    //         return response()->json($this->result);
    //     } else {
    //         $this->result->status = true;
    //         $this->result->status_code = 404;
    //         $this->result->message = 'An Error Ocurred, Dealer Update failed';
    //         return response()->json($this->result);
    //     }
    // }

    public function get_all_products()
    {
        $products = Products::where('status', '1')->get();
        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get all products was successful';
        $this->result->data = $products;
        return response()->json($this->result);
    }

    public function upload_product_csv(Request $request)
    {
        $csv = $request->file('csv');

        if ($csv == null) {
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = 'Please upload products in csv format';
            return response()->json($this->result);
        }

        if ($csv->getSize() > 0) {
            $file = fopen($_FILES['csv']['tmp_name'], 'r');
            $csv_data = [];
            while (($col = fgetcsv($file, 1000, ',')) !== false) {
                $csv_data[] = $col;
            }

            array_shift($csv_data);
            // remove the first row of the csv

            $test = [];

            foreach ($csv_data as $key => $value) {
                # code...

                $atlas_id = $value[2];
                $check_atlas_id = Products::where('atlas_id', $atlas_id)
                    ->get()
                    ->first();

                if ($check_atlas_id) {
                    $type = $value[9];

                    if ($type == 'special') {
                        $special = $value[8];
                        $desc_spec = $value[5];
                        $cond_data = explode('+', $desc_spec);
                        $condition = $cond_data[0];
                        $booking = $value[7];
                        $regular = $value[6];

                        $spec_data = [
                            'booking' => floatval($booking),
                            'special' => floatval($special),
                            'cond' => intval($condition),
                            'type' => strtolower($type),
                            'desc' => strtolower($desc_spec),
                        ];

                        if (!empty($check_atlas_id->spec_data)) {
                            $spec = json_decode(
                                $check_atlas_id->spec_data,
                                true
                            );
                            array_push($spec, $spec_data);
                            $new_spec = json_encode($spec);

                            Products::where('atlas_id', $atlas_id)->update([
                                'cond' => $condition,
                            ]);

                            Products::where('atlas_id', $atlas_id)->update([
                                'spec_data' => $new_spec,
                            ]);
                        } else {
                            $data = [];
                            array_push($data, $spec_data);
                            $new_spec = json_encode($data);

                            Products::where('atlas_id', $atlas_id)->update([
                                'cond' => $condition,
                            ]);

                            // Products::where('atlas_id', $atlas_id)->update([
                            //     'grouping' => $grouping,
                            // ]);
                            Products::where('atlas_id', $atlas_id)->update([
                                'spec_data' => $new_spec,
                            ]);
                        }
                    }

                    if ($type == 'assorted') {
                        $special = $value[8];
                        $desc_spec = $value[5];
                        $cond_data = explode('+', $desc_spec);
                        $condition = $cond_data[0];
                        $booking = $value[7];
                        $regular = $value[6];
                        $grouping = $value[10];

                        $spec_data = [
                            'booking' => floatval($booking),
                            'special' => floatval($special),
                            'cond' => intval($condition),
                            'type' => strtolower($type),
                            'desc' => strtolower($desc_spec),
                        ];

                        if (!empty($check_atlas_id->spec_data)) {
                            $spec = json_decode(
                                $check_atlas_id->spec_data,
                                true
                            );
                            array_push($spec, $spec_data);
                            $new_spec = json_encode($spec);

                            Products::where('atlas_id', $atlas_id)->update([
                                'cond' => $condition,
                            ]);
                            Products::where('atlas_id', $atlas_id)->update([
                                'grouping' => $grouping,
                            ]);

                            Products::where('atlas_id', $atlas_id)->update([
                                'spec_data' => $new_spec,
                            ]);
                        } else {
                            $data = [];
                            array_push($data, $spec_data);
                            $new_spec = json_encode($data);

                            Products::where('atlas_id', $atlas_id)->update([
                                'cond' => $condition,
                            ]);

                            Products::where('atlas_id', $atlas_id)->update([
                                'grouping' => $grouping,
                            ]);
                            Products::where('atlas_id', $atlas_id)->update([
                                'spec_data' => $new_spec,
                            ]);
                        }
                    }
                } else {
                    $spec_arr = [];
                    $vendor_code = $value[0];
                    $vendor_name = $value[1];
                    $atlas_id = $value[2];
                    $vendor_product_code = $value[3];
                    $xref = $value[4];
                    $description = $value[5];
                    $regular_price = $value[6];
                    $booking_price = $value[7];
                    // $type = $value[9] ? $value[9] : '';

                    $type = array_key_exists('9', $value) ? $value[9] : '';

                    switch ($type) {
                        case 'special':
                            # code...
                            break;

                        case 'assorted':
                            # code...
                            break;

                        default:
                            $save_product = Products::create([
                                'atlas_id' => $atlas_id,
                                'description' => $description,
                                'status' => '1',
                                'vendor_code' => $vendor_code,
                                'vendor' => $vendor_code,
                                'vendor_name' => $vendor_name,
                                'vendor_product_code' => $vendor_product_code,
                                'xref' => $xref,
                                'regular' => $regular_price,
                                'booking' => $booking_price,
                            ]);

                            if (!$save_product) {
                                $this->result->status = false;
                                $this->result->status_code = 422;
                                $this->result->message =
                                    'Sorry File could not be uploaded. Try again later.';
                                return response()->json($this->result);
                            }
                            break;
                    }
                }
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Products uploaded successfully';
            return response()->json($this->result);
            fclose($file);
        }
    }

    public function get_all_dealer_users()
    {
        $vendors = Users::where('status', '1')
            ->where('role', '4')
            ->orderBy('id', 'desc')
            ->get();
        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get all dealer users was successful';
        $this->result->data = $vendors;
        return response()->json($this->result);
    }

    public function deactivate_dealer_user($id)
    {
        // update to the db
        $update = Users::where('id', $id)->update([
            'status' => '0',
        ]);

        if ($update) {
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Dealer User Deactivated Successfully';
            return response()->json($this->result);
        } else {
            $this->result->status = true;
            $this->result->status_code = 404;
            $this->result->message = 'An Error Ocurred, Dealer Update failed';
            return response()->json($this->result);
        }
    }

    public function register_dealer_users(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'location' => 'required',
            'password' => 'required',
            'accountId' => 'required',
            'companyName' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $first_name = $request->firstName;
            $last_name = $request->lastName;
            $email = $request->email;
            $location = $request->location;
            $privilege_vendors = $request->privilegedVendors;
            $password = bcrypt($request->password);
            $password_show = $request->password;
            $company_name = $request->companyName;
            $accountId = $request->accountId;
            $full_name = $first_name . ' ' . $last_name;

            $role = '4';
            $role_name = 'dealer';

            if (Users::where('email', $email)->exists()) {
                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message =
                    'Dealer Email has been registered already ';
                return response()->json($this->result);
            } else {
                // save to the db
                $save_vendor = Users::create([
                    'full_name' => $full_name,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'password' => $password,
                    'password_show' => $password_show,
                    'role' => $role,
                    'role_name' => $role_name,
                    'privilege_vendors' => $privilege_vendors,
                    'username' => $email,
                    'location' => $location,
                    'company_name' => $company_name,
                    'company_code' => $accountId,
                    'account_id' => $accountId,
                ]);
            }

            if ($save_vendor) {
                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'Dealer User Successfully Added';
                return response()->json($this->result);
            } else {
                $this->result->status = true;
                $this->result->status_code = 404;
                $this->result->message =
                    'An Error Ocurred, Vendor Addition failed';
                return response()->json($this->result);
            }
        }
    }

    public function upload_dealer_users(Request $request)
    {
        $csv = $request->file('csv');
        if ($csv == null) {
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = 'Please upload dealer in csv format';
            return response()->json($this->result);
        }
        if ($csv->getSize() > 0) {
            $file = fopen($_FILES['csv']['tmp_name'], 'r');
            $csv_data = [];
            while (($col = fgetcsv($file, 1000, ',')) !== false) {
                $csv_data[] = $col;
            }
            array_shift($csv_data);
            // remove the first row of the csv

            foreach ($csv_data as $key => $value) {
                $dealer_code = $value[0];
                $dealer_name = $value[1];
                $first_name = strtolower($value[2]);
                $last_name = strtolower($value[3]);
                $password = bcrypt($value[4]);
                $password_show = $value[4];
                $email = strtolower($value[5]);
                $privilege_vendors = $value[6];
                $full_name = $first_name . ' ' . $last_name;
                $role = '4';
                $role_name = 'dealer';

                if (Users::where('email', $email)->exists()) {
                } else {
                    $save_dealer = Users::create([
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'full_name' => $full_name,
                        'email' => $email,
                        'password' => $password,
                        'password_show' => $password_show,
                        'role' => $role,
                        'role_name' => $role_name,
                        'dealer_name' => $dealer_name,
                        'privileged_vendors' => $privilege_vendors,
                        'account_id' => $dealer_code,
                        'dealer_code' => $dealer_code,
                        'company_name' => $dealer_name,
                    ]);
                }

                if (!$save_dealer) {
                    $this->result->status = false;
                    $this->result->status_code = 422;
                    $this->result->message =
                        'Sorry File could not be uploaded. Try again later.';
                    return response()->json($this->result);
                }
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Dealer uploaded successfully';
        return response()->json($this->result);
        fclose($file);
    }

    public function edit_vendor_user_data(Request $request)
    {
        // process the request
        $username = $request->username;
        $email = $request->email;
        $firstName = $request->firstName;
        $lastName = $request->lastName;
        $password = $request->password;
        $phone = $request->phone;
        $privilegeVendor = $request->privilegeVendor;
        $privilegeDealer = $request->privilegeDealer;
        $role = $request->role;
        $status = $request->status;
        $vendor = $request->vendor;
        $vendorId = $request->vendorId;

        if ($firstName != '') {
            $update = Users::where('id', $vendorId)->update([
                'first_name' => $firstName,
            ]);
        }

        if ($role != '') {
            if ($role == '1') {
                $role_name = 'admin';
            }
            if ($role == '2') {
                $role_name = 'branch manager';
            }
            if ($role == '3') {
                $role_name = 'vendor';
            }
            if ($role == '4') {
                $role_name = 'dealer';
            }
            if ($role == '5') {
                $role_name = 'inside sales';
            }
            if ($role == '6') {
                $role_name = 'outside sales';
            }

            $update = Users::where('id', $vendorId)->update([
                'role' => $role,
                'role_name' => $role_name,
            ]);
        }

        if ($privilegeDealer != '') {
            $update = Users::where('id', $vendorId)->update([
                'privileged_dealers' => $privilegeDealer,
            ]);
        }

        if ($privilegeVendor != '') {
            $update = Users::where('id', $vendorId)->update([
                'privileged_vendors' => $privilegeVendor,
            ]);
        }

        if ($status != '') {
            $update = Users::where('id', $vendorId)->update([
                'status' => $status,
            ]);
        }

        if ($phone != '') {
            $update = Users::where('id', $vendorId)->update([
                'phone' => $phone,
            ]);
        }

        if ($password != '') {
            $hash_password = bcrypt($password);

            $update = Users::where('id', $vendorId)->update([
                'password' => $hash_password,
                'password_show' => $password,
            ]);
        }

        if ($lastName != '') {
            $update = Users::where('id', $vendorId)->update([
                'last_name' => $lastName,
            ]);
        }

        if ($email != '') {
            $update = Users::where('id', $vendorId)->update([
                'email' => $email,
            ]);
        }

        if ($vendor != '') {
            $vendor = $request->vendor;
            $vendorName = $request->vendorName;
            $update = Users::where('id', $vendorId)->update([
                'vendor_name' => $vendorName,
                'vendor_code' => $vendor,
                'company_name' => $vendorName,
            ]);
        }

        if ($username != '') {
            $update = Users::where('id', $vendorId)->update([
                'username' => $username,
            ]);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Vendor User Updated Successfully';
        return response()->json($this->result);
    }

    public function upload_dealers(Request $request)
    {
        $csv = $request->file('csv');
        if ($csv == null) {
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = 'Please upload dealer in csv format';
            return response()->json($this->result);
        }

        if ($csv->getSize() > 0) {
            $file = fopen($_FILES['csv']['tmp_name'], 'r');
            $csv_data = [];
            while (($col = fgetcsv($file, 1000, ',')) !== false) {
                $csv_data[] = $col;
            }
            array_shift($csv_data);
            // remove the first row of the csv

            foreach ($csv_data as $key => $value) {
                $dealer_code = $value[0];
                $dealer_name = $value[1];
                $role_name = 'dealer';
                $role_id = '4';

                if (!Dealer::where('dealer_code', $dealer_code)->exists()) {
                    $save_dealer = Dealer::create([
                        'dealer_name' => $dealer_name,
                        'dealer_code' => $dealer_code,
                        'role_name' => $role_name,
                        'role_id' => $role_id,
                    ]);

                    if (!$save_dealer) {
                        $this->result->status = false;
                        $this->result->status_code = 422;
                        $this->result->message =
                            'Sorry File could not be uploaded. Try again later.';
                        return response()->json($this->result);
                    }
                }
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Dealer uploaded successfully';
        return response()->json($this->result);
        fclose($file);
    }

    public function get_vendor_user($id)
    {
        if (Users::where('id', $id)->exists()) {
            // post with the same slug already exists
            $user = Users::where('id', $id)
                ->get()
                ->first();

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'get vendor users was successful';
            $this->result->data = $user;
            return response()->json($this->result);
        } else {
            $this->result->status = true;
            $this->result->status_code = 404;
            $this->result->message = 'Vendor User not found';
            return response()->json($this->result);
        }
    }

    public function activate_vendor_user($id)
    {
        // update to the db
        $update = Users::where('id', $id)->update([
            'status' => '1',
        ]);

        if ($update) {
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Vendor User Activated Successfully';
            return response()->json($this->result);
        } else {
            $this->result->status = true;
            $this->result->status_code = 404;
            $this->result->message = 'An Error Ocurred, Vendor Update failed';
            return response()->json($this->result);
        }
    }

    public function deactivate_vendor_user($id)
    {
        // update to the db
        $update = Users::where('id', $id)->update([
            'status' => '0',
        ]);

        if ($update) {
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Vendor User Activated Successfully';
            return response()->json($this->result);
        } else {
            $this->result->status = true;
            $this->result->status_code = 404;
            $this->result->message = 'An Error Ocurred, Vendor Update failed';
            return response()->json($this->result);
        }
    }

    public function activate_vendor($id)
    {
        // update to the db
        $update = Vendors::where('id', $id)->update([
            'status' => '1',
        ]);

        if ($update) {
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Vendor Activated Successfully';
            return response()->json($this->result);
        } else {
            $this->result->status = true;
            $this->result->status_code = 404;
            $this->result->message = 'An Error Ocurred, Vendor Update failed';
            return response()->json($this->result);
        }
    }

    public function deactivate_vendor($id)
    {
        // update to the db
        $update = Vendors::where('id', $id)->update([
            'status' => '0',
        ]);

        if ($update) {
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Vendor Deactivated Successfully';
            return response()->json($this->result);
        } else {
            $this->result->status = true;
            $this->result->status_code = 404;
            $this->result->message = 'An Error Ocurred, Vendor Update failed';
            return response()->json($this->result);
        }
    }

    public function edit_vendor_data(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendorName' => 'required',
            'vendorCode' => 'required',
            'vendorId' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $name = $request->vendorName;
            $code = $request->vendorCode;
            $id = $request->vendorId;

            // update to the db
            $update = Vendors::where('id', $id)->update([
                'vendor_name' => $name,
                'vendor_code' => $code,
            ]);

            if ($update) {
                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'Vendor Updated Successfully';
                return response()->json($this->result);
            } else {
                $this->result->status = true;
                $this->result->status_code = 404;
                $this->result->message =
                    'An Error Ocurred, Vendor Update failed';
                return response()->json($this->result);
            }
        }
    }

    public function get_all_vendor_users()
    {
        $vendor_user = Users::where('role', '3')
            ->orderBy('id', 'desc')
            ->get();
        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'get all vendor users was successful';
        $this->result->data = $vendor_user;
        return response()->json($this->result);
    }

    public function upload_admin(Request $request)
    {
        $csv = $request->file('csv');
        if ($csv == null) {
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = 'Please upload admin in csv format';
            return response()->json($this->result);
        }

        if ($csv->getSize() > 0) {
            $file = fopen($_FILES['csv']['tmp_name'], 'r');
            $csv_data = [];
            while (($col = fgetcsv($file, 1000, ',')) !== false) {
                $csv_data[] = $col;
            }
            array_shift($csv_data);
            // remove the first row of the csv

            foreach ($csv_data as $key => $value) {
                $role = 0;
                if (strtolower($value[3]) == 'admin') {
                    $role = 1;
                }

                if (strtolower($value[3]) == 'branch manager') {
                    $role = 2;
                }

                if (strtolower($value[3]) == 'inside sales') {
                    $role = 5;
                }
                if (strtolower($value[3]) == 'outside sales') {
                    $role = 6;
                }

                $name = $value[0];
                $designation = $value[1];
                $email = $value[2];
                $role_name = $value[3];
                $first_level_access = $value[4];
                $second_level_access = $value[5];
                $password = bcrypt($value[6]);
                $password_show = $value[6];
                $region = $value[7];

                $save_admin = Admin::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'password_show' => $password_show,
                    'role' => $role,
                    'designation' => $designation,
                    'role_name' => $role_name,
                    'region_ab' => $region,
                    'first_level_access' => $first_level_access,
                    'second_level_access' => $second_level_access,
                ]);

                if (!$save_admin) {
                    $this->result->status = false;
                    $this->result->status_code = 422;
                    $this->result->message =
                        'Sorry File could not be uploaded. Try again later.';
                    return response()->json($this->result);
                }
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Admin uploaded successfully';
        return response()->json($this->result);
        fclose($file);
    }

    public function register_vendor_users(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'fullName' => 'required',
            'location' => 'required',
            'password' => 'required',
            'vendor' => 'required',
            'vendorName' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $name = $request->fullName;
            $email = $request->email;
            $location = $request->location;
            $vendor_code = $request->vendor;
            $privilege_vendors = $request->privilegeVendors;
            $password = bcrypt($request->password);
            $password_show = $request->password;
            $vendor_name = $request->vendorName;

            // $name_split = explode(' ', $name);
            // $first_name = $name_split[0];

            // save to the db
            $save_vendor = Users::create([
                'full_name' => $name,
                'first_name' => $name,
                'email' => $email,
                'password' => $password,
                'password_show' => $password_show,
                'role' => '3',
                'role_name' => 'vendor',
                'vendor_code' => $vendor_code,
                'vendor_name' => $vendor_name,
                'privilege_vendors' => $privilege_vendors,
                'username' => $email,
                'location' => $location,
                'company_name' => $vendor_name,
            ]);

            if ($save_vendor) {
                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'Vendor Successfully Added';
                return response()->json($this->result);
            } else {
                $this->result->status = true;
                $this->result->status_code = 404;
                $this->result->message =
                    'An Error Ocurred, Vendor Addition failed';
                return response()->json($this->result);
            }
        }
    }

    public function upload_vendor_users(Request $request)
    {
        $csv = $request->file('csv');
        if ($csv == null) {
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = 'Please upload dealers in csv format';
            return response()->json($this->result);
        }

        if ($csv->getSize() > 0) {
            $file = fopen($_FILES['csv']['tmp_name'], 'r');
            $csv_data = [];
            while (($col = fgetcsv($file, 1000, ',')) !== false) {
                $csv_data[] = $col;
            }
            array_shift($csv_data);
            // remove the first row of the csv

            foreach ($csv_data as $key => $value) {
                // `full_name`, `first_name`, `last_name`, `email`, `password`,
                // `password_show`, `role`, `role_name`, `dealer`, `vendor`,
                // `vendor_name`, `privileged_vendors`, `username`, `account_id`,
                // `phone`, `status`, `order_status`, `location`, `company_name`,
                // `last_login`,`login_device`, `place_order_date`, `created_at`,
                // `updated_at`
                $vendor_name = $value[0];
                $username = $value[1];
                $first_name = $value[2];
                $password = bcrypt($value[3]);
                // $password = bcrypt($value[4]);
                $password_show = $value[3];
                $privilege_vendors = $value[4];
                $email = strtolower($value[5]);
                $vendor_code = $value[7];
                $role = '3';
                $role_name = 'vendor';

                if (Users::where('email', $email)->exists()) {
                } else {
                    $save_users = Users::create([
                        'full_name' => $first_name,
                        'first_name' => $first_name,
                        'email' => $email,
                        'password' => $password,
                        'password_show' => $password_show,
                        'role' => $role,
                        'role_name' => $role_name,
                        // 'vendor' => $vendor,
                        'vendor_name' => $vendor_name,
                        'privileged_vendors' => $privilege_vendors,
                        'username' => $email,
                        'company_name' => $vendor_name,
                        'vendor_code' => $vendor_code,
                    ]);

                    if (!$save_users) {
                        $this->result->status = false;
                        $this->result->status_code = 422;
                        $this->result->message =
                            'Sorry File could not be uploaded. Try again later.';
                        return response()->json($this->result);
                    }
                }
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Vendor Users uploaded successfully';
        return response()->json($this->result);
        fclose($file);
    }

    public function register_vendors(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendorName' => 'required',
            'vendorCode' => 'required',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {
            // process the request
            $name = $request->vendorName;
            $code = $request->vendorCode;

            // save to the db
            $save_vendor = Vendors::create([
                'vendor_name' => $name,
                'vendor_code' => $code,
                'role_name' => 'vendor',
                'role' => '3',
            ]);

            if ($save_vendor) {
                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message = 'Vendor Successfully Added';
                return response()->json($this->result);
            } else {
                $this->result->status = true;
                $this->result->status_code = 404;
                $this->result->message =
                    'An Error Ocurred, Vendor Addition failed';
                return response()->json($this->result);
            }
        }
    }

    public function upload_vendors(Request $request)
    {
        $csv = $request->file('csv');
        if ($csv == null) {
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = 'Please upload dealers in csv format';
            return response()->json($this->result);
        }

        if ($csv->getSize() > 0) {
            $file = fopen($_FILES['csv']['tmp_name'], 'r');
            $csv_data = [];
            while (($col = fgetcsv($file, 1000, ',')) !== false) {
                $csv_data[] = $col;
            }
            array_shift($csv_data);
            // remove the first row of the csv
            foreach ($csv_data as $key => $value) {
                $vendor_name = $value[0];
                $vendor_id = $value[2];
                $role = 3;

                $save_product = Vendors::create([
                    'vendor_name' => $vendor_name,
                    'role_name' => 'vendor',
                    'vendor_code' => $vendor_id,
                    'role' => $role,
                ]);

                if (!$save_product) {
                    $this->result->status = false;
                    $this->result->status_code = 422;
                    $this->result->message =
                        'Sorry File could not be uploaded. Try again later.';
                    return response()->json($this->result);
                }
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Vendors uploaded successfully';
        return response()->json($this->result);
        fclose($file);
    }

    public function upload_users(Request $request)
    {
        $csv = $request->file('csv');
        if ($csv == null) {
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = 'Please upload dealers in csv format';
            return response()->json($this->result);
        }

        if ($csv->getSize() > 0) {
            $file = fopen($_FILES['csv']['tmp_name'], 'r');
            $csv_data = [];
            while (($col = fgetcsv($file, 1000, ',')) !== false) {
                $csv_data[] = $col;
            }
            array_shift($csv_data);
            // remove the first row of the csv
            foreach ($csv_data as $key => $value) {
                //$sep = explode( $value[ 1 ], '' );
                $dealer_code = $value[0];
                $full_name = $value[1];
                $location_text = $value[2];
                $phone = $value[3];
                $email = $value[4];
                $password = $value[5];
                $last_name = '';
                $location = 0;

                $save_product = Users::create([
                    'first_name' => $full_name,
                    'last_name' => null,
                    'email' => $email,
                    'password' => bcrypt($password),
                    'account_id' => $dealer_code,
                    'phone' => $phone,
                    'location' => $location_text,
                    'password_show' => $password,
                    'full_name' => $full_name,
                    'company_name' => $full_name,
                    'role' => '4',
                ]);

                if (!$save_product) {
                    $this->result->status = false;
                    $this->result->status_code = 422;
                    $this->result->message =
                        'Sorry File could not be uploaded. Try again later.';
                    return response()->json($this->result);
                }
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Dealers uploaded successfully';
            return response()->json($this->result);
            fclose($file);
        }
    }

    public function admin_login(Request $request)
    {
        if (
            !($token = Auth::guard('admin')->attempt([
                'email' => $request->email,
                'password' => $request->password,
            ]))
        ) {
            $this->result->status_code = 401;
            $this->result->message = 'Invalid login credentials';
            return response()->json($this->result);
        }

        $admin = Admin::query()
            ->where('email', $request->email)
            ->get()
            ->first();

        $this->result->token = $this->respondWithToken($token);
        $this->result->status = true;
        $this->result->data->admin = $admin;
        return response()->json($this->result);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' =>
                auth()
                    ->factory()
                    ->getTTL() * 60,
        ]);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
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
        return $tr->translate($text);
    }

    public function admin_get_all_reports()
    {
        /// App::setLocale('fr');

        $reports = Report::orderBy('id', 'desc')->get();
        $res_data = [];

        if ($reports) {
            foreach ($reports as $value) {
                $user = $value->user_id;
                $user_data = Users::where('id', $user)
                    ->get()
                    ->first();

                if ($user_data) {
                    $data = [
                        'full_name' => !is_null($user_data->full_name)
                            ? $user_data->full_name
                            : '',
                        'first_name' => $user_data->first_name,
                        'last_name' => $user_data->last_name,
                        'email' => $user_data->email,
                        'subject' => $value->subject,
                        'description' => $value->description,
                        'file_url' => $value->file_url,
                        'ticket_id' => $value->ticket_id,
                        'status' => $value->status,
                        'created_at' => $value->created_at,
                    ];

                    array_push($res_data, $data);
                }
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'All reports fetched successfully';
        $this->result->data = $res_data;
        return response()->json($this->result);
    }

    public function get_all_reports()
    {
        $reports = Report::join('users', 'users.id', '=', 'reports.user_id')
            ->select(
                'reports.*',
                'users.full_name',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.role',
                'users.role_name',
                'users.dealer_name',
                'users.vendor_name'
            )
            ->orderBy('reports.id', 'desc')
            ->get();

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'All reports fetched successfully';
        $this->result->data = $reports;
        return response()->json($this->result);
    }

    // get all reports by user_id
    public function fetch_reports_by_user_id($user_id)
    {
        $reports = Report::where('user_id', $user_id)
            ->join('users', 'users.id', '=', 'reports.user_id')
            ->select(
                'reports.*',
                'users.full_name',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.role',
                'users.role_name',
                'users.dealer_name',
                'users.vendor_name'
            )
            ->orderBy('reports.id', 'desc')
            ->get();

        if ($reports) {
            foreach ($reports as $value) {
                $ticket = $value->ticket_id;

                $count_admin_res = Report::where('ticket_id', $ticket)
                    ->where('company_name', 'atlas')
                    ->count();

                $value->admin_count = $count_admin_res;
            }
        }

        if (!$reports) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch report by user id";
            return response()->json($this->result);
        }

        if (count($reports) == 0) {
            $this->result->status = true;
            $this->result->status_code = 204;
            $this->result->data = $reports;
            $this->result->message = 'No report found for user id';
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $reports;
        $this->result->message = 'Reports from user id fetched successfully';
        return response()->json($this->result);
    }

    // dealer dashboard details
    public function dealer_dashboard()
    {
        // no of vendors ordered from / number of total vendors
    }

    public function testing_api()
    {
        echo 'hello woel';
    }
}
