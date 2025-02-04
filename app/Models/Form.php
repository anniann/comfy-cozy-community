<?php

namespace App\Models;

class Form
{
    public string $name;
    public string $email;
    
    public string $location = "";

    public function __construct($name, $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

}
