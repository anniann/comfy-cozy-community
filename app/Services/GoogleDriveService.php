<?php

namespace App\Services;

use Exception;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Exception;

use App\Models\File;

use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected $client;

    public function __construct()
    {
        if (getenv('GOOGLE_APPLICATION_CREDENTIALS_JSON')) {
            Log::info('GOOGLE_APPLICATION_CREDENTIALS_JSON exists');
            $jsonFilePath = sys_get_temp_dir() . '/google-service-account.json';
            file_put_contents(
                $jsonFilePath,
                base64_decode(getenv('GOOGLE_APPLICATION_CREDENTIALS_JSON'))
            );
            putenv("GOOGLE_APPLICATION_CREDENTIALS=$jsonFilePath");
        }

        $this->client = new Google_Client();
        $this->client->useApplicationDefaultCredentials();
        $this->client->addScope(Google_Service_Drive::DRIVE);
        $appPath = base_path('app');
        Log::info('App Path: {path}', ['path' => $appPath]);
    }

    public function getEventFolders()
    {
        $appPath = base_path('app');
        Log::info('App Path: {path}', ['path' => $appPath]);
        $service = new Google_Service_Drive($this->client);
        $folderId = env('GOOGLE_EVENTS_FOLDER_ID');
        // var_dump($folderId);
        $fileObjects = [];
        try {
            // Get files from Google Drive (list up to 100 files)
            $files = $service->files->listFiles([
                'q' => "'{$folderId}' in parents and mimeType = 'application/vnd.google-apps.folder'", // Filter files by folder ID
                'fields' => 'files(id, name)',
            ]);
            // var_dump($files->files);

            // Check if files were returned
            if (count($files->files) > 0) {
                Log::info("Files retrieved successfully:\n");
                foreach ($files->files as $file) {
                    Log::info("File ID: " . $file->id . " | File Name: " . $file->name . "\n");
                    $fileObjects[] = new File($file->id, $file->name, $file->mimeType);
                }
            } else {
                Log::info("No files found in the specified folder.\n");
            }
        } catch (Google_Service_Exception $e) {
            // Handle API error
            Log::error('API error occurred: {err}', ['err' => $e->getMessage()]);
        } catch (Exception $e) {
            // Handle any other errors
            Log::error('An error occurred: {err}', ['err' => $e->getMessage()]);
        }

        // var_dump($fileObjects);
        return $fileObjects;
    }
}
