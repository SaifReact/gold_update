<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
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
    return view('auth/login');
});
Route::get('/signup', function () {
    return view('auth/signup');
});
Route::get('/forget-password', function () {
    return view('auth/forget-password');
});

Route::get('/dashboard', function () {
    return view('dashboard/index');
});


Route::post('login', [UserController::class, 'weblogin']);