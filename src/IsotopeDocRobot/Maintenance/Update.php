<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yanickwitschi
 * Date: 6/14/13
 * Time: 11:48 AM
 * To change this template use File | Settings | File Templates.
 */

namespace IsotopeDocRobot\Maintenance;

use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Markdown\ParserCollection;
use IsotopeDocRobot\Markdown\Parsers\CurrentVersionParser;
use IsotopeDocRobot\Markdown\Parsers\ImageParser;
use IsotopeDocRobot\Markdown\Parsers\MessageParser;
use IsotopeDocRobot\Markdown\Parsers\NewVersionParser;
use IsotopeDocRobot\Markdown\Parsers\RootParser;
use IsotopeDocRobot\Markdown\Parsers\RouteParser;
use IsotopeDocRobot\Routing\Routing;
use IsotopeDocRobot\Service\GitHubBookParser;
use IsotopeDocRobot\Service\GitHubCachedBookParser;
use IsotopeDocRobot\Service\GitHubConnector;

class Update implements \executable
{

    /**
     * Return true if the module is active
     * @return boolean
     */
    public function isActive()
    {
        return false;
    }


    /**
     * Generate the module
     * @return string
     */
    public function run()
    {
        if (\Input::post('FORM_SUBMIT') == 'isotope-docrobot-update') {
            foreach (\Input::post('version') as $version) {
                foreach (\Input::post('lang') as $lang) {
                    foreach (\Input::post('book') as $book) {

                        if (\Input::post('fetch') == 'yes') {
                            $connector = new GitHubConnector($version, $lang, $book);
                            $connector->purgeCache();
                            $connector->updateAll();
                        }

                        $context = new Context('html');
                        $context->setBook($book);
                        $context->setLanguage($lang);
                        $context->setVersion($version);

                        try {
                            $routing = new Routing($context);
                        } catch (\InvalidArgumentException $e) {
                            continue;
                        }

                        $parserCollection = new ParserCollection($context, $routing);

                        $bookParser = new GitHubCachedBookParser(
                            'system/cache/isotope/docrobot',
                            $context,
                            new GitHubBookParser(
                                $context,
                                $parserCollection
                            )
                        );

                        $bookParser->purgeCache();
                        $bookParser->parseAllRoutes($routing);
                    }
                }
            }
        }

        $objTemplate = new \BackendTemplate('be_isotope_docrobot_maintenance');
        $objTemplate->action = ampersand(\Environment::get('request'));
        $objTemplate->headline = specialchars('Isotope DocRobot');

        $arrOptions = array();
        foreach (trimsplit(',', $GLOBALS['TL_CONFIG']['iso_docrobot_versions']) as $strVersion) {
            $arrOptions[] = array(
                'value' => $strVersion,
                'label' => $strVersion
            );
        }

        $arrSettings['id'] = 'version';
        $arrSettings['name'] = 'version';
        $arrSettings['label'] = 'Version';
        $arrSettings['mandatory'] = true;
        $arrSettings['multiple'] = true;
        $arrSettings['options'] = $arrOptions;
        $versionChoice = new \CheckBox($arrSettings);
        $objTemplate->versionChoice = $versionChoice->parse();
        unset($arrSettings);

        $arrOptions = array();
        foreach (deserialize($GLOBALS['TL_CONFIG']['iso_docrobot_languages'], true) as $arrLanguage) {
            $arrOptions[] = array(
                'value' => $arrLanguage['language'],
                'label' => $arrLanguage['language']
            );
        }

        $arrSettings['id'] = 'lang';
        $arrSettings['name'] = 'lang';
        $arrSettings['label'] = 'Sprache';
        $arrSettings['mandatory'] = true;
        $arrSettings['multiple'] = true;
        $arrSettings['options'] = $arrOptions;
        $langChoice = new \CheckBox($arrSettings);
        $objTemplate->langChoice = $langChoice->parse();
        unset($arrSettings);

        $arrOptions = array();
        foreach (trimsplit(',', $GLOBALS['TL_CONFIG']['iso_docrobot_books']) as $strBook) {
            $arrOptions[] = array(
                'value' => $strBook,
                'label' => $strBook
            );
        }

        $arrSettings['id'] = 'book';
        $arrSettings['name'] = 'book';
        $arrSettings['label'] = 'Buch';
        $arrSettings['mandatory'] = true;
        $arrSettings['multiple'] = true;
        $arrSettings['options'] = $arrOptions;
        $bookChoice = new \CheckBox($arrSettings);
        $objTemplate->bookChoice = $bookChoice->parse();
        unset($arrSettings);

        return $objTemplate->parse();
    }
}
