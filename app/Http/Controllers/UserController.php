<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Dealer;
use App\Models\Users;

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
use App\Models\ProgramCountdown;

class UserController extends Controller
{
    //

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

    public function login(Request $request)
    {
        //valid credential
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (
            !($token = Auth::guard('api')->attempt([
                'email' => $request->email,
                'password' => $request->password,
            ]))
        ) {
            $this->result->status_code = 401;
            $this->result->message = 'Invalid login credentials';
            return response()->json($this->result);
        }

        $active_staff = Users::query()
            ->where('email', $request->email)
            ->get()
            ->first();

        if ($active_staff['status'] == 0) {
            $this->result->status_code = 401;
            $this->result->message = 'Account has been deactivated';
            return response()->json($this->result);
        }

        if ($active_staff['role'] != '1') {
            $count_down = ProgramCountdown::where('status', '1')
                ->get()
                ->first();

            $end_date = $count_down->end_countdown_date;
            $end_time = $count_down->end_countdown_time;
            $end_count = $end_date . ' ' . $end_time;
            $end_program = Carbon::createFromFormat(
                'Y-m-d H:i',
                $end_count
            )->format('Y-m-d H:i');

            $ch = new Carbon($end_program);
            $current = Carbon::now();

            if (!$ch->gt($current)) {
                $this->result->status = false;
                $this->result->message = 'Program has closed';
                return response()->json($this->result);
            }
        }

        $dealer = Users::where('email', $request->email)->first();
        $dealer_details = Users::where('email', $request->email)->get();

        $dealer_details[0]->update([
            'last_login' => Carbon::now(),
        ]);

        $this->result->token = $this->respondWithToken($token);
        $this->result->status = true;
        $this->result->data->dealer = $dealer;
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
}
