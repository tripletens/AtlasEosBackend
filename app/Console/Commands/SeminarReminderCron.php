<?php

namespace App\Console\Commands;

use App\Mail\SeminarEmail;
use App\Models\Seminar;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SeminarReminderCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seminar:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is a reminder to the dealers that bookmarked to attend a seminar';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $send_seminar_reminder = $this->select_seminars_to_remind();

        if(!$send_seminar_reminder){
            return $this->error('sorry seminar reminders couldnt be sent');
        }

        $this->info("/n Seminar Reminders sent successfully ". $send_seminar_reminder);
    }

    // fetch all the dealers that joined the seminar
    public function fetch_all_dealers_in_seminar($seminar_id)
    {
        // role 4 is for dealers
        $fetch_dealers = Users::where('role', 4)->join('seminar_members', 'users.id', '=', 'seminar_members.dealer_id')
            ->where('seminar_members.seminar_id', $seminar_id)
            ->get();

        if (!$fetch_dealers) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                'Sorry you cannot fetch the dealers that joined the seminar. Try again later';
            return response()->json($this->result);
        }

        return $fetch_dealers;
    }


    public function fetch_only_dealer_emails($seminar_id)
    {
        $all_dealers = $this->fetch_all_dealers_in_seminar($seminar_id);
        $dealer_emails = [];
        foreach ($all_dealers as $dealer) {
            array_push($dealer_emails, $dealer->email);
        }
        return $dealer_emails;
    }

    public function select_seminars_to_remind()
    {
        // selects all the seminars that are 15 mins less than the current time
        // time difference between current time and seminar time
        // if the difference is less than 15 mins, send an email to the dealer
        $current_time = Carbon::now()->format('H:i:s');
        $current_date = Carbon::now()->format('Y-m-d');

        $get_all_seminars_with_seminar_date_and_seminar_time_less_than_today = Seminar::where('status', 1)
            ->where('seminar_date', $current_date)
            ->get();

        #get the difference between the current time and the seminar time
        $get_all_seminars_with_seminar_date_and_seminar_time_less_than_today->each(function ($seminar) {
            $current_time = Carbon::now();
            $seminar_time = Carbon::parse($seminar->seminar_date . $seminar->start_time);
            $difference = $seminar_time->diffInMinutes($current_time, $absolute = false);

            // $difference < -15  < -15 && $difference < 1
            if ($difference < 15 && $difference < 1) {
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

        return $get_all_seminars_with_seminar_date_and_seminar_time_less_than_today;
    }

    public function send_reminder_email($emails, $dealer_and_seminar_data)
    {
        // select all the people that bookmarked the individual seminars
        // send them an email each
        foreach ($emails as $recipient) {
            Mail::to($recipient)->send(new SeminarEmail($dealer_and_seminar_data));
        }
    }

    // check the status of the seminar based on the time difference
    public function check_seminar_status(){
        $all_seminars = Seminar::all();

        return $all_seminars;
    }
}
