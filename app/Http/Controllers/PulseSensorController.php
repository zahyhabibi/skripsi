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
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
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

        // dd([
        //     'env' => env('FIREBASE_PRIVATE_KEY_PATH'),
        //     'resolved' => storage_path('app/' . env('FIREBASE_PRIVATE_KEY_PATH')),
        //     'exists' => file_exists(storage_path('app/' . env('FIREBASE_PRIVATE_KEY_PATH'))),
        //     'users' => $users,
        // ]);

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
        

  $script = base_path('scripts/hf_predict.mjs');
            $command = sprintf(
                'node %s %d %d %f',
                escapeshellarg($script),
                (int) $validatedData['age'],
                (int) $validatedData['gender'],
                (float) $validatedData['heart_rate']
            );

            Log::info('Memanggil Hugging Face API via gradio client', ['command' => $command]);

            $output = shell_exec($command . ' 2>&1');
            $decoded = json_decode($output, true);

            if ($decoded !== null) {
                $predictionResult = $decoded;
                Log::info('API Response Success', ['response' => $predictionResult]);
            } else {
               $apiError = "Error dari API: " . $output;
                Log::error('API Prediction Error', ['output' => $output]);
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


    public function predictTest()
{
    // Contoh payload: sesuaikan dengan endpoint Space kamu
    $payload = [
        'fn'   => '/predict',     // ganti jika endpointnya berbeda
        'data' => [78, 24],       // ganti sesuai urutan input Space (mis: bpm, age, ...)
        // atau untuk cek skema:
        // 'describe' => true,
    ];

    $nodeScript = base_path('scripts/predict.mjs');

    // Jalankan "node scripts/predict.mjs '<json>'" dengan ENV yang kita pass dari Laravel
    $process = new Process([
        'node',
        $nodeScript,
        json_encode($payload),
    ], null, [ // ENV untuk proses Node
        'HF_SPACE_URL' => env('HF_SPACE_URL'),
        'HF_TOKEN'     => env('HF_TOKEN'),
    ]);

    $process->setTimeout(30); // detik
    $process->run();

    $stdout = $process->getOutput();
    $stderr = $process->getErrorOutput();

    // Kalau gagal (exit code != 0) atau output kosong → anggap error
    if (!$process->isSuccessful()) {
        return response()->json([
            'success' => false,
            'error'   => 'Node process failed',
            'stderr'  => $stderr,
            'stdout'  => $stdout,
        ], 500);
    }

    $json = json_decode($stdout, true);

    if (!$json || empty($json['ok'])) {
        return response()->json([
            'success' => false,
            'error'   => $json['error'] ?? 'Unknown error',
            'raw'     => $stdout,
        ], 500);
    }

    return response()->json([
        'success' => true,
        'data'    => $json['result'] ?? $json,
    ]);
}
}
