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
        Route::post(
            '/register-vendor-users',
            'AdminController@register_vendor_users'
        );

        Route::get(
            '/get-all-vendor-users',
            'AdminController@get_all_vendor_users'
        );

        Route::post(
            '/upload-vendor-users',
            'AdminController@upload_vendor_users'
        ); # working fine
        Route::post('/upload-admin', 'AdminController@upload_admin'); # working fine
        Route::post('/edit-vendor-data', 'AdminController@edit_vendor_data');
        Route::get(
            '/deactivate-vendor/{id}',
            'AdminController@deactivate_vendor'
        );

        Route::get('/activate-vendor/{id}', 'AdminController@activate_vendor');

        Route::get(
            '/deactivate-vendor-user/{id}',
            'AdminController@deactivate_vendor_user'
        );
        Route::get(
            '/activate-vendor-user/{id}',
            'AdminController@activate_vendor_user'
        );

        Route::get('/get-vendor-user/{id}', 'AdminController@get_vendor_user');

        Route::post('/upload-dealers', 'AdminController@upload_dealers');

        Route::post(
            '/edit-vendor-user',
            'AdminController@edit_vendor_user_data'
        );

        Route::get('/testing', 'AdminController@testing_api');
    }
);

///////////////// Users (DEALERS AND VENDORS) /////////////
Route::group(
    ['namespace' => 'App\Http\Controllers', 'middleware' => 'cors'],
    function () {
        Route::post('/login', 'UserController@login');
        Route::get('/get-all-vendors', 'VendorController@get_all_vendors');
        Route::post('/create-report', 'DealerController@create_report');
        Route::post('/create-faq', 'DealerController@create_faq');
        Route::get('/fetch-all-faqs', 'DealerController@fetch_all_faqs');

        //---------------------- seminar apis here -------------------- //
        Route::post('/create-seminar', 'SeminarController@create_seminar');
        Route::get('/fetch-all-seminars', 'SeminarController@fetch_all_seminars');
        Route::get('/fetch-scheduled-seminars', 'SeminarController@fetch_scheduled_seminars');
        Route::get('/fetch-ongoing-seminars', 'SeminarController@fetch_ongoing_seminars');
        Route::get('/fetch-watched-seminars', 'SeminarController@fetch_watched_seminars');
        Route::post('/join-seminar', 'SeminarController@bookmark_seminar');
        
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
