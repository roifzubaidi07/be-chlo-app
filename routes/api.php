<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProcurementRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/requests', [ProcurementRequestController::class, 'index']);
    Route::post('/requests', [ProcurementRequestController::class, 'store']);
    Route::get('/requests/{procurementRequest}', [ProcurementRequestController::class, 'show']);

    Route::post('/requests/{procurementRequest}/approve', [ProcurementRequestController::class, 'approve'])
        ->middleware('role:approver');
    Route::post('/requests/{procurementRequest}/reject', [ProcurementRequestController::class, 'reject'])
        ->middleware('role:approver');
    Route::post('/requests/{procurementRequest}/procure', [ProcurementRequestController::class, 'procure'])
        ->middleware('role:purchasing');
});
