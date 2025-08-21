<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Prediksi Risiko Penyakit Jantung</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-database-compat.js"></script>
    <script>
        // Inisialisasi Firebase
        const firebaseConfig = {
            apiKey: "{{ config('services.firebase.api_key') }}",
            authDomain: "{{ config('services.firebase.project_id') }}.firebaseapp.com",
            databaseURL: "{{ config('services.firebase.database.url') }}",
            projectId: "{{ config('services.firebase.project_id') }}",
        };
        firebase.initializeApp(firebaseConfig);
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js"></script>
    <script type="module">
        import { client } from "https://cdn.jsdelivr.net/npm/@gradio/client@0.1.4/dist/index.min.js";
        window.gradio_client = client; // Membuatnya tersedia secara global untuk app.js
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
        .toggle-checkbox:checked ~ .dot { transform: translateX(140%); }
        .toggle-checkbox:checked ~ .block { background-color: #2563eb; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-100 to-indigo-200 min-h-screen flex items-center justify-center p-4">

    <input type="file" id="csv-file-input" accept=".csv" style="display: none;">

    <main class="w-full max-w-3xl bg-white p-8 rounded-xl shadow-lg border border-gray-200">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Prediksi Risiko Penyakit Jantung</h1>
            <p class="text-gray-600 mt-2">Isi formulir secara manual, pilih pengguna, atau impor dari file CSV.</p>
        </div>

        <form id="prediction-form" class="space-y-6">
            
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label for="user_id" class="block text-sm font-medium text-gray-700">Pilih Orang (Opsional):</label>
                    
                <label for="new-user-switch" class="flex items-center cursor-pointer">
                    <span class="text-sm font-medium text-gray-700 mr-3">Input Nama Baru</span>
                    <div class="relative">
                        <input type="checkbox" id="new-user-switch" class="toggle-checkbox absolute w-full h-full opacity-0 cursor-pointer">
                        <div class="block bg-gray-200 w-12 h-6 rounded-full transition"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition"></div>
                    </div>
                </label>
                </div>

                <div id="user-input-container">
                    <select id="user_id" class="w-full mt-1 block px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        <option value="">-- Pilih Pengguna --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>

                    <input type="text" id="new_user_name" placeholder="Masukkan nama pengguna baru" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" style="display: none;">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="age" class="block text-sm font-medium text-gray-700 mb-1">Usia (tahun):</label>
                    <input type="number" id="age" placeholder="Contoh: 55" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                </div>

                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin:</label>
                    <select id="gender" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white" required>
                        <option value="" disabled selected>Pilih Jenis Kelamin</option>
                        <option value="1">Laki-laki</option>
                        <option value="0">Perempuan</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="heart_rate" class="block text-sm font-medium text-gray-700 mb-1">Detak Jantung (BPM):</label>
                <div class="flex items-center space-x-4">
                    <input type="number" id="heart_rate" placeholder="Contoh: 80" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                    
                    <div class="flex items-center mt-1 flex-shrink-0">
                        <label for="sensor-switch" class="flex items-center cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" id="sensor-switch" class="toggle-checkbox absolute w-full h-full opacity-0 cursor-pointer">
                                <div class="block bg-gray-200 w-12 h-6 rounded-full transition"></div>
                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition"></div>
                            </div>
                            <div class="ml-3 text-gray-700 text-sm font-medium">
                                Gunakan Sensor
                                <small id="sensor-status-text" class="block text-xs"></small>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex flex-col md:flex-row gap-4">
                <button type="submit" class="w-full md:w-2/3 bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg shadow-md hover:bg-blue-700 transition-colors">
                    Prediksi Data Tunggal
                </button>
                
                <button type="button" id="import-csv-btn" class="w-full md:w-1/3 flex-shrink-0 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-3 px-3 rounded-lg border border-gray-300">
                    <i class="fas fa-file-csv mr-2"></i>
                    Import dari CSV
                </button>
            </div>
        </form>

        <div id="result-container" class="mt-6" style="display: none;"></div>
    </main>

    </body>
</html>