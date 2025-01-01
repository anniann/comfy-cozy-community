<?php

namespace App\Http\Controllers;
use App\Services\EventService;

/* Serves both site and API reqs.
*/
class EventController extends Controller
{
    private $driveService;

    public function __construct(EventService $driveService)
    {
        $this->driveService = $driveService;
    }

    public function index()
    {
        $currentEvents = $this->driveService->listAllResources();
        return response()->json(['events' => $currentEvents]);
    }

    public function show(string $eventId)
    {
        // $eventId = $request->query('id', "");
        if (empty($eventId)) {
            return response()->json(['error' => 'Event ID is required.'], 400);
        }
        $event = $this->driveService->getResource($eventId);
        return response()->json(['eventID' => $event]);
    }

    public function reload()
    {
        /* TODO Hook into front end
        * Get all event folder ids
        * Get all description, images, etc for each event
        * Send to frontend and reload events on site
        */
        return response('Success', 200);
    }
}
