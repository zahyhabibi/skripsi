<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PulseSensorController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [PulseSensorController::class, 'index']);
Route::get('/api/users/search', [PulseSensorController::class, 'searchUsers'])->name('api.users.search');
Route::get('/api/get-user-data/{user}', [PulseSensorController::class, 'getUserData'])->name('api.user.data');
Route::post('/predict-result', [PulseSensorController::class, 'getPrediction'])->name('predict.result');
Route::get('/predict/describe', [PulseSensorController::class, 'predictDescribe']);

Route::get('/hf-config-ping', function () {
    $res = \Illuminate\Support\Facades\Http::timeout(15)->get('https://zahyhabibi-heartrate-app-ultimate.hf.space/config');
    return ['status' => $res->status(), 'body' => substr($res->body(), 0, 200)];
});



Route::get('/debug-firebase', function () {
    $path = config('services.firebase.credentials');
    return [
        'env_path' => env('FIREBASE_PRIVATE_KEY_PATH'),
        'resolved_path' => $path,
        'exists' => file_exists($path),
        'readable' => is_readable($path),
    ];
});
