<?php

namespace IsotopeDocRobot\Search;

use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Routing\Routing;

class Indexer
{
    public function addManualPagesToDSI($arrPages)
    {
        $arrVersions = trimsplit(',', $GLOBALS['TL_CONFIG']['iso_docrobot_versions']);
        $strLatestVersion = $arrVersions[0];
        $arrLanguages = deserialize($GLOBALS['TL_CONFIG']['iso_docrobot_languages'], true);
        $arrBooks = trimsplit(',', $GLOBALS['TL_CONFIG']['iso_docrobot_books']);

        foreach ($arrLanguages as $arrLanguage) {
            foreach ($arrBooks as $strBook) {

                $pageModel = \PageModel::findWithDetails($arrLanguage['page']);
                $domain = ($pageModel->rootUseSSL ? 'https://' : 'http://') . ($pageModel->domain ?: \Environment::get('host')) . TL_PATH . '/';;

                $context = new Context('html');
                $context->setBook($strBook);
                $context->setLanguage($arrLanguage['language']);
                $context->setVersion($strLatestVersion);

                try {
                    $routing = new Routing($context);
                } catch (\InvalidArgumentException $e) {
                    continue;
                }

                foreach ($routing->getRoutes() as $route) {
                    $arrPages[] = $domain . $routing->getHrefForRoute($route);
                }
            }
        }

        return $arrPages;
    }
}