<?php

namespace App\Services;

use App\Models\File;
use App\Models\Form;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;

class ApplicationService extends BaseDriveService
{
    public function __construct(string $folderId)
    {
        parent::__construct($folderId);
    }

    // Gets all applications currently in the Google Drive
    public function listAllResources()
    {
        Log::info("Received listAllResources request \n");
        $query = "'{$this->folderId}' in parents and mimeType = 'application/vnd.google-apps.folder'";
        $files = $this->queryDriveListFiles($query, 'files(id, name, mimeType)');

        return $files;
    }

    // Gets application content from Google Drive if it exists
    public function getResource(string $appId): ?File
    {
        Log::info("Received getResource request \n");

        $content = $this->queryFileContent($appId, 'text/markdown');
        return new File($appId, $appId, 'text/markdown', $content);
    }


    private function createFileNameFromString(string $input): string
    {
        return strtolower(str_replace(' ', '_', $input));
    }

    public function uploadResource(Form $form): ?string
    {
        Log::info("Received uploadResource request \n");
        $drive_file = new DriveFile();
        $fileName = $this->createFileNameFromString($form->name);
        $drive_file->setName($fileName);
        $drive_file->setMimeType("application/vnd.google-apps.document");

        $content = "";

        $content .= "**Applicant: ** " . $form->name . "\n";
        $content .= "**Email: ** " . $form->email . "\n";
        $content .= "**Location: ** " . $form->location . "\n";

        $id = $this->uploadFile($drive_file, $content);
        Log::info('File successfully updated. Name: {name}, ID: {id}', ['name' => $fileName, 'id' => $id]);

        return $id;
    }
}
