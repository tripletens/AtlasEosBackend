<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PromotionalFlier;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Vendors;
use Illuminate\Http\File;

class PromotionalFlierController extends Controller
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

    public function switch_promotional_flier_status($id)
    {
        $pro_flier = PromotionalFlier::where('id', $id)
            ->get()
            ->first();
        $current_state = $pro_flier->status;

        $switch_state = PromotionalFlier::where('id', $id)->update([
            'status' => $current_state == '1' ? '0' : '1',
        ]);

        // if ($current_state == '1') {
        //     $switch_state = PromotionalFlier::where('id', $id)->update([
        //         'status' => '0',
        //     ]);
        // } else {
        //     $switch_state = PromotionalFlier::where('id', $id)->update([
        //         'status' => '1',
        //     ]);
        // }

        if ($switch_state) {
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message =
                'Deactivate promotional flier was successfull';
        } else {
            $this->result->status = false;
            $this->result->status_code = 200;
            $this->result->message = 'Something went wrong, try again';
        }

        return response()->json($this->result);
    }

    public function create_promotional_flier(Request $request)
    {
        // 'vendor_id', 'name', 'pdf_url', 'description', 'image_url',
        //  'status', 'created_at', 'updated_at', 'deleted_at'
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required',
            'name' => 'required|string',
            'pdf' => 'required|mimes:pdf,doc,docx,xls,jpg,jpeg,png,gif',
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {

            return "hello";
            // if ($request->hasFile('pdf')) {
            //     $filenameWithExt = $request
            //         ->file('pdf')
            //         ->getClientOriginalName();
            //     $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            //     $extension = $request
            //         ->file('pdf')
            //         ->getClientOriginalExtension();
            //     $fileNameToStore = Str::slug($filename, '_', $language = 'en') . '_' . time() . '.' . $extension;
            //     $local_filepath =
            //         env('APP_URL') .
            //         Storage::url(
            //             $request
            //                 ->file('pdf')
            //                 ->storeAs('public/pdf', $fileNameToStore)
            //         );

            //     // Save to S3 (Grab the file object and stream it to S3 to a folder called 'file-uploads'. File is set to a public file that can be accessed by URL.
            //     $savetoS3 = Storage::disk('s3')->putFile('pdf', new File($local_filepath), 'public');


            //     // Delete local copy so it does not take space on your server
            //     Storage::delete($local_filepath);

            //     // $imageName = time().'.'.$request->image->extension();

            //     // $path = Storage::disk('s3')->put(
            //     //     'pdf',
            //     //     $request->pdf,
            //     //     'public'
            //     // );

            //     $full_file_path = Storage::disk('s3')->url($savetoS3);

            //     // Storage::setVisibility($full_file_path, 'public');
            // }

            // $name = $request->input('name');
            // // $pdf_url = $request->input('pdf_url');
            // $vendor_id = $request->input('vendor_id');
            // $description = $request->input('description');
            // $image_url = $request->input('image_url');

            // $createPromotionalFlier = PromotionalFlier::create([
            //     'name' => $name ? $name : null,
            //     'pdf_url' => $request->hasFile('pdf') ? $full_file_path : null,
            //     'vendor_id' => $vendor_id ? $vendor_id : null,
            //     'description' => $description ? $description : null,
            //     'image_url' => $image_url ? $image_url : null,
            // ]);

            // if (!$createPromotionalFlier) {
            //     $this->result->status = true;
            //     $this->result->status_code = 400;
            //     $this->result->message =
            //         'An error ocurred, Promotional Flier addition failed';
            //     return response()->json($this->result);
            // }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->message = 'Promotional Flier added successfully';
            return response()->json($this->result);
        }
    }
    public function show_all_promotional_fliers()
    {
        $all_promotional_fliers = PromotionalFlier::all();

        if (!$all_promotional_fliers) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch all the promotional fliers";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $all_promotional_fliers;
        $this->result->message = 'All Promotional Fliers fetched Successfully';
        return response()->json($this->result);
    }
    public function edit_promotional_flier(Request $request, $id)
    {
        $one_promotional_flier = PromotionalFlier::find($id);

        if (!$one_promotional_flier) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch the promotional flier";
            return response()->json($this->result);
        } else {
            // edit the promotional flier
            // 'vendor_id', 'name', 'pdf_url', 'description', 'image_url',
            //  'status', 'created_at', 'updated_at', 'deleted_at'
            $validator = Validator::make($request->all(), [
                'vendor_id' => 'required',
                'name' => 'required|string',
                'pdf' => 'required|mimes:pdf,doc,docx,xls,jpg,jpeg,png,gif',
            ]);

            if ($validator->fails()) {
                $response['response'] = $validator->messages();
                $this->result->status = false;
                $this->result->status_code = 422;
                $this->result->message = $response;

                return response()->json($this->result);
            } else {
                // 'vendor_id', 'name', 'pdf_url', 'description', 'image_url',
                //  'status', 'created_at', 'updated_at', 'deleted_at'

                $name = $request->input('name');
                $pdf = $request->file('pdf');
                $vendor_id = $request->input('vendor_id');
                $description = $request->input('description');
                $image_url = $request->input('image_url');

                if ($request->hasFile('pdf')) {
                    $filenameWithExt = $request
                        ->file('pdf')
                        ->getClientOriginalName();
                    $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension = $request
                        ->file('pdf')
                        ->getClientOriginalExtension();
                    $fileNameToStore =
                        Str::slug($filename, '_', $language = 'en') .
                        '_' .
                        time() .
                        '.' .
                        $extension;
                    $filepath =
                        env('APP_URL') .
                        Storage::url(
                            $request
                                ->file('pdf')
                                ->storeAs('public/pdf', $fileNameToStore)
                        );
                }

                $update_promotional_flier = $one_promotional_flier->update([
                    'name' => $name ? $name : null,
                    'pdf_url' => $request->hasFile('pdf') ? $filepath : null,
                    'vendor_id' => $vendor_id ? $vendor_id : null,
                    'description' => $description ? $description : null,
                    'image_url' => $image_url ? $image_url : null,
                ]);

                if (!$update_promotional_flier) {
                    $this->result->status = true;
                    $this->result->status_code = 400;
                    $this->result->message =
                        'An error ocurred, Promotional Flier could not be updated';
                    return response()->json($this->result);
                }

                $this->result->status = true;
                $this->result->status_code = 200;
                $this->result->message =
                    'Promotional Flier updated successfully';
                return response()->json($this->result);
            }
        }
    }

    public function show_promotional_flier_by_id($id)
    {
        $one_promotional_flier = PromotionalFlier::find($id);

        if (!$one_promotional_flier) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch the promotional fliers";
            return response()->json($this->result);
        } else {
        }
    }

    public function show_promotional_flier_by_vendor_id($vendor_id)
    {
        $one_promotional_flier = PromotionalFlier::where(
            'vendor_id',
            $vendor_id
        )->get();

        if (!$one_promotional_flier) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch the promotional fliers";
            return response()->json($this->result);
        } else {
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $one_promotional_flier;
            $this->result->message = 'Promotional Flier fetched Successfully';
            return response()->json($this->result);
        }
    }

    // delete the promotional flier
    public function delete_promotional_flier($id)
    {
        $promotional_flier = PromotionalFlier::find($id);

        if (!$promotional_flier) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch the promotional flier";
            return response()->json($this->result);
        }

        $delete_promotional_flier = $promotional_flier->delete();

        if (!$delete_promotional_flier) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't delete the promotional flier";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $promotional_flier;
        $this->result->message = 'Promotional Flier deleted Successfully';
        return response()->json($this->result);
    }

    # get all the vendors that have promotional fliers
    public function get_all_vendors_with_promotional_fliers()
    {
        $all_vendors_with_promotional_fliers = PromotionalFlier::select(
            'vendor_id'
        )
            ->groupBy('vendor_id')
            ->get();
        // ::distinct('vendor_id')
        // ->distinct()

        // return $all_vendors_with_promotional_fliers;

        if (!$all_vendors_with_promotional_fliers) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch all the vendors";
            return response()->json($this->result);
        }

        # get the vendor details
        $vendors = Vendors::whereIn(
            'vendor_code',
            $all_vendors_with_promotional_fliers
        )->get();

        if (!$vendors) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch the vendors";
            return response()->json($this->result);
        } else {
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $vendors;
            $this->result->message = 'Vendors fetched Successfully';
            return response()->json($this->result);
        }
    }
}
