<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemOrderController;
use App\Http\Controllers\PartyCakesController;
use App\Http\Controllers\PickListController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/update-counted-value', [ItemOrderController::class, 'updateCountedValue']);
Route::post('/update-all-counted-values', [ItemOrderController::class, 'updateAllCountedValues']);
Route::apiResource('partys-cakes', PartycakesController::class);

/*<==================PICKLIST====================>*/
Route::resource('picklist', PickListController::class);
/* Route::post('/update-adjustment', [PickListController::class, 'updateAdjustment'])->name('update.adjustment'); */
Route::post('/update-actual', [PickListController::class, 'updateActual']);
