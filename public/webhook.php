<?php

use Haste\Http\Response\Response;

/**
 * Initialize the system
 */
define('TL_MODE', 'FE');
define('BYPASS_TOKEN_CHECK', true);

require '../../../initialize.php';

// Only allow GitHub IP range
$checker = new Whitelist\Check();
$checker->whitelist(array('192.30.252.0/22'));
if (!$checker->check(\Environment::get('ip'))) {
    $objResponse = new Response('Forbidden', 403);
    $objResponse->send();
}

// get json data
if (($data = json_decode(file_get_contents("php://input"))) === false) {
    $objResponse = new Response('Bad Request', 400);
    $objResponse->send();
}

// extract the branch/version
$refchunks = explode('/', $data->ref);
$version = array_pop($refchunks);

// store languages and books to update
$booksToUpdate = array();
$languagesToUpdate = array();

// get the added and modified data (ignore deleted, we don't care really)
$files = array_merge($data->commits[0]->added, $data->commits[0]->modified);

// update mirror
foreach ($files as $file) {
    $chunks = explode('/', $file);

    $lang = $chunks[0];
    $book = $chunks[1];

    // check if valid
    if (!in_array($lang, $GLOBALS['ISOTOPE_DOCROBOT_LANGUAGES']) || !in_array($book, $GLOBALS['ISOTOPE_DOCROBOT_BOOKS'])) {
        continue;
    }

    $connector = new \IsotopeDocRobot\Service\GitHubConnector($version, $lang, $book);
    $connector->updateFile($file);

    $booksToUpdate[] = $book;
    $languagesToUpdate[] = $lang;
}

$booksToUpdate = array_unique($booksToUpdate);
$languagesToUpdate = array_unique($languagesToUpdate);

foreach ($booksToUpdate as $book) {
    foreach ($languagesToUpdate as $lang) {
        $routing = new \IsotopeDocRobot\Routing\Routing(
            sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s/config.json',
                $version,
                $lang,
                $book)
        );

        $parserCollection = new \IsotopeDocRobot\Service\ParserCollection();
        $parserCollection->addParser(new \IsotopeDocRobot\Markdown\Parsers\NewVersionParser());
        $parserCollection->addParser(new \IsotopeDocRobot\Markdown\Parsers\MessageParser());
        $parserCollection->addParser(new \IsotopeDocRobot\Markdown\Parsers\RootParser($version));

        $parser = new \IsotopeDocRobot\Service\GitHubBookParser($version, $lang, $book, $routing, $parserCollection);
        $parser->updateFromMirror();
    }
}

$objResponse = new Response('', 200);
$objResponse->send();