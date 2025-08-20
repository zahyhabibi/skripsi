<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediksi Penyakit Jantung</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Select2 CSS for styled select boxes -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* Custom base styles */
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Hide number input spinners */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* --- Info Icon & Popup Styles --- */
        .info-container {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .info-icon {
            margin-left: 8px;
            color: #6B7280; /* gray-500 */
            cursor: pointer;
            font-size: 0.9em;
        }

        .popup-info {
            display: none;
            position: absolute;
            background-color: #334155; /* slate-700 */
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.85em;
            width: 250px;
            bottom: 125%; /* Position above the icon */
            left: 50%;
            transform: translateX(-50%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 10;
            opacity: 0;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .popup-info.show {
            display: block;
            opacity: 1;
        }

        /* Triangle arrow for the popup */
        .popup-info::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 8px;
            border-style: solid;
            border-color: #334155 transparent transparent transparent;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-100 to-indigo-200 min-h-screen flex items-center justify-center p-4">

    <main class="container max-w-3xl w-full bg-white p-8 rounded-xl shadow-lg border border-gray-200">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Prediksi Risiko Penyakit Jantung</h1>
        <p class="text-gray-600 mb-8 text-center max-w-2xl mx-auto">
            Isi formulir di bawah ini secara manual atau pilih pengguna untuk mengisi data secara otomatis.
        </p>

        <!-- Prediction Form -->
        <form action="{{ route('predict.result') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            @csrf
            <!-- User Selection & CSV Import -->
            <div class="md:col-span-2">
                <div class="flex justify-between items-center mb-1">
                    <label for="user_id" class="block text-sm font-medium text-gray-700">Pilih Orang (Opsional):</label>
                    <button type="button" class="inline-flex items-center bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium py-1 px-3 rounded-lg border border-gray-300 transition-colors">
                        <i class="fas fa-file-csv mr-2"></i>
                        Import dari CSV
                    </button>
                </div>
                <select id="user_id" name="user_id" class="select2-users mt-1 block w-full">
                    <option value="">-- Pilih Pengguna --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id', $inputData['user_id'] ?? '') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Age -->
            <div>
                <label for="Age" class="block text-sm font-medium text-gray-700 mb-1">Usia (tahun):</label>
                <input type="number" id="age" name="age" value="{{ old('Age', $inputData['Age'] ?? '') }}" placeholder="Contoh: 55"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base" required>
            </div>

            <!-- Gender -->
            <div>
                <label for="Gender" class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin:</label>
                {{-- DIUBAH: name="Gender", value="1" untuk Laki-laki, value="0" untuk Perempuan --}}
                <select id="gender" name="gender" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base bg-white" required>
                    <option value="" disabled {{ old('Gender', $inputData['Gender'] ?? '') == '' ? 'selected' : '' }}>Pilih Jenis Kelamin</option>
                    <option value="1" {{ old('Gender', $inputData['Gender'] ?? '') == '1' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="0" {{ old('Gender', $inputData['Gender'] ?? '') == '0' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            <!-- Heart Rate (Full Width) -->
            <div class="md:col-span-2">
                <label for="Heart_rate" class="block text-sm font-medium text-gray-700 mb-1">Detak Jantung (BPM):</label>
                <input type="number" id="heart_rate" name="heart_rate" value="{{ old('Heart_rate', $inputData['Heart_rate'] ?? '') }}" placeholder="Contoh: 80"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base" required>
            </div>

            <!-- Submit Button -->
            <div class="md:col-span-2 flex justify-center mt-6">
                <button type="submit" class="w-full md:w-1/2 bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Prediksi Sekarang
                </button>
            </div>
        </form>

<div class="md:col-span-2 mt-8">

    {{-- Menampilkan Hasil Prediksi Sukses dari Hugging Face --}}
    @if(isset($predictionResult) && isset($predictionResult['data'][0]['label']))
        @php
            // Ambil data hasil prediksi dari struktur JSON Gradio
            $predictionData = $predictionResult['data'][0]['confidences'];
            
            // Cari label dengan confidence tertinggi
            $topPredictionLabel = $predictionResult['data'][0]['label'];
            
            // Cari confidence untuk label "Risiko Tinggi"
            $highRiskConfidence = 0;
            foreach ($predictionData as $item) {
                if ($item['label'] === 'Risiko Tinggi') {
                    $highRiskConfidence = $item['confidence'];
                    break;
                }
            }
        @endphp

        <div class="p-6 border rounded-lg text-center w-full max-w-md mx-auto
            {{ $topPredictionLabel === 'Risiko Tinggi' ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }}">

            <h3 class="text-xl font-bold mb-2
                {{ $topPredictionLabel === 'Risiko Tinggi' ? 'text-red-800' : 'text-green-800' }}">
                Hasil Prediksi
            </h3>

            <p class="text-gray-600">Probabilitas Risiko Tinggi:</p>
            <p class="text-3xl font-bold my-2
                {{ $topPredictionLabel === 'Risiko Tinggi' ? 'text-red-600' : 'text-green-600' }}">
                {{ number_format($highRiskConfidence * 100, 2) }}%
            </p>

            <p class="mt-4 text-lg font-semibold
                {{ $topPredictionLabel === 'Risiko Tinggi' ? 'text-red-800' : 'text-green-800' }}">
                Kesimpulan: {{ $topPredictionLabel }}
            </p>

            {{-- Anda bisa menampilkan detail probabilitas jika mau --}}
            <div class="mt-4 text-sm text-gray-500">
                <p>Detail Probabilitas:</p>
                @foreach($predictionData as $result)
                    <span>{{ $result['label'] }}: {{ number_format($result['confidence'] * 100, 1) }}%</span>
                    @if(!$loop->last) | @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- Menampilkan Error dari API (Kode ini tetap sama) --}}
    @if(isset($apiError))
        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg text-center max-w-md mx-auto">
            <strong>Peringatan:</strong> {{ $apiError }}
        </div>
    @endif

    {{-- Menampilkan Error Validasi Form (Kode ini tetap sama) --}}
    @if ($errors->any())
        <div class="mt-4 p-4 bg-red-50 border-red-300 text-red-800 rounded-lg max-w-md mx-auto">
            <strong class="font-bold">Harap perbaiki error berikut:</strong>
            <ul class="list-disc list-inside mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
    </main>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi Select2
            $('#user_id').select2({
                placeholder: '-- Pilih Pengguna --',
                width: '100%'
            });

            // Event listener saat pengguna dipilih
            $('#user_id').on('change', function() {
                const userId = $(this).val();

                // Fungsi untuk mereset form
                const resetForm = () => {
                    $('#Age').val('');
                    $('#Gender').val('').trigger('change');
                    $('#Heart_rate').val('');
                    // $('#Blood_sugar').val('');
                };

                if (!userId) {
                    resetForm();
                    return;
                }

                // Ambil data pengguna dari API
                $.ajax({
                    url: `/api/get-user-data/${userId}`,
                    type: 'GET',
                    success: function(data) {
                        $('#Age').val(data.age || '');
                        // Backend mengirim '1' atau '0', yang cocok dengan value di HTML
                        $('#Gender').val(data.gender).trigger('change');
                        $('#Heart_rate').val(data.heart_rate || '');
                        // Coba isi gula darah jika ada di data (opsional)
                        // $('#Blood_sugar').val(data.blood_sugar || '');
                    },
                    error: function(error) {
                        console.error('Error fetching user data:', error);
                        resetForm(); // Reset form jika gagal mengambil data
                    }
                });
            });

            // Info icon hover effect
            document.querySelectorAll('.info-icon').forEach(icon => {
                const popup = icon.nextElementSibling;
                popup.textContent = icon.getAttribute('data-info');

                icon.addEventListener('mouseover', () => popup.classList.add('show'));
                icon.addEventListener('mouseout', () => popup.classList.remove('show'));
            });
        });
    </script>
</body>
</html>
