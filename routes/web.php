<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DealerController;

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

// Route::get('/generate-pdf/{code}', 'DealerController@generate_pdf');

Route::get('/generate-pdf/{dealer}/{lang}', [
    DealerController::class,
    'generate_pdf',
]);
