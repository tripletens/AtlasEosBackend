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

// use Maatwebsite\Excel\Facades\Excel;
// use App\Imports\ProductsImport;
// use App\Http\Helpers;
// use App\Models\Products;
// use App\Models\Category;
// use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Storage;
// use App\Models\Branch;
// use App\Models\Promotional_ads;
// use App\Models\Cart;
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

    public function activate_vendor_user()
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

    public function deactivate_vendor_user()
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

    ///// Permission Role Access
    // admin == 1
    // branch manager == 2
    // vendor == 3
    // dealer == 4
    // inside sales == 5
    // outside == 6

    public function get_all_vendor_users()
    {
        $vendor_user = Users::where('role', '3')->get();
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
                $dealer_code = $value[0];
                $vendor_name = $value[1];
                $first_name = $value[2];
                $last_name = $value[3];
                $password = bcrypt($value[4]);
                $password_show = $value[4];
                $email = $value[5];
                $privilege_vendors = $value[6];

                $role = '3';
                $role_name = 'vendor';

                $save_users = Users::create([
                    'full_name' => $first_name . ' ' . $last_name,
                    'first_name' => $first_name,
                    'email' => $email,
                    'password' => $password,
                    'password_show' => $password_show,
                    'role' => $role,
                    'role_name' => $role_name,
                    // 'vendor' => $vendor,
                    'vendor_name' => $vendor_name,
                    'privileged_vendors' => json_encode($privilege_vendors),
                    'username' => $email,
                    'company_name' => $vendor_name,
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
                'vendor_id' => $code,
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
                    'role' => '2',
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
