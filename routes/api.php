<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\produkController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\penjualanController;
use App\Http\Controllers\Api\SettingController;

Route::post('login', [AuthController::class, 'login']);

Route::apiResource('produks', produkController::class)->middleware(['auth:sanctum']);
Route::get('produks/barcode/{barcode}', [produkController::class, 'showByBarcode'])->middleware(['auth:sanctum']);
Route::get('pembayaran', [PembayaranController::class, 'index'])->middleware(['auth:sanctum']);
Route::get('setting', [SettingController::class, 'index'])->middleware(['auth:sanctum']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


