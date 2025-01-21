<?php

namespace WhatsAppPHP\Entity;

use Exception;
use WhatsAppPHP\Util;

class QRCode
{
    /**
     * The raw QR Code value.
     * @var string
     */
    public $raw;

    /**
     * QR Code image representation in base64.
     * @var string
     */
    public $base64;

    /**
     * Create QR Code entity.
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
     * Gets the QR Code image as a binary string representation.
     * @return string Image blob.
     */
    public function toBlob()
    {
        $base64String = $this->base64;

        if (Util::stringContains($base64String, 'base64,')) {
            $base64String = explode('base64,', $base64String)[1];
        }

        return base64_decode($base64String);
    }

    /**
     * Saves the QR Code image to a file.
     * @param string $path Path where to save the file.
     * @param string $filename (Optional) Image filename, if blank it will be random.
     * @return string Returns the saved file location.
     */
    public function save(string $path, ?string $filename = null)
    {
        if (is_null($filename)) $filename = uniqid('wa_') . '.png';
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $fullPath = $path . $filename;

        if (!file_put_contents($fullPath, $this->toBlob())) {
            throw new Exception("Error saving data to file: {$fullPath}");
        }

        return $fullPath;
    }

    /**
     * Gets the base64 representation of the QR Code.
     * @return string QR Code as base64.
     */
    public function __toString()
    {
        return $this->base64;
    }
}
