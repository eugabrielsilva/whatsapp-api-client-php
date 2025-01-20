# WhatsApp API Client for PHP

A PHP client SDK for [whatsapp-api](https://github.com/eugabrielsilva/whatsapp-api).

## Installation

```
composer require eugabrielsilva/whatsapp-php
```

## Usage

```php
use WhatsAppPHP\Client;

require_once 'vendor/autoload.php';

// Create the Client instance with the base API URL and your auth token
$client = Client::create('http://localhost:3000', 'optional_auth_token');
```

## Testing

Copy `tests/_env.example.php` to `tests/_env.php` to setup the test environment.
