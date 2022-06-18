<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;

class ProductsController extends Controller
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
    // fetch all new products
    public function fetch_all_new_products(){
        $fetch_new_products = Products::where('check_new',true)->orderby('id','desc')->get();

        if(!$fetch_new_products){
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message = "An Error Ocurred, we couldn't fetch all the new products";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_new_products;
        $this->result->message = 'All new Products fetched Successfully';
        return response()->json($this->result);
    }

    // sort new products by vendor id
    public function sort_newproduct_by_vendor_id($vendor_id){
        $fetch_new_products_by_vendor = Products::where('vendor',$vendor_id)->orderby('id','desc')->get();

        if(!$fetch_new_products_by_vendor){
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message = "An Error Ocurred, we couldn't fetch all the new products for the vendor";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_new_products_by_vendor;
        $this->result->message = 'All new products for vendor fetched successfully';
        return response()->json($this->result);
    }
    // sort new product by atlas_id
    public function sort_newproduct_by_atlas_id($atlas_id){
        $fetch_new_products_by_atlas_id = Products::where('atlas_id',$atlas_id)->orderby('id','desc')->get();

        if(!$fetch_new_products_by_atlas_id){
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message = "An Error Ocurred, we couldn't fetch the new products with the atlas id";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_new_products_by_atlas_id;
        $this->result->message = 'All new product with atlas id fetched successfully';
        return response()->json($this->result);
    }
}
