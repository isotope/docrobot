<?php

namespace IsotopeDocRobot\Search;

use IsotopeDocRobot\Routing\Routing;

class Indexer
{
    public function addManualPagesToDSI($arrPages)
    {
        $latestVersion = trimsplit(',', $GLOBALS['TL_CONFIG']['iso_docrobot_versions'])[0];
        $arrLanguages = deserialize($GLOBALS['TL_CONFIG']['iso_docrobot_languages'], true);

        foreach ($arrLanguages as $arrLanguage) {
            $pageModel = \PageModel::findWithDetails($arrLanguage['page']);
            $domain = ($pageModel->rootUseSSL ? 'https://' : 'http://') . ($pageModel->domain ?: \Environment::get('host')) . TL_PATH . '/';;


            foreach (trimsplit(',', $GLOBALS['TL_CONFIG']['iso_docrobot_books']) as $book) {
                try {
                    $routing = new Routing(
                        sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s/config.json',
                            $latestVersion,
                            $arrLanguage['language'],
                            $book)
                    );
                } catch (\InvalidArgumentException $e) {
                    continue;
                }

                foreach ($routing->getRoutes() as $route) {
                    $arrPages[] = $domain . $routing->getHrefForRoute($route, $pageModel, $latestVersion, $arrLanguage['language']);
                }
            }
        }

        return $arrPages;
    }
}