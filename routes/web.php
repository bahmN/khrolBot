<?php

use App\Http\Controllers\PaymentController;
use DefStudio\Telegraph\Facades\Telegraph;
use Illuminate\Support\Facades\Route;

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
    $response = Telegraph::botInfo()->send()->json();
    $response['url_bot'] = 'https://t.me/hrolus_bot';
    unset(
        $response['result']['can_join_groups'],
        $response['result']['can_read_all_group_messages'],
        $response['result']['supports_inline_queries'],
        $response['result']['can_connect_to_business']
    );
    return view('main', ['data' => json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)]);
});


Route::post('/webhook/crypto', [PaymentController::class, 'responseCrypto']);

Route::post('/webhook/lava', [PaymentController::class, 'responseLava']);
