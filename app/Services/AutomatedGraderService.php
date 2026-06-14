<?php

namespace App\Services;

use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class AutomatedGraderService
{
    /**
     * Grade a practical zip file upload autonomously.
     * 
     * @param UploadedFile $file The ZIP file uploaded by the student
     * @param array $gradingRules JSON parsed array containing rules (e.g., ['required_folder' => 'NVQ_Exam'])
     * @return array [ 'success' => boolean, 'message' => string ]
     */
    public function gradePracticalUpload(UploadedFile $file, array $gradingRules): array
    {
        // 1. Verify it is a valid Zip file
        if ($file->getClientOriginalExtension() !== 'zip') {
            return [
                'success' => false,
                'message' => 'අවලංගු ගොනුවකි. කරුණාකර .zip file එකක් පමණක් ඇතුලත් කරන්න.'
            ];
        }

        $zip = new ZipArchive();
        $res = $zip->open($file->getRealPath());

        if ($res !== true) {
            return [
                'success' => false,
                'message' => 'ඔබගේ ZIP ගොනුව විවෘත කිරීමට නොහැක. එය Corrupt වී ඇත.'
            ];
        }

        // Extract all entries in the zip
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }
        $zip->close();

        // 2. Autonomous Verification Rules Engine
        
        // Rule A: Check for a required Specific Folder Name
        if (isset($gradingRules['required_folder'])) {
            $requiredFolder = $gradingRules['required_folder'];
            $folderFound = false;
            
            foreach ($entries as $entry) {
                // Check if the exact folder name exists in the zip structure
                if (Str::contains($entry, $requiredFolder)) {
                    $folderFound = true;
                    break;
                }
            }

            if (!$folderFound) {
                return [
                    'success' => false,
                    'message' => "ප්‍රායෝගික පරීක්ෂණය අසමත් විය. අපි බලාපොරොත්තු වූ '{$requiredFolder}' නමැති ෆෝල්ඩරය ඔබගේ ZIP එක ඇතුලේ නැත."
                ];
            }
        }

        // Rule B: Check for a required file inside
        if (isset($gradingRules['required_file'])) {
            $requiredFile = $gradingRules['required_file'];
            $fileFound = false;
            
            foreach ($entries as $entry) {
                if (Str::endsWith($entry, $requiredFile)) {
                    $fileFound = true;
                    break;
                }
            }

            if (!$fileFound) {
                return [
                    'success' => false,
                    'message' => "ප්‍රායෝගික පරීක්ෂණය අසමත් විය. පරීක්ෂණය සමත් වීමට '{$requiredFile}' නමැති ගොනුව අනිවාර්යයෙන්ම තිබිය යුතුය."
                ];
            }
        }

        // If it passes all AI rules dynamically injected from the Database!
        return [
            'success' => true,
            'message' => 'සුභ පැතුම්! ඔබගේ ප්‍රායෝගික පරීක්ෂණය ඉතා නිවැරදියි! ඔබ අදාළ කුසලතාවය 100% ක් නිර්මාණය කර ඇත.'
        ];
    }
}
