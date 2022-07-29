<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Seminar;
use App\Models\SeminarMembers;
use App\Models\Users;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SeminarEmail;
use App\Models\Dealer;

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

    public function check_seminar_status($seminar_date,$start_time,$stop_time){
        $seminar_time = 
        $current_time = Carbon::now();
        $seminar_time = Carbon::parse($seminar_date . $seminar_time);
        
        $difference = $seminar_time->diffInMinutes($current_time, $absolute = false);

        // check for scheduled =  1
        // check for ongoing = 2 
        // check for completed = 3

        return $difference;
        // $difference = $seminar_time->diffInMinutes($current_time, $absolute = false);
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
            // $status = $request->input('status');

            return $this->check_seminar_status($seminar_time,$seminar_date);

            $createseminar = Seminar::create([
                'seminar_name' => $seminar_name ? $seminar_name : null,
                'vendor_name' => $vendor_name ? $vendor_name : null,
                'vendor_id' => $vendor_id ? $vendor_id : null,
                'seminar_date' => $seminar_date ? $seminar_date : null,
                'seminar_time' => $seminar_time ? $seminar_time : null,
                'bookmark' => $bookmark ? $bookmark : null,
                // 'status' => $status ? $status : null,
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
    public function fetch_all_seminars($dealer_id){
        $fetch_seminars = Seminar::orderBy('id','desc')->get();
        // $check_bookmarked = [];

        if(!$fetch_seminars){
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message = "An Error Ocurred, we couldn't fetch all the Seminars";
            return response()->json($this->result);
        }

        // check if the user has bookmarked the seminar

        foreach($fetch_seminars as $seminar){
            $check_bookmarked = 
                SeminarMembers::where('seminar_id',$seminar->id)->where('dealer_id',$dealer_id)->first();
            if($check_bookmarked){
                $seminar->bookmarked = true;
            }else{
                $seminar->bookmarked = false;
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_seminars;
        $this->result->message = 'Seminar fetched Successfully';
        return response()->json($this->result);
    }

    // fetch all scheduled seminars
    public function fetch_scheduled_seminars($dealer_id){
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

        foreach($fetch_seminars as $seminar){
            $check_bookmarked = 
                SeminarMembers::where('seminar_id',$seminar->id)->where('dealer_id',$dealer_id)->first();
            if($check_bookmarked){
                $seminar->bookmarked = true;
            }else{
                $seminar->bookmarked = false;
            }
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_seminars;
        $this->result->message = 'Scheduled Seminars fetched Successfully';
        return response()->json($this->result);
    }

    // fetch all ongoing seminars
    public function fetch_ongoing_seminars($dealer_id){
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

        foreach($fetch_seminars as $seminar){
            $check_bookmarked = 
                SeminarMembers::where('seminar_id',$seminar->id)->where('dealer_id',$dealer_id)->first();
            if($check_bookmarked){
                $seminar->bookmarked = true;
            }else{
                $seminar->bookmarked = false;
            }
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
        $fetch_seminars = Seminar::where('status',3)->orderBy('id','desc')->get();

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

        foreach($fetch_seminars as $seminar){
            $check_bookmarked = 
                SeminarMembers::where('seminar_id',$seminar->id)->where('dealer_id',$dealer_id)->first();
            if($check_bookmarked){
                $seminar->bookmarked = true;
            }else{
                $seminar->bookmarked = false;
            }
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
            'dealer_id' => 'required',
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
    public function fetch_all_dealers_in_seminar($seminar_id){
        // role 4 is for dealers
        $fetch_dealers = Users::where('role',4)->join('seminar_members','users.id','=','seminar_members.dealer_id')
        ->where('seminar_members.seminar_id', $seminar_id)
        ->get();

        if(!$fetch_dealers){
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                'Sorry you cannot fetch the dealers that joined the seminar. Try again later';
            return response()->json($this->result);
        }

        return $fetch_dealers;

        // # get all the dealers in
        // $this->result->status = true;
        // $this->result->status_code = 200;
        // $this->result->data = $fetch_dealers;
        // $this->result->message = 'You have successfully fetched all the dealers that joined the seminar';
        // return response()->json($this->result);
    }


    public function fetch_only_dealer_emails($seminar_id){
        $all_dealers = $this->fetch_all_dealers_in_seminar($seminar_id);
        $dealer_emails = [];
        foreach($all_dealers as $dealer){
            array_push($dealer_emails,$dealer->email);
        }
        return $dealer_emails;
    }

    // fetch all the dealers that didnt bookmark a seminar
    public function fetch_all_dealers_not_in_seminar(){

        # get all the dealers that didnt bookmark a seminar
        $fetch_dealers = Users::where('role',4)->join('seminar_members','users.id','=','seminar_members.dealer_id')
        ->where('seminar_members.bookmark_status',0)
        ->get();

        // $fetch_dealers = SeminarMembers::all()->join('users','seminar_members.id','!=','users.id')
        // ->get();

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
        // time difference between current time and seminar time
        // if the difference is less than 15 mins, send an email to the dealer
        $current_time = Carbon::now()->format('H:i:s');
        $current_date = Carbon::now()->format('Y-m-d');

        $get_all_seminars_with_seminar_date_and_seminar_time_less_than_today = Seminar::where('status',1)
        ->where('seminar_date',$current_date)
        ->get();

        #get the difference between the current time and the seminar time
        $get_all_seminars_with_seminar_date_and_seminar_time_less_than_today->each(function($seminar){
            $current_time = Carbon::now();
            $seminar_time = Carbon::parse($seminar->seminar_date . $seminar->seminar_time);
            $difference = $seminar_time->diffInMinutes($current_time, $absolute = false);

            // $difference < -15  < -15 && $difference < 1
            if($difference < 1000000000000000){
                // $this->send_email_to_dealer($seminar);
                $all_dealers_that_joined_seminar = $this->fetch_all_dealers_in_seminar($seminar->id);
                $all_dealer_emails = $this->fetch_only_dealer_emails($seminar->id);

                $mail_data = [
                    'seminar_data' => $seminar,
                    'dealer_data' => $all_dealers_that_joined_seminar
                ];

                $this->send_reminder_email($all_dealer_emails, $mail_data);
            }
            $seminar->difference = $difference;
        });

        return true;

        // $this->result->status = true;
        // $this->result->status_code = 200;
        // $this->result->data = $get_all_seminars_with_seminar_date_and_seminar_time_less_than_today;
        // $this->result->message = 'Seminar reminders sent successfully';
        // return response()->json($this->result);
        // return $get_all_seminars_with_seminar_date_and_seminar_time_less_than_today;
    }


    public function send_reminder_email($emails, $dealer_and_seminar_data){
        // select all the people that bookmarked the individual seminars
        // send them an email each
        foreach ($emails as $recipient) {
            Mail::to($recipient)->send(new SeminarEmail($dealer_and_seminar_data));
        }
    }
}
