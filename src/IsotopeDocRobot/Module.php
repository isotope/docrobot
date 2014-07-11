<?php

namespace IsotopeDocRobot;

use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Routing\Routing;
use IsotopeDocRobot\Service\GitHubBookParser;
use IsotopeDocRobot\Service\GitHubCachedBookParser;

class Module extends \Module
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'mod_isotope_docrobot';

    protected $versions = array();

    /* @var $bookParser \IsotopeDocRobot\Service\GitHubCachedBookParser */
    protected $bookParser = null;
    /* @var $context \IsotopeDocRobot\Context\Context */
    protected $context = null;
    /* @var $routing \IsotopeDocRobot\Routing\Routing */
    protected $routing = null;

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
        $this->versions = trimsplit(',', $GLOBALS['TL_CONFIG']['iso_docrobot_versions']);

        // defaults
        $this->context = new Context('html');
        $this->context->setVersion($this->versions[0]);
        $this->context->setLanguage($objPage->rootLanguage);
        $this->context->setBook($this->iso_docrobot_book);

        // override default version
        if (\Input::get('v')) {
            $currentVersion = \Input::get('v');
            if (!in_array($currentVersion, $this->versions)) {
                // 404
                $objError = new \PageError404();
                $objError->generate($objPage->id);
            }

            $this->context->setVersion($currentVersion);
        }

        // Set title
        $objPage->title = ($objPage->pageTitle ?: $objPage->title) . ' (v ' . $this->context->getVersion() . ')';

        // load routing and book parser
        try {
            $this->routing = new Routing($this->context);
            $this->routing->setRootTitle($objPage->title);
        } catch (\InvalidArgumentException $e) {
            return '';
        }

        // Load root route as default
        $this->routing->setCurrentRoute($this->routing->getRootRoute());

        // load current route
        if (\Input::get('r')) {
            $input = \Input::get('r');

            if ($route = $this->routing->getRouteForAlias($input)) {
                $this->routing->setCurrentRoute($route);
                // update title
                $objPage->title .= ' <span>' . $this->routing->getCurrentRoute()->getTitle() . '</span>';
            } else {
                // 404
                $objError = new \PageError404();
                $objError->generate($objPage->id);
            }
        }

        $this->bookParser = new GitHubCachedBookParser(
            'system/cache/isotope/docrobot',
            new GitHubBookParser($this->context, $this->routing)
        );

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        $GLOBALS['TL_CSS'][] = 'system/modules/isotope_docrobot/assets/jquery.autocomplete.css';
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope_docrobot/assets/jquery.autocomplete.min.js';
        global $objPage;

        // version change
        $objForm = new \Haste\Form\Form('version_change', 'POST', function($objHaste) {
            return \Input::post('FORM_SUBMIT') === $objHaste->getFormId();
        });
        $objForm->addFormField('version', array(
                                               'label'         => 'Version:',
                                               'inputType'     => 'select',
                                               'options'       => trimsplit(',', $GLOBALS['TL_CONFIG']['iso_docrobot_versions']),
                                               'default'       => $this->context->getVersion()
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
        $this->Template->feedbackForm = $this->generateFeedbackForm();
        $this->Template->navigation = $this->routing->generateNavigation();
        $this->Template->quickNavigatonData = $this->getQuickNavigationData();
        $this->Template->isIncomplete = $this->routing->getCurrentRoute()->isIncomplete();

        $this->Template->incompleteMsgBody = sprintf($GLOBALS['TL_LANG']['ISOTOPE_DOCROBOT']['incompleteMsgBody'],
            'https://github.com/isotope/docs',
            \PageModel::findByPk($this->jumpTo)->getFrontendUrl()
        );

        $this->Template->isNew = $this->routing->getCurrentRoute()->isNew();
        $this->Template->newDate = $this->routing->getCurrentRoute()->getNewAsDateTime()->format($GLOBALS['TL_CONFIG']['dateFormat']);
        $this->Template->index = false;

        // Only add book navigation on route sites
        if (\Input::get('r')) {
            $this->Template->bookNavigation = $this->routing->generateBookNavigation();
            $this->Template->index = true;
        }

        // content
        $strContent = $this->bookParser->parseRoute($this->routing->getCurrentRoute());

        if ($strContent === '') {
            $strContent = '<p>' . sprintf($GLOBALS['TL_LANG']['ISOTOPE_DOCROBOT']['noContentMsg'], 'https://github.com/isotope/docs') . '</p>';
        }

        $this->Template->content = $strContent;
    }

    protected function getQuickNavigationData()
    {
        global $objPage;
        $arrNav = array();
        foreach ($this->routing->getRoutes() as $route) {
            $arrNav[] = array(
                $route->getTitle(),
                $this->routing->getHrefForRoute($route, $objPage)
            );
        }

        return specialchars(json_encode($arrNav));
    }

    protected function generateFeedbackForm()
    {
        if (!$this->iso_docrobot_form) {
            return '';
        }

        $objForm = new \Haste\Form\Form('docrobot_comment', 'POST', function($objHaste) {
            return \Input::post('FORM_SUBMIT') === $objHaste->getFormId();
        });

        $objForm->addFieldsFromFormGenerator($this->iso_docrobot_form);

        if ($objForm->validate()) {
            $_SESSION['FORM_DATA'] = array();

            $objEmail = new \Email();
            $objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
            $objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
            $objEmail->subject = 'Neues Feedback auf isotopeecommerce.org';

            $strText = sprintf('Folgendes Feedback für die Version %s und Route "%s" ist eingegangen:' . "\n\n",
                $this->context->getVersion(),
                $this->routing->getCurrentRoute()->getName()
            );

            foreach ($objForm->getFormFields() as $strField => $arrDca) {
                if ($objForm->getWidget($strField) instanceof \uploadable) {
                    $arrFile  = $_SESSION['FILES'][$strField];

                    if ($arrFile['tmp_name']) {
                        $objEmail->attachFileFromString(file_get_contents($arrFile['tmp_name']), $arrFile['name'], $arrFile['type']);
                    }
                } else {
                    if ($value = $objForm->fetch($strField)) {
                        $strText .= $arrDca['label'] . ':' . "\n" . $value . "\n\n";

                        if ($strField == 'email') {
                            $objEmail->replyTo($value);
                        }
                    }
                }

                $_SESSION['FORM_DATA'][$strField] = $value;
            }

            $objEmail->text = $strText;
            $objEmail->sendTo($GLOBALS['TL_ADMIN_EMAIL']);
            $_SESSION['DOCROBOT_FORM_MESSAGE'] = $GLOBALS['TL_LANG']['ISOTOPE_DOCROBOT']['feedbackFormMessageSuccess'];
            $_SESSION['DOCROBOT_FORM_MESSAGETYPE'] = 'success';
        } elseif ($objForm->isSubmitted()) {
            $_SESSION['DOCROBOT_FORM_MESSAGE'] = $GLOBALS['TL_LANG']['ISOTOPE_DOCROBOT']['feedbackFormMessageError'];
            $_SESSION['DOCROBOT_FORM_MESSAGETYPE'] = 'error';
        } else {
            $_SESSION['DOCROBOT_FORM_MESSAGE']  = '';
            $_SESSION['DOCROBOT_FORM_MESSAGETYPE']  = '';
        }

        $this->Template->feedbackFormMessage = $_SESSION['DOCROBOT_FORM_MESSAGE'];
        $this->Template->feedbackFormMessageType = $_SESSION['DOCROBOT_FORM_MESSAGETYPE'];

        return $objForm->generate();
    }
}