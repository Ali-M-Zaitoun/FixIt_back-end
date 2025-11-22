<?php

namespace App\Services;

use App\Http\Requests\BaseUserRequest;
use App\Models\Citizen;
use App\Models\Complaint;
use App\Models\User;
use App\Models\UserOTP;
use Illuminate\Http\UploadedFile;

class FileManagerService
{
    public function storeFile($model, array|UploadedFile $files, $folderPath, $relationName = 'media', ?callable $typeResolver = null)
    {
        $files = is_array($files) ? $files : [$files];

        foreach ($files as $file) {
            $fileName = $file->getClientOriginalName();
            $file->storeAs($folderPath, $fileName, 'public');
            $model->{$relationName}()->create([
                'path' => "$folderPath/$fileName",
                'type' => $typeResolver ? $typeResolver($file) : 'file'
            ]);
        }
    }
    
    public function detectFileType($file)
    {
        return in_array(strtolower($file->getClientOriginalExtension()), ['pdf', 'doc', 'docx'])
            ? 'file'
            : 'img';
    }
}
