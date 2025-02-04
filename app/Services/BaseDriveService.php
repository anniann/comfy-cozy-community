<?php

namespace App\Services;

use App\Contracts\DriveService;
use App\Models\File;
use Google\Service\Drive\DriveFile;

use Illuminate\Support\Facades\Log;

use Google_Client;
use Google_Service_Drive;
use Exception;
use Google_Service_Exception;

abstract class BaseDriveService implements DriveService
{
    /* The Google Drive API Client */
    protected $driveService;
    /* The Google Drive folder ID to get find all the resource folders/names */
    protected $folderId;

    public function __construct(string $folderId)
    {
        $this->initializeClient();
        $this->folderId = $folderId;
        // var_dump($folderId);
    }

    private function initializeClient()
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

        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Google_Service_Drive::DRIVE);
        // $appPath = base_path('app');
        // Log::info('App Path: {path}', ['path' => $appPath]);
        $this->driveService = new Google_Service_Drive($client);
    }

    protected function queryDriveListFiles(string $query, string $fields = 'files(id, name, mimeType)', string $orderBy = 'name_natural'): ?array
    {
        $fileObjects = [];
        try {
            // Get files from Google Drive (list up to 100 files)
            $files = $this->driveService->files->listFiles([
                'q' => $query,
                'fields' => $fields,
                'orderBy' => $orderBy,
            ]);
            // var_dump($files->files);

            // Check if files were returned
            if (count($files->files) > 0) {
                Log::info("Files retrieved successfully\n");
                foreach ($files->files as $file) {
                    Log::info("File ID: " . $file->id . " | File Name: " . $file->name . "\n");
                    $fileObjects[] = new File($file->id, $file->name, $file->mimeType);
                }
            } else {
                Log::info("No files found in the specified folder.\n");
            }
        } catch (Google_Service_Exception $e) {
            // Handle API error. TODO: revisit https://github.com/googleapis/google-api-php-client/issues/1339
            Log::error('Drive Error occured while querying listFiles', [
                'err' => json_decode($e->getMessage())->error->errors[0]->message, 
                'query' => $query
            ]);
        } catch (Exception $e) {
            // Handle any other errors
            Log::error('An error occurred: {err}', ['err' => $e->getMessage()]);
        }
        return $fileObjects;
    }

    protected function queryFileContent(string $fileId, string $mimeType): ?string
    {
        try {
            // Download the file content
            $response = $this->driveService->files->export($fileId, $mimeType, ['alt' => 'media']);
            $content = $response->getBody()->getContents();
            
            return $content;
        } catch (Google_Service_Exception $e) {
            // Handle API error
            Log::error('Drive Error occured while querying listFiles', [
                'err' => json_decode($e->getMessage())->error->errors[0]->message, 
                'fileId' => $fileId
            ]);
            return null;
        } catch (Exception $e) {
            // Handle any other errors
            Log::error('An error occurred: {err}', ['err' => $e->getMessage()]);
            return null;
        }
    }

    protected function uploadFile(DriveFile $file, string $content): ?string
    {
        try {
            $file->parents = [$this->folderId];
            $uploadedFile = $this->driveService->files->create($file, [
                'data' => $content,
                'mimeType' => 'text/plain',
                'uploadType' => 'multipart'
            ]);
            Log::info("File uploaded successfully: " . $uploadedFile->getId());
            return $uploadedFile->getId();
        } catch (Google_Service_Exception $e) {
            // Handle API error
            Log::error('Drive Error occured while uploading file', [
                'err' => json_decode($e->getMessage())->error->errors[0]->message, 
                'fileName' => $file->getName
            ]);
            return "";
        } catch (Exception $e) {
            // Handle any other errors
            Log::error('An error occurred: {err}', ['err' => $e->getMessage()]);
            return "";
        }
    }
}
