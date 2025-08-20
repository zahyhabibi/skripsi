<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PulseSensorController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [PulseSensorController::class, 'index']);
Route::get('/api/users/search', [PulseSensorController::class, 'searchUsers'])->name('api.users.search');
Route::get('/api/get-user-data/{user}', [PulseSensorController::class, 'getUserData']);
// Route::post('/predict-result', [PulseSensorController::class, 'getPrediction'])->name('predict.result');


Route::get('/debug-firebase', function () {
    $path = config('services.firebase.credentials');
    return [
        'env_path' => env('FIREBASE_PRIVATE_KEY_PATH'),
        'resolved_path' => $path,
        'exists' => file_exists($path),
        'readable' => is_readable($path),
    ];
});
