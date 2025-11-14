<?php

use App\Http\Controllers\CitizenController;

use App\Http\Controllers\MinistryBranchController;
use App\Http\Controllers\MinistryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserOTPController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('sign-up', [UserController::class, 'signUp']);
Route::post('login', [UserController::class, 'login']);

Route::post('verify-otp', [UserOTPController::class, 'verifyOtp']);
Route::post('resend-otp', [UserOTPController::class, 'resendOtp']);

Route::prefix('citizen')->group(function () {})->middleware('auth:sanctum');

Route::prefix('ministry')->middleware('auth:sanctum')->group(function () {
    Route::controller(MinistryController::class)->group(function () {
        Route::post('add', 'add')->middleware('role:super_admin');
        Route::get('get-ministries', 'getMinistries');
    });

    Route::controller(MinistryBranchController::class)->group(function () {
        Route::post('branches/add', 'add')->middleware('role:super_admin');
        Route::get('branches/{ministry_id}', 'getBranches');
    });
});
