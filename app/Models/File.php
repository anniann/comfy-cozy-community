<?php

namespace App\Models;

class File
{
    public string $id;
    public string $name;
    public $mimeType;
    public $textContent;

    public function __construct($id=0, $name="", $mimeType="", $textContent= "")
    {
        $this->id = $id;
        $this->name = $name;
        $this->mimeType = $mimeType;
        $this->textContent = $textContent;
    }

    public function getFileInfo()
    {
        return "{$this->name} (ID: {$this->id}, Type: {$this->mimeType})";
    }
}
