<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            ['name' => 'Joao Silva', 'email' => 'joao.silva@buzzvel.com', 'country' => 'Portugal', 'currency' => 'EUR'],
            ['name' => 'Maria Costa', 'email' => 'maria.costa@buzzvel.com', 'country' => 'Brazil', 'currency' => 'BRL'],
            ['name' => 'John Smith', 'email' => 'john.smith@buzzvel.com', 'country' => 'United Kingdom', 'currency' => 'GBP'],
            ['name' => 'Carlos Rodriguez', 'email' => 'carlos.rodriguez@buzzvel.com', 'country' => 'Mexico', 'currency' => 'MXN'],
            ['name' => 'Yuki Tanaka', 'email' => 'yuki.tanaka@buzzvel.com', 'country' => 'Japan', 'currency' => 'JPY'],
        ];

        foreach ($employees as $employee) {
            User::factory()->create([
                ...$employee,
                'password' => Hash::make('password'),
                'role' => User::ROLE_EMPLOYEE,
            ]);
        }

        User::factory()->finance()->create([
            'name' => 'Anna Mueller',
            'email' => 'anna.mueller@buzzvel.com',
            'country' => 'Germany',
            'currency' => 'EUR',
            'password' => Hash::make('password'),
        ]);
    }
}
