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
     * Seed the users table with Admin, Lecturers, and Students.
     * User roles: 0 = Admin, 1 = Lecturer, 2 = Student
     */
    public function run(): void
    {
        // Create Admin users
        User::create([
            'username' => 'admin-1',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('password123'),
            'user_role' => 0, // Admin role
        ]);

        User::create([
            'username' => 'admin-2',
            'email' => 'admin2@gmail.com',
            'password' => Hash::make('password123'),
            'user_role' => 0, // Admin role
        ]);

        // Create Lecturer users (3 lecturers as requested)
        User::create([
            'username' => 'lecturer-1',
            'email' => 'lecturer1@gmail.com',
            'password' => Hash::make('password123'),
            'user_role' => 1, // Lecturer role
        ]);

        User::create([
            'username' => 'lecturer-2',
            'email' => 'lecturer2@gmail.com',
            'password' => Hash::make('password123'),
            'user_role' => 1, // Lecturer role
        ]);

        User::create([
            'username' => 'lecturer-3',
            'email' => 'lecturer3@gmail.com',
            'password' => Hash::make('password123'),
            'user_role' => 1, // Lecturer role
        ]);

        // Create Student users
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'username' => 'student-' . $i,
                'email' => 'student' . $i . '@gmail.com',
                'password' => Hash::make('password123'),
                'user_role' => 2, // Student role
            ]);
        }
    }
}
