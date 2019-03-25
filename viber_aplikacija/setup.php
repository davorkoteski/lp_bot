<?php
require_once 'vendor/autoload.php';
use Viber\Client;
$config = require('config.php');
$apiKey = $config['apiKey']; // from PA "Edit Details" page
$webhookUrl = $config['webhookUrl']; // for exmaple https://my.com/bot.php
try {
    $client = new Client(['token' => $apiKey]);
    $result = $client->setWebhook($webhookUrl);
    echo "Success!\n"; // print_r($result);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}