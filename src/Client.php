<?php

namespace WhatsAppPHP;

use CURLFile;
use Exception;
use WhatsAppPHP\Entity\Chat;
use WhatsAppPHP\Entity\Message;
use WhatsAppPHP\Entity\Profile;
use WhatsAppPHP\Entity\QRCode;
use WhatsAppPHP\Exception\RequestException;

/**
 * WhatsApp PHP client instance.
 * @author Gabriel Silva
 * @license MIT
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
     * @param string|null $authToken (Optional) Authentication token if any.
     * @param int $timeout (Optional) Request timeout in seconds.
     * @return Client Returns the client instance.
     */
    public static function create(string $hostUrl, ?string $authToken = null, int $timeout = 30)
    {
        self::$hostUrl = rtrim($hostUrl, '/');
        self::$authToken = $authToken;
        self::$timeout = $timeout;
        self::$instance = new self();
        return self::$instance;
    }

    /**
     * Sets the API auth token.
     * @param string|null $authToken Authentication token if any.
     * @return Client Returns the client instance.
     */
    public function setAuthToken(?string $authToken)
    {
        self::$authToken = $authToken;
        return $this;
    }

    /**
     * Sets the base URL of the API.
     * @param string $hostUrl URL.
     * @return Client Returns the client instance.
     */
    public function setHostUrl(string $hostUrl)
    {
        self::$hostUrl = $hostUrl;
        return $this;
    }

    /**
     * Sets the request timeout.
     * @param int $timeout (Optional) Request timeout in seconds.
     * @return Client Returns the client instance.
     */
    public function setTimeout(int $timeout)
    {
        self::$timeout = $timeout;
        return $this;
    }

    /**
     * Gets the current Client instance.
     * @return Client Returns the client instance.
     * @throws Exception Throws an exception if the Client instance was not created.
     */
    public static function getInstance()
    {
        if (self::$instance === null) throw new Exception('Client instance was not created.');
        return self::$instance;
    }

    /**
     * Checks if the client is logged in to WhatsApp.
     * @return bool Returns true on logged in, otherwise false.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function checkLogin()
    {
        $response = $this->request('check-login');
        return $response['status'] ?? false;
    }

    /**
     * Performs a login to WhatsApp, if the client is not logged yet.
     * @return QRCode|bool Returns the QR Code, false if already logged in.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function login()
    {
        $response = $this->request('login');
        return isset($response['data']) ? new QRCode($response['data']) : false;
    }

    /**
     * Disconnects from WhatsApp, if the client is already logged in.
     * @return bool Returns true on success, false on failure.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function logout()
    {
        $response = $this->request('logout', 'POST');
        return $response['status'] ?? false;
    }

    /**
     * Sends an Online presence status to the client.
     * @return bool Returns true on success, false on failure.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function setOnline()
    {
        $response = $this->request('set-online', 'POST');
        return $response['status'] ?? false;
    }

    /**
     * Sends an Offline presence status to the client.
     * @return bool Returns true on success, false on failure.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function setOffline()
    {
        $response = $this->request('set-offline', 'POST');
        return $response['status'] ?? false;
    }

    /**
     * Gets the messages from a chat.
     * @param string $number Contact number to fetch messages.
     * @param int|null $limit (Optional) Maximum number of messages to fetch. Leave blank to get as many as possible.
     * @return Message[] Returns a list of messages.
     * @throws RequestException Throws an exception if the request fails.
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
     * Searches for a message.
     * @param string $query Query string to search.
     * @param string|null $number (Optional) Specific phone number to search in.
     * @param int|null $limit (Optional) Maximum number of messages to fetch.
     * @param int|null $page (Optional) Results page number.
     * @return Message[] Returns a list of messages.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function searchMessages(string $query, ?string $number = null, ?int $limit = null, ?int $page = null)
    {
        if (!is_null($number)) $number = Util::formatNumber($number);
        $response = $this->request('search-messages', 'GET', [
            'query' => $query,
            'limit' => $limit,
            'number' => $number,
            'page' => $page,
        ]);

        return array_map(function ($message) {
            return new Message($message);
        }, $response['messages'] ?? []);
    }

    /**
     * Get a list of available chats.
     * @return Chat[] Returns a list of chats.
     * @throws RequestException Throws an exception if the request fails.
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
     * @param string $number Phone number to fetch profile.
     * @return Profile|null Returns the profile if found.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function getProfile(string $number)
    {
        $number = Util::formatNumber($number);
        $response = $this->request("get-profile/$number");
        return isset($response['profile']) ? new Profile($response['profile']) : null;
    }

    /**
     * Gets a list of all contact profiles.
     * @return Profile[] Returns a list of profiles.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function getContacts()
    {
        $response = $this->request('get-contacts');

        return array_map(function ($contact) {
            return new Profile($contact);
        }, $response['contacts'] ?? []);
    }

    /**
     * Gets the current connected user profile.
     * @return Profile|null Returns the profile if found.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function getUser()
    {
        $response = $this->request('get-me');
        return isset($response['profile']) ? new Profile($response['profile']) : null;
    }

    /**
     * Sends a text message.
     * @param string $number Phone number to send message.
     * @param string $message Message body.
     * @param string|null $replyTo (Optional) Another message ID to reply to.
     * @return bool Returns true on success, false on failure.
     * @throws RequestException Throws an exception if the request fails.
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
     * @param string $number Phone number to send location.
     * @param int $latitude Latitude coordinates.
     * @param int $longitude Longitude coordinates.
     * @param string|null $address (Optional) Address name to include in the message.
     * @param string|null $url (Optional) URL to include in the message.
     * @param string|null $replyTo (Optional) Another message ID to reply to.
     * @return bool Returns true on success, false on failure.
     * @throws RequestException Throws an exception if the request fails.
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
     * @param string $number Phone number to send media.
     * @param string $file File location path or remote URL.
     * @param string|null $message (Optional) Caption to send with the media.
     * @param bool $viewOnce (Optional) Send the media as view once.
     * @param bool $asDocument (Optional) Send the media as a document.
     * @param bool $asVoice (Optional) Send audio media as a voice.
     * @param bool $asGif (Optional) Send video media as a GIF.
     * @param bool $asSticker (Optional) Send image media as a sticker.
     * @param string|null $replyTo (Optional) Another message ID to reply to.
     * @return bool Returns true on success, false on failure.
     * @throws RequestException Throws an exception if the request fails.
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
     * @param string $number Phone number to send the sticker.
     * @param string $file Sticker image file location path or remote URL.
     * @param string|null $replyTo (Optional) Another message ID to reply to.
     * @return bool Returns true on success, false on failure.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function sendSticker(string $number, string $file, ?string $replyTo = null)
    {
        return $this->sendMedia($number, $file, null, false, false, false, false, true, $replyTo);
    }

    /**
     * Sends a voice message.
     * @param string $number Phone number to send the voice message.
     * @param string $file Audio file location path or remote URL.
     * @param bool $viewOnce (Optional) Send the audio as view once.
     * @param string|null $replyTo (Optional) Another message ID to reply to.
     * @return bool Returns true on success, false on failure.
     * @throws RequestException Throws an exception if the request fails.
     */
    public function sendVoice(string $number, string $file, bool $viewOnce = false, ?string $replyTo = null)
    {
        return $this->sendMedia($number, $file, null, $viewOnce, false, true, $replyTo);
    }

    /**
     * Consumes a message received webhook.
     * @param string $body Request body in raw format.
     * @return Message|null Returns the message if valid.
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
     * @return array|null Returns the response if valid.
     * @throws RequestException Throws an exception if the request fails.
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

        // Cleanup data
        $data = array_filter($data, function ($value) {
            return !is_null($value);
        });

        // Upload file if any
        if (!is_null($file)) {
            if (!is_file($file)) {
                $fileName = Util::downloadFile($file, sys_get_temp_dir(), uniqid('wa_') . '.tmp');
            }
            $fileName = realpath($file);
            $data['file'] = new CURLFile($fileName, mime_content_type($fileName), basename($file));
        }

        // Prepare headers and data
        if (!empty($data['file'])) {
            $headers = [
                'Content-Type: multipart/form-data',
                "Authorization: Bearer $token",
            ];

            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            $headers = [
                'Content-Type: application/json',
                "Authorization: Bearer $token",
            ];

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
