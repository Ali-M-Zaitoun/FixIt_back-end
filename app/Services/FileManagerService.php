<?php

namespace App\Services;

use App\Http\Requests\BaseUserRequest;
use App\Models\Citizen;
use App\Models\Complaint;
use App\Models\User;
use App\Models\UserOTP;

class FileManagerService
{
    public function storeComplaintMedia(Complaint $complaint, array $files, string $ministryAbbr, string $governorateCode, string $referenceNumber)
    {
        $folder = sprintf(
            'complaints/%s/%s/%s/%s',
            now()->format('Y/m/d'),
            $ministryAbbr,
            $governorateCode,
            $referenceNumber
        );

        foreach ($files as $file) {
            $filename = $file->getClientOriginalName();
            $file->storeAs($folder, $filename, 'public');

            $complaint->media()->create([
                'path' => "$folder/$filename",
                'type' => $this->detectFileType($file),
            ]);
        }
    }

    public function storeImg($img, $citizen)
    {
        $folder = sprintf('citizen/%s', $citizen->id);
        $fileName = $img->getClientOriginalName();
        $img->storeAs($folder, $fileName, 'public');
        $citizen->image()->create([
            'path' => "$folder/$fileName",
            'type' => "img"
        ]);
    }

    protected function detectFileType($file)
    {
        return in_array(strtolower($file->getClientOriginalExtension()), ['pdf', 'doc', 'docx'])
            ? 'file'
            : 'img';
    }
}
