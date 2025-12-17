<?php

use App\Http\Controllers\CitizenController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FcmTokenController;
use App\Http\Controllers\MinistryBranchController;
use App\Http\Controllers\MinistryController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserOTPController;
use Illuminate\Support\Facades\Route;

Route::controller(UserController::class)->group(function () {
    Route::post('sign-up', 'signUp');
    Route::post('login', 'login');
    Route::post('refresh-token', 'refreshToken');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
    Route::post('updateInfo', 'update')->middleware('auth:sanctum');

    Route::post('upload-profile-img', 'uploadProfileImage')->middleware('auth:sanctum');
    Route::delete('delete-profile-img', 'deleteProfileImage')->middleware('auth:sanctum');
});

Route::post('/fcm-token', [FcmTokenController::class, 'store'])
    ->middleware('auth:sanctum');

Route::post('testNotification', [FcmTokenController::class, 'testNotification'])->middleware('auth:sanctum');


Route::post('verify-otp', [UserOTPController::class, 'verifyOtp']);
Route::post('resend-otp', [UserOTPController::class, 'resendOtp']);

Route::prefix('complaint')
    ->middleware(['auth:sanctum', 'active.user'])
    ->controller(ComplaintController::class)
    ->group(function () {

        Route::post('submit', 'submit');
        Route::get('my', 'getMyComplaints');
        Route::get('/', 'read')->middleware(['permission:complaint.read']);
        Route::get('/{complaint_id}', 'readOne')->middleware(['permission:complaint.process', 'check.access']);

        Route::post('startProcessing/{complaint_id}', 'startProcessing')->middleware(['permission:complaint.process', 'check.access']);
        Route::post('updateStatus/{id}', 'updateStatus')->middleware(['permission:complaint.process', 'check.employee.access']);
        Route::delete('delete/{complaint_id}', 'delete')->middleware('check.access');
    });

Route::prefix('complaint/reply')
    ->middleware(['auth:sanctum', 'active.user', 'check.access'])
    ->controller(ReplyController::class)
    ->group(function () {
        Route::post('add/{complaint_id}', 'addReply');
        Route::get('read/{complaint_id}', 'read');
        Route::delete('delete/{reply_id}', 'delete');
    });

Route::get('get-governorates', [MinistryController::class, 'getGovernorates'])->middleware(['auth:sanctum', 'active.user']);

Route::prefix('ministry')
    ->middleware(['auth:sanctum', 'active.user'])
    ->group(function () {

        Route::controller(MinistryController::class)->group(function () {
            Route::post('store', 'store')->middleware('role:super_admin');
            Route::get('read', 'read');
            Route::get('readOne/{id}', 'readOne');
            Route::post('{id}/assign-manager/{manager_id}', 'assignManager')->middleware('role:super_admin');
        });

        Route::get('{ministry_id}/complaints', [ComplaintController::class, 'getByMinistry'])
            ->middleware(['permission:complaint.read']);

        Route::prefix('branch')->controller(MinistryBranchController::class)->group(function () {
            Route::post('store', 'store')->middleware('role:super_admin');
            Route::get('read', 'read');
            Route::get('read/{id}', 'readOne');
            Route::post('{id}/assign-manager/{manager_id}', 'assignManager')->middleware('role:super_admin|ministry_manager');
        });
        Route::get('branch/{branch_id}/complaints', [ComplaintController::class, 'getByBranch'])->middleware('permission:complaint.read');
    });

Route::prefix('employee')->middleware(['auth:sanctum', 'active.user'])->controller(EmployeeController::class)->group(function () {
    Route::post('store', 'store')->middleware('permission:employee.create');
    Route::get('read', 'read')->middleware('permission:employee.read');
    Route::get('readOne/{id}', 'readOne')->middleware('permission:employee.read');
    Route::get('getByBranch/{branch_id}', 'getByBranch')->middleware('permission:employee.read');
    Route::get('getByMinistry/{ministry_id}', 'getByMinistry')->middleware('permission:employee.read');

    Route::post('promote-employee/{employee_id}', 'promoteEmployee')->middleware('permission:employee.update');
});

Route::prefix('citizen')->middleware(['auth:sanctum', 'active.user'])->controller(CitizenController::class)->group(function () {
    Route::get('read', 'read');
    Route::get('read/{id}', 'readOne');
    Route::get('myAccount', 'myAccount');
});
