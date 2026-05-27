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
            'start_date' => '2026-01-01',
            'end_date' => '2026-04-30',
        ]);

        Semester::create([
            'name' => 'Trimester 2',
            'start_date' => '2026-05-01',
            'end_date' => '2026-07-31',
        ]);

        Semester::create([
            'name' => 'Trimester 3',
            'start_date' => '2026-08-01',
            'end_date' => '2026-11-30',
        ]);
    }
}
