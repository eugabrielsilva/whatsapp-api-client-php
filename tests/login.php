<?php

use WhatsAppPHP\Client;

require '_env.php';
require '../vendor/autoload.php';

$client = Client::create(API_HOST, API_TOKEN, API_TIMEOUT);

$qr = $client->login();

var_dump($qr);
