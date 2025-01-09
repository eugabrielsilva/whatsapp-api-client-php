<?php

namespace WhatsAppPHP\Entity;

use WhatsAppPHP\Client;
use WhatsAppPHP\Util;

class Chat
{
    public $id;
    public $name;
    public $date;
    public $unreadMessages;
    public $isGroup;
    public $isMuted;
    public $isReadonly;
    public $isArchived;
    public $isPinned;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $key = Util::snakeToCamelCase($key);
            $this->{$key} = $value;
        }
    }

    public function getProfile()
    {
        $number = Util::formatNumber($this->id);
        return Client::getInstance()->getProfile($number);
    }

    public function getMessages(?int $limit = null)
    {
        $number = Util::formatNumber($this->id);
        return Client::getInstance()->getChat($number, $limit);
    }

    public function sendMessage(string $message)
    {
        $number = Util::formatNumber($this->id);
        return Client::getInstance()->sendMessage($number, $message);
    }

    public function sendLocation(int $latitude, int $longitude, ?string $address = null, ?string $url = null)
    {
        $number = Util::formatNumber($this->id);
        return Client::getInstance()->sendLocation($number, $latitude, $longitude, $address, $url);
    }
}
