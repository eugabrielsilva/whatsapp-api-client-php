<?php

namespace WhatsAppPHP\Entity;

use WhatsAppPHP\Client;
use WhatsAppPHP\Exception\DownloadMediaException;
use WhatsAppPHP\Util;

class Profile
{
    public $number;
    public $name;
    public $contactName;
    public $shortname;
    public $profilePicture;
    public $status;
    public $isSaved;
    public $isBlocked;
    public $isBusiness;
    public $isEnterprise;
    public $isMe;
    public $isValid;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $key = Util::snakeToCamelCase($key);
            $this->{$key} = $value;
        }
    }

    public function getMessages(?int $limit = null)
    {
        $number = Util::formatNumber($this->number);
        return Client::getInstance()->getChat($number, $limit);
    }

    public function sendMessage(string $message)
    {
        $number = Util::formatNumber($this->number);
        return Client::getInstance()->sendMessage($number, $message);
    }

    public function sendLocation(int $latitude, int $longitude, ?string $address = null, ?string $url = null)
    {
        $number = Util::formatNumber($this->number);
        return Client::getInstance()->sendLocation($number, $latitude, $longitude, $address, $url);
    }

    public function downloadProfilePicture(string $path, ?string $filename = null)
    {
        if (empty($this->profilePicture)) {
            throw new DownloadMediaException("Profile picture is not available for user {$this->number}.");
        }

        $fileContent = @file_get_contents($this->profilePicture);

        if ($fileContent === false) {
            throw new DownloadMediaException("Failed to download the profile picture file from: {$this->profilePicture}.");
        }

        if (is_null($filename)) $filename = basename(parse_url($this->profilePicture, PHP_URL_PATH));
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $fullPath = $path . $filename;

        if (file_put_contents($fullPath, $fileContent) === false) {
            throw new DownloadMediaException("Failed to save the profile picture file to: {$fullPath}.");
        }

        return $fullPath;
    }
}
