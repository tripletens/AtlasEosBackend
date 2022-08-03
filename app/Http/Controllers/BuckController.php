<?php

namespace App\Http\Controllers;

use App\Models\Bucks;
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
            'file' => 'required|mimes:pdf,doc,docx,xls,jpg,jpeg,png,gif',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {

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
                'img_url' => $request->hasFile('file') ? $filepath : null
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
}
