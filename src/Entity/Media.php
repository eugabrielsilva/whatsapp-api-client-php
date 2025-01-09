<?php

namespace WhatsAppPHP\Entity;

use WhatsAppPHP\Client;
use WhatsAppPHP\Exception\DownloadMediaException;
use WhatsAppPHP\Util;

class Media
{
    public $url;
    public $type;
    public $extension;
    public $filename;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $key = Util::snakeToCamelCase($key);
            $this->{$key} = $value;
        }
    }

    public function downloadFile(string $path, ?string $filename = null)
    {
        if (empty($this->url)) {
            throw new DownloadMediaException("Media is not available for download.");
        }

        $fileContent = @file_get_contents($this->url);

        if ($fileContent === false) {
            throw new DownloadMediaException("Failed to download the media file from: {$this->url}.");
        }

        if (is_null($filename)) $filename = basename(parse_url($this->url, PHP_URL_PATH));
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $fullPath = $path . $filename;

        if (file_put_contents($fullPath, $fileContent) === false) {
            throw new DownloadMediaException("Failed to save the media file to: {$fullPath}.");
        }

        return $fullPath;
    }
}
