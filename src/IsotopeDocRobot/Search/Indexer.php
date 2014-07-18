<?php

namespace IsotopeDocRobot\Search;

use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Routing\Routing;

class Indexer
{
    public function addManualPagesToDSI($arrPages)
    {
        $versions = trimsplit(',', $GLOBALS['TL_CONFIG']['iso_docrobot_versions']);
        $latestVersion = $versions[0];
        $arrLanguages = deserialize($GLOBALS['TL_CONFIG']['iso_docrobot_languages'], true);

        foreach ($arrLanguages as $arrLanguage) {
            foreach (trimsplit(',', $GLOBALS['TL_CONFIG']['iso_docrobot_books']) as $book) {

                $context = new Context('html');
                $context->setBook($book);
                $context->setLanguage($arrLanguage['language']);
                $context->setVersion($latestVersion);

                try {
                    $routing = new Routing($context);
                } catch (\InvalidArgumentException $e) {
                    continue;
                }

                foreach ($routing->getRoutes() as $route) {
                    $arrPages[] = 'https://' . $routing->getHrefForRoute($route);
                }
            }
        }

        return $arrPages;
    }
}