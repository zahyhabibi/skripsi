<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserDummy extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Definisikan data Anda ke dalam sebuah array
        $usersData = [
            [
                'fullname' => 'dahlah',
                'age' => 30,
                'gender' => 'male',
            ],
            [
                'fullname' => 'asep',
                'age' => 25,
                'gender' => 'female',
            ],
        ];


        foreach ($usersData as $data) {
            User::create($data);
        }
    }
}
