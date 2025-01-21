<?php

namespace WhatsAppPHP\Entity;

use WhatsAppPHP\Client;
use WhatsAppPHP\Exception\DownloadMediaException;
use WhatsAppPHP\Util;

/**
 * WhatsApp PHP Profile entity.
 * @package eugabrielsilva/whatsapp-php
 */
class Profile
{
    /**
     * Profile number.
     * @var string
     */
    public $number;

    /**
     * Profile public name.
     * @var string
     */
    public $name;

    /**
     * Profile saved contact name, if any.
     * @var string|null
     */
    public $contactName;

    /**
     * Profile short name, if any.
     * @var string|null
     */
    public $shortname;

    /**
     * Profile picture URL, if available.
     * @var string|null
     */
    public $profilePicture;

    /**
     * User status, if available.
     * @var string|null
     */
    public $status;

    /**
     * Is user saved in my contacts?
     * @var bool
     */
    public $isSaved;

    /**
     * Is user blocked?
     * @var bool
     */
    public $isBlocked;

    /**
     * Is user a business account?
     * @var bool
     */
    public $isBusiness;

    /**
     * Is user an enterprise account?
     * @var bool
     */
    public $isEnterprise;

    /**
     * Is user myself?
     * @var bool
     */
    public $isMe;

    /**
     * Is a valid WA user?
     * @var bool
     */
    public $isValid;

    /**
     * Create Profile entity.
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
     * Gets the messages from this profile.
     * @param int|null $limit (Optional) Maximum number of messages to fetch. Leave blank to get as many as possible.
     * @return Message[] Returns a list of messages.
     */
    public function getMessages(?int $limit = null)
    {
        return Client::getInstance()->getMessages($this->number, $limit);
    }

    /**
     * Sends a text message to this profile.
     * @param string $message Message body.
     * @return bool Returns true on success, false on failure.
     */
    public function sendMessage(string $message)
    {
        return Client::getInstance()->sendMessage($this->number, $message);
    }

    /**
     * Sends a location pin to this profile.
     * @param int $latitude Latitude coordinates.
     * @param int $longitude Longitude coordinates.
     * @param string|null $address (Optional) Address name to include in the message.
     * @param string|null $url (Optional) URL to include in the message.
     * @return bool Returns true on success, false on failure.
     */
    public function sendLocation(int $latitude, int $longitude, ?string $address = null, ?string $url = null)
    {
        return Client::getInstance()->sendLocation($this->number, $latitude, $longitude, $address, $url);
    }

    /**
     * Sends a media message to this profile.
     * @param string $file File location path or remote URL.
     * @param string|null $message (Optional) Caption to send with the media.
     * @param bool $viewOnce (Optional) Send the media as view once.
     * @param bool $asDocument (Optional) Send the media as a document.
     * @param bool $asVoice (Optional) Send audio media as a voice.
     * @param bool $asGif (Optional) Send video media as a GIF.
     * @param bool $asSticker (Optional) Send image media as a sticker.
     * @return bool Returns true on success, false on failure.
     */
    public function sendMedia(string $file, ?string $message = null, bool $viewOnce = false, bool $asDocument = false, bool $asVoice = false, bool $asGif = false, bool $asSticker = false)
    {
        return Client::getInstance()->sendMedia($this->number, $file, $message, $viewOnce, $asDocument, $asVoice, $asGif, $asSticker);
    }

    /**
     * Sends a sticker to this profile.
     * @param string $file Sticker image file location path or remote URL.
     * @return bool Returns true on success, false on failure.
     */
    public function sendSticker(string $file)
    {
        return $this->sendMedia($file, null, false, false, false, false, true);
    }

    /**
     * Sends a voice message to this profile.
     * @param string $file Audio file location path or remote URL.
     * @param bool $viewOnce (Optional) Send the audio as view once.
     * @return bool Returns true on success, false on failure.
     */
    public function sendVoice(string $file, bool $viewOnce = false)
    {
        return $this->sendMedia($file, null, $viewOnce, false, true);
    }

    /**
     * Downloads the profile picture, if available, to the local disk.
     * @param string $path Location folder in where to salve the file.
     * @param string $filename (Optional) Custom filename to set, leave blank to use the original filename.
     * @return string Returns the downloaded file location.
     */
    public function downloadProfilePicture(string $path, ?string $filename = null)
    {
        if (empty($this->profilePicture)) throw new DownloadMediaException("Profile picture is not available for user {$this->number}.");
        return Util::downloadFile($this->profilePicture, $path, $filename);
    }
}
