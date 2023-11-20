<?php

class LanguageDetectionFailedException extends Exception {}
class TranslationFailedException extends Exception {}
class NoGifFoundException extends Exception {}
class RequestFailedException extends Exception {}


require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/GoogleTranslateAPI.php';
require_once __DIR__ . '/GiphyAPI.php';

use SergiX44\Nutgram\Nutgram;
use GuzzleHttp\Client;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$bot = new Nutgram($_ENV['BOT_TOKEN']);
$guzzleClient = new Client();
$translateAPI = new GoogleTranslateAPI();
$giphyAPI = new GiphyAPI($_ENV['GIPHY_KEY']);

$bot->onText('gif {query}', function (Nutgram $bot, string $query) use ($giphyAPI, $translateAPI) {
    $lang = 'en';
    try {
        echo "Detecting language...\n";
        $lang = $translateAPI->detectLanguage($query);
        echo "Detected language: $lang\n";
    } catch (RequestFailedException|LanguageDetectionFailedException $e) {
        echo $e->getMessage() . "\n";
    }

    if ($lang == 'en') {
        echo "No need to translate.\n";
        $englishQuery = $query;
    } else {
        echo "Translating...\n";
        try {
            $englishQuery = $translateAPI->translate($query, $lang);
        } catch (RequestFailedException|TranslationFailedException $e) {
            echo $e->getMessage() . "\n";
            $englishQuery = $query;
        }
    }

    try {
        echo "Searching gif: '$englishQuery'\n";
        $gifUrl = $giphyAPI->getRandomGifUrl($englishQuery);
        $bot->sendAnimation($gifUrl);
    } catch (RequestFailedException|NoGifFoundException $e) {
        echo $e->getMessage() . "\n";
        $bot->sendMessage("Sorry, habe nichts gefunden für '$query'.");
    }
});

echo "Bot starting!\n";
$bot->run();
