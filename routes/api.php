<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PulseSensorController;

Route::get('/get-sensor-data/{user}', [PulseSensorController::class, 'getSensorData']);
Route::get('/get-sensor-data/{user?}', [PulseSensorController::class, 'getSensorData']);
