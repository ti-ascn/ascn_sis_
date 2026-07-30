<?php

use App\Http\Controllers\Api\ContactoWebController;

Route::get('/contactoweb', [ContactoWebController::class, 'index']);
Route::post('/contactoweb', [ContactoWebController::class, 'store'])->middleware('throttle:3,1');

