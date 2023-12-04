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

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [\App\Http\Controllers\AuthenticationController::class, 'login']);
    Route::post('register', [\App\Http\Controllers\AuthenticationController::class, 'register']);

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('logout', [\App\Http\Controllers\AuthenticationController::class, 'logout']);
        Route::get('user', [\App\Http\Controllers\AuthenticationController::class, 'user']);
        Route::get('unapproved-users', [\App\Http\Controllers\AuthenticationController::class, 'getUnApprovedUsers']);
        Route::post('approve', [\App\Http\Controllers\AuthenticationController::class, 'approve']);
        Route::post('profile', [\App\Http\Controllers\AuthenticationController::class, 'profile']);
    });
});

Route::group(['prefix' => 'reports'], function () {
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('users', [\App\Http\Controllers\ReportsController::class, 'getUsers']);
        Route::get('certificates', [\App\Http\Controllers\ReportsController::class, 'getCertificates']);
    });
});

Route::group(['prefix' => 'cert', 'middleware' => 'auth:sanctum'], function () {
    Route::post('add', [\App\Http\Controllers\CertificatesController::class, 'add']);
    Route::post('remove', [\App\Http\Controllers\CertificatesController::class, 'remove']);
    Route::get('get', [\App\Http\Controllers\CertificatesController::class, 'get']);
});
