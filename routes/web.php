<?php

use Illuminate\Support\Facades\Route;
use App\Services\GoogleDriveService;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/drive-files', function (GoogleDriveService $googleDriveService) {
    $fileObjects = $googleDriveService->getEventFolders();
    return view('drive-files', ['fileObjects' => $fileObjects]);
});