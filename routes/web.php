<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DealerController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\SalesRepController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return phpinfo();

    // return view('welcome');
});



// Route::get('/generate-pdf/{code}', 'DealerController@generate_pdf');

Route::get('/generate-pdf/{dealer}/{lang}/{current_time}', [
    DealerController::class,
    'generate_pdf',
]);

Route::get('/generate-special-order-pdf/{dealer}/{lang}/{current_time}', [
    DealerController::class,
    'generate_special_order_pdf',
]);

Route::get('/generate-vendor-sales-summary-pdf/{vendor}/{lang}/{create_time}', [
    VendorController::class,
    'generate_sales_summary_pdf',
]);

Route::get('/generate-sales-rep-purchasers-pdf/{user}/', [
    SalesRepController::class,
    'generate_sales_rep_purchasers_pdf',
]);

Route::get(
    '/generate-vendor-purchaser-summary/{user}/{dealer}/{vendor}/{lang}/{created_time}',
    [VendorController::class, 'generate_vendor_purchasers_summary']
);

Route::get(
    '/generate-vendor-view-summary/{dealer}/{vendor}/{lang}/{created_time}',
    [VendorController::class, 'generate_vendor_view_summary']
);
