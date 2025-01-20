<?php

use WhatsAppPHP\Client;

require '_env.php';
require '../vendor/autoload.php';

$client = Client::create(API_HOST, API_TOKEN, API_TIMEOUT);

$messages = $client->getMessages(API_TEST_NUMBER);

var_dump($messages);
