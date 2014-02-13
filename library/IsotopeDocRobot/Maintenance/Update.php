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

        $arrSettings['id'] = 'version';
        $arrSettings['name'] = 'version';
        $arrSettings['label'] = 'Version';
        $arrSettings['mandatory'] = true;
        $arrSettings['multiple'] = true;
        $arrSettings['options'] = $arrOptions;
        $versionChoice = new \CheckBox($arrSettings);
        $objTemplate->versionChoice = $versionChoice->parse();

        $arrOptions = array();
        foreach ($GLOBALS['ISOTOPE_DOCROBOT_LANGUAGES'] as $strLanguage) {
            $arrOptions[] = array(
                'value' => $strLanguage,
                'label' => $strLanguage
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

        $arrOptions = array();
        foreach ($GLOBALS['ISOTOPE_DOCROBOT_BOOKS'] as $strBook) {
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

        return $objTemplate->parse();
    }
}
