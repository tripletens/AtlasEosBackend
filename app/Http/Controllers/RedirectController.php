<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectController extends Controller
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

    public function redirect_Unauthenticated_Users(){

        $this->result->status = false;
        $this->result->status_code = 405;
        $this->result->message = "Sorry you are not authorized to use this service.";
        $this->result->data = null;
        return response()->json($this->result,405);
    }
}
