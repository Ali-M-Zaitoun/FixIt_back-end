<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CitizenController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FcmTokenController;
use App\Http\Controllers\MinistryBranchController;
use App\Http\Controllers\MinistryController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\ReportController;
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
Route::get('my_tokens', [FcmTokenController::class, 'my_tokens'])->middleware('auth:sanctum');


Route::get('getLog', [ActivityController::class, 'getLog'])->middleware('auth:sanctum');
Route::post('verify-otp', [UserOTPController::class, 'verifyOtp']);
Route::post('resend-otp', [UserOTPController::class, 'resendOtp']);

Route::prefix('complaint')
    ->middleware(['auth:sanctum', 'active.user'])
    ->controller(ComplaintController::class)
    ->group(function () {

        Route::post('submit', 'submit');
        Route::get('my', 'getMyComplaints');
        Route::get('/', 'read')->middleware(['permission:complaint.read']);
        Route::get('/{complaint}', 'readOne')->middleware(['permission:complaint.process']);

        Route::post('startProcessing/{complaint}', 'startProcessing')->middleware(['permission:complaint.process']);
        Route::post('updateStatus/{complaint}', 'updateStatus')->middleware(['permission:complaint.process']);
        Route::delete('delete/{complaint}', 'delete');
    });

Route::prefix('complaint')
    ->middleware(['auth:sanctum', 'active.user'])
    ->controller(ReportController::class)
    ->group(function () {
        Route::get('downloadComplaintReport/{complaint}', 'downloadComplaintReport');
        Route::get('downloadBranchReport/{branch}', 'downloadBranchReport');
        Route::get('downloadMinistryReport/{ministry}', 'downloadMinistryReport');
    });

/* For Testing */

// Route::get('downloadComplaintReport/{complaint}', [ReportController::class, 'downloadComplaintReport']);
// Route::get('downloadBranchReport/{branch}', [ReportController::class, 'downloadBranchReport']);
// Route::get('downloadMinistryReport/{ministry}', [ReportController::class, 'downloadMinistryReport']);

Route::prefix('complaint/reply')
    ->middleware(['auth:sanctum', 'active.user'])
    ->controller(ReplyController::class)
    ->group(function () {
        Route::post('add/{complaint}', 'addReply');
        Route::get('read/{complaint}', 'read');
        Route::delete('delete/{reply}', 'delete');
    });

Route::get('get-governorates', [MinistryController::class, 'getGovernorates'])->middleware(['auth:sanctum', 'active.user']);

Route::prefix('ministry')
    ->middleware(['auth:sanctum', 'active.user'])
    ->group(function () {

        Route::controller(MinistryController::class)->group(function () {
            Route::post('store', 'store')->middleware('role:super_admin');
            Route::get('read', 'read');
            Route::get('readOne/{ministry}', 'readOne');
            Route::post('update/{ministry}', 'update');
            Route::post('{ministry}/assign-manager/{employee}', 'assignManager')->middleware('role:super_admin');
            Route::post('{ministry}/remove-manager', 'removeManager')->middleware('role:super_admin');
            Route::delete('delete/{ministry}', 'delete')->middleware('role:super_admin');
        });

        Route::get('{ministry_id}/complaints', [ComplaintController::class, 'getByMinistry'])
            ->middleware(['permission:complaint.read']);

        Route::prefix('branch')->controller(MinistryBranchController::class)->group(function () {
            Route::post('store', 'store')->middleware('role:super_admin');
            Route::get('read', 'read');
            Route::get('read/{id}', 'readOne');
            Route::post('update/{branch}', 'update');
            Route::post('{branch}/assign-manager/{employee}', 'assignManager')->middleware('role:super_admin|ministry_manager');
            Route::post('{branch}/remove-manager', 'removeManager')->middleware('role:super_admin|ministry_manager');
            Route::delete('delete/{branch}', 'delete');
        });
        Route::get('branch/{branch_id}/complaints', [ComplaintController::class, 'getByBranch']);
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
