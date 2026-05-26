<?php

namespace Database\Seeders;

use App\Models\Software;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SoftwareSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the software table with 5 items per laboratory.
     */
    public function run(): void
    {
        // Software list for labs
        $softwareList = [
            // Lab 1 (AR1001) - Programming Lab
            [
                'lab_id' => 1,
                'software_name' => 'Visual Studio Code',
                'version' => '1.96.0',
                'expiry_date' => '2026-12-31',
                'status' => 1, // Active
            ],
            [
                'lab_id' => 1,
                'software_name' => 'Python 3.11',
                'version' => '3.11.5',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 1,
                'software_name' => 'GCC Compiler',
                'version' => '13.2.0',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 1,
                'software_name' => 'Git Version Control',
                'version' => '2.42.0',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 1,
                'software_name' => 'Docker Desktop',
                'version' => '24.0.6',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],

            // Lab 2 (AR1002) - Web Development Lab
            [
                'lab_id' => 2,
                'software_name' => 'Node.js',
                'version' => '20.10.0',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 2,
                'software_name' => 'PHP 8.2',
                'version' => '8.2.12',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 2,
                'software_name' => 'MySQL Server',
                'version' => '8.0.35',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 2,
                'software_name' => 'Apache Web Server',
                'version' => '2.4.58',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 2,
                'software_name' => 'Postman API Testing',
                'version' => '12.4.0',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],

            // Lab 3 (AR1003) - Database Lab
            [
                'lab_id' => 3,
                'software_name' => 'Oracle Database Express Edition',
                'version' => '21c',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 3,
                'software_name' => 'PostgreSQL',
                'version' => '16.1',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 3,
                'software_name' => 'MongoDB Community',
                'version' => '7.0.3',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 3,
                'software_name' => 'DBeaver Database Tool',
                'version' => '23.3.0',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 3,
                'software_name' => 'SQL Server Express',
                'version' => '2022',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],

            // Lab 4 (AR2002) - Mobile Development Lab
            [
                'lab_id' => 4,
                'software_name' => 'Android Studio',
                'version' => '2023.3.1',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 4,
                'software_name' => 'Xcode',
                'version' => '15.1',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 4,
                'software_name' => 'Flutter SDK',
                'version' => '3.16.1',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 4,
                'software_name' => 'React Native',
                'version' => '0.73.0',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 4,
                'software_name' => 'Firebase Tools',
                'version' => '13.1.0',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],

            // Lab 5 (AR2003) - Networking Lab
            [
                'lab_id' => 5,
                'software_name' => 'Cisco Packet Tracer',
                'version' => '8.2.3',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 5,
                'software_name' => 'Wireshark Network Analyzer',
                'version' => '4.2.0',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 5,
                'software_name' => 'VirtualBox Hypervisor',
                'version' => '7.0.12',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 5,
                'software_name' => 'Linux Ubuntu Server',
                'version' => '22.04 LTS',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
            [
                'lab_id' => 5,
                'software_name' => 'OpenVPN Connect',
                'version' => '3.4.0',
                'expiry_date' => '2026-12-31',
                'status' => 1,
            ],
        ];

        foreach ($softwareList as $software) {
            Software::create($software);
        }
    }
}
