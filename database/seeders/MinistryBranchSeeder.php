<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Ministry;
use App\Models\MinistryBranch;
use Illuminate\Database\Seeder;

class MinistryBranchSeeder extends Seeder
{
    public function run(): void
    {
        $governorateIds = range(1, 14);
        $ministries = Ministry::all();

        foreach ($ministries as $ministry) {

            $branchTemplates = $this->getBranchTemplates($ministry->abbreviation);

            foreach ($branchTemplates as $index => $template) {
                $branch = MinistryBranch::create([
                    'ministry_id'    => $ministry->id,
                    'governorate_id' => $governorateIds[$index % count($governorateIds)],
                ]);

                foreach ($template as $locale => $name) {
                    $branch->translations()->create([
                        'locale' => $locale,
                        'name'   => $name,
                    ]);
                }
            }
        }
    }

    private function getBranchTemplates($abbr): array
    {
        return match ($abbr) {
            'MoH' => [
                ['ar' => 'مديرية الصحة', 'en' => 'Health Directorate'],
                ['ar' => 'منظومة الإسعاف والطوارئ', 'en' => 'Ambulance and Emergency System'],
                ['ar' => 'مركز الرعاية الصحية الأولية', 'en' => 'Primary Healthcare Center'],
                ['ar' => 'إدارة المشافي العامة', 'en' => 'Public Hospitals Administration'],
                ['ar' => 'دائرة الرقابة الدوائية', 'en' => 'Pharmaceutical Control Department'],
            ],
            'MoE' => [
                ['ar' => 'مديرية التربية', 'en' => 'Education Directorate'],
                ['ar' => 'دائرة الامتحانات', 'en' => 'Examinations Department'],
                ['ar' => 'مجمع الخدمات التربوية', 'en' => 'Educational Services Complex'],
                ['ar' => 'إدارة الأبنية المدرسية', 'en' => 'School Buildings Administration'],
                ['ar' => 'مركز المناهج والوسائل التعليمية', 'en' => 'Curricula and Teaching Aids Center'],
            ],
            'MoI' => [
                ['ar' => 'فرع الهجرة والجوازات', 'en' => 'Immigration and Passports Branch'],
                ['ar' => 'مديرية الشؤون المدنية', 'en' => 'Civil Affairs Directorate'],
                ['ar' => 'قسم السجل المدني', 'en' => 'Civil Registry Department'],
                ['ar' => 'قيادة الشرطة', 'en' => 'Police Command'],
                ['ar' => 'فرع المرور', 'en' => 'Traffic Branch'],
            ],
            'MoF' => [
                ['ar' => 'مديرية مالية المحافظة', 'en' => 'Provincial Finance Directorate'],
                ['ar' => 'دائرة الدخل والضرائب', 'en' => 'Income and Tax Department'],
                ['ar' => 'قسم العقارات والمصالح العقارية', 'en' => 'Real Estate Interests Department'],
                ['ar' => 'مديرية الجمارك', 'en' => 'Customs Directorate'],
                ['ar' => 'مكتب الاستعلام الضريبي', 'en' => 'Tax Inquiry Office'],
            ],
            'MoCIT' => [
                ['ar' => 'مركز خدمة المواطن الإلكتروني', 'en' => 'Electronic Citizen Service Center'],
                ['ar' => 'مديرية الاتصالات', 'en' => 'Telecommunications Directorate'],
                ['ar' => 'قسم الخدمات التقنية', 'en' => 'Technical Services Department'],
                ['ar' => 'إدارة خدمات البريد', 'en' => 'Postal Services Administration'],
                ['ar' => 'مركز النفاذ والبيانات', 'en' => 'Data and Access Center'],
            ],
            'MoO' => [
                ['ar' => 'شركة مصفاة النفط', 'en' => 'Oil Refinery Company'],
                ['ar' => 'مديرية حقول الغاز', 'en' => 'Gas Fields Directorate'],
                ['ar' => 'شركة محروقات', 'en' => 'Fuel Company'],
                ['ar' => 'إدارة الثروة المعدنية', 'en' => 'Mineral Resources Administration'],
                ['ar' => 'مركز توزيع المشتقات النفطية', 'en' => 'Petroleum Derivatives Distribution Center'],
            ],
            // Default generic branches for other ministries
            default => [
                ['ar' => 'المديرية المركزية', 'en' => 'Central Directorate'],
                ['ar' => 'دائرة الشؤون الإدارية', 'en' => 'Administrative Affairs Department'],
                ['ar' => 'مكتب خدمة المراجعين', 'en' => 'Customer Service Office'],
                ['ar' => 'فرع الخدمات العامة', 'en' => 'General Services Branch'],
                ['ar' => 'قسم المتابعة والتقييم', 'en' => 'Monitoring and Evaluation Department'],
            ],
        };
    }
}
