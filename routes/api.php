<?php

use App\Http\Controllers\AlartTableController;
use App\Http\Controllers\HydraController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\GoldFrontEnd;
use App\Http\Controllers\IncrDecrController;
use App\Http\Controllers\NewsAlartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\BuyController;
use App\Http\Controllers\SellController;

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

//use the middleware 'hydra.log' with any request to get the detailed headers, request parameters and response logged in logs/laravel.log

Route::get('hydra', [HydraController::class, 'hydra']);
Route::get('hydra/version', [HydraController::class, 'version']);

Route::apiResource('users', UserController::class)->except(['edit', 'create', 'store', 'update'])->middleware(['auth:sanctum', 'ability:admin,super-admin,user']);
Route::post('users', [UserController::class, 'store']);
Route::put('users/{user}', [UserController::class, 'update'])->middleware(['auth:sanctum', 'ability:admin,super-admin,user']);
Route::post('users/{user}', [UserController::class, 'update'])->middleware(['auth:sanctum', 'ability:admin,super-admin,user']);
Route::patch('users/{user}', [UserController::class, 'update'])->middleware(['auth:sanctum', 'ability:admin,super-admin,user']);
Route::get('me', [UserController::class, 'me'])->middleware('auth:sanctum');
Route::post('login', [UserController::class, 'login']);

Route::apiResource('roles', RoleController::class)->except(['create', 'edit'])->middleware(['auth:sanctum', 'ability:admin,super-admin,user']);
Route::apiResource('users.roles', UserRoleController::class)->except(['create', 'edit', 'show', 'update'])->middleware(['auth:sanctum', 'ability:admin,super-admin']);
// **************************** incr-decr *******************************************
Route::get('incr-decr', [IncrDecrController::class, 'index']);
Route::put('incr-decr-update', [IncrDecrController::class, 'update'])->middleware(['auth:sanctum', 'ability:admin,super-admin']);
// ****************************** Front end without auth api *************************
Route::get('gold-price', [GoldFrontEnd::class, 'goldPrice']); //UAE Dirham price
Route::get('gold-price-status', [GoldFrontEnd::class, 'goldStatus']); //UAE Dirham price
Route::get('gold-price-business-insider', [GoldFrontEnd::class, 'goldPriceBI']); //UAE Dirham price
Route::get('gold-price-apon-jewelary', [GoldFrontEnd::class, 'showBroadcastData']); //UAE Dirham price
Route::get('gold-price-crystal-jewelary', [GoldFrontEnd::class, 'showBroadcastDataCrystal']); //UAE Dirham price
Route::get('site-setting', [SettingController::class, 'index']); //app setting

// **************************** historical Data ***********
Route::get('type', [GoldFrontEnd::class, 'type']);
Route::get('historical-data', [GoldFrontEnd::class, 'getHistoricalData']);
Route::get('live-rate-hourly', [GoldFrontEnd::class, 'getHourlyLiveRateData']);


//product alart, show all data for web, show single data for web, app, data update for web status change
Route::apiResource('set-alart', AlartTableController::class)->except(['create', 'edit', 'update']);
Route::get('/news-alart', [NewsAlartController::class, 'index']); //News Alart
Route::post('/news-alart', [NewsAlartController::class, 'store'])->middleware(['auth:sanctum', 'ability:admin,super-admin']);; //News Alart post
Route::get('product', [ProductController::class, 'index']); //product

// ************************ Order ************************
Route::post('order', [OrderController::class, 'store'])->middleware(['auth:sanctum', 'ability:admin,super-admin,user']);
Route::put('order-status', [OrderController::class, 'status'])->middleware(['auth:sanctum', 'ability:admin,super-admin']);
Route::get('order-list', [OrderController::class, 'index'])->middleware(['auth:sanctum', 'ability:admin,super-admin,user']);
// ************************** gold trands*****************
Route::get('gold-trends', [OrderController::class, 'goldTrends']);

// **************************** Profile edit api **********
Route::put('profile-update', [UserController::class,'updateProfile'])->middleware(['auth:sanctum', 'ability:user']);

// **************************** Support api **********
Route::get('/supports', [SupportController::class, 'index']);
Route::post('/supports', [SupportController::class, 'store']);

// **************************** Buy api **********
Route::get('/buys', [BuyController::class, 'index']);
Route::post('/buys', [BuyController::class, 'store']);

// **************************** Sell api **********
Route::get('/sells', [SellController::class, 'index']);
Route::post('/sells', [SellController::class, 'store']);