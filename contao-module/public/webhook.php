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
$files = array();
foreach ($data->commits as $commit) {
    $files = array_merge($files, $commit->added, $commit->modified);
}
$files = array_unique($files);

$arrLanguages = array();
$arrLanguageSettings = deserialize($GLOBALS['TL_CONFIG']['iso_docrobot_languages'], true);
foreach($arrLanguageSettings as $arrLanguage) {
    $arrLanguages[] = $arrLanguage['language'];
}
$arrBooks = trimsplit(',', $GLOBALS['TL_CONFIG']['iso_docrobot_books']);

// update mirror
foreach ($files as $file) {
    $chunks = explode('/', $file);

    $lang = $chunks[0];
    $book = $chunks[1];

    // check if valid
    if (!in_array($lang, $arrLanguages) || !in_array($book, $arrBooks)) {
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

        // Only works for HTML for now
        $context = new \IsotopeDocRobot\Context\Context('html');
        $context->setBook($book);
        $context->setLanguage($lang);
        $context->setVersion($version);

        try {
            $routing = new \IsotopeDocRobot\Routing\Routing($context);
        } catch (\InvalidArgumentException $e) {
            continue;
        }

        $bookParser = new \IsotopeDocRobot\Service\GitHubCachedBookParser(
            'system/cache/isotope/docrobot',
            new \IsotopeDocRobot\Service\GitHubBookParser(
                $context,
                $routing
            )
        );

        // We could optimize this by purging only affected routes
        $bookParser->purgeCache();
        $bookParser->parseAllRoutes();
    }
}

$objResponse = new Response('', 200);
$objResponse->send();