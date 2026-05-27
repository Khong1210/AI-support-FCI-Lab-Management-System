<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the users table with required roles.
     * User roles: 1 = Admin, 2 = Faculty Manager, 3 = Lab Staff, 4 = Lab Committee, 5 = Lecturer
     */
    public function run(): void
    {
        User::create([
            'username' => 'admin-1',
            'email' => 'admin1@example.com',
            'password' => Hash::make('12345678'),
            'user_role' => 1, // Admin
        ]);

        User::create([
            'username' => 'faculty-manager-1',
            'email' => 'faculty.manager1@example.com',
            'password' => Hash::make('12345678'),
            'user_role' => 2, // Faculty Manager
        ]);

        User::create([
            'username' => 'lab-staff-1',
            'email' => 'lab.staff1@example.com',
            'password' => Hash::make('12345678'),
            'user_role' => 3, // Lab Staff
        ]);

        User::create([
            'username' => 'lab-committee-1',
            'email' => 'lab.committee1@example.com',
            'password' => Hash::make('12345678'),
            'user_role' => 4, // Lab Committee
        ]);

        User::create([
            'username' => 'lecturer-1',
            'email' => 'lecturer1@example.com',
            'password' => Hash::make('12345678'),
            'user_role' => 5, // Lecturer
        ]);

        User::create([
            'username' => 'lecturer-2',
            'email' => 'lecturer2@example.com',
            'password' => Hash::make('12345678'),
            'user_role' => 5, // Lecturer
        ]);

        User::create([
            'username' => 'lecturer-3',
            'email' => 'lecturer3@example.com',
            'password' => Hash::make('12345678'),
            'user_role' => 5, // Lecturer
        ]);
    }
}
