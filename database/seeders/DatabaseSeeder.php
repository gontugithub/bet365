<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => "admin",
            'email' => env('ADMIN_EMAIL'),
            'password' => Hash::make(env('ADMIN_PASSWORD')),
            'rol' => "admin"
        ]);

        User::create([
            "name"=> "Ivan santamaria",
            "email"=> "ivanmail@gmail.com",
            "password"=> "pass123456",
            'rol' => "admin"
        ]);
    }
}
