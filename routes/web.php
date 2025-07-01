<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return view('index');
});

Route::get('/sensor-data', [SensorController::class, 'getLatestSensorData']);
Route::post('/send-dummy-data', [SensorController::class, 'sendDummyData']);
Route::get('/hasil-prediksi', [SensorController::class, 'getAndPredictData'])->name('prediction.result');
