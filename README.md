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

$client = Client::create('http://localhost:3000', 'auth_token_optional');
```