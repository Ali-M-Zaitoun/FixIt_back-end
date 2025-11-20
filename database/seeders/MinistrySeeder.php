<?php

namespace Database\Seeders;

use App\Models\Ministry;
use App\Models\MinistryTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MinistrySeeder extends Seeder
{
    public function run(): void
    {
        $ministries = [
            [
                'abbreviation' => 'MoAD',
                'translations' => [
                    'ar' => ['name' => 'وزارة التنمية الإدارية', 'description' => 'الجهة المسؤولة عن تطوير العمل الإداري وتنمية الموارد البشرية.'],
                    'en' => ['name' => 'Ministry of Administrative Development', 'description' => 'Responsible for developing administrative work and human resources.'],
                ],
            ],
            [
                'abbreviation' => 'MoAAR',
                'translations' => [
                    'ar' => ['name' => 'وزارة الزراعة والإصلاح الزراعي', 'description' => 'تشرف على الزراعة والإصلاح الزراعي في سوريا.'],
                    'en' => ['name' => 'Ministry of Agriculture and Agrarian Reform', 'description' => 'Oversees agriculture and agrarian reform in Syria.'],
                ],
            ],
            [
                'abbreviation' => 'MoCIT',
                'translations' => [
                    'ar' => ['name' => 'وزارة الاتصالات والتقانة', 'description' => 'تشرف على الاتصالات وتكنولوجيا المعلومات.'],
                    'en' => ['name' => 'Ministry of Communications and Information Technology', 'description' => 'Supervises telecommunications and information technology.'],
                ],
            ],
            [
                'abbreviation' => 'MoE',
                'translations' => [
                    'ar' => ['name' => 'وزارة التربية', 'description' => 'تشرف على التعليم الأساسي والثانوي في سوريا.'],
                    'en' => ['name' => 'Ministry of Education', 'description' => 'Oversees basic and secondary education in Syria.'],
                ],
            ],
            [
                'abbreviation' => 'MoHESR',
                'translations' => [
                    'ar' => ['name' => 'وزارة التعليم العالي والبحث العلمي', 'description' => 'تشرف على الجامعات ومؤسسات التعليم العالي.'],
                    'en' => ['name' => 'Ministry of Higher Education and Scientific Research', 'description' => 'Supervises universities and higher education institutions.'],
                ],
            ],
            [
                'abbreviation' => 'MoH',
                'translations' => [
                    'ar' => ['name' => 'وزارة الصحة', 'description' => 'الجهة المسؤولة عن الصحة العامة والمستشفيات الحكومية.'],
                    'en' => ['name' => 'Ministry of Health', 'description' => 'Responsible for public health and hospitals.'],
                ],
            ],
            [
                'abbreviation' => 'MoF',
                'translations' => [
                    'ar' => ['name' => 'وزارة المالية', 'description' => 'تدير السياسات المالية والضرائب والموازنة العامة للدولة.'],
                    'en' => ['name' => 'Ministry of Finance', 'description' => 'Manages fiscal policies, taxation, and state budget.'],
                ],
            ],
            [
                'abbreviation' => 'MoJ',
                'translations' => [
                    'ar' => ['name' => 'وزارة العدل', 'description' => 'تشرف على القضاء والمحاكم في سوريا.'],
                    'en' => ['name' => 'Ministry of Justice', 'description' => 'Supervises the judiciary and courts.'],
                ],
            ],
            [
                'abbreviation' => 'MoLAE',
                'translations' => [
                    'ar' => ['name' => 'وزارة الإدارة المحلية والبيئة', 'description' => 'تشرف على الوحدات الإدارية والبيئة المحلية.'],
                    'en' => ['name' => 'Ministry of Local Administration and Environment', 'description' => 'Oversees local administrative units and environmental policies.'],
                ],
            ],
            [
                'abbreviation' => 'MoTr',
                'translations' => [
                    'ar' => ['name' => 'وزارة النقل', 'description' => 'تنظم وتدير قطاع النقل في سوريا.'],
                    'en' => ['name' => 'Ministry of Transport', 'description' => 'Regulates and manages the transport sector.'],
                ],
            ],
            [
                'abbreviation' => 'MoT',
                'translations' => [
                    'ar' => ['name' => 'وزارة السياحة', 'description' => 'تروج للسياحة وتشرف على المنشآت السياحية.'],
                    'en' => ['name' => 'Ministry of Tourism', 'description' => 'Promotes tourism and supervises tourist facilities.'],
                ],
            ],
            [
                'abbreviation' => 'MoWR',
                'translations' => [
                    'ar' => ['name' => 'وزارة الموارد المائية', 'description' => 'تشرف على إدارة واستثمار الموارد المائية.'],
                    'en' => ['name' => 'Ministry of Water Resources', 'description' => 'Manages and utilizes water resources.'],
                ],
            ],
            [
                'abbreviation' => 'MoEFT',
                'translations' => [
                    'ar' => ['name' => 'وزارة الاقتصاد والتجارة الخارجية', 'description' => 'تدير السياسات الاقتصادية والتجارة الخارجية.'],
                    'en' => ['name' => 'Ministry of Economy and Foreign Trade', 'description' => 'Handles economic policy and foreign trade.'],
                ],
            ],
            [
                'abbreviation' => 'MoITCP',
                'translations' => [
                    'ar' => ['name' => 'وزارة التجارة الداخلية وحماية المستهلك', 'description' => 'تنظم الأسواق وتحمي حقوق المستهلكين.'],
                    'en' => ['name' => 'Ministry of Internal Trade and Consumer Protection', 'description' => 'Regulates markets and protects consumer rights.'],
                ],
            ],
            [
                'abbreviation' => 'MoC',
                'translations' => [
                    'ar' => ['name' => 'وزارة الثقافة', 'description' => 'تعنى بالثقافة والفنون والتراث.'],
                    'en' => ['name' => 'Ministry of Culture', 'description' => 'Responsible for culture, arts, and heritage.'],
                ],
            ],
            [
                'abbreviation' => 'MoI',
                'translations' => [
                    'ar' => ['name' => 'وزارة الداخلية', 'description' => 'تشرف على الأمن الداخلي وإدارة الأحوال المدنية.'],
                    'en' => ['name' => 'Ministry of Interior', 'description' => 'Oversees internal security and civil affairs.'],
                ],
            ],
            [
                'abbreviation' => 'MoD',
                'translations' => [
                    'ar' => ['name' => 'وزارة الدفاع', 'description' => 'المسؤولة عن شؤون الجيش والدفاع الوطني.'],
                    'en' => ['name' => 'Ministry of Defense', 'description' => 'Responsible for military and national defense affairs.'],
                ],
            ],
            [
                'abbreviation' => 'MoAE',
                'translations' => [
                    'ar' => ['name' => 'وزارة الأوقاف', 'description' => 'تشرف على الشؤون الدينية وإدارة الأوقاف الإسلامية.'],
                    'en' => ['name' => 'Ministry of Awqaf (Religious Endowments)', 'description' => 'Oversees religious affairs and Islamic endowments.'],
                ],
            ],
            [
                'abbreviation' => 'MoSAL',
                'translations' => [
                    'ar' => ['name' => 'وزارة الشؤون الاجتماعية والعمل', 'description' => 'تدير شؤون العمل والرعاية الاجتماعية.'],
                    'en' => ['name' => 'Ministry of Social Affairs and Labor', 'description' => 'Handles labor affairs and social welfare.'],
                ],
            ],
            [
                'abbreviation' => 'MoPW',
                'translations' => [
                    'ar' => ['name' => 'وزارة الأشغال العامة والإسكان', 'description' => 'تشرف على البنية التحتية والإسكان العام.'],
                    'en' => ['name' => 'Ministry of Public Works and Housing', 'description' => 'Responsible for infrastructure and public housing.'],
                ],
            ],
            [
                'abbreviation' => 'MoO',
                'translations' => [
                    'ar' => ['name' => 'وزارة النفط والثروة المعدنية', 'description' => 'تدير موارد النفط والغاز والثروات المعدنية.'],
                    'en' => ['name' => 'Ministry of Oil and Mineral Resources', 'description' => 'Manages oil, gas, and mineral resources.'],
                ],
            ],
            [
                'abbreviation' => 'MoYS',
                'translations' => [
                    'ar' => ['name' => 'وزارة التربية الرياضية والشباب', 'description' => 'تدعم الأنشطة الشبابية والرياضية.'],
                    'en' => ['name' => 'Ministry of Youth and Sports', 'description' => 'Supports youth and sports activities.'],
                ],
            ],
        ];

        foreach ($ministries as $item) {
            $ministry = Ministry::create([
                'status' => true,
                'abbreviation' => $item['abbreviation'],
            ]);

            foreach ($item['translations'] as $locale => $trans) {
                $ministry->translations()->create([
                    'locale'      => $locale,
                    'name'        => $trans['name'],
                    'description' => $trans['description'],
                ]);
            }
        }
    }
}
