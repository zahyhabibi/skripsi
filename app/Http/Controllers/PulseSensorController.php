<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use App\Models\User;
use App\Models\HeartRate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class PulseSensorController extends Controller
{
    protected $database;

    public function __construct(Factory $firebaseFactory)
    {
        $this->database = $firebaseFactory->createDatabase();
    }
    public function index()
    {
        $users = User::all();

        dd([
            'env' => env('FIREBASE_PRIVATE_KEY_PATH'),
            'resolved' => storage_path('app/' . env('FIREBASE_PRIVATE_KEY_PATH')),
            'exists' => file_exists(storage_path('app/' . env('FIREBASE_PRIVATE_KEY_PATH'))),
            'users' => $users,
        ]);

        return view('index', ['users' => $users]);
    }



    public function searchUsers(Request $request)
    {
        $searchTerm = $request->input('q');
        $users = User::where('name', 'LIKE', '%' . $searchTerm . '%')
                        ->limit(10)
                        ->get(['id', 'name as text']);

        return response()->json($users);
    }

    public function getUserData(User $user)
    {
        $avgHeartRate = HeartRate::where('user_id', $user->id)
                                    ->latest('recorded_at')
                                    ->limit(100)
                                    ->avg('heart_rate');

        $apiGender = ($user->gender === 'male') ? 1 : 0;

        return response()->json([
            'age' => $user->age,
            'gender' => $apiGender,
            'heart_rate' => $avgHeartRate ? round($avgHeartRate) : null,
        ]);
    }

    /**
     * DISESUAIKAN TOTAL: Fungsi ini sekarang hanya menggunakan 3 parameter.
     */
 public function getPrediction(Request $request)
    {
        // 1. Validasi input dari form
        $validator = Validator::make($request->all(), [
            'age' => 'required|integer|min:1',
            'gender' => 'required|integer|in:0,1',
            'heart_rate' => 'required|numeric|min:30',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Ambil data yang sudah divalidasi
        $validatedData = $validator->validated();
        
        $predictionResult = null;
        $apiError = null;

        try {
        
            $payload = [
                "data" => [
                    (int)$validatedData['age'],
                    (int)$validatedData['gender'],
                    (float)$validatedData['heart_rate'],
                ]
            ];

         
            Log::info('Memanggil Hugging Face API', ['url' => env('HUGGINGFACE_API_URL'), 'input' => $payload]);

            $response = Http::withToken(env('HUGGINGFACE_API_TOKEN'))
                ->timeout(60) 
                ->post(env('HUGGINGFACE_API_URL'), $payload);


            if ($response->successful()) {
                $predictionResult = $response->json();
                Log::info('API Response Success', ['response' => $predictionResult]);
            } else {
                $apiError = "Error dari API: " . $response->status() . " - " . $response->body();
                Log::error('API Prediction Error', ['status' => $response->status(), 'body' => $response->body()]);
            }

        } catch (\Exception $e) {
            $apiError = "Terjadi kesalahan koneksi ke API: " . $e->getMessage();
            Log::error('API Connection Error', ['error' => $e->getMessage()]);
        }
        

        $users = User::all();


        return view('index', [
            'users' => $users,
            'predictionResult' => $predictionResult,
            'apiError' => $apiError,
            'inputData' => $request->all()
        ]);
    }
}
