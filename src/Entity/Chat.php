<?php

namespace WhatsAppPHP\Entity;

use WhatsAppPHP\Client;
use WhatsAppPHP\Util;

/**
 * WhatsApp PHP Chat entity.
 * @package eugabrielsilva/whatsapp-php
 */
class Chat
{
    /**
     * Chat ID / number.
     * @var string
     */
    public $id;

    /**
     * Contact name.
     * @var string
     */
    public $name;

    /**
     * Last message date formatted locally.
     * @var string
     */
    public $date;

    /**
     * Number of unread messages.
     * @var int
     */
    public $unreadMessages;

    /**
     * Is chat a group?
     * @var bool
     */
    public $isGroup;

    /**
     * Is chat muted?
     * @var bool
     */
    public $isMuted;

    /**
     * Is chat read-only?
     * @var bool
     */
    public $isReadonly;

    /**
     * Is chat archived?
     * @var bool
     */
    public $isArchived;

    /**
     * Is chat pinned?
     * @var bool
     */
    public $isPinned;

    /**
     * Create Chat entity.
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
     * Gets the profile associated with this chat.
     * @return Profile|null
     */
    public function getProfile()
    {
        $number = Util::formatNumber($this->id);
        return Client::getInstance()->getProfile($number);
    }

    /**
     * Gets the messages from this chat.
     * @param int|null $limit (Optional) Maximum number of messages to fetch. Leave blank to get as many as possible.
     * @return Message[]
     */
    public function getMessages(?int $limit = null)
    {
        $number = Util::formatNumber($this->id);
        return Client::getInstance()->getChat($number, $limit);
    }

    /**
     * Sends a text message to this chat.
     * @param string $message Message body.
     * @return bool
     */
    public function sendMessage(string $message)
    {
        $number = Util::formatNumber($this->id);
        return Client::getInstance()->sendMessage($number, $message);
    }

    /**
     * Sends a location pin to this chat.
     * @param int $latitude Latitude coordinates.
     * @param int $longitude Longitude coordinates.
     * @param string|null $address (Optional) Address name to include in the message.
     * @param string|null $url (Optional) URL to include in the message.
     * @return bool
     */
    public function sendLocation(int $latitude, int $longitude, ?string $address = null, ?string $url = null)
    {
        $number = Util::formatNumber($this->id);
        return Client::getInstance()->sendLocation($number, $latitude, $longitude, $address, $url);
    }
}
