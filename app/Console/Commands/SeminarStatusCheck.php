<?php

namespace App\Console\Commands;

use App\Models\Seminar;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SeminarStatusCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seminar:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks the current status of the seminar and updates it with respect to the time difference.';

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
        // return 0;
        $this->info("/n Seminar status check working successfully");
        $this->check_seminar_status();
    }

    public function check_seminar_status(){
        $all_seminars = Seminar::all();

        # get current time
        $current_time = Carbon::now()->format('H:i:s');
        $current_date = Carbon::now()->format('Y-m-d');

        $seminar_differences = [];

        foreach($all_seminars as $key => $item){
            $seminar_time = Carbon::parse($item->seminar_date . $item->start_time);

            $startseminar_difference = $seminar_time->diffInMinutes($current_date . ' ' . $current_time, $absolute = false);

            // minus means that the seminar is still ahead like future
            // check if the start difference is <= 0
            // check for ongoing status of 2

            if($startseminar_difference >= 0){
                // check the status to in progress
                $find_seminar = Seminar::find($item->id);

                $find_seminar->update([
                    'status' => 2
                ]);
            }

            // check for ended seminars

            $seminar_endtime = Carbon::parse($item->seminar_date . $item->stop_time);

            $stopseminar_difference = $seminar_endtime->diffInMinutes($current_date . ' ' . $current_time, $absolute = false);

            if($stopseminar_difference >= 0){
                // check the status to watched / ended
                $find_seminar_new = Seminar::find($item->id);

                $find_seminar_new->update([
                    'status' => 3
                ]);
            }
        }

        // $seminar_time = Carbon::parse($all_seminars[0]->seminar_date . $all_seminars[0]->start_time);

        // $startseminar_difference = $seminar_time->diffInMinutes($current_date . ' ' . $current_time, $absolute = false);

        // // minus means that the seminar is still ahead like future
        // // check if the start difference is <= 0

        // if($startseminar_difference > 0){
        //     // check the status to in progress
        //     $find_seminar = semina
        // }

        // $seminar_endtime = Carbon::parse($all_seminars[0]->seminar_date . $all_seminars[0]->stop_time);

        // $stopseminar_difference = $seminar_endtime->diffInMinutes($current_date . ' ' . $current_time, $absolute = false);

        // dd($stopseminar_difference . " => current time " . $current_date . ' ' . $current_time . ' => seminar time ' . $all_seminars[0]->seminar_date . ' - '.  $all_seminars[0]->start_time);
    }
}
