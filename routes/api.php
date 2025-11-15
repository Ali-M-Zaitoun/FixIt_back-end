<?php

use App\Http\Controllers\CitizenController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MinistryBranchController;
use App\Http\Controllers\MinistryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserOTPController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('sign-up', [UserController::class, 'signUp']);
Route::post('login', [UserController::class, 'login']);
Route::post('refresh-token', [UserController::class, 'refreshToken']);

Route::post('verify-otp', [UserOTPController::class, 'verifyOtp']);
Route::post('resend-otp', [UserOTPController::class, 'resendOtp']);

Route::prefix('complaint')->middleware('auth:sanctum')->controller(ComplaintController::class)->group(function () {
    Route::post('submit', 'submit');
    Route::get('get-complaints/{ministry_branch_id}', 'getComplaints');
});

Route::prefix('ministry')->middleware('auth:sanctum')->group(function () {
    Route::controller(MinistryController::class)->group(function () {
        Route::post('add', 'add')->middleware('role:super_admin');
        Route::get('get-ministries', 'getMinistries');
        Route::get('get-info/{ministry_id}', 'getMinistryInfo');
    });

    Route::controller(MinistryBranchController::class)->group(function () {
        Route::post('branches/add', 'add')->middleware('role:super_admin');
        Route::get('branches/{ministry_id}', 'getBranches');
    });
});

Route::prefix('employee')->middleware('auth:sanctum')->controller(EmployeeController::class)->group(function () {
    Route::post('add', 'add');
    Route::get('get-employees', 'getEmployees');
    Route::get('get-employees/{ministry_branch_id}', 'getEmployeesInBranch');
});
