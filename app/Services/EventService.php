<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Support\Facades\Log;

class EventService extends BaseDriveService
{
    public function __construct(string $folderId)
    {
        parent::__construct($folderId);
        // var_dump($folderId);
    }

    // Gets all events currently in the Google Drive
    public function listAllResources()
    {
        Log::info("Received listAllResources request \n");
        // TODO: don't think this is needed, reevaluate later
        // $currentEvents = Cache::get('events', []);
        // if (!empty($currentEvents) && !$fetch) {
        //     return $currentEvents;
        // }

        #TODO ignore resources with WIP prefex to their names
        // Get all event files in "events" folder
        $query = "'{$this->folderId}' in parents and mimeType = 'application/vnd.google-apps.folder'";
        $files = $this->queryDriveListFiles($query, 'files(id, name, mimeType)');

        // var_dump($files);
        return $files;
    }

    // Gets event content from Google Drive if it exists
    public function getResource(string $eventId): ?File
    {
        Log::info("Received getResource request \n");
        # TODO: search the events folder to get the file id of the description content
        # TODO: photo content just do the id for 1 photo for now
        # TODO: check format of descriptions file
        # TODO: works for now but kind of messy and inefficient

        // Get "description" file in events folder by id
        $query = "name = 'description' and ('{$eventId}' in parents) and mimeType = 'application/vnd.google-apps.document'";
        $files = $this->queryDriveListFiles($query);

        if (count($files) != 1) {
            // Maybe eventID is actually a name so try that
            $eventIDQuery = "name = '{$eventId}' and '{$this->folderId}' in parents and mimeType = 'application/vnd.google-apps.folder'";
            $events = $this->queryDriveListFiles($eventIDQuery, 'files(id, name, mimeType)');
            if (count($events) != 1) {
                Log::error('Unexpected number of files returned: {err}', ['err' => "expected 1 file", 'count' => count($files)]);
                return null;
            }
            $query = "name = 'description' and ('{$events[0]->id}' in parents) and mimeType = 'application/vnd.google-apps.document'";
            $files = $this->queryDriveListFiles($query);
        }

        $content = $this->queryFileContent($files[0]->id, 'text/markdown');
        return new File($eventId, $files[0]->name, 'text/markdown', $content);
    }
}
