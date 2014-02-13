<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yanickwitschi
 * Date: 6/14/13
 * Time: 11:48 AM
 * To change this template use File | Settings | File Templates.
 */

namespace IsotopeDocRobot\Maintenance;


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
                        // delete the cache
                        $folder = new \Folder(sprintf('system/cache/isotope/docrobot/%s/%s/%s', $version, $lang, $book));
                        $folder->delete();

                        $updater = new \IsotopeDocRobot\Service\GitHubConnector($version, $lang, $book);
                        // generate a fresh config
                        $updater->refreshConfigurationFile();
                        $updater->updateAll();
                    }
                }
            }
        }

        $objTemplate = new \BackendTemplate('be_isotope_docrobot_maintenance');
        $objTemplate->action = ampersand(\Environment::get('request'));
        $objTemplate->headline = specialchars('Isotope DocRobot');

        $arrOptions = array();
        foreach ($GLOBALS['ISOTOPE_DOCROBOT_VERSIONS'] as $strVersion) {
            $arrOptions[] = array(
                'value' => $strVersion,
                'label' => $strVersion
            );
        }
        $versionChoice = new \CheckBox();
        $versionChoice->label = 'Version';
        $versionChoice->name = 'version';
        $versionChoice->mandatory = true;
        $versionChoice->multiple = true;
        $versionChoice->options = $arrOptions;
        $objTemplate->versionChoice = $versionChoice->parse();

        $arrOptions = array();
        foreach ($GLOBALS['ISOTOPE_DOCROBOT_LANGUAGES'] as $strLanguage) {
            $arrOptions[] = array(
                'value' => $strLanguage,
                'label' => $strLanguage
            );
        }
        $langChoice = new \CheckBox();
        $langChoice->label = 'Sprache';
        $langChoice->name = 'lang';
        $langChoice->mandatory = true;
        $langChoice->multiple = true;
        $langChoice->options = $arrOptions;
        $objTemplate->langChoice = $langChoice->parse();

        $arrOptions = array();
        foreach ($GLOBALS['ISOTOPE_DOCROBOT_BOOKS'] as $strBook) {
            $arrOptions[] = array(
                'value' => $strBook,
                'label' => $strBook
            );
        }
        $bookChoice = new \CheckBox();
        $bookChoice->label = 'Buch';
        $bookChoice->name = 'book';
        $bookChoice->mandatory = true;
        $bookChoice->multiple = true;
        $bookChoice->options = $arrOptions;
        $objTemplate->bookChoice = $bookChoice->parse();

        return $objTemplate->parse();
    }
}
