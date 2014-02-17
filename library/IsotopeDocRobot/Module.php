<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yanickwitschi
 * Date: 6/14/13
 * Time: 9:21 AM
 * To change this template use File | Settings | File Templates.
 */

namespace IsotopeDocRobot;


use IsotopeDocRobot\Routing\Route;
use IsotopeDocRobot\Routing\Routing;
use IsotopeDocRobot\Service\GitHubBookParser;

class Module extends \Module
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'mod_isotope_docrobot';

    protected $versions = array();
    protected $currentVersion;
    protected $language = '';
    protected $book = '';
    protected $bookParser = null;
    protected $routing = null;
    protected $currentRoute = 'root';

    /**
     * Display back end wildcard
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['isotope_docrobot'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }
        global $objPage;
        $this->versions = $GLOBALS['ISOTOPE_DOCROBOT_VERSIONS'];

        // defaults
        $this->currentVersion = $this->versions[0];
        $this->language = $objPage->rootLanguage;
        $this->book = $this->iso_docrobot_book;

        // override default version
        if (\Input::get('v')) {
            $this->currentVersion = \Input::get('v');
        }

        // Set title
        $objPage->title = ($objPage->pageTitle ?: $objPage->title) . ' (v ' . $this->currentVersion . ')';

        // load routing and book parser
        $this->routing = new Routing(
            sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s/config.json',
                $this->currentVersion,
                $this->language,
                $this->book)
        );
        $this->bookParser = new GitHubBookParser($this->currentVersion, $this->language, $this->book, $this->routing);

        // load current route
        if (\Input::get('r')) {
            $input = \Input::get('r');

            if ($route = $this->routing->getRouteForAlias($input)) {
                $this->currentRoute = $route->getName();
            } else {
                // 404
                $objError = new \PageError404();
                $objError->generate($objPage->id);
            }
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        global $objPage;

        // version change
        $objForm = new \Haste\Form\Form('version_change', 'POST', function($objHaste) {
            return \Input::post('FORM_SUBMIT') === $objHaste->getFormId();
        });
        $objForm->addFormField('version', array(
                                               'label'         => 'Version:',
                                               'inputType'     => 'select',
                                               'options'       => $GLOBALS['ISOTOPE_DOCROBOT_VERSIONS'],
                                               'default'       => $this->currentVersion
                                          ));

        if ($objForm->validate()) {

            $strParams = '/v/' . $objForm->fetch('version');

            // if we're on a certain site, we try to find it in the other version too
            if (\Input::get('r')) {
                $strParams .= '/r/' . \Input::get('r');
            }

            \System::redirect($objPage->getFrontendUrl($strParams));
        }

        $this->Template->form = $objForm;
        $this->Template->navigation = $this->generateNavigation($this->routing->getRootRoute()->getChildren());

        // content
        $path = sprintf('%s/system/cache/isotope/docrobot/%s/%s/%s/%s.html',
            TL_ROOT,
            $this->currentVersion,
            $this->language,
            $this->book,
            $this->currentRoute);

        if (is_file($path)) {
            $strContent = file_get_contents($path);
        } else {
            $strContent = '<p>' . sprintf($GLOBALS['TL_LANG']['ISOTOPE_DOCROBOT']['noContentMsg'], 'https://github.com/isotope/docs') . '</p>';
        }

        $this->Template->content = $strContent;
    }


    protected function generateNavigation($routes, $level=1)
    {
        global $objPage;
        $objTemplate = new \FrontendTemplate('nav_default');
        $objTemplate->type = get_class($this);
        $objTemplate->level = 'level_' . $level++;
        $items = array();

        foreach ($routes as $route) {

            // children
            $subitems = '';
            if ($route->hasChildren()) {
                $subitems = $this->generateNavigation($route->getChildren(), $level);
            }

            $row = array();
            $row['isActive']    = ($this->currentRoute == $route->getName()) ? true : false;
            $row['subitems']    = $subitems;
            $row['href']        = $this->routing->getHrefForRoute($objPage, $this->currentVersion, $route);
            $row['title']       = specialchars($route->getTitle(), true);
            $row['pageTitle']   = specialchars($route->getTitle(), true);
            $row['link']        = $route->getTitle();
            $items[]            = $row;
        }

        // Add classes first and last
        if (!empty($items))
        {
            $last = count($items) - 1;

            $items[0]['class'] = trim($items[0]['class'] . ' first');
            $items[$last]['class'] = trim($items[$last]['class'] . ' last');
        }

        $objTemplate->items = $items;
        return !empty($items) ? $objTemplate->parse() : '';
    }
}