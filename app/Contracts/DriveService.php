<?php

namespace App\Contracts;

interface DriveService
{
    public function listAllResources();
    public function getResource(string $eventId);
}
