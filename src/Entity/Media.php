<?php

namespace WhatsAppPHP\Entity;

use WhatsAppPHP\Client;
use WhatsAppPHP\Exception\DownloadMediaException;
use WhatsAppPHP\Util;

/**
 * WhatsApp PHP Media entity.
 * @package eugabrielsilva/whatsapp-php
 */
class Media
{
    /**
     * Media URL.
     * @var string
     */
    public $url;

    /**
     * Media mime type, if any.
     * @var string
     */
    public $type;

    /**
     * Media extension.
     * @var string
     */
    public $extension;

    /**
     * Media custom filename, if any.
     * @var string|null
     */
    public $filename;

    /**
     * Create Media entity.
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
     * Downloads the media file, if available, to the local disk.
     * @param string $path Location folder in where to salve the file.
     * @param string $filename (Optional) Custom filename to set, leave blank to use the original filename.
     * @return string
     */
    public function downloadFile(string $path, ?string $filename = null)
    {
        if (empty($this->url)) throw new DownloadMediaException("Media is not available for download.");
        return Util::downloadFile($this->url, $path, $filename);
    }
}
