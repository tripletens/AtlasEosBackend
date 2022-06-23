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
// use App\Models\Promotional_ads;
use App\Models\Cart;
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
        $seminar = Seminar::all();
        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $seminar;
        $this->result->message = 'All Seminar Fetched Successfully';
        return response()->json($this->result);
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

            // update to the db
            $save = Seminar::create([
                'topic' => $topic,
                'vendor_name' => $vendor_name,
                'vendor_id' => $vendor_id,
                'seminar_date' => $seminar_date,
                'start_time' => $start_time,
                'stop_time' => $stop_time,

                'link' => $link,
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

    public function dashboard()
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

        $this->result->status = true;
        $this->result->status_code = 200;

        $this->result->data->total_logged_vendors = $logged_vendors;
        $this->result->data->total_logged_admin = $logged_admin;
        $this->result->data->total_logged_dealers = $logged_dealers;

        $this->result->data->total_vendors = $total_vendors;
        $this->result->data->total_dealers = $total_dealers;
        $this->result->data->total_products = $total_products;
        $this->result->data->total_order = $total_order;

        $this->result->message = 'Admin Dashboard Data';
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
            'special' => 'required',
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
                'special' => $special,
                'vendor' => $vendor,
            ]);

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
                    // $booking = $value[8];
                    // $special = $value[9];
                    // $condition = $value[10];
                    // $type = $value[11];
                    // $grouping = $value[12];
                    // $desc_spec = $value[6];

                    // $spec_data = [
                    //     'booking' => floatval($booking),
                    //     'special' => floatval($special),
                    //     'cond' => intval($condition),
                    //     'type' => strtolower($type),
                    //     'desc' => strtolower($desc_spec),
                    // ];

                    // if ($special == '') {
                    //     continue;
                    // } else {
                    //     if (!empty($check_atlas_id->spec_data)) {
                    //         $spec = json_decode(
                    //             $check_atlas_id->spec_data,
                    //             true
                    //         );
                    //         array_push($spec, $spec_data);
                    //         $new_spec = json_encode($spec);

                    //         Products::where('atlas_id', $atlas_id)->update([
                    //             'grouping' => $grouping,
                    //         ]);
                    //         Products::where('atlas_id', $atlas_id)->update([
                    //             'spec_data' => $new_spec,
                    //         ]);
                    //     } else {
                    //         $data = [];
                    //         array_push($data, $spec_data);
                    //         $new_spec = json_encode($data);
                    //         //$new_spec = $new_spec;

                    //         Products::where('atlas_id', $atlas_id)->update([
                    //             'grouping' => $grouping,
                    //         ]);
                    //         Products::where('atlas_id', $atlas_id)->update([
                    //             'spec_data' => $new_spec,
                    //         ]);
                    //     }
                    // }
                } else {
                    $spec_arr = [];
                    $vendor_code = $value[0];
                    $vendor_name = $value[1];
                    $atlas_id = $value[2];
                    $vendor_product_code = $value[3];
                    $xref = $value[4];
                    $description = $value[5];
                    $regular_price = $value[6];
                    $special_price = $value[7];

                    $save_product = Products::create([
                        'atlas_id' => $atlas_id,
                        'description' => $description,
                        'status' => '1',
                        'vendor_code' => $vendor_code,
                        'vendor' => $vendor_code,
                        'vendor_name' => $vendor_name,
                        'vendor_product_code' => $vendor_product_code,
                        'xref' => $xref,
                        'booking' => $regular_price,
                        'special' => $special_price,
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
            $vendorName = $request->vendorName;
            $vendorCode = $request->vendorCode;
            $update = Users::where('id', $vendorId)->update([
                'vendor_name' => $vendorName,
                'vendor_code' => $vendorCode,
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
                'vendor' => $vendor_code,
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
                }

                if (!$save_users) {
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

    public function testing_api()
    {
        echo 'hello woel';
    }
}
