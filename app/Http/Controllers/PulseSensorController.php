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

    /**
     * DISESUAIKAN TOTAL: Fungsi ini sekarang hanya menggunakan 3 parameter.
     */
public function getPrediction(Request $request)
{
    $validator = Validator::make($request->all(), [
        'age'        => 'required|integer|min:1',
        'gender'     => 'required|integer|in:0,1',
        'heart_rate' => 'required|numeric|min:30',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $v = $validator->validated();

    // TODO: SESUAIKAN urutan berikut dengan "describe" dari Space kamu!
    $payload = [
        'fn'   => '/predict', // atau dari describe
        'data' => [
            (float)$v['heart_rate'],
            (int)$v['age'],
            (int)$v['gender'], // 0/1 bila Space memang menerima angka; jika label string, ubah ke "male"/"female"
        ],
    ];

    $script = base_path('scripts/predict.mjs');
    if (!file_exists($script)) {
        return back()->with('apiError', "Script not found: $script");
    }

    $proc = new Process(
        ['node', $script, json_encode($payload)],
        base_path(),
        [
            // Public space: token boleh kosong
            'HF_TOKEN' => env('HF_TOKEN') ?: env('HUGGINGFACE_API_TOKEN'),
        ]
    );
    $proc->setTimeout(60);
    $proc->run();

    $stdout = $proc->getOutput();
    $stderr = $proc->getErrorOutput();
    $json   = json_decode($stdout, true);

    $users = User::all();

    if (!$proc->isSuccessful() || !$json) {
        return view('index', [
            'users' => $users,
            'predictionResult' => null,
            'apiError' => "Node failed or invalid JSON",
            'inputData' => $request->all(),
            'debug' => compact('stdout','stderr')
        ]);
    }

    if (!($json['ok'] ?? false)) {
        return view('index', [
            'users' => $users,
            'predictionResult' => null,
            'apiError' => $json['error'] ?? 'Space error',
            'inputData' => $request->all(),
            'debug' => ['status' => $json['status'] ?? null, 'logs' => $json['logs'] ?? null],
        ]);
    }

    $proc = new \Symfony\Component\Process\Process(
    ['node', $script, json_encode($payload)],
    base_path(),
    [
        'HF_SPACE_URL' => env('HF_SPACE_URL'),                      // <— TAMBAH INI
        'HF_TOKEN'     => env('HF_TOKEN') ?: env('HUGGINGFACE_API_TOKEN'),
    ]
);

    return view('index', [
        'users' => $users,
        'predictionResult' => $json['result'] ?? null,
        'apiError' => null,
        'inputData' => $request->all(),
    ]);

}
public function predictDescribe()
{
    $script = base_path('scripts/predict.mjs');

    $proc = new \Symfony\Component\Process\Process(
        ['node', $script, json_encode(['describe' => true])],
        base_path(),
        [
            'HF_SPACE_URL' => env('HF_SPACE_URL'),                   // <— TAMBAH INI
            'HF_TOKEN'     => env('HF_TOKEN') ?: env('HUGGINGFACE_API_TOKEN'),
        ]
    );
    $proc->setTimeout(60);
    $proc->run();

    $stdout = $proc->getOutput();
    $stderr = $proc->getErrorOutput();

    return response()->json([
        'stderr' => $stderr,
        'result' => json_decode($stdout, true) ?: $stdout,
    ]);
}



public function predictRun(Request $r)
{
    // CONTOH SAJA — ganti dengan urutan & tipe input dari describe
    // Misal: [/predict] butuh [bpm:number, age:number, sex:string, activity:string]
    $data = [
        (float) $r->input('bpm'),
        (int)   $r->input('age'),
        (string)$r->input('sex'),       // "male" / "female" (sesuai Dropdown label)
        (string)$r->input('activity'),  // "rest", "walk", dst (sesuai Dropdown)
    ];

    $payload = [
        'fn'   => '/predict', // atau nama fungsi yang benar dari describe
        'data' => $data,
    ];

    $script = base_path('scripts/predict.mjs');

    $proc = new Process(
        ['node', $script, json_encode($payload)],
        base_path(),
        [
            'HF_SPACE_URL' => env('HF_SPACE_URL'),
            'HF_TOKEN'     => env('HF_TOKEN') ?: env('HUGGINGFACE_API_TOKEN'),
        ]
    );
    $proc->setTimeout(60);
    $proc->run();

    $stdout = $proc->getOutput();
    $stderr = $proc->getErrorOutput();
    $json   = json_decode($stdout, true);

    if (!$proc->isSuccessful() || !$json) {
        return response()->json([
            'success' => false,
            'reason'  => 'node_failed_or_invalid_json',
            'stderr'  => $stderr,
            'stdout'  => $stdout,
        ], 500);
    }

    if (!($json['ok'] ?? false)) {
        return response()->json([
            'success' => false,
            'reason'  => 'space_error',
            'error'   => $json['error'] ?? null,
            'status'  => $json['status'] ?? null,
            'logs'    => $json['logs'] ?? null,
        ], 502);
    }

    return response()->json([
        'success' => true,
        'data'    => $json['result'] ?? null,
        'logs'    => $json['logs'] ?? null, // opsional, buat debugging UI
    ]);
}
}
