<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users
        User::firstOrCreate(['email' => 'fuelman@gmail.com'], [
            'name' => 'Fuelman User',
            'password' => Hash::make('password'),
            'role' => 'fuelman',
        ]);

        User::firstOrCreate(['email' => 'gl@gmail.com'], [
            'name' => 'Group Leader User',
            'password' => Hash::make('password'),
            'role' => 'group_leader',
        ]);

        User::firstOrCreate(['email' => 'spv@gmail.com'], [
            'name' => 'Supervisor User',
            'password' => Hash::make('password'),
            'role' => 'supervisor',
        ]);

        // 2. Seed Tanks (from the Excel layout)
        $tanks = [
            ['code' => 'SPM1', 'main_hole' => 'TENGAH'],
            ['code' => 'SPM2', 'main_hole' => 'TENGAH'],
            ['code' => 'SPM3', 'main_hole' => '(D+B)/2'],
            ['code' => 'FT05', 'main_hole' => 'TENGAH'],
        ];

        foreach ($tanks as $tank) {
            Tank::firstOrCreate(
                ['code' => $tank['code'], 'main_hole' => $tank['main_hole']],
                $tank,
            );
        }
    }
}
