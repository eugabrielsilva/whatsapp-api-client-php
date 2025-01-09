<?php

namespace WhatsAppPHP;

use Exception;
use WhatsAppPHP\Entity\Chat;
use WhatsAppPHP\Entity\Message;
use WhatsAppPHP\Entity\Profile;
use WhatsAppPHP\Exception\RequestException;

class Client
{
    private static $hostUrl;
    private static $authToken;
    private static $instance;

    private function __construct()
    {
        // Unallow construction because of Singleton pattern
    }

    /**
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
     * @return Client
     */
    public static function getInstance()
    {
        if (self::$instance === null) throw new Exception('Client instance was not created.');
        return self::$instance;
    }

    /**
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
     * @return Profile|null
     */
    public function getProfile(string $number)
    {
        $number = Util::formatNumber($number);
        $response = $this->request("get-profile/$number", 'GET');
        return isset($response['profile']) ? new Profile($response['profile']) : null;
    }

    /**
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
     * @return object|null
     */
    private function request(string $url, string $method = 'GET', ?array $data = null)
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
