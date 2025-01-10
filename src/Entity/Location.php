<?php

namespace WhatsAppPHP\Entity;

use WhatsAppPHP\Client;
use WhatsAppPHP\Util;

/**
 * WhatsApp PHP Location entity.
 * @package eugabrielsilva/whatsapp-php
 */
class Location
{
    /**
     * Latitude coordinates.
     * @var int
     */
    public $latitude;

    /**
     * Longitude coordinates.
     * @var int
     */
    public $longitude;

    /**
     * Location name, if any.
     * @var string|null
     */
    public $name;

    /**
     * Location address, if any.
     * @var string|null
     */
    public $address;

    /**
     * Location URL, if any.
     * @var string|null
     */
    public $url;

    /**
     * Create Location entity.
     * @param array $data (Optional) Associative array of data to populate.
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $key = Util::snakeToCamelCase($key);
            $this->{$key} = $value;
        }
    }

    /**
     * Converts the location to a Google Maps link.
     * @return string
     */
    public function toGoogleMaps()
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    /**
     * Converts the location to an Apple Maps link.
     * @return string
     */
    public function toAppleMaps()
    {
        return "https://maps.apple.com/?ll={$this->latitude},{$this->longitude}";
    }

    /**
     * Converts the location to a Waze link.
     * @return string
     */
    public function toWaze()
    {
        return "https://www.waze.com/ul?ll={$this->latitude},{$this->longitude}&navigate=yes";
    }
}
