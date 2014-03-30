<?php


namespace IsotopeDocRobot\Service;

use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Routing\Route;
use IsotopeDocRobot\Routing\Routing;

class GitHubBookParser
{
    private $parserCollection = null;
    private $context = null;
    private $languageBackup = '';

    public function __construct(Context $context, $parserCollection)
    {
        $this->context = $context;
        $this->parserCollection = $parserCollection;
        $this->languageBackup = $GLOBALS['TL_LANGUAGE'];
    }

    /**
     * @return string
     */
    public function parseAllRoutes(Routing $routing)
    {
        $data = '';
        foreach ($routing->getRoutes() as $route) {
            $this->parseRoute($route);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function parseRoute(Route $route)
    {
        return $this->parse(
            $route->getMirrorContent(
                $this->context->getVersion(),
                $this->context->getBook(),
                $this->context->getLanguage()
            )
        );
    }

    /**
     * @return string
     */
    private function parse($data)
    {
        // parse in the correct language of Contao
        \System::loadLanguageFile('default', $this->context->getLanguage());

        // parsers
        foreach ($this->parserCollection->getParsers() as $parser) {
            $data = $parser->parseMarkdown($data);
        }

        // reset language
        \System::loadLanguageFile('default', $this->languageBackup);

        return $data;
    }
}