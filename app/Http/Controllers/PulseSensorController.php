<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use App\Models\User;
use App\Models\HeartRate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Exception\ProcessFailedException;


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

public function getSensorData(User $user = null)
{
    try {

        $reference = $this->database->getReference('data_sensor');
        
        $snapshot = $reference->getSnapshot();

        if ($snapshot->exists()) {
            $data = $snapshot->getValue();


            if (isset($data['bpm'])) {
                return response()->json([
                    'success' => true,
                    'heart_rate' => $data['bpm'] 
                ]);
            }
        }
        
        return response()->json(['success' => false, 'message' => 'Data BPM sensor tidak ditemukan.'], 404);

    } catch (\Throwable $e) {
        Log::error('Firebase sensor data fetch failed: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Gagal terhubung ke server data sensor.'], 500);
    }
}

}
