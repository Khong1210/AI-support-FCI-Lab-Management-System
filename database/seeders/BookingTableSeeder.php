<?php

namespace Database\Seeders;

use App\Models\Booking;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class BookingTableSeeder extends Seeder
{
    public function run(): void
    {

        DB::table('bookings')->insert([
            [
                'id' => 1,
                'type' => 'booking',
                'user_id' => 5,
                'booker_name' => 'Khong1',
                'lab_id' => 1,
                'purpose' => 'Workshop',
                'date' => '2026-04-01',
                'start_time' => '11:00:00',
                'end_time' => '13:00:00',
                'status' => 2,
                'created_at' => '2026-06-26 21:30:34',
                'updated_at' => '2026-06-26 21:30:34',
            ],
            [
                'id' => 2,
                'type' => 'maintenance',
                'user_id' => 4,
                'booker_name' => 'Jun1',
                'lab_id' => 1,
                'purpose' => 'Computer Maintenance',
                'date' => '2026-04-10',
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
                'status' => 2,
                'created_at' => '2026-06-26 21:31:11',
                'updated_at' => '2026-06-26 21:31:45',
            ],
            [
                'id' => 3,
                'type' => 'booking',
                'user_id' => 6,
                'booker_name' => 'JunQuan',
                'lab_id' => 1,
                'purpose' => 'Competition',
                'date' => '2026-04-03',
                'start_time' => '12:00:00',
                'end_time' => '18:00:00',
                'status' => 2,
                'created_at' => '2026-06-26 21:34:18',
                'updated_at' => '2026-06-26 21:34:28',
            ],
            [
                'id' => 4,
                'type' => 'maintenance',
                'user_id' => 3,
                'booker_name' => null,
                'lab_id' => 1,
                'purpose' => 'Laboratory Software Maintenance',
                'date' => '2026-04-04',
                'start_time' => '10:00:00',
                'end_time' => '16:00:00',
                'status' => 2,
                'created_at' => '2026-06-26 21:35:49',
                'updated_at' => '2026-06-26 21:36:02',
            ],
        ]);
    }
}
