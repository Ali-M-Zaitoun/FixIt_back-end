<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Complaint;
use App\Models\Citizen;
use App\Models\Governorate;
use App\Models\MinistryBranch;
use Faker\Factory as Faker;

class ComplaintSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $citizens = Citizen::all();
        $governorates = Governorate::all();
        $branches = MinistryBranch::all();

        for ($i = 0; $i < 10; $i++) {
            $citizen = $citizens->random();
            $governorate = $governorates->random();
            $branch = $branches->random();

            $reference_number = sprintf(
                '%s_%s_%s',
                $branch->ministry->abbreviation,
                $governorate->code,
                Str::random(8)
            );

            $complaint = Complaint::create([
                'reference_number'   => $reference_number,
                'type'               => $faker->randomElement(['service', 'infrastructure', 'other']),
                'description'        => $faker->sentence(8),
                'status'             => 'new',
                'governorate_id'     => $governorate->id,
                'city_name'          => $faker->city,
                'street_name'        => $faker->streetName,
                'citizen_id'         => $citizen->id,
                'ministry_branch_id' => $branch->id,
                'locked_by'          => null,
                'locked_at'          => null,
            ]);

            // إضافة ملفات dummy
            $this->addDummyMedia($complaint, $faker);
        }
    }

    protected function addDummyMedia(Complaint $complaint)
    {
        $folder = "complaints/{$complaint->id}";
        Storage::disk('public')->makeDirectory($folder);

        $yourImagePath = database_path('seeders/files/photo_2025-09-18_20-52-43.jpg');
        $imageName = "image_{$complaint->id}.jpg";

        Storage::disk('public')->putFileAs($folder, new \Illuminate\Http\File($yourImagePath), $imageName);

        $complaint->media()->create([
            'path' => "$folder/$imageName",
            'type' => 'img',
        ]);
    }
}
