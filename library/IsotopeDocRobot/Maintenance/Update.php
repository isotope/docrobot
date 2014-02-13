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

        $versionChoice = new \CheckBox();
        $versionChoice->label = 'Version';
        $versionChoice->name = 'version';
        $versionChoice->mandatory = true;
        $versionChoice->multiple = true;
        $versionChoice->options = array(
            array(
                'value' => '1.4',
                'label' => '1.4'
            ),
            array(
                'value' => '2.0',
                'label' => '2.0'
            ),

        );
        $objTemplate->versionChoice = $versionChoice->parse();

        $langChoice = new \CheckBox();
        $langChoice->label = 'Sprache';
        $langChoice->name = 'lang';
        $langChoice->mandatory = true;
        $langChoice->multiple = true;
        $langChoice->options = array(
            array(
                'value' => 'de',
                'label' => 'de'
            ));
        $objTemplate->langChoice = $langChoice->parse();

        $bookChoice = new \CheckBox();
        $bookChoice->label = 'Buch';
        $bookChoice->name = 'book';
        $bookChoice->mandatory = true;
        $bookChoice->multiple = true;
        $bookChoice->options = array(
            array(
                'value' => 'manual',
                'label' => 'manual'
            ));
        $objTemplate->bookChoice = $bookChoice->parse();

        return $objTemplate->parse();
    }
}
