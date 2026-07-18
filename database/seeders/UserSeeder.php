<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Микки Маус', 'email' => 'mickey@example.com'],
            ['name' => 'Дональд Дак', 'email' => 'donald@example.com'],
            ['name' => 'Гуфи', 'email' => 'goofy@example.com'],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password123'),
                ]
            );
        }
    }
}
