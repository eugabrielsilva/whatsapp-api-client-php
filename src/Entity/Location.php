<?php

namespace WhatsAppPHP\Entity;

use WhatsAppPHP\Client;
use WhatsAppPHP\Util;

class Location
{
    public $latitude;
    public $longitude;
    public $name;
    public $address;
    public $url;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $key = Util::snakeToCamelCase($key);
            $this->{$key} = $value;
        }
    }

    public function toGoogleMaps()
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }
}
