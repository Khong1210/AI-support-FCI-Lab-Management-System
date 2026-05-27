<?php

namespace Database\Seeders;

use App\Models\Laboratory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaboratorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the laboratories table.
     */
    public function run(): void
    {
        $labs = [
            ['lab_name' => 'AR1001', 'status' => 1, 'capacity' => 30],
            ['lab_name' => 'AR1002', 'status' => 1, 'capacity' => 30],
            ['lab_name' => 'AR1003', 'status' => 1, 'capacity' => 30],
            ['lab_name' => 'AR2002', 'status' => 1, 'capacity' => 25],
            ['lab_name' => 'AR2003', 'status' => 1, 'capacity' => 25],
        ];

        foreach ($labs as $lab) {
            Laboratory::create($lab);
        }
    }
}
