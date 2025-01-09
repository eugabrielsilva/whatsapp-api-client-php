<?php

namespace WhatsAppPHP;

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
    public static function create(string $hostUrl, ?string $authToken = null)
    {
        self::$hostUrl = trim($hostUrl, '/');
        self::$authToken = $authToken;
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
     * Gets the messages from a chat.
     * @param string $number Contact number to fetch messages.
     * @param int|null $limit (Optional) Maximum number of messages to fetch. Leave blank to get as many as possible.
     * @return Message[]
     */
    public function getChat(string $number, ?int $limit = null)
    {
        $number = Util::formatNumber($number);
        if ($limit) $limit = ['limit' => $limit];

        $response = $this->request("get-chat/$number", 'GET', $limit);

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
     * Sends a text message.
     * @param string $number Contact number to send message.
     * @param string $message Message body.
     * @return bool
     */
    public function sendMessage(string $number, string $message)
    {
        $number = Util::formatNumber($number);
        $response = $this->request("send-message/{$number}", 'POST', [
            'message' => $message
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
     * @return bool
     */
    public function sendLocation(string $number, int $latitude, int $longitude, ?string $address = null, ?string $url = null)
    {
        $number = Util::formatNumber($number);
        $response = $this->request("send-location/$number", 'POST', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $address,
            'url' => $url
        ]);

        return $response['status'] ?? false;
    }

    /**
     * Perform an HTTP request.
     * @param string $url URL path to request.
     * @param string $method (Optional) HTTP method.
     * @param array $data (Optional) Associative array with the request body.
     * @return object|null
     */
    private function request(string $url, string $method = 'GET', array $data = [])
    {
        // Initialize CURL
        $ch = curl_init();
        $token = self::$authToken;

        // Set CURL options
        curl_setopt_array($ch, [
            CURLOPT_AUTOREFERER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYSTATUS => false,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization Bearer $token"
            ],
        ]);

        // Parse data array
        if (!empty($data)) {
            if ($method === 'GET') {
                $url = $url . '?' . http_build_query($data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        // Set URL and make request
        curl_setopt($ch, CURLOPT_URL, self::$hostUrl . '/' . $url);
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
            throw new RequestException($body['error'] ?? 'Unexpected HTTP error.', $info['http_code']);
        }

        // Close connection
        curl_close($ch);

        // Return response
        return $body;
    }
}
