<?php

namespace App\Http\Controllers;
use App\Services\ApplicationService;
use App\Models\Form;

/* Serves both site and API reqs.
*/
class ApplicationController extends Controller
{
    private $driveService;

    public function __construct(ApplicationService $driveService)
    {
        $this->driveService = $driveService;
    }

    public function show(string $appId)
    {
        if (empty($appId)) {
            return response()->json(['error' => 'Application ID is required.'], 400);
        }
        $app = $this->driveService->getResource($appId);
        return response()->json(['appID' => $app]);
    }

    public function upload()
    {
        $name = $_POST['name'] ?? null;
        if (empty($name)) 
        {
            return response('failed: must include name', 400);
        }
        $email = $_POST['email'] ?? null;
        if (empty($email)) 
        {
            return response('failed: must include email', 400);
        }
        $form = new Form($name, $email);


        $appId = $this->driveService->uploadResource($form);
        return response()->json(['appID' => $appId]);
    }
}
