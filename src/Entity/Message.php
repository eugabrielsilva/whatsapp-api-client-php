<?php

namespace WhatsAppPHP\Entity;

use DateTime;
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
     * Message date.
     * @var DateTime|string
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
            } else if ($key === 'timestamp') {
                $this->date = new DateTime("@$value");
            } else {
                $key = Util::snakeToCamelCase($key);
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Gets the profile of the message sender.
     * @return Profile|null Returns the profile if found.
     */
    public function getFromProfile()
    {
        return Client::getInstance()->getProfile($this->from);
    }

    /**
     * Gets the profile of the message receiver.
     * @return Profile|null Returns the profile if found.
     */
    public function getToProfile()
    {
        return Client::getInstance()->getProfile($this->to);
    }

    /**
     * Replies the message to the sender.
     * @param string $message Message body.
     * @return bool Returns true on success, false on failure.
     */
    public function reply(string $message)
    {
        return Client::getInstance()->sendMessage($this->from, $message, $this->id);
    }

    /**
     * Gets the message body as string.
     * @return string Message body.
     */
    public function __toString()
    {
        return $this->body ?? '';
    }
}
