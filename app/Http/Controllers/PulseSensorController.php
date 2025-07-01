<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Illuminate\Support\Facades\Http; // <-- BARIS BARU: Import HTTP Client Laravel
use Illuminate\Support\Facades\Log;  // <-- BARIS BARU: Untuk mencatat log jika ada error

class SensorController extends Controller
{
    protected $database;

    public function __construct(Factory $firebaseFactory)
    {
        $this->database = $firebaseFactory->createDatabase();
    }

    /**
     * Metode baru untuk mengambil data sensor, mengirimkannya ke Colab untuk prediksi,
     * dan menampilkan hasilnya.
     */
    public function getAndPredictData()
    {
        try {
            // 1. AMBIL DATA DARI FIREBASE (MODIFIKASI)
            // Kita ambil 50 data terakhir untuk dianalisis oleh model DNN.
            // Sesuaikan angka '50' dengan jumlah input yang dibutuhkan model Anda.
            $reference = $this->database->getReference('sensor_data');
            $snapshot = $reference->orderByKey()->limitToLast(50)->getValue();

            if (empty($snapshot)) {
                return view('predict_result', ['error' => 'Tidak ada data sensor yang ditemukan di Firebase.']);
            }

            // 2. SIAPKAN DATA UNTUK MODEL (BARU)
            // Kita ubah data dari Firebase menjadi array numerik sederhana yang akan dikirim ke Colab.
            // PENTING: Ganti 'bpm' dengan nama field yang berisi nilai sensor Anda (misal: 'value', 'heart_rate').
            $sensorValues = array_column($snapshot, 'bpm');

            // Hapus nilai null jika ada data yang tidak lengkap
            $sensorValues = array_filter($sensorValues, fn($value) => !is_null($value));

            if (empty($sensorValues)) {
                return view('predict_result', ['error' => 'Data sensor yang ditemukan tidak valid atau tidak memiliki nilai \'bpm\'.']);
            }

            // 3. PANGGIL API COLAB (BARU)
            // Ambil URL API dari file .env untuk keamanan dan fleksibilitas.
            $colabApiUrl = config('services.colab.url');

            if (!$colabApiUrl) {
                Log::error('URL API Colab belum diatur di .env atau config/services.php');
                return view('predict_result', ['error' => 'Konfigurasi URL layanan prediksi belum diatur.']);
            }

            // Kirim data ke API Colab dengan timeout 30 detik
            $response = Http::timeout(30)->post($colabApiUrl, [
                'sensor_values' => array_values($sensorValues) // kirim sebagai array biasa
            ]);

            // 4. TAMPILKAN HASIL (BARU)
            if ($response->successful()) {
                // Jika API call berhasil, kirim data JSON dari Colab ke view
                return view('predict_result', ['result' => $response->json()]);
            } else {
                // Jika gagal, catat error dan tampilkan pesan kesalahan
                Log::error('Gagal menghubungi API Colab: ' . $response->body());
                return view('predict_result', ['error' => 'Layanan prediksi sedang tidak aktif atau terjadi kesalahan.']);
            }

        } catch (\Throwable $e) {
            Log::error('Error di SensorController@getAndPredictData: ' . $e->getMessage());
            return view('predict_result', ['error' => 'Terjadi kesalahan internal saat memproses data: ' . $e->getMessage()]);
        }
    }

    /**
     * Metode lama Anda, bisa tetap dipertahankan jika masih dibutuhkan
     * untuk keperluan lain (misal: debugging API).
     */
    public function getLatestSensorData()
    {
        try {
            $reference = $this->database->getReference('sensor_data');
            $snapshot = $reference->limitToLast(1)->getValue();

            if (!empty($snapshot)) {
                $latestData = reset($snapshot);
                return response()->json([
                    'status' => 'success',
                    'data' => $latestData
                ]);
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No sensor data found.'
                ]);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve sensor data: ' . $e->getMessage()
            ], 500);
        }
    }
}
