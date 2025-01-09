<?php

namespace WhatsAppPHP\Entity;

use WhatsAppPHP\Client;
use WhatsAppPHP\Util;

class Message
{
    public $id;
    public $type;
    public $from;
    public $to;
    public $body;
    public $date;
    public $isTemporary;
    public $isForwarded;
    public $isMine;
    public $isBroadcast;
    public $media;
    public $location;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (is_null($value)) continue;
            if ($key === 'media') {
                $this->media = new Media($value);
            } else if ($key === 'location') {
                $this->location = new Location($value);
            } else {
                $key = Util::snakeToCamelCase($key);
                $this->{$key} = $value;
            }
        }
    }

    public function getFromProfile()
    {
        $number = Util::formatNumber($this->from);
        return Client::getInstance()->getProfile($number);
    }

    public function getToProfile()
    {
        $number = Util::formatNumber($this->to);
        return Client::getInstance()->getProfile($number);
    }
}
