<?php

namespace IsotopeDocRobot;


use IsotopeDocRobot\Markdown\Parsers\ImageParser;
use IsotopeDocRobot\Markdown\Parsers\IncompleteParser;
use IsotopeDocRobot\Markdown\Parsers\MessageParser;
use IsotopeDocRobot\Markdown\Parsers\RouteParser;
use IsotopeDocRobot\Markdown\Parsers\SitemapParser;
use IsotopeDocRobot\Routing\Routing;
use IsotopeDocRobot\Service\GitHubBookParser;
use IsotopeDocRobot\Service\ParserCollection;
use Haste\Http\Response\Response;

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
    protected $currentRoute = null;

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
        try {
            $this->routing = new Routing(
                sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s/config.json',
                    $this->currentVersion,
                    $this->language,
                    $this->book)
            );
        } catch (\InvalidArgumentException $e) {
            return '';
        }

        // Load root route as default
        $this->currentRoute = $this->routing->getRootRoute();

        // load current route
        if (\Input::get('r')) {
            $input = \Input::get('r');

            if ($route = $this->routing->getRouteForAlias($input)) {
                $this->currentRoute = $route;
                // update title
                $objPage->title .= ' - ' . $this->currentRoute->getTitle();
            } else {
                // 404
                $objError = new \PageError404();
                $objError->generate($objPage->id);
            }
        }

        $parserCollection = new ParserCollection();
        $parserCollection->addParser(new RouteParser($this->routing, $objPage, $this->currentVersion, $this->language));
        $parserCollection->addParser(new SitemapParser($this->generateNavigation($this->routing->getRootRoute()->getChildren(), 1, true)));
        $parserCollection->addParser(new ImageParser($this->language, $this->book, $objPage, $this->currentVersion));
        $parserCollection->addParser(new IncompleteParser());
        $parserCollection->addParser(new MessageParser());

        $this->bookParser = new GitHubBookParser($this->currentVersion, $this->language, $this->book, $this->routing, $parserCollection);
        $this->bookParser->loadLanguage();

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
        $strContent = $this->bookParser->getContentForRoute($this->currentRoute);

        if ($strContent === '') {
            $strContent = '<p>' . sprintf($GLOBALS['TL_LANG']['ISOTOPE_DOCROBOT']['noContentMsg'], 'https://github.com/isotope/docs') . '</p>';
        }

        $this->Template->content = $strContent;
        $this->bookParser->resetLanguage();
    }


    protected function generateNavigation($routes, $level=1, $blnIsSitemap=false)
    {
        global $objPage;
        $objTemplate = new \FrontendTemplate('nav_default');
        $objTemplate->type = get_class($this);
        $objTemplate->level = 'level_' . $level;
        $items = array();

        foreach ($routes as $route) {

            $blnIsInTrail = in_array($route->getName(), $this->currentRoute->getTrail());

            if (!$blnIsSitemap) {
                // Only show route if it's one of those
                // - the current route
                // - a route in the trail
                // - a sibling of any route in the trail
                // - a sibling of the current route
                // - a child of the current route
                if (!(
                    $route === $this->currentRoute
                    || $blnIsInTrail
                    || $this->routing->isSiblingOfOneInTrail($route, $this->currentRoute->getTrail())
                    || $this->currentRoute->isSiblingOfMine($route)
                    || $this->currentRoute->isChildOfMine($route)
                )) {
                    continue;
                }
            }

            // CSS class
            $strClass = ($blnIsInTrail) ? 'trail' : '';
            $strClass .=  ' ' . $route->getConfig()->type;

            // Incomplete
            if ($route->isIncomplete()) {
                $strClass .= ' incomplete';
            }

            // children
            $subitems = '';
            if ($route->hasChildren()) {
                $subitems = $this->generateNavigation($route->getChildren(), ($level + 1), $blnIsSitemap);
                $strClass .= ' subnav';
            }

            $row = array();
            $row['isActive']    = ($this->currentRoute->getName() == $route->getName()) ? true : false;
            $row['subitems']    = $subitems;
            $row['href']        = $this->routing->getHrefForRoute($route, $objPage, $this->currentVersion, $this->language);
            $row['title']       = specialchars($route->getTitle(), true);
            $row['pageTitle']   = specialchars($route->getTitle(), true);
            $row['link']        = $route->getTitle();
            $row['class']       = trim($strClass);
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