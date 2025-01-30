<?php

namespace WhatsAppPHP\Entity;

use DateTime;
use WhatsAppPHP\Client;
use WhatsAppPHP\Util;

/**
 * WhatsApp PHP Chat entity.
 * @author Gabriel Silva
 * @license MIT
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
     * Last message date.
     * @var DateTime|string
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
            if ($key === 'timestamp') {
                $this->date = new DateTime("@$value");
            } else {
                $key = Util::snakeToCamelCase($key);
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Gets the profile associated with this chat.
     * @return Profile|null Returns the profile if found.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function getProfile()
    {
        return Client::getInstance()->getProfile($this->id);
    }

    /**
     * Gets the messages from this chat.
     * @param int|null $limit (Optional) Maximum number of messages to fetch. Leave blank to get as many as possible.
     * @return Message[] Returns a list of messages.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function getMessages(?int $limit = null)
    {
        return Client::getInstance()->getMessages($this->id, $limit);
    }

    /**
     * Sends a text message to this chat.
     * @param string $message Message body.
     * @return bool Returns true on success, false on failure.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function sendMessage(string $message)
    {
        return Client::getInstance()->sendMessage($this->id, $message);
    }

    /**
     * Sends a location pin to this chat.
     * @param int $latitude Latitude coordinates.
     * @param int $longitude Longitude coordinates.
     * @param string|null $address (Optional) Address name to include in the message.
     * @param string|null $url (Optional) URL to include in the message.
     * @return bool Returns true on success, false on failure.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function sendLocation(int $latitude, int $longitude, ?string $address = null, ?string $url = null)
    {
        return Client::getInstance()->sendLocation($this->id, $latitude, $longitude, $address, $url);
    }

    /**
     * Sends a media message to this chat.
     * @param string $file File location path or remote URL.
     * @param string|null $message (Optional) Caption to send with the media.
     * @param bool $viewOnce (Optional) Send the media as view once.
     * @param bool $asDocument (Optional) Send the media as a document.
     * @param bool $asVoice (Optional) Send audio media as a voice.
     * @param bool $asGif (Optional) Send video media as a GIF.
     * @param bool $asSticker (Optional) Send image media as a sticker.
     * @return bool Returns true on success, false on failure.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function sendMedia(string $file, ?string $message = null, bool $viewOnce = false, bool $asDocument = false, bool $asVoice = false, bool $asGif = false, bool $asSticker = false)
    {
        return Client::getInstance()->sendMedia($this->id, $file, $message, $viewOnce, $asDocument, $asVoice, $asGif, $asSticker);
    }

    /**
     * Sends a sticker to this chat.
     * @param string $file Sticker image file location path or remote URL.
     * @return bool Returns true on success, false on failure.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function sendSticker(string $file)
    {
        return $this->sendMedia($file, null, false, false, false, false, true);
    }

    /**
     * Sends a voice message to this chat.
     * @param string $file Audio file location path or remote URL.
     * @param bool $viewOnce (Optional) Send the audio as view once.
     * @return bool Returns true on success, false on failure.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function sendVoice(string $file, bool $viewOnce = false)
    {
        return $this->sendMedia($file, null, $viewOnce, false, true);
    }

    /**
     * Searches for a message in this chat.
     * @param string $query Query string to search.
     * @param int|null $limit (Optional) Maximum number of messages to fetch.
     * @param int|null $page (Optional) Results page number.
     * @return Message[] Returns a list of messages.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function searchMessages(string $query, ?int $limit = null, ?int $page = null)
    {
        return Client::getInstance()->searchMessages($query, $this->id, $limit, $page);
    }
}
