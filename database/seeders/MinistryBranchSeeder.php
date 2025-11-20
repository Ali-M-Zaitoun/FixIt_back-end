<?php

namespace Database\Seeders;

use App\Models\Governorate;
use App\Models\Ministry;
use App\Models\MinistryBranch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MinistryBranchSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                "ministry_id" => 1,
                "governorate_id" => 1,
                "translations" => [
                    "ar" => ["name" => "مديرية الصحة"],
                    "en" => ["name" => "Health Directorate"],
                ],
            ],
            [
                "ministry_id" => 1,
                "governorate_id" => 2,
                "translations" => [
                    "ar" => ["name" => "مركز الرعاية الطبية"],
                    "en" => ["name" => "Medical Care Center"],
                ],
            ],
            [
                "ministry_id" => 2,
                "governorate_id" => 3,
                "translations" => [
                    "ar" => ["name" => "مكتب التربية والتعليم"],
                    "en" => ["name" => "Office of Education"],
                ],
            ],
            [
                "ministry_id" => 2,
                "governorate_id" => 4,
                "translations" => [
                    "ar" => ["name" => "مديرية المناهج والتدريب"],
                    "en" => ["name" => "Curriculum and Training Directorate"],
                ],
            ],
            [
                "ministry_id" => 3,
                "governorate_id" => 5,
                "translations" => [
                    "ar" => ["name" => "دائرة الكهرباء العامة"],
                    "en" => ["name" => "General Electricity Department"],
                ],
            ],
            [
                "ministry_id" => 3,
                "governorate_id" => 6,
                "translations" => [
                    "ar" => ["name" => "محطة توليد الطاقة الشمالية"],
                    "en" => ["name" => "Northern Power Generation Station"],
                ],
            ],
            [
                "ministry_id" => 4,
                "governorate_id" => 7,
                "translations" => [
                    "ar" => ["name" => "مركز الاتصالات الرئيسي"],
                    "en" => ["name" => "Main Communications Center"],
                ],
            ],
            [
                "ministry_id" => 4,
                "governorate_id" => 8,
                "translations" => [
                    "ar" => ["name" => "إدارة خدمات الإنترنت"],
                    "en" => ["name" => "Internet Services Administration"],
                ],
            ],
            [
                "ministry_id" => 5,
                "governorate_id" => 9,
                "translations" => [
                    "ar" => ["name" => "إدارة النقل والمواصلات"],
                    "en" => ["name" => "Transportation Administration"],
                ],
            ],
            [
                "ministry_id" => 5,
                "governorate_id" => 10,
                "translations" => [
                    "ar" => ["name" => "مكتب تراخيص المركبات"],
                    "en" => ["name" => "Vehicle Licensing Office"],
                ],
            ],
            [
                "ministry_id" => 6,
                "governorate_id" => 11,
                "translations" => [
                    "ar" => ["name" => "مديرية الثقافة العامة"],
                    "en" => ["name" => "General Directorate of Culture"],
                ],
            ],
            [
                "ministry_id" => 6,
                "governorate_id" => 12,
                "translations" => [
                    "ar" => ["name" => "مكتبة الثقافة الوطنية"],
                    "en" => ["name" => "National Culture Library"],
                ],
            ],
            [
                "ministry_id" => 7,
                "governorate_id" => 13,
                "translations" => [
                    "ar" => ["name" => "دائرة المالية المركزية"],
                    "en" => ["name" => "Central Finance Department"],
                ],
            ],
            [
                "ministry_id" => 7,
                "governorate_id" => 14,
                "translations" => [
                    "ar" => ["name" => "مكتب الضرائب الإقليمي"],
                    "en" => ["name" => "Regional Tax Office"],
                ],
            ]
        ];

        foreach ($data as $branch) {
            $x = MinistryBranch::create([
                'ministry_id' => $branch['ministry_id'],
                'governorate_id' => $branch['governorate_id']
            ]);

            foreach ($branch['translations'] as $locale => $trans) {
                $x->translations()->create([
                    'locale' => $locale,
                    'name'  => $trans['name']
                ]);
            }
        }
    }
}
