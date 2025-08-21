document.addEventListener('DOMContentLoaded', () => {
    // === Inisialisasi Semua Elemen HTML ===
    const form = document.getElementById('prediction-form');
    const resultContainer = document.getElementById('result-container');
    const importCsvBtn = document.getElementById('import-csv-btn');
    const csvFileInput = document.getElementById('csv-file-input');
    const userSelect = document.getElementById('user_id');
    const ageInput = document.getElementById('age');
    const genderSelect = document.getElementById('gender');
    const heartRateInput = document.getElementById('heart_rate');
    const sensorSwitch = document.getElementById('sensor-switch');
    const sensorStatusText = document.getElementById('sensor-status-text');
    const newUserSwitch = document.getElementById('new-user-switch');
    const newUserNameInput = document.getElementById('new_user_name');
    
    // Variabel untuk menyimpan BPM rata-rata sebelum diganti oleh sensor
    let originalBpm = null;
    let latestPredictionData = null;

    // =======================================================
    // === FUNGSI-FUNGSI HELPER (Tidak Perlu Diubah) =========
    // =======================================================

    async function fetchPrediction(data) { /* ... (fungsi ini sudah benar) ... */ }
    function displaySingleResult(prediction) { /* ... (fungsi ini sudah benar) ... */ }
    async function startSequentialPrediction(dataFromCsv) { /* ... (fungsi ini sudah benar) ... */ }

    // =======================================================
    // === EVENT LISTENERS (BAGIAN INI YANG DIPERBAIKI) ======
    // =======================================================

    newUserSwitch.addEventListener('change', (event) => {
    if (event.target.checked) {
        // JIKA SWITCH DINYALAKAN (Input Nama Baru)
        userSelect.style.display = 'none'; // Sembunyikan dropdown
        newUserNameInput.style.display = 'block'; // Tampilkan input teks

        // Reset pilihan dropdown & trigger event change untuk mengosongkan form
        if (userSelect.value !== '') {
            userSelect.value = '';
            userSelect.dispatchEvent(new Event('change'));
        }

    } else {
        // JIKA SWITCH DIMATIKAN (Pilih Pengguna Lama)
        userSelect.style.display = 'block'; // Tampilkan dropdown
        newUserNameInput.style.display = 'none'; // Sembunyikan input teks
        newUserNameInput.value = ''; // Kosongkan nilai input nama baru
    }
});

    // 1. FUNGSI AUTO-FILL: Saat user dipilih dari dropdown
   userSelect.addEventListener('change', async (event) => {
        const userId = event.target.value;
        sensorSwitch.disabled = !userId;
        sensorSwitch.checked = false;
        sensorStatusText.textContent = ''; // Hapus status saat ganti user

        if (!userId) {
            form.reset();
            genderSelect.value = "";
            originalBpm = null;
            return;
        }
        try {
            const response = await fetch(`/api/get-user-data/${userId}`);
            const data = await response.json();
            ageInput.value = data.age || '';
            genderSelect.value = data.gender !== null ? data.gender : '';
            heartRateInput.value = data.heart_rate || '';
            originalBpm = data.heart_rate;
        } catch (error) {
            console.error('Error fetching user data:', error);
            form.reset();
        }
    });

    // 2. FUNGSI SAKLAR SENSOR
sensorSwitch.addEventListener('change', async (event) => {
        const userId = userSelect.value;
        
        if (event.target.checked) { // Jika saklar dinyalakan (ON)
            sensorStatusText.textContent = 'Menghubungkan...';
            sensorStatusText.className = 'block text-xs text-gray-500'; // Warna netral saat loading
            sensorSwitch.disabled = true;

            try {
                const apiUrl = userId ? `/api/get-sensor-data/${userId}` : '/api/get-sensor-data';
                const response = await fetch(apiUrl);
                const data = await response.json();

                if (data.success) {
                    if (!userId) { originalBpm = heartRateInput.value; }
                    heartRateInput.value = data.heart_rate;

                    // Tampilkan status SUKSES
                    sensorStatusText.textContent = 'Sensor Terhubung';
                    sensorStatusText.className = 'block text-xs text-green-600 font-semibold'; // Warna hijau
                } else {
                    // Tampilkan status GAGAL
                    sensorStatusText.textContent = data.message || 'Gagal terhubung';
                    sensorStatusText.className = 'block text-xs text-red-600 font-semibold'; // Warna merah
                    event.target.checked = false;
                }
            } catch (error) {
                console.error('Error fetching sensor data:', error);
                // Tampilkan status KESALAHAN KONEKSI
                sensorStatusText.textContent = 'Koneksi error';
                sensorStatusText.className = 'block text-xs text-red-600 font-semibold'; // Warna merah
                event.target.checked = false;
            } finally {
                sensorSwitch.disabled = false;
                // Hapus pesan status setelah 3 detik
                setTimeout(() => {
                    sensorStatusText.textContent = '';
                }, 3000);
            }
        } 
        else { // Jika saklar dimatikan (OFF)
            heartRateInput.value = originalBpm || '';
            sensorStatusText.textContent = ''; // Hapus pesan status
        }
    });

    // 3. PREDIKSI TUNGGAL: Saat form utama di-submit
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        
        resultContainer.style.display = 'block';
        resultContainer.innerHTML = `<div class="text-center p-4"><i class="fas fa-spinner fa-spin text-blue-600 text-2xl"></i></div>`;

        const data = {
            age: parseInt(ageInput.value),
            gender: parseInt(genderSelect.value),
            heart_rate: parseInt(heartRateInput.value),
        };
        const result = await fetchPrediction(data);
        displaySingleResult(result);
    });

    // 4. IMPORT CSV: Saat tombol Import diklik
    importCsvBtn.addEventListener('click', () => {
        csvFileInput.click();
    });

    // 5. IMPORT CSV: Saat file sudah dipilih
    csvFileInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (!file) return;

        resultContainer.style.display = 'block';
        resultContainer.innerHTML = `<p class="text-center">Membaca file...</p>`;

        Papa.parse(file, {
            header: true,
            skipEmptyLines: true,
            complete: (results) => startSequentialPrediction(results.data),
            error: (err) => console.error(err)
        });
        
        event.target.value = '';
    });


    // --- Salin-tempel lagi fungsi-fungsi di bawah ini agar lengkap ---
    // (Fungsi-fungsi ini tidak berubah dari jawaban sebelumnya)

        async function fetchPrediction(data) {
                try {
                    // Gunakan client yang sudah dimuat di window
                    const client = await window.gradio_client.connect("zahyhabibi/heartrate-app-ultimate");
                    const result = await client.predict("/predict", {
                        age: data.age,
                        gender: data.gender,
                        heart_rate: data.heart_rate,
                    });
                    return result.data[0];
                } catch (error) {
                    console.error("API call failed for:", data, error);
                    return { error: "API Call Failed" };
                }
            }

async function savePredictionToDatabase(dataToSave, saveButton) {
    try {
        // 'dataToSave' sudah berisi semua yang kita butuhkan, termasuk 'probabilitas'
        const response = await fetch('/api/save-prediction', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            // Langsung kirim data yang sudah diolah
            body: JSON.stringify(dataToSave) 
        });

        const result = await response.json();
        if (result.success) {
            console.log('Prediction saved successfully.');
            saveButton.innerHTML = `<i class="fas fa-check mr-2"></i>Tersimpan`;
        } else {
            console.error('Failed to save prediction:', result.errors);
            saveButton.innerHTML = `Gagal Menyimpan, Coba Lagi`;
            saveButton.disabled = false;
        }
    } catch (error) {
        console.error('Error while saving prediction:', error);
        saveButton.innerHTML = `Error, Coba Lagi`;
        saveButton.disabled = false;
    }
}

function displaySingleResult(prediction) {
    resultContainer.style.display = 'block';
    
    if (prediction.error) {
        resultContainer.innerHTML = `<div class="p-4 bg-yellow-50 text-yellow-800 rounded-lg text-center"><strong>Peringatan:</strong> Terjadi kesalahan.</div>`;
        return;
    }

    // 1. Siapkan data untuk disimpan nanti
    const isNewUser = newUserSwitch.checked;
    const dataForSaving = {
        age: parseInt(ageInput.value),
        gender: parseInt(genderSelect.value),
        heart_rate: parseInt(heartRateInput.value),
        hasil: prediction.label,
        probabilitas: prediction.confidences.find(c => c.label === 'Risiko Tinggi')?.confidence || 0
    };

    if (isNewUser) {
        dataForSaving.new_user_name = newUserNameInput.value;
    } else {
        dataForSaving.user_id = userSelect.value;
    }
    // Simpan data ke variabel global
    latestPredictionData = dataForSaving;

    // 2. Tampilkan hasil seperti biasa, dengan tambahan tombol "Simpan"
    const topLabel = prediction.label;
    const highRiskConfidence = dataForSaving.probabilitas;
    const isHighRisk = topLabel === 'Risiko Tinggi';
    const bgColor = isHighRisk ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200';
    const textColor = isHighRisk ? 'text-red-800' : 'text-green-800';
    const accentColor = isHighRisk ? 'text-red-600' : 'text-green-600';
    
    resultContainer.innerHTML = `
        <div class="p-6 border rounded-lg text-center ${bgColor}">
            <h3 class="text-xl font-bold mb-2 ${textColor}">Hasil Prediksi</h3>
            <p class="text-gray-600">Probabilitas Risiko Tinggi:</p>
            <p class="text-4xl font-bold my-2 ${accentColor}">${(highRiskConfidence * 100).toFixed(2)}%</p>
            <p class="mt-4 text-lg font-semibold ${textColor}">Kesimpulan: ${topLabel}</p>
            
            <div class="mt-6">
                <button id="save-prediction-btn" class="bg-blue-600 text-white font-semibold py-2 px-5 rounded-lg shadow-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>Simpan Hasil
                </button>
            </div>
        </div>`;

    // 3. Tambahkan event listener ke tombol yang baru dibuat
    const saveButton = document.getElementById('save-prediction-btn');
    if (saveButton) {
        saveButton.addEventListener('click', () => {
            if (latestPredictionData) {
                // Beri feedback visual saat tombol diklik
                saveButton.disabled = true;
                saveButton.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...`;
                // Panggil fungsi simpan yang sudah ada
                savePredictionToDatabase(latestPredictionData, saveButton);
            }
        });
    }
}

    async function startSequentialPrediction(dataFromCsv) {
        resultContainer.style.display = 'block';
        const initialRowsHtml = dataFromCsv.map((row, index) => {
            const nama = row['Nama'] || row['nama'] || '-';
            const age = row['Age'] || row['age'];
            const gender = row['Gender'] || row['gender'];
            const heart_rate = row['Heart rate'] || row['heart_rate'];
            return `
                <tr id="result-row-${index}" class="border-b">
                    <td class="px-4 py-2">${index + 1}</td><td class="px-4 py-2">${nama}</td>
                    <td class="px-4 py-2">${age}</td><td class="px-4 py-2">${gender}</td>
                    <td class="px-4 py-2">${heart_rate}</td>
                    <td class="result-cell px-4 py-2 text-gray-500">Menunggu...</td>
                    <td class="confidence-cell px-4 py-2 text-gray-500">-</td>
                </tr>`;
        }).join('');
        resultContainer.innerHTML = `
            <h3 class="text-xl font-bold mb-4">Hasil Prediksi Massal (${dataFromCsv.length} data)</h3>
            <div class="overflow-x-auto border rounded-lg"><table class="min-w-full bg-white text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2">#</th><th class="px-4 py-2">Nama</th><th class="px-4 py-2">Age</th>
                        <th class="px-4 py-2">Gender</th><th class="px-4 py-2">Heart Rate</th>
                        <th class="px-4 py-2">Hasil</th><th class="px-4 py-2">Probabilitas</th>
                    </tr>
                </thead>
                <tbody>${initialRowsHtml}</tbody>
            </table></div>`;
        for (const [index, rowData] of dataFromCsv.entries()) {
            const rowElement = document.getElementById(`result-row-${index}`);
            const resultCell = rowElement.querySelector('.result-cell');
            const confidenceCell = rowElement.querySelector('.confidence-cell');
            resultCell.innerHTML = `<i class="fas fa-spinner fa-spin text-blue-500"></i>`;
            const genderRaw = (rowData['Gender'] || rowData['gender'] || '').toUpperCase();
            const predictionInput = {
                gender: (genderRaw === 'M' || genderRaw === 'LAKI-LAKI' || genderRaw === '1') ? 1 : 0,
                age: parseInt(rowData['Age'] || rowData['age']),
                heart_rate: parseFloat(rowData['Heart rate'] || rowData['heart_rate'])
            };
            const predictionResult = await fetchPrediction(predictionInput);
            if (predictionResult.error) {
                resultCell.innerHTML = `<span class="text-xs text-yellow-700">Gagal</span>`;
                confidenceCell.textContent = '-';
            } else {
                const isHighRisk = predictionResult.label === 'Risiko Tinggi';
                resultCell.innerHTML = `<span class="text-xs font-semibold ${isHighRisk ? 'text-red-700' : 'text-green-700'}">${predictionResult.label}</span>`;
                let confidence = 0;
                predictionResult.confidences.forEach(c => {
                    if (c.label === predictionResult.label) confidence = c.confidence;
                });
                confidenceCell.textContent = `${(confidence * 100).toFixed(1)}%`;
            }
        }
    }
});