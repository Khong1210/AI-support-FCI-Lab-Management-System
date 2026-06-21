<?php

namespace Database\Seeders;

use App\Models\Semester;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the semesters table with trimeters.
     */
    public function run(): void
    {
        Semester::create([
            'name' => 'Trimester 1',
            'start_date' => '2025-11-03',
            'end_date' => '2026-02-08',
        ]);

        Semester::create([
            'name' => 'Trimester 2',
            'start_date' => '2026-03-30',
            'end_date' => '2026-07-05',
        ]);

        Semester::create([
            'name' => 'Trimester 3',
            'start_date' => '2026-08-10',
            'end_date' => '2026-10-27',
        ]);
    }
}
