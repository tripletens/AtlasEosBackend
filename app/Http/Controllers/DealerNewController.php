<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;

class DealerNewController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => ['login', 'register', 'test'],
        ]);
        $this->result = (object) [
            'status' => false,
            'status_code' => 200,
            'message' => null,
            'data' => (object) null,
            'token' => null,
            'debug' => null,
        ];
    }

    public function dealer_get_vendor_products($code)
    {
        if (
            Products::where('vendor', $code)
            ->where('status', '1')
            ->exists()
        ) {
            $vendor_products = Products::where('products.vendor', $code)
                ->where('products.status', '1')
                ->join('product_desc', 'product_desc.atlas_id', '=', 'products.atlas_id')
                ->orderBy('products.xref', 'asc')
                ->select('products.*', 'product_desc.*')
                ->get();

            foreach ($vendor_products as $value) {
                $value->spec_data = json_decode($value->spec_data);
            }

            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = $vendor_products;
            $this->result->message = 'all Vendor Products Data';
        } else {
            $this->result->status = true;
            $this->result->status_code = 200;
            $this->result->data = [];
            $this->result->message = 'no product found';
        }

        return response()->json($this->result);
    }
}
