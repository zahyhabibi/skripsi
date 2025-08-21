<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PulseSensorController;


Route::get('/', [PulseSensorController::class, 'index']);
Route::get('/api/users/search', [PulseSensorController::class, 'searchUsers'])->name('api.users.search');
Route::get('/api/get-user-data/{user}', [PulseSensorController::class, 'getUserData']);
Route::post('/api/save-prediction', [PulseSensorController::class, 'savePrediction']);


Route::get('/debug-firebase', function () {
    $path = config('services.firebase.credentials');
    return [
        'env_path' => env('FIREBASE_PRIVATE_KEY_PATH'),
        'resolved_path' => $path,
        'exists' => file_exists($path),
        'readable' => is_readable($path),
    ];
});

Route::get('/health', function () {
    return response('OK', 200);
});

Route::get('/debug-config-url', function () {
    echo "<h1>Hasil Debug URL</h1>";
    echo "<strong>config('app.url'): </strong>" . config('app.url') . "<br>";
    echo "<strong>asset('test.js'): </strong>" . asset('test.js') . "<br>";
    echo "<strong>request()->isSecure(): </strong>" . (request()->isSecure() ? 'true' : 'false') . "<br>";
});
