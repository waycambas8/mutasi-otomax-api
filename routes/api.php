<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestingController;
use App\Http\Controllers\UserController\UserController;
use App\Http\Controllers\TicketController\TicketBrowseController;
use App\Http\Controllers\MutasiController\MutasiBrowseController;
use App\Http\Controllers\TransaksiController\TransaksiController;
use App\Http\Controllers\PricelistController\PricelistController;
use App\Http\Controllers\DashboardController\DashboardController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("testing", [TestingController::class,"testing"])->name("testing");

Route::controller(UserController::class)->group(function(){
    Route::post("login","login")->name("login")->middleware("login");
});

Route::group(['prefix' => 'ticket'], function () {
    Route::post('/', [ TicketBrowseController::class,"get"]);
});

Route::group(['prefix' => 'mutasi'], function() {
    Route::post("/", [MutasiBrowseController::class,"get"]);
    Route::post("/get_type", [MutasiBrowseController::class,"get_type"]);
    Route::post("/detail", [MutasiBrowseController::class,"detail"]);

});

Route::group(['prefix' => 'transaksi'], function() {
    Route::post("/", [TransaksiController::class,"get"]);
});


Route::group(['prefix' => 'pricelist'], function() {
    Route::post("/", [PricelistController::class,"get"]);
    Route::post("get_produk", [PricelistController::class,"get_produk"]);

});

Route::group(['prefix' => 'dashboard'], function() {
    Route::post("/saldo", [DashboardController::class,"saldo"]);

});