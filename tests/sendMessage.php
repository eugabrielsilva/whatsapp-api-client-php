<?php

use WhatsAppPHP\Client;

require '_env.php';
require '../vendor/autoload.php';

$client = Client::create(API_HOST, API_TOKEN, API_TIMEOUT);

$status = $client->sendMessage(API_TEST_NUMBER, 'Hello world.');

var_dump($status);
