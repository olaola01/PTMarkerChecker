<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PendingRequestController;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::post('/register', [AuthController::class, 'register']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/get-pending-requests', [PendingRequestController::class, 'getPendingRequests'])->name('get-pending-requests');

    Route::post('/create-user-info', [PendingRequestController::class, 'create'])->name('create-user-info');

    Route::put('/approve-request/{id}/{request_type}', [PendingRequestController::class, 'approve'])->name('approve-request');

    Route::delete('/decline-request/{id}', [PendingRequestController::class, 'decline'])->name('decline-request');

    Route::post('/update-user-info', [PendingRequestController::class, 'update'])->name('update-user-info');

    Route::put('/delete-user-info/{id}/{request_type}', [PendingRequestController::class, 'delete'])->name('delete-user-info');
});
