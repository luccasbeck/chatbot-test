<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;

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

Route::get(
    '/checkStatus',
    [BotController::class, 'getStatus']
);

Route::post(
    '/balance',
    [BotController::class, 'getBalance']
);

Route::post(
    '/deposit',
    [BotController::class, 'deposit']
);

Route::post(
    '/withdraw',
    [BotController::class, 'withdraw']
);

Route::post(
    '/account',
    [BotController::class, 'createAccount']
);

Route::post(
    '/login',
    [BotController::class, 'logIn']
);
