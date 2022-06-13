<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Seminar;
use Carbon\Carbon;

class SeminarController extends Controller
{
    public function __construct()
    {
        // $this->middleware( 'auth:api');
        $this->result = (object) [
            'status' => false,
            'status_code' => 200,
            'message' => null,
            'data' => (object) null,
            'token' => null,
            'debug' => null,
        ];
    }

    // create seminar api
    public function create_seminar(Request $request){
        // `seminar_name`, `vendor_name`, `vendor_id`, `seminar_date`, `seminar_time`, 
        // `bookmark`, `status`, `created_at`, `updated_at`, `deleted_at`
        // status => [ 1 => 'scheduled', 2 => 'ongoing', 3 => 'watched']
        $validator = Validator::make($request->all(), [
            'seminar_name' => 'required',
            'vendor_name' => 'required',
            'vendor_id' => 'required',
            'seminar_date' => 'required|date',
            'seminar_time' => 'required|date_format:H:i',
            'bookmark' => 'required|boolean',
            'status' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {

            $seminar_name = $request->input('seminar_name');
            $vendor_name = $request->input('vendor_name');
            $vendor_id = $request->input('vendor_id');
            $seminar_date = Carbon::parse($request->input('seminar_date'))->format('Y-m-d H:i');
            $seminar_time = Carbon::parse($request->input('seminar_time'))->format('H:i:s');
            $bookmark = $request->input('bookmark');
            $status = $request->input('status');

            $createseminar = Seminar::create([
                'seminar_name' => $seminar_name ? $seminar_name : null,
                'vendor_name' => $vendor_name ? $vendor_name : null,
                'vendor_id' => $vendor_id ? $vendor_id : null,
                'seminar_date' => $seminar_date ? $seminar_date : null,
                'seminar_time' => $seminar_time ? $seminar_time : null,
                'bookmark' => $bookmark ? $bookmark : null,
                'status' => $status ? $status : null,
            ]);

            if (!$createseminar) {
                $this->result->status = true;
                $this->result->status_code = 400;
                $this->result->message =
                    'An error ocurred, seminar addition failed';
                return response()->json($this->result);
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Seminar created successfully';
            return response()->json($this->result);
        }
    }
    public function fetch_all_seminars(){
        $fetch_seminars = Seminar::orderBy('id','desc')->get();

        if(!$fetch_seminars){
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message = "An Error Ocurred, we couldn't fetch all the Seminars";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_seminars;
        $this->result->message = 'Seminar fetched Successfully';
        return response()->json($this->result);
    }

    public function fetch_scheduled_seminars(){
        // this is for active seminars
        $fetch_seminars = Seminar::where('status',1)->orderBy('id','desc')->get();

        if(!$fetch_seminars){
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message = "An Error Ocurred, we couldn't fetch all the scheduled/active Seminars";
            return response()->json($this->result);
        }

        if(count($fetch_seminars) == 0){
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $fetch_seminars;
            $this->result->message = "Sorry no Active/Scheduled Seminar available";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_seminars;
        $this->result->message = 'Scheduled Seminars fetched Successfully';
        return response()->json($this->result);
    }

    public function fetch_ongoing_seminars(){
        // this is for active seminars
        $fetch_seminars = Seminar::where('status',2)->orderBy('id','desc')->get();

        if(!$fetch_seminars){
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message = "An Error Ocurred, we couldn't fetch all the ongoing Seminars";
            return response()->json($this->result);
        }

        if(count($fetch_seminars) == 0){
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $fetch_seminars;
            $this->result->message = "Sorry no Ongoing Seminar now";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_seminars;
        $this->result->message = 'Ongoing Seminars fetched Successfully';
        return response()->json($this->result);
    }

    public function fetch_watched_seminars(){
        // this is for active seminars
        $fetch_seminars = Seminar::where('status',0)->orderBy('id','desc')->get();

        if(!$fetch_seminars){
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message = "An Error Ocurred, we couldn't fetch all the watched Seminars";
            return response()->json($this->result);
        }

        if(count($fetch_seminars) == 0){
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $fetch_seminars;
            $this->result->message = "Sorry no watched Seminar now";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_seminars;
        $this->result->message = 'Watched Seminars fetched Successfully';
        return response()->json($this->result);
    }
    
    
}
