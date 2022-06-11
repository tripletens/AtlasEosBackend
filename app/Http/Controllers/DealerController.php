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
use App\Models\Report;

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

    public function login(){
        echo "login page setup";
    }

    public function create_report(Request $request){
        $validator = Validator::make($request->all(), [
            'subject' => 'required',
            'description' => 'required',
            'photo' => 'mimes:pdf,doc,docx,xls,jpg,jpeg,png,gif'
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {

            if($request->hasFile('file'))
            {
                $filenameWithExt    = $request->file('file')->getClientOriginalName();
                $filename           = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension          = $request->file('file')->getClientOriginalExtension();
                $fileNameToStore    = $filename.'_'.time().'.'.$extension;
                $filepath               = env('APP_URL') . Storage::url($request->file('file')->storeAs('public/reports', $fileNameToStore));   
            }

            $subject = $request->input('subject');
                $description = $request->input('description');

                $create_report = Report::create([
                    'subject' => $subject ? $subject : null,
                    'description' => $description ? $description : null,
                    'file_url' => $request->hasFile('file') ? $filepath : null 
                ]);

                if(!$create_report){
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
}
