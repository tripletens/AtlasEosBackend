<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Seminar;
use App\Models\SeminarMembers;
use App\Models\Users;
use Carbon\Carbon;
use DB;

class SeminarController extends Controller
{
    protected $SEMINAR_REMINDER_TIME = 5;
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

    // fetch all the seminars
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

    // fetch all scheduled seminars
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

    // fetch all ongoing seminars
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

    // fetched watched seminars
    public function fetch_watched_seminars(){
        // this is for ended seminars
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

    // bookmark a seminar i.e join a seminar
    public function join_seminar(Request $request){
        // `seminar_id`, `dealer_id`, `bookmark_status`
        // `current_seminar_status`, `status`,
        $validator = Validator::make($request->all(), [
            'seminar_id' => 'required|integer',
            'dealer_id' => 'required|integer',
            'bookmark_status' => 'required|integer',
            'current_seminar_status' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {

            // check if the seminar exists


            $seminar_id = $request->input('seminar_id');
            $dealer_id = $request->input('dealer_id');
            $bookmark_status = $request->input('bookmark_status');
            $current_seminar_status = $request->input('current_seminar_status');

            $createseminar = SeminarMembers::create([
                'seminar_id' => $seminar_id ? $seminar_id : null,
                'dealer_id' => $dealer_id ? $dealer_id : null,
                'bookmark_status' => $bookmark_status ? $bookmark_status : null,
                'current_seminar_status' => $current_seminar_status ? $current_seminar_status : null
            ]);

            if (!$createseminar) {
                $this->result->status = true;
                $this->result->status_code = 400;
                $this->result->message =
                    'Sorry you cannot join the seminar at this time. Try again later';
                return response()->json($this->result);
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'You have successfully joined the seminar';
            return response()->json($this->result);
        }
    }

    // fetch all the dealers that joined the seminar
    public function fetch_all_dealers_in_seminar(){
        // role 4 is for dealers
        $fetch_dealers = Users::where('role',4)->join('seminar_members','users.id','=','seminar_members.dealer_id')
        ->get();

        if(!$fetch_dealers){
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                'Sorry you cannot fetch the dealers that joined the seminar. Try again later';
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_dealers;
        $this->result->message = 'You have successfully fetched all the dealers that joined the seminar';
        return response()->json($this->result);
    }

    // fetch all the dealers that didnt bookmark a seminar
    public function fetch_all_dealers_not_in_seminar(){

        $fetch_dealers = SeminarMembers::all()->join('users','seminar_members.id','!=','users.id')
        ->get();

        if(!$fetch_dealers){
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                'Sorry you cannot fetch the dealers that joined the seminar. Try again later';
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_dealers;
        $this->result->message = 'You have successfully fetched all the dealers that joined the seminar';
        return response()->json($this->result);
    }

    // activate seminar cron job

    // send email to dealer that bookmarked the seminar 15 mins before the seminar time

    public function select_seminars_to_remind(){
        // selects all the seminars that are 15 mins less than the current time
        $current_time = Carbon::now();
        $time_diff = Carbon::diff();
        // $seminar_reminder_time = SEMINAR_REMINDER_TIME;
        // $find_seminars = Seminar::where();
    }

    public function select_dealers_bookmarked_seminar($seminar_id){
        // selects all the dealers that bookmarked the individual seminars

    }

    public function send_reminder_email(){
        // select all the people that bookmarked the individual seminars
        // send them an email each
    }
}
