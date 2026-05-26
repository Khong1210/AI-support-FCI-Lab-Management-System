<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the equipment table with 5 items per laboratory.
     */
    public function run(): void
    {
        // Equipment list for labs
        $equipmentList = [
            // Lab 1 (AR1001)
            [
                'lab_id' => 1,
                'equipment_name' => 'Desktop Computer - Dell Optiplex',
                'serial_number' => 'DELL-OP-001-2024',
                'type' => 'Computer',
                'purchase_date' => '2023-06-15',
                'status' => 1, // Active
            ],
            [
                'lab_id' => 1,
                'equipment_name' => 'Monitor LG 27" 4K',
                'serial_number' => 'LG-27-4K-001',
                'type' => 'Monitor',
                'purchase_date' => '2023-06-15',
                'status' => 1,
            ],
            [
                'lab_id' => 1,
                'equipment_name' => 'Keyboard Mechanical RGB',
                'serial_number' => 'MECH-RGB-001',
                'type' => 'Peripheral',
                'purchase_date' => '2023-07-20',
                'status' => 1,
            ],
            [
                'lab_id' => 1,
                'equipment_name' => 'Mouse Logitech MX Master 3',
                'serial_number' => 'LOG-MX-001',
                'type' => 'Peripheral',
                'purchase_date' => '2023-07-20',
                'status' => 1,
            ],
            [
                'lab_id' => 1,
                'equipment_name' => 'Printer HP LaserJet Pro',
                'serial_number' => 'HP-LJ-001-2024',
                'type' => 'Printer',
                'purchase_date' => '2023-08-10',
                'status' => 1,
            ],

            // Lab 2 (AR1002)
            [
                'lab_id' => 2,
                'equipment_name' => 'Desktop Computer - HP EliteDesk',
                'serial_number' => 'HP-ED-001-2024',
                'type' => 'Computer',
                'purchase_date' => '2023-06-16',
                'status' => 1,
            ],
            [
                'lab_id' => 2,
                'equipment_name' => 'Monitor ASUS 27" Full HD',
                'serial_number' => 'ASUS-27-FHD-001',
                'type' => 'Monitor',
                'purchase_date' => '2023-06-16',
                'status' => 1,
            ],
            [
                'lab_id' => 2,
                'equipment_name' => 'Keyboard Corsair K70',
                'serial_number' => 'COR-K70-001',
                'type' => 'Peripheral',
                'purchase_date' => '2023-07-21',
                'status' => 1,
            ],
            [
                'lab_id' => 2,
                'equipment_name' => 'Mouse Razer DeathAdder',
                'serial_number' => 'RAZ-DA-001',
                'type' => 'Peripheral',
                'purchase_date' => '2023-07-21',
                'status' => 1,
            ],
            [
                'lab_id' => 2,
                'equipment_name' => 'Scanner Fujitsu ScanSnap',
                'serial_number' => 'FUJ-SS-001-2024',
                'type' => 'Scanner',
                'purchase_date' => '2023-08-11',
                'status' => 1,
            ],

            // Lab 3 (AR1003)
            [
                'lab_id' => 3,
                'equipment_name' => 'Desktop Computer - Lenovo ThinkCentre',
                'serial_number' => 'LEN-TC-001-2024',
                'type' => 'Computer',
                'purchase_date' => '2023-06-17',
                'status' => 1,
            ],
            [
                'lab_id' => 3,
                'equipment_name' => 'Monitor BenQ 24" IPS',
                'serial_number' => 'BNQ-24-IPS-001',
                'type' => 'Monitor',
                'purchase_date' => '2023-06-17',
                'status' => 1,
            ],
            [
                'lab_id' => 3,
                'equipment_name' => 'Keyboard SteelSeries Apex',
                'serial_number' => 'STEEL-AP-001',
                'type' => 'Peripheral',
                'purchase_date' => '2023-07-22',
                'status' => 1,
            ],
            [
                'lab_id' => 3,
                'equipment_name' => 'Mouse SteelSeries Rival 3',
                'serial_number' => 'STEEL-RIV-001',
                'type' => 'Peripheral',
                'purchase_date' => '2023-07-22',
                'status' => 1,
            ],
            [
                'lab_id' => 3,
                'equipment_name' => 'Projector Epson EB-2250U',
                'serial_number' => 'EPS-250U-001',
                'type' => 'Projector',
                'purchase_date' => '2023-08-12',
                'status' => 1,
            ],

            // Lab 4 (AR2002)
            [
                'lab_id' => 4,
                'equipment_name' => 'Laptop Dell XPS 15',
                'serial_number' => 'DELL-XPS-001',
                'type' => 'Laptop',
                'purchase_date' => '2023-09-01',
                'status' => 1,
            ],
            [
                'lab_id' => 4,
                'equipment_name' => 'External SSD Samsung 1TB',
                'serial_number' => 'SAM-SSD-001',
                'type' => 'Storage',
                'purchase_date' => '2023-09-05',
                'status' => 1,
            ],
            [
                'lab_id' => 4,
                'equipment_name' => 'USB-C Hub 7-in-1',
                'serial_number' => 'USB-HUB-001',
                'type' => 'Accessory',
                'purchase_date' => '2023-09-10',
                'status' => 1,
            ],
            [
                'lab_id' => 4,
                'equipment_name' => 'Wireless Headphones Sony WH-1000',
                'serial_number' => 'SONY-WH-001',
                'type' => 'Audio',
                'purchase_date' => '2023-09-15',
                'status' => 1,
            ],
            [
                'lab_id' => 4,
                'equipment_name' => 'Webcam Logitech 4K Pro',
                'serial_number' => 'LOG-4K-001',
                'type' => 'Camera',
                'purchase_date' => '2023-09-20',
                'status' => 1,
            ],

            // Lab 5 (AR2003)
            [
                'lab_id' => 5,
                'equipment_name' => 'Laptop HP ProBook 15',
                'serial_number' => 'HP-PB-001',
                'type' => 'Laptop',
                'purchase_date' => '2023-09-02',
                'status' => 1,
            ],
            [
                'lab_id' => 5,
                'equipment_name' => 'External HDD Seagate 2TB',
                'serial_number' => 'SEA-HDD-001',
                'type' => 'Storage',
                'purchase_date' => '2023-09-06',
                'status' => 1,
            ],
            [
                'lab_id' => 5,
                'equipment_name' => 'Docking Station Dell',
                'serial_number' => 'DELL-DOCK-001',
                'type' => 'Accessory',
                'purchase_date' => '2023-09-11',
                'status' => 1,
            ],
            [
                'lab_id' => 5,
                'equipment_name' => 'Wireless Mouse Logitech MX Anywhere',
                'serial_number' => 'LOG-MXA-001',
                'type' => 'Peripheral',
                'purchase_date' => '2023-09-16',
                'status' => 1,
            ],
            [
                'lab_id' => 5,
                'equipment_name' => 'Monitor Mount Dual Arm',
                'serial_number' => 'MOUNT-DUAL-001',
                'type' => 'Accessory',
                'purchase_date' => '2023-09-21',
                'status' => 1,
            ],
        ];

        foreach ($equipmentList as $equipment) {
            Equipment::create($equipment);
        }
    }
}
