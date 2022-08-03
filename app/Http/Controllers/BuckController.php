<?php

namespace App\Http\Controllers;

use App\Models\Bucks;
use App\Models\PromotionalFlier;
use App\Models\Vendors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BuckController extends Controller
{
    //
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
    // upload file 

    public function upload_file(){
        
    }
    // add show bucks 
    public function create_buck(Request $request)
    {
        // `vendor_name`, `vendor_code`,`title`, `description`, `img_url`,
        //  `status`,

        $validator = Validator::make($request->all(), [
            'vendor_name' => 'required',
            'vendor_code' => 'required|string',
            'title' => 'required|string',
            'description' => 'required',
            'status' => 'required|boolean',
            'pdf' => 'required|mimes:pdf,doc,docx',
            'image' => 'required|mimes:jpg,jpeg,png,gif',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {

            if ($request->hasFile('image')) {
                $image_filenameWithExt = $request
                    ->file('image')
                    ->getClientOriginalName();
                $image_filename = pathinfo($image_filenameWithExt, PATHINFO_FILENAME);
                $image_extension = $request
                    ->file('image')
                    ->getClientOriginalExtension();
                $image_fileNameToStore = Str::slug($image_filename,'_',$language='en') . '_' . time() . '.' . $image_extension;
                $img_filepath =
                    env('APP_URL') .
                    Storage::url(
                        $request
                            ->file('image')
                            ->storeAs('public/bucks', $image_fileNameToStore)
                    );
            }

            if ($request->hasFile('pdf')) {
                $filenameWithExt = $request
                    ->file('pdf')
                    ->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request
                    ->file('pdf')
                    ->getClientOriginalExtension();
                $fileNameToStore = Str::slug($filename,'_',$language='en') . '_' . time() . '.' . $extension;
                $pdf_filepath =
                    env('APP_URL') .
                    Storage::url(
                        $request
                            ->file('pdf')
                            ->storeAs('public/bucks', $fileNameToStore)
                    );
            }

            // `vendor_name`, `vendor_code`,`title`, `description`, `img_url`,
            //  `status`,

            $vendor_name = $request->input('vendor_name');
            $vendor_code = $request->input('vendor_code');
            $title = $request->input('title');
            $description = $request->input('description');
            $vendor_code = $request->input('vendor_code');
            $status = $request->input('status');

            $createBuck = Bucks::create([
                'vendor_name' => $vendor_name ? $vendor_name : null,
                'vendor_code' => $vendor_code ? $vendor_code : null,
                'title' => $title ? $title : null,
                'description' => $description ? $description : null,
                'status' => $status ? $status : null,
                'img_url' => $request->hasFile('image') ? $img_filepath : null,
                'pdf_url' => $request->hasFile('pdf') ? $pdf_filepath : null
            ]);

            if (!$createBuck) {
                $this->result->status = true;
                $this->result->status_code = 400;
                $this->result->message =
                    'An error ocurred, Show Bucks addition failed';
                return response()->json($this->result);
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Show Bucks added successfully';
            return response()->json($this->result);
        }
    }

    // fetch show bucks and promotional flier by vendor code 

    public function fetch_show_buck_promotional_flier($vendor_code){

        $fetch_vendor_details = Vendors::where('vendor_code',$vendor_code)->get()->first();

        if (!$fetch_vendor_details) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message = "An Error Ocurred, we couldn't fetch the vendor with that vendor code";
            return response()->json($this->result);
        } 

        $fetch_show_bucks = Bucks::where('vendor_code',$vendor_code)->get();

        if (!$fetch_show_bucks) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message = "An Error Ocurred, we couldn't fetch the show bucks with that vendor code";
            return response()->json($this->result);
        } else {

            // check for promotional fliers with vendor code 
            $fetch_promotional_flier = PromotionalFlier::where('vendor_id',$vendor_code)->get();
            
            if (!$fetch_promotional_flier) {
                $this->result->status = true;
                $this->result->status_code = 400;
                $this->result->message = "An Error Ocurred, we couldn't fetch the promotional flier with that vendor code";
                return response()->json($this->result);
            }
            // send details to you
            $vendor_name = $fetch_vendor_details->vendor_name;
            $vendor_code = $fetch_vendor_details->vendor_code;
            $vendor_status = $fetch_vendor_details->status == 1 ? true : false;
            $data = [
                "vendor_name" => $vendor_name,
                "vendor_code" => $vendor_code,
                "vendor_status" => $vendor_status,
                "bucks" => $fetch_show_bucks,
                "promotional_fliers" => $fetch_promotional_flier
            ];

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $data;
            $this->result->message = 'Promotional Flier fetched Successfully';
            return response()->json($this->result);
        }
    }

    public function edit_buck(Request $request){
        $validator = Validator::make($request->all(), [
            'vendor_name' => 'required',
            'vendor_code' => 'required|string',
            'title' => 'required|string',
            'description' => 'required',
            'status' => 'required|boolean',
            'file' => 'required|mimes:pdf,doc,docx,xls,jpg,jpeg,Jpeg,png,gif',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {

            $vendor_code = $request->input('vendor_code');
            $vendor_name = $request->input('vendor_name');
            $title = $request->input('title');
            $description = $request->input('description');
            $status = $request->input('status');
            $file =   $request->hasFile('file');

            $check_buck = Bucks::where('vendor_code',$vendor_code)->get()->first();

            if (!$check_buck) {
                $this->result->status = true;
                $this->result->status_code = 400;
                $this->result->message = "An Error Ocurred, we couldn't fetch the show bucks with that vendor code";
                return response()->json($this->result);
            }

            if ($request->hasFile('file')) {
                $filenameWithExt = $request
                    ->file('file')
                    ->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request
                    ->file('file')
                    ->getClientOriginalExtension();
                $fileNameToStore = Str::slug($filename,'_',$language='en') . '_' . time() . '.' . $extension;
                $filepath =
                    env('APP_URL') .
                    Storage::url(
                        $request
                            ->file('file')
                            ->storeAs('public/bucks', $fileNameToStore)
                    );
            }

            $check_buck->vendor_name = $vendor_name ? $vendor_name : null;
            $check_buck->vendor_code = $vendor_code ? $vendor_code : null;
            $check_buck->title = $title ? $title : null;
            $check_buck->description = $description ? $description : null;
            $check_buck->status = $status ? $status : null;
            $check_buck->img_url = $file ? $filepath : null;


            $update_buck = $check_buck->save();

            if (!$update_buck) {
                $this->result->status = true;
                $this->result->status_code = 400;
                $this->result->message = "An Error Ocurred, we couldn't update the show bucks with that vendor code";
                return response()->json($this->result);
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $update_buck;
            $this->result->message = 'Show Buck updated Successfully';
            return response()->json($this->result);
        }
    }
}
