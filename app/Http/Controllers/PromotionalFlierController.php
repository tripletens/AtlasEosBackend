<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PromotionalFlier;
use Illuminate\Support\Facades\Validator;

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
    public function create_promotional_flier(Request $request)
    {
        // 'vendor_id', 'name', 'pdf_url', 'description', 'image_url',
        //  'status', 'created_at', 'updated_at', 'deleted_at'
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required',
            'name' => 'required|string',
            'pdf_url' => 'required|string'
        ]);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
            $this->result->status = false;
            $this->result->status_code = 422;
            $this->result->message = $response;

            return response()->json($this->result);
        } else {

            $name = $request->input('name');
            $pdf_url = $request->input('pdf_url');
            $vendor_id = $request->input('vendor_id');
            $description = $request->input('description');
            $image_url = $request->input('image_url');

            $createseminar = PromotionalFlier::create([
                'name' => $name ? $name : null,
                'pdf_url' => $pdf_url ? $pdf_url : null,
                'vendor_id' => $vendor_id ? $vendor_id : null,
                'description' => $description ? $description : null,
                'image_url' => $image_url ? $image_url : null
            ]);

            if (!$createseminar) {
                $this->result->status = true;
                $this->result->status_code = 400;
                $this->result->message =
                    'An error ocurred, Promotional Flier addition failed';
                return response()->json($this->result);
            }

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
            $this->result->message = "An Error Ocurred, we couldn't fetch all the promotional fliers";
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
            $this->result->message = "An Error Ocurred, we couldn't fetch the promotional flier";
            return response()->json($this->result);
        } else {
            // edit the promotional flier
            // 'vendor_id', 'name', 'pdf_url', 'description', 'image_url',
            //  'status', 'created_at', 'updated_at', 'deleted_at'
            $validator = Validator::make($request->all(), [
                'vendor_id' => 'required',
                'name' => 'required|string',
                'pdf_url' => 'required|string'
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
                $pdf_url = $request->input('pdf_url');
                $vendor_id = $request->input('vendor_id');
                $description = $request->input('description');
                $image_url = $request->input('image_url');

                $update_promotional_flier = $one_promotional_flier->update([
                    'name' => $name ? $name : null,
                    'pdf_url' => $pdf_url ? $pdf_url : null,
                    'vendor_id' => $vendor_id ? $vendor_id : null,
                    'description' => $description ? $description : null,
                    'image_url' => $image_url ? $image_url : null
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
                $this->result->message = 'Promotional Flier updated successfully';
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
            $this->result->message = "An Error Ocurred, we couldn't fetch the promotional fliers";
            return response()->json($this->result);
        } else {
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $one_promotional_flier;
            $this->result->message = 'Promotional Flier fetched Successfully';
            return response()->json($this->result);
        }
    }
    public function delete_promotional_flier($id)
    {
        $promotional_flier = PromotionalFlier::find($id);

        if (!$promotional_flier) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message = "An Error Ocurred, we couldn't fetch the promotional flier";
            return response()->json($this->result);
        }

        $delete_promotional_flier = $promotional_flier->delete();

        if (!$delete_promotional_flier) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message = "An Error Ocurred, we couldn't delete the promotional flier";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $promotional_flier;
        $this->result->message = 'Promotional Flier deleted Successfully';
        return response()->json($this->result);
    }
}
