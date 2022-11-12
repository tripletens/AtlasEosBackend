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
    public function fetch_all_new_products()
    {
        $fetch_new_products = Products::where('check_new', '1')
            ->orderby('vendor_name', 'asc')
            ->get();

        if ($fetch_new_products) {
            $vendor_arr = [];
            foreach ($fetch_new_products as $value) {
                $vendor_name = $value->vendor_name;
                $vendor_code = $value->vendor_code;

                // if (!in_array($vendor_code, $vendor_arr)) {
                //     array_push($vendor_arr, $vendor_code);
                // }

                if (!in_array($vendor_name, $vendor_arr)) {
                    array_push($vendor_arr, $vendor_name);
                }
            }

            ////  return count($vendor_arr);

            $mix_up = [];

            global $glo_vendor;

            $count_check = 0;

            foreach ($vendor_arr as $value) {
                $glo_vendor = $value;

                $single = [];

                foreach ($fetch_new_products as $db_value) {
                    $vendor_name = $db_value->vendor_name;

                    // if ($db_value->spec_data != null) {
                    //     $db_value->spec_data = json_decode(
                    //         $db_value->spec_data
                    //     );
                    // }

                    if ($glo_vendor == $vendor_name) {
                        array_push($single, $db_value);
                    }
                }

                $count_check += count($single);

                $dd = [
                    'vendor' => $value,
                    'data' => $single,
                ];

                array_push($mix_up, $dd);
            }

            $res = [];

            foreach ($mix_up as $value) {
                usort($value['data'], function ($object1, $object2) {
                    return $object1->xref > $object2->xref;
                });

                foreach ($value['data'] as $data_val) {
                    array_push($res, $data_val);
                }
            }
        }

        // foreach ($fetch_new_products as $value) {
        //     $value->spec_data = json_decode($value->spec_data);
        // }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $res;
        $this->result->message = 'All new Products fetched Successfully';
        return response()->json($this->result);
    }

    // sort new products by vendor id
    public function sort_newproduct_by_vendor_id($vendor_id)
    {
        $fetch_new_products_by_vendor = Products::where('check_new', '1')
            ->where('vendor', $vendor_id)
            ->orderby('id', 'asc')
            ->get();

        foreach ($fetch_new_products_by_vendor as $value) {
            $value->spec_data = json_decode($value->spec_data);
        }

        if (!$fetch_new_products_by_vendor) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch all the new products for the vendor";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_new_products_by_vendor;
        $this->result->message =
            'All new products for vendor fetched successfully';
        return response()->json($this->result);
    }
    // sort new product by atlas_id
    public function sort_newproduct_by_atlas_id($atlas_id)
    {
        $fetch_new_products_by_atlas_id = Products::where('atlas_id', $atlas_id)
            ->orderby('atlas_id', 'asc')
            ->get();

        if (!$fetch_new_products_by_atlas_id) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch the new products with the atlas id";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_new_products_by_atlas_id;
        $this->result->message =
            'All new product with atlas id fetched successfully';
        return response()->json($this->result);
    }

    // fetch all the products by vendors id
    public function fetch_all_products_by_vendor_id($vendor_id)
    {
        $fetch_all_products_by_vendor = Products::where('vendor', $vendor_id)
            ->orderby('atlas_id', 'asc')
            ->get();

        if (!$fetch_all_products_by_vendor) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch all the products for the vendor";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_all_products_by_vendor;
        $this->result->message = 'All products for vendor fetched successfully';
        return response()->json($this->result);
    }

    // fetch all the new products by vendor code
    public function fetch_all_products_by_vendor_code($vendor_code)
    {
        $fetch_all_products_by_vendor_code = Products::where(
            'vendor_code',
            $vendor_code
        )
            ->orderby('atlas_id', 'asc')
            ->get();

        if (!$fetch_all_products_by_vendor_code) {
            $this->result->status = true;
            $this->result->status_code = 400;
            $this->result->message =
                "An Error Ocurred, we couldn't fetch all the products for the vendor";
            return response()->json($this->result);
        }

        $this->result->status = true;
        $this->result->status_code = 200;
        $this->result->data = $fetch_all_products_by_vendor_code;
        $this->result->message =
            'All products for vendor code fetched successfully';
        return response()->json($this->result);
    }
}
