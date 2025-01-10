<?php

namespace WhatsAppPHP;

use CURLFile;
use Exception;
use WhatsAppPHP\Entity\Chat;
use WhatsAppPHP\Entity\Message;
use WhatsAppPHP\Entity\Profile;
use WhatsAppPHP\Exception\RequestException;

/**
 * WhatsApp PHP client instance.
 * @package eugabrielsilva/whatsapp-php
 */
class Client
{
    /**
     * Base URL of the API.
     * @var string
     */
    private static $hostUrl;

    /**
     * Authentication token if any.
     * @var string|null
     */
    private static $authToken;

    /**
     * Request timeout.
     * @var int
     */
    private static $timeout;

    /**
     * Client instance.
     * @var Client|null
     */
    private static $instance;

    /**
     * Disable construction because of Singleton pattern.
     */
    private function __construct() {}

    /**
     * Creates a Client instance.
     * @param string $hostUrl Base URL of the API.
     * @param string|null $authToken Authentication token if any.
     * @return Client
     */
    public static function create(string $hostUrl, ?string $authToken = null, int $timeout = 30)
    {
        self::$hostUrl = trim($hostUrl, '/');
        self::$authToken = $authToken;
        self::$timeout = $timeout;
        self::$instance = new self();
        return self::$instance;
    }

    /**
     * Gets the current Client instance.
     * @return Client
     */
    public static function getInstance()
    {
        if (self::$instance === null) throw new Exception('Client instance was not created.');
        return self::$instance;
    }

    /**
     * Checks if the client is logged in to WhatsApp.
     * @return bool
     */
    public function checkLogin()
    {
        $response = $this->request('check-login');
        return $response['status'] ?? false;
    }

    /**
     * Performs a login to WhatsApp, if the client is not logged yet.
     * @return string|bool
     */
    public function login()
    {
        $response = $this->request('login');
        return $response ?? false;
    }

    /**
     * Disconnects from WhatsApp, if the client is already logged in.
     * @return bool
     */
    public function logout()
    {
        $response = $this->request('logout', 'POST');
        return $response['status'] ?? false;
    }

    /**
     * Gets the messages from a chat.
     * @param string $number Contact number to fetch messages.
     * @param int|null $limit (Optional) Maximum number of messages to fetch. Leave blank to get as many as possible.
     * @return Message[]
     */
    public function getMessages(string $number, ?int $limit = null)
    {
        $number = Util::formatNumber($number);
        if ($limit) $limit = ['limit' => $limit];

        $response = $this->request("get-chat/$number", 'GET', $limit ?? []);

        return array_map(function ($message) {
            return new Message($message);
        }, $response['messages'] ?? []);
    }

    /**
     * Get a list of available chats.
     * @return Chat[]
     */
    public function getChats()
    {
        $response = $this->request('get-chats');

        return array_map(function ($message) {
            return new Chat($message);
        }, $response['chats'] ?? []);
    }

    /**
     * Gets a user profile.
     * @param string $number Contact number to fetch profile.
     * @return Profile|null
     */
    public function getProfile(string $number)
    {
        $number = Util::formatNumber($number);
        $response = $this->request("get-profile/$number", 'GET');
        return isset($response['profile']) ? new Profile($response['profile']) : null;
    }

    /**
     * Gets a list of all contact profiles.
     * @return Profile[]
     */
    public function getContacts()
    {
        $response = $this->request('get-contacts');

        return array_map(function ($contact) {
            return new Profile($contact);
        }, $response['contacts'] ?? []);
    }

    /**
     * Sends a text message.
     * @param string $number Contact number to send message.
     * @param string $message Message body.
     * @param string|null $replyTo (Optional) Another message ID to reply to.
     * @return bool
     */
    public function sendMessage(string $number, string $message, ?string $replyTo = null)
    {
        $number = Util::formatNumber($number);
        $response = $this->request("send-message/{$number}", 'POST', [
            'message' => $message,
            'reply_to' => $replyTo
        ]);

        return $response['status'] ?? false;
    }

    /**
     * Sends a location pin.
     * @param string $number Contact number to send location.
     * @param int $latitude Latitude coordinates.
     * @param int $longitude Longitude coordinates.
     * @param string|null $address (Optional) Address name to include in the message.
     * @param string|null $url (Optional) URL to include in the message.
     * @param string|null $replyTo (Optional) Another message ID to reply to.
     * @return bool
     */
    public function sendLocation(string $number, int $latitude, int $longitude, ?string $address = null, ?string $url = null, ?string $replyTo = null)
    {
        $number = Util::formatNumber($number);
        $response = $this->request("send-location/$number", 'POST', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $address,
            'url' => $url,
            'reply_to' => $replyTo
        ]);

        return $response['status'] ?? false;
    }

    /**
     * Sends a media message.
     * @param string $number Contact number to send media.
     * @param string $file File location path or remote URL.
     * @param string|null $message (Optional) Caption to send with the media.
     * @param bool $viewOnce (Optional) Send the media as view once.
     * @param bool $asDocument (Optional) Send the media as a document.
     * @param bool $asVoice (Optional) Send audio media as a voice.
     * @param bool $asGif (Optional) Send video media as a GIF.
     * @param bool $asSticker (Optional) Send image media as a sticker.
     * @param string|null $replyTo (Optional) Another message ID to reply to.
     * @return bool
     */
    public function sendMedia(string $number, string $file, ?string $message = null, bool $viewOnce = false, bool $asDocument = false, bool $asVoice = false, bool $asGif = false, bool $asSticker = false, ?string $replyTo = null)
    {
        $number = Util::formatNumber($number);
        $response = $this->request("send-media/$number", 'POST', [
            'message' => $message,
            'view_once' => $viewOnce,
            'as_document' => $asDocument,
            'as_voice' => $asVoice,
            'as_gif' => $asGif,
            'as_sticker' => $asSticker,
            'reply_to' => $replyTo
        ], $file);

        return $response['status'] ?? false;
    }

    /**
     * Sends a sticker.
     * @param string $number Contact number to send the sticker.
     * @param string $file Sticker image file location path or remote URL.
     * @param string|null $replyTo (Optional) Another message ID to reply to.
     * @return bool
     */
    public function sendSticker(string $number, string $file, ?string $replyTo = null)
    {
        return $this->sendMedia($number, $file, null, false, false, false, false, true, $replyTo);
    }

    /**
     * Sends a voice message.
     * @param string $number Contact number to send the voice message.
     * @param string $file Audio file location path or remote URL.
     * @param bool $viewOnce (Optional) Send the audio as view once.
     * @param string|null $replyTo (Optional) Another message ID to reply to.
     * @return bool
     */
    public function sendVoice(string $number, string $file, bool $viewOnce = false, ?string $replyTo = null)
    {
        return $this->sendMedia($number, $file, null, $viewOnce, false, true, $replyTo);
    }

    /**
     * Consumes a message received webhook.
     * @param string $body Request body in raw format.
     * @return Message|null
     */
    public function consumeWebhook(string $body)
    {
        $body = json_decode($body, true);
        if (!$body || empty($body['type']) || $body['type'] !== 'message_received' || empty($body['data'])) return null;
        return new Message($body['data']);
    }

    /**
     * Perform an HTTP request.
     * @param string $url URL path to request.
     * @param string $method (Optional) HTTP method.
     * @param array $data (Optional) Associative array with the request body.
     * @param string $file (Optional) Uploadable file location or URL.
     * @return array|null
     */
    private function request(string $url, string $method = 'GET', array $data = [], ?string $file = null)
    {
        // Initialize CURL
        $ch = curl_init();
        $token = self::$authToken ?? '';

        // Set CURL options
        curl_setopt_array($ch, [
            CURLOPT_AUTOREFERER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYSTATUS => false,
            CURLOPT_CONNECTTIMEOUT => self::$timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => false,
        ]);

        // Upload file if any
        if (!is_null($file)) {
            if (is_file($file)) {
                $fileName = realpath($file);
            } else {
                $fileName = Util::downloadFile($file, sys_get_temp_dir(), uniqid('wa_') . '.tmp');
            }
            $data['file'] = new CURLFile($fileName, mime_content_type($fileName), basename($file));
        }

        // Prepare headers and data
        if (!empty($data['file'])) {
            $headers = [
                "Authorization: Bearer $token",
            ];

            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            $headers = [
                'Content-Type: application/json',
                "Authorization: Bearer $token",
            ];

            // Cleanup data
            $data = array_filter($data, function ($value) {
                return !is_null($value);
            });

            if (!empty($data)) {
                if ($method === 'GET') {
                    $url = $url . '?' . http_build_query($data);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
            }
        }

        // Set headers and URL
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, self::$hostUrl . '/' . $url);

        // Execute request
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        // Handle general errors
        if (curl_errno($ch)) {
            throw new RequestException(curl_error($ch), curl_errno($ch));
        }

        // Parse response
        $body = json_decode($response, true);

        // Handle HTTP and body errors
        if (
            (isset($info['http_code']) && $info['http_code'] >= 400) ||
            (isset($body['status']) && $body['status'] === false)
        ) {
            throw new RequestException($body['error'] ?? 'Unexpected HTTP error.', $info['http_code'], $body['details'] ?? null);
        }

        // Close connection
        curl_close($ch);

        // Return response
        return $body;
    }
}
