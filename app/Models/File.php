<?php

namespace App\Models;

class File
{
    public $id;
    public $name;
    public $mimeType;

    public function __construct($id, $name, $mimeType)
    {
        $this->id = $id;
        $this->name = $name;
        $this->mimeType = $mimeType;
    }

    public function getFileInfo()
    {
        return "{$this->name} (ID: {$this->id}, Type: {$this->mimeType})";
    }
}
