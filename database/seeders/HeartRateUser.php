<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\HeartRate;

class HeartRateUser extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil SEMUA user yang sudah ada di database
        $users = User::all();

        // Jika tidak ada user, tampilkan pesan dan hentikan seeder
        if ($users->isEmpty()) {
            $this->command->info('Tidak ada user di database. Silakan jalankan seeder User terlebih dahulu.');
            return;
        }

        // 2. Lakukan loop untuk setiap user yang ditemukan
        foreach ($users as $user) {
            // 3. Gunakan HeartRate::create() untuk membuat data baru
            // Metode ini akan menjalankan HasUuids secara otomatis
            HeartRate::create([
                'user_id'    => $user->id, // PENTING: Mengambil ID (yang berupa UUID) dari user
                'heart_rate' => rand(65, 90), // Membuat angka acak antara 65-90
                // 'status'     => 'normal', // Anda bisa tambahkan kolom lain jika ada
                // 'recorded_at'=> now(),
            ]);
        }

        $this->command->info('Seeder HeartRate berhasil dijalankan untuk ' . $users->count() . ' user.');
    }
}
