<?php

namespace IsotopeDocRobot\Search;

use IsotopeDocRobot\Routing\Routing;

class Indexer
{
    public function addManualPagesToDSI($arrPages)
    {
        $latestVersion = $GLOBALS['ISOTOPE_DOCROBOT_VERSIONS'][0];


        foreach ($GLOBALS['ISOTOPE_DOCROBOT_LANGUAGES'] as $language => $pageId) {

            $pageModel = \PageModel::findWithDetails($pageId);
            $domain = ($pageModel->rootUseSSL ? 'https://' : 'http://') . ($pageModel->domain ?: \Environment::get('host')) . TL_PATH;


            foreach ($GLOBALS['ISOTOPE_DOCROBOT_BOOKS'] as $book) {
                try {
                    $routing = new Routing(
                        sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s/config.json',
                            $latestVersion,
                            $language,
                            $book)
                    );
                } catch (\InvalidArgumentException $e) {
                    continue;
                }

                foreach ($routing->getRoutes() as $route) {
                    $arrPages[] = $domain . $routing->getHrefForRoute($route, $pageModel, $latestVersion, $language);
                }
            }
        }

        return $arrPages;
    }
}