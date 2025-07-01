<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediksi Penyakit Jantung</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .container {
            max-width: 800px;
        }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        /* Style for the info icon and popup */
        .info-container {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        .info-icon {
            margin-left: 8px;
            color: #6B7280;
            cursor: pointer;
            font-size: 0.9em;
        }
        .popup-info {
            display: none;
            position: absolute;
            background-color: #334155;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.85em;
            width: 250px;
            top: 50%;
            left: calc(100% + 10px); /* Position to the right of the icon */
            transform: translateY(-50%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 10;
            opacity: 0;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .popup-info.show {
            display: block;
            opacity: 1;
            transform: translateY(-50%) translateX(0);
        }
        /* Triangle for the popup */
        .popup-info::before {
            content: '';
            position: absolute;
            top: 50%;
            left: -8px; /* Position to the left of the popup */
            transform: translateY(-50%);
            border-width: 8px;
            border-style: solid;
            border-color: transparent #334155 transparent transparent;
        }

         body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f3f4f6; }
        .card { background-color: white; padding: 2rem; border-radius: 0.75rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
        .status-normal { color: #10b981; }
        .status-tinggi { color: #ef4444; }
        .status-error { color: #f97316; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-100 to-indigo-200 min-h-screen flex items-center justify-center p-4">
    <div class="container bg-white p-8 rounded-xl shadow-lg border border-gray-200">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Prediksi Risiko Penyakit Jantung</h1>
        <p class="text-gray-600 mb-8 text-center">
            Silakan masukkan informasi kesehatan Anda dengan lengkap untuk membantu memprediksi kemungkinan risiko penyakit jantung.
        </p>

        <form id="predictionForm" class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="col-span-full">
                <div class="info-container">
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap:</label>
                    <i class="fas fa-info-circle info-icon" data-info="Masukkan nama lengkap Anda. Informasi ini hanya untuk identifikasi dan tidak digunakan dalam perhitungan prediksi."></i>
                    <div class="popup-info"></div>
                </div>
                <input type="text" id="nama" name="nama" placeholder="Contoh: Budi Santoso"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base">
            </div>

            <div>
                <div class="info-container">
                    <label for="Age" class="block text-sm font-medium text-gray-700 mb-1">Usia (tahun):</label>
                    <i class="fas fa-info-circle info-icon" data-info="Usia Anda dalam tahun. Ini adalah salah satu faktor penting dalam prediksi risiko."></i>
                    <div class="popup-info"></div>
                </div>
                <input type="number" id="Age" name="Age" min="1" max="120" step="1" placeholder="Contoh: 45"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base" required>
            </div>
            <div>
                <div class="info-container">
                    <label for="Sex" class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin:</label>
                    <i class="fas fa-info-circle info-icon" data-info="Pilih jenis kelamin Anda. Faktor biologis yang berbeda pada pria dan wanita dapat mempengaruhi risiko penyakit jantung."></i>
                    <div class="popup-info"></div>
                </div>
                <select id="Sex" name="Sex"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base bg-white" required>
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="M">Laki-laki</option>
                    <option value="F">Perempuan</option>
                </select>
            </div>
            <div>
                <div class="info-container">
                    <label for="ChestPainType" class="block text-sm font-medium text-gray-700 mb-1">Tipe Nyeri Dada:</label>
                    <i class="fas fa-info-circle info-icon" data-info="Deskripsi nyeri dada yang Anda alami. Tipe nyeri dada dapat mengindikasikan kondisi jantung yang berbeda.
                    <br><br><b>Typical Angina (TA):</b> Nyeri dada klasik yang berhubungan dengan penyakit jantung koroner.
                    <br><b>Atypical Angina (ATA):</b> Nyeri dada yang tidak sepenuhnya khas, tetapi mungkin masih terkait jantung.
                    <br><b>Non-Anginal Pain (NAP):</b> Nyeri dada yang bukan berasal dari jantung.
                    <br><b>Asymptomatic (ASY):</b> Tidak ada nyeri dada yang dilaporkan."></i>
                    <div class="popup-info"></div>
                </div>
                <select id="ChestPainType" name="ChestPainType"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base bg-white" required>
                    <option value="">Pilih Tipe Nyeri Dada</option>
                    <option value="TA">Angina Tipikal (TA)</option>
                    <option value="ATA">Angina Atipikal (ATA)</option>
                    <option value="NAP">Nyeri Non-Anginal (NAP)</option>
                    <option value="ASY">Tanpa Gejala (ASY)</option>
                </select>
            </div>


            <div>
                <div class="info-container">
                    <label for="ExerciseAngina" class="block text-sm font-medium text-gray-700 mb-1">Angina Akibat Olahraga:</label>
                    <i class="fas fa-info-circle info-icon" data-info="Apakah Anda mengalami nyeri dada (angina) saat berolahraga? Ini bisa menjadi tanda penyempitan pembuluh darah jantung.
                    <br><br><b>Ya (Y):</b> Anda mengalami nyeri dada saat berolahraga.
                    <br><b>Tidak (N):</b> Anda tidak mengalami nyeri dada saat berolahraga."></i>
                    <div class="popup-info"></div>
                </div>
                <select id="ExerciseAngina" name="ExerciseAngina"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base bg-white" required>
                    <option value="">Pilih</option>
                    <option value="N">Tidak</option>
                    <option value="Y">Ya</option>
                </select>
            </div>


            <div class="content-center col-span-full">
                     <div class="col-span-full flex justify-center mt-6">
                    <label for="MaxHR" class="block text-sm font-medium text-gray-700 mb-1 ">Detak Jantung Maksimal Tercapai:</label>
                    <i class="fas fa-info-circle info-icon" data-info="Jumlah detak jantung tertinggi yang Anda capai selama latihan atau tes stres. Ini menunjukkan kapasitas jantung Anda."></i>
                    <div class="popup-info"></div>
                </div>
                <span id="MaxHR" name="MaxHR" style="text-align: center;"  class="disable mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base">BPM AVG Count</span>
                <span style="text-align: center;" class="disable mt-1 block w-full px-4 py-2">status BPM: - </span>
            </div>

            <div class="col-span-full flex justify-center mt-6">
<div class="card">
        <h1 class="text-2xl font-bold mb-4 text-gray-800">Hasil Analisis Detak Jantung</h1>

        @if(isset($result) && !isset($result['error']))
            <p class="text-lg text-gray-600">Status Terdeteksi:</p>
            <p class="text-5xl font-bold my-2 {{ $result['status'] == 'Normal' ? 'status-normal' : 'status-tinggi' }}">
                {{ $result['status'] }}
            </p>
            <p class="text-gray-500">Rata-rata BPM: {{ number_format($result['bpm'] ?? 0, 2) }}</p>
        @else
            <p class="text-lg text-gray-600">Status Terdeteksi:</p>
            <p class="text-3xl font-bold my-2 status-error">
                Gagal Memproses
            </p>
            <p class="text-gray-500">{{ $error ?? 'Terjadi kesalahan tidak diketahui.' }}</p>
        @endif

        <div class="mt-6">
            <a href="{{ route('prediction.result') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                Refresh Data
            </a>
        </div>
    </div>
            </div>
        </form>

        <div id="result" class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200 text-center text-lg font-medium text-gray-800 hidden">
            </div>

        <div id="error-message" class="mt-4 p-3 bg-red-100 rounded-lg border border-red-300 text-center text-sm text-red-700 hidden">
            </div>
    </div>

    <script>
        $(document).ready(function() {
            // Simulasi pengambilan data detak jantung dari backend
            function fetchHeartRateData() {
                // Ganti dengan panggilan AJAX ke backend Anda untuk mendapatkan data detak jantung
                return new Promise((resolve) => {
                    setTimeout(() => {
                        // Simulasi data detak jantung
                        const heartRateData = [72, 75, 78, 80, 76, 74, 73, 77, 79, 81];
                        resolve(heartRateData);
                    }, 1000); // Simulasi delay 1 detik
                });
            }

            // Fungsi untuk menghitung rata-rata detak jantung
            function calculateAverageHeartRate(data) {
                const sum = data.reduce((a, b) => a + b, 0);
                return (sum / data.length).toFixed(2); // Mengembalikan rata-rata dengan dua desimal
            }

            // Memperbarui elemen MaxHR dengan rata-rata detak jantung
            fetchHeartRateData().then(data => {
                const averageHeartRate = calculateAverageHeartRate(data);
                $('#MaxHR').text(`Rata-rata Detak Jantung: ${averageHeartRate} BPM`);
                $('.disable').text(`status BPM: ${averageHeartRate}`);
            });
        });


        document.addEventListener('DOMContentLoaded', () => {
            const infoIcons = document.querySelectorAll('.info-icon');

            infoIcons.forEach(icon => {
                const popup = icon.nextElementSibling; // Get the next sibling, which is the popup-info div
                const infoText = icon.dataset.info;
                popup.innerHTML = infoText;

                let timeout; // Variable to hold the timeout for hiding

                icon.addEventListener('mouseenter', () => {
                    clearTimeout(timeout); // Clear any existing hide timeouts
                    popup.classList.add('show');
                });

                icon.addEventListener('mouseleave', () => {
                    // Set a timeout to hide the popup after a short delay
                    timeout = setTimeout(() => {
                        popup.classList.remove('show');
                    }, 200); // 200ms delay
                });

                popup.addEventListener('mouseenter', () => {
                    clearTimeout(timeout); // If mouse re-enters popup, clear hide timeout
                });

                popup.addEventListener('mouseleave', () => {
                    timeout = setTimeout(() => {
                        popup.classList.remove('show');
                    }, 200); // 200ms delay
                });
            });
        });

        document.getElementById('predictionForm').addEventListener('submit', async function(event) {
            event.preventDefault(); // Mencegah form submit secara default

            const resultDiv = document.getElementById('result');
            const errorDiv = document.getElementById('error-message');
            resultDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');

            try {
                const formData = new FormData(event.target);
                const inputData = {};
                for (const [key, value] of formData.entries()) {
                    if (key === 'nama') {
                        // Nama hanya untuk identifikasi, tidak digunakan dalam model
                        inputData[key] = value;
                    } else if (['Age', 'RestingBP', 'Cholesterol', 'MaxHR', 'Oldpeak'].includes(key)) {
                        inputData[key] = parseFloat(value);
                    } else {
                        inputData[key] = value; // Keep as string for categorical features
                    }
                }

                // Simulasi memanggil fungsi prediksi model Python
                // Dalam skenario nyata, Anda akan mengirim `inputData` ke endpoint API backend
                // yang kemudian memanggil fungsi `predict_heart_disease` di Python.
                // Untuk contoh ini, kita akan menggunakan nilai acak.
                // Ganti ini dengan panggilan API sebenarnya jika Anda memiliki backend.

                // Contoh prediksi acak sederhana di sisi klien untuk demonstrasi:
                const simulatedProbability = Math.random(); // Placeholder for actual model prediction

                let predictionText = "";
                let predictionColor = "";
                const userName = inputData.nama || "Anda"; // Gunakan "Anda" jika nama tidak diberikan

                // Bagian ini perlu diganti dengan hasil prediksi model sebenarnya dari Python
                if (simulatedProbability > 0.5) {
                    predictionText = `Halo ${userName}, berdasarkan data yang Anda masukkan, kemungkinan Anda memiliki **risiko tinggi** penyakit jantung adalah sekitar **${(simulatedProbability * 100).toFixed(2)}%**. Disarankan untuk berkonsultasi dengan profesional medis.`;
                    predictionColor = "text-red-700 bg-red-50 border-red-200";
                } else {
                    predictionText = `Halo ${userName}, berdasarkan data yang Anda masukkan, kemungkinan Anda memiliki **risiko rendah** penyakit jantung adalah sekitar **${((1 - simulatedProbability) * 100).toFixed(2)}%**. Tetap jaga kesehatan Anda.`;
                    predictionColor = "text-green-700 bg-green-50 border-green-200";
                }

                resultDiv.innerHTML = predictionText;
                resultDiv.classList.remove('hidden', 'bg-blue-50', 'text-gray-800', 'border-blue-200');
                resultDiv.classList.add(predictionColor);

            } catch (error) {
                console.error("Error during prediction:", error);
                errorDiv.innerHTML = "Terjadi kesalahan saat memproses prediksi. Mohon coba lagi.";
                errorDiv.classList.remove('hidden');
            }
        });
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</body>
</html>
