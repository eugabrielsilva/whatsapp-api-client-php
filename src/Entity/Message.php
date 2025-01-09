<?php

namespace WhatsAppPHP\Entity;

use WhatsAppPHP\Client;
use WhatsAppPHP\Util;

/**
 * WhatsApp PHP Message entity.
 * @package eugabrielsilva/whatsapp-php
 */
class Message
{
    /**
     * Message ID.
     * @var string
     */
    public $id;

    /**
     * Message type.
     * @var string
     */
    public $type;

    /**
     * Message from number.
     * @var string
     */
    public $from;

    /**
     * Message to number.
     * @var string
     */
    public $to;

    /**
     * Message body.
     * @var string
     */
    public $body;

    /**
     * Message date formatted locally.
     * @var string
     */
    public $date;

    /**
     * Is message temporary?
     * @var bool
     */
    public $isTemporary;

    /**
     * Is forwarded message?
     * @var bool
     */
    public $isForwarded;

    /**
     * Was the message sent by me?
     * @var bool
     */
    public $isMine;

    /**
     * Is message from a broadcast?
     * @var bool
     */
    public $isBroadcast;

    /**
     * Message media, if any.
     * @var Media|null
     */
    public $media;

    /**
     * Message location, if any.
     * @var Location|null
     */
    public $location;

    /**
     * Create Message entity.
     * @param array $data (Optional) Associative array of data to populate.
     */
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

    /**
     * Gets the profile of the message sender.
     * @return Profile|null
     */
    public function getFromProfile()
    {
        $number = Util::formatNumber($this->from);
        return Client::getInstance()->getProfile($number);
    }

    /**
     * Gets the profile of the message receiver.
     * @return Profile|null
     */
    public function getToProfile()
    {
        $number = Util::formatNumber($this->to);
        return Client::getInstance()->getProfile($number);
    }
}
