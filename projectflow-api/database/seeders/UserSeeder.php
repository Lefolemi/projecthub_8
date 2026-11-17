<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@projectflow.test'],
            [
                'name' => 'Admin Putri',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'alya@projectflow.test'],
            [
                'name' => 'Alya Member',
                'password' => Hash::make('password123'),
                'role' => 'member',
            ]
        );
    }
}