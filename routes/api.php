<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

///////////////// Admin /////////////
Route::group(
    [
        'namespace' => 'App\Http\Controllers',
        ///  'middleware' => 'cors',
    ],
    function () {
        Route::post('/admin-login', 'AdminController@admin_login');
        Route::post('/upload-users', 'AdminController@upload_users');
        Route::post('/upload-vendors', 'AdminController@upload_vendors');
        Route::post('/register-vendors', 'AdminController@register_vendors');
        Route::post('/upload-vendor-users','AdminController@upload_vendor_users'); # working fine 
        Route::get('/testing', 'AdminController@testing_api');
    }
);

///////////////// Users (DEALERS AND VENDORS) /////////////
Route::group(
    ['namespace' => 'App\Http\Controllers', 'middleware' => 'cors'],
    function () {
        Route::post('/login', 'UserController@login');
        Route::post('/get-all-vendors', 'VendorController@get_all_vendors');
        Route::post('/create-report', 'DealerController@create_report');
        Route::post('/create-faq', 'DealerController@create_faq');
        Route::get('/fetch-all-faqs', 'DealerController@fetch_all_faqs');
        
    }
);

///////////////// Dealer /////////////
// Route::group(
//     ['namespace' => 'App\Http\Controllers', 'middleware' => 'cors'],
//     function () {
//         Route::post('/dealer-login', 'DealerController@login');
//     }
// );

///////////////// Branch /////////////
Route::group(
    ['namespace' => 'App\Http\Controllers', 'middleware' => 'cors'],
    function () {
        Route::post('/branch-login', 'BranchController@login');
    }
);
