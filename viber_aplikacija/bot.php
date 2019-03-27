<?php

require_once 'vendor/autoload.php';
use Viber\Bot;
use Viber\Api\Sender;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
$config = require('./config.php');
$apiKey = $config['apiKey'];

// reply name
$botSender = new Sender([
    'name' => 'LicencePlate Bot',
    'avatar' => 'https://developers.viber.com/img/favicon.ico',
]);
// log bot interaction
$log = new Logger('bot');
$log->pushHandler(new StreamHandler('/tmp/bot.log'));
//DATABASE CONNECTION
$configuration = array(
    'db_dsn' => 'mysql:host=localhost;dbname=lp_message',
    'db_user' => 'root',
    'db_pass' => ''
);
try {
    $pdo = new PDO(
        $configuration['db_dsn'],
        $configuration['db_user'],
        $configuration['db_pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $log->info('Connected successfully');
}
catch (PDOException $e) {
    $log->info('Connection failed: '. $e->getMessage());
}

// API CALLS
function apiPost($url, $data) {
    $client = new GuzzleHttp\Client();
    $response = $client->post(
        $url,
        [GuzzleHttp\RequestOptions::JSON => $data]);
    return $response->getBody()->getContents();
}
function apiSelect($url, $data) {
    $client = new GuzzleHttp\Client();
    $response = $client->get($url . $data);
    return $response->getBody()->getContents();
}

$urlSelectUsers = 'http://127.0.0.1:8000/api/select-users/';
$urlAddUsers = 'http://127.0.0.1:8000/api/add-users';
$urlSelectPlates = 'http://127.0.0.1:8000/api/select-plates/';
$urlAddPlates = 'http://127.0.0.1:8000/api/add-plates';
$urlAddMessages = 'http://127.0.0.1:8000/api/add-messages';
$urlSelectUsersByPlate = 'http://127.0.0.1:8000/api/select-users-by-plate/';

try {
    // create bot instance
    $bot = new Bot(['token' => $apiKey]);
    $bot
        ->onConversation(function ($event) use ($bot, $botSender, $log) {
            $log->info('onConversation ' . var_export($event, true));
            // this event fires if user open chat, you can return "welcome message"
            // to user, but you can't send more messages!
            return (new Viber\Api\Message\Text())
                ->setSender($botSender)
                ->setText('To register type register and your licence plate, for example register BT123AB');
        })
        ->onText('/^register$/', function ($event) use ($bot, $botSender, $log, $pdo, $urlSelectUsers, $urlAddUsers) {
            $log->info('register' . var_export($event, true));
            $message = (new \Viber\Api\Message\Text())
                ->setSender($botSender)
                ->setReceiver($event->getSender()->getId());
            $id = $event->getSender()->getId();
            $name = $event->getSender()->getName();
            $data = array('name' => $name, 'app_id' => $id, 'created_at' => date("Y-m-d H:i:s"));
            try {
                apiSelect($urlSelectUsers, $id);
                $bot->getClient()->sendMessage(
                    $message
                        ->setText('This user is already registered')
                );
            } catch (Exception $e) {
                apiPost($urlAddUsers, $data);
                $bot->getClient()->sendMessage(
                    $message
                        ->setText('You have successfully registered')
                );
            }
        })
        ->onText('|register .*|s', function ($event) use ($bot, $botSender, $log, $pdo, $urlSelectUsers, $urlAddUsers, $urlSelectPlates, $urlAddPlates) {
            $log->info('register plate' . var_export($event, true));
            $text_message = explode(' ', $event->getMessage()->getText());
            $plate_number = $text_message[1];
            $id = $event->getSender()->getId();
            $name = $event->getSender()->getName();
            $message = (new \Viber\Api\Message\Text())
                ->setSender($botSender)
                ->setReceiver($event->getSender()->getId());
            try {
                apiSelect($urlSelectPlates, $plate_number);
                $bot->getClient()->sendMessage(
                    $message
                        ->setText('This plate is already registered')
                );
            } catch (Exception $e) {
                try {
                    $user = apiSelect($urlSelectUsers, $id);
                    $user = json_decode($user, true);
                    $data = array('number' => $plate_number, 'user_id' => $user['id'], 'created_at' => date("Y-m-d H:i:s"));
                    apiPost($urlAddPlates, $data);
                    $bot->getClient()->sendMessage(
                        $message
                            ->setText('Plate successfuly registered')
                    );
                } catch (Exception $e) {
                    $data = array('name' => $name, 'app_id' => $id, 'created_at' => date("Y-m-d H:i:s"));
                    $user = apiPost($urlAddUsers, $data);
                    $user = json_decode($user, true);
                    $data = array('number' => $plate_number, 'user_id' => $user['id'], 'created_at' => date("Y-m-d H:i:s"));
                    apiPost($urlAddPlates, $data);
                    $bot->getClient()->sendMessage(
                        $message
                            ->setText('You have registered')
                    );
                }
            }
        })
        ->onText('|.* .*|s', function ($event) use ($bot, $botSender, $log, $pdo, $urlSelectUsers, $urlSelectPlates, $urlAddMessages, $urlSelectUsersByPlate) {
            $log->info('register plate' . var_export($event, true));
            $message = (new \Viber\Api\Message\Text())
                ->setSender($botSender)
                ->setReceiver($event->getSender()->getId());
            $text_message = explode(' ', $event->getMessage()->getText());
            $plate_number = $text_message[0];
            $message_name = substr(strstr($event->getMessage()->getText(), " "), 1);
            $id = $event->getSender()->getId();
            try {
                $plate = apiSelect($urlSelectPlates, $plate_number);
                $plate = json_decode($plate, true);
                try {
                    $user = apiSelect($urlSelectUsers, $id);
                    $user = json_decode($user, true);
                    $data = array('name' => $message_name, 'user_id' => $user['id'], 'plate_id' => $plate['id'], 'created_at' => date("Y-m-d H:i:s"));
                    apiPost($urlAddMessages, $data);
                    $userByPlate = apiSelect($urlSelectUsersByPlate, $plate_number);
                    $userByPlate = json_decode($userByPlate, true);
                    $bot->getClient()->sendMessage(
                        (new \Viber\Api\Message\Text())
                            ->setSender($botSender)
                            ->setReceiver($userByPlate['app_id'])
                            ->setText($message_name)
                    );
                    $bot->getClient()->sendMessage(
                        $message
                            ->setText('The message was sent')
                    );
                } catch (Exception $e) {
                    $bot->getClient()->sendMessage(
                        $message
                            ->setText('You need to register first')
                    );
                }
            } catch (Exception $e) {
                $bot->getClient()->sendMessage(
                    $message
                        ->setText('This plate does not exist')
                );
            }
        })
        ->run();
} catch (Exception $e) {
    $log->warning('Exception: ', $e->getMessage());
    if ($bot) {
        $log->warning('Actual sign: ' . $bot->getSignHeaderValue());
        $log->warning('Actual body: ' . $bot->getInputBody());
    }
}