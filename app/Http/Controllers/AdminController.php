<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Admin;
use App\Models\Dealer;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;
use App\Http\Helpers;
use App\Models\Products;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Branch;
use App\Models\Promotional_ads;
use App\Models\Cart;
use App\Models\Catalogue_Order;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendDealerDetailsMail;

use App\Models\DealerCart;
use App\Models\ServiceParts;
use App\Models\CardedProducts;
use App\Models\PromotionalCategory;

use Barryvdh\DomPDF\Facade as PDF;

class AdminController extends Controller
{
    public function __construct()
    {
        //// $this->middleware( 'auth:api', [ 'except' => [ 'login', 'register', 'test' ] ] );
        $this->result = (object) [
            'status' => false,
            'status_code' => 200,
            'message' => null,
            'data' => (object) null,
            'token' => null,
            'debug' => null,
        ];
    }
}
