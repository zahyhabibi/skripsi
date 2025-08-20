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
                'name' => 'dedi',
                'age' => 30,
                'gender' => 'male',
            ],
            [
                'name' => 'asep',
                'age' => 19,
                'gender' => 'male',
            ],
            [
                'name' => 'indriyani',
                'age' => 25,
                'gender' => 'female',
            ],
        ];


        foreach ($usersData as $data) {
            User::create($data);
        }
    }
}
