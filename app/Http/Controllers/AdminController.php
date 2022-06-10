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

class AdminController extends Controller
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
                //$sep = explode( $value[ 1 ], '' );
                $vendor_name = $value[0];
                $role_name = $value[1];
                $vendor_id = $value[2];
                $role = 3;

                $save_product = Vendors::create([
                    'vendor_name' => $vendor_name,
                    'role_name' => strtolower($role_name),
                    'vendor_id' => $vendor_id,
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
        $this->result->message = 'Dealers uploaded successfully';
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
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->message = 'Dealers uploaded successfully';
        return response()->json($this->result);
        fclose($file);
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

        // $admin = Admin::where('email', $request->email)->first();
        // $admin->role = 'admin';

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
