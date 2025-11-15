<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernorateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $governorates = [
            ['code' => 'HA', 'name' => 'Al-Hasakah'],
            ['code' => 'LA', 'name' => 'Latakia'],
            ['code' => 'QU', 'name' => 'Quneitra'],
            ['code' => 'RA', 'name' => 'Raqqa'],
            ['code' => 'SU', 'name' => 'As-Suwayda'],
            ['code' => 'DR', 'name' => 'Daraa'],
            ['code' => 'DI', 'name' => 'Deir ez-Zor'],
            ['code' => 'HL', 'name' => 'Aleppo'],
            ['code' => 'HM', 'name' => 'Hama'],
            ['code' => 'HI', 'name' => 'Homs'],
            ['code' => 'ID', 'name' => 'Idlib'],
            ['code' => 'RD', 'name' => 'Rif Dimashq'],
            ['code' => 'DM', 'name' => 'Damascus'],
            ['code' => 'TA', 'name' => 'Tartus'],
        ];

        DB::table('governorates')->insert($governorates);
    }
}
