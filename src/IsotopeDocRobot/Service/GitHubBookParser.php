<?php


namespace IsotopeDocRobot\Service;

use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Markdown\ContextAwareInterface;
use IsotopeDocRobot\Markdown\ParserInterface;
use IsotopeDocRobot\Markdown\RoutingAwareInterface;
use IsotopeDocRobot\Routing\Route;
use IsotopeDocRobot\Routing\Routing;
use Ciconia\Ciconia;

class GitHubBookParser
{
    private $parsers = array();
    private $registeredParsers = array();
    private $context = null;
    private $routing = null;
    private $languageBackup = '';

    public function __construct(Context $context, Routing $routing)
    {
        $this->context = $context;
        $this->routing = $routing;
        $this->languageBackup = $GLOBALS['TL_LANGUAGE'];

        // One day when I find time I might think about using a DIC
        $this->parsers = $GLOBALS['ISOTOPE_DOCROBOT']['parsers'];

        $this->registerParsers();
    }

    /**
     * @return \IsotopeDocRobot\Routing\Routing|null
     */
    public function getRouting()
    {
        return $this->routing;
    }

    /**
     * @return \IsotopeDocRobot\Context\Context|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function parseAllRoutes()
    {
        $data = '';
        foreach ($this->routing->getRoutes() as $route) {
            $this->parseRoute($route);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function parseRoute(Route $route)
    {
        return $this->parse($route->getContent($this->context));
    }

    /**
     * Used by a parser to register an event
     * @param ParserInterface
     * @param event name
     * @param callback method name
     */
    public function register(ParserInterface $parser, $event, $callback)
    {
        $this->registeredParsers[$event][] = array($parser, $callback);
    }

    /**
     * @return string
     */
    private function parse($data)
    {
        // parse in the correct language of Contao
        \System::loadLanguageFile('default', $this->context->getLanguage());

        // before parsers
        foreach ((array) $this->registeredParsers['before'] as $parserArray) {
            $data = call_user_func_array(array($parserArray[0], $parserArray[1]), array($data));
        }

        // main markdown parsing
        $ciconia = new Ciconia();
        $data = $ciconia->render($data);

        // after parsers
        foreach ((array) $this->registeredParsers['after'] as $parserArray) {
            $data = call_user_func_array(array($parserArray[0], $parserArray[1]), array($data));
        }

        // reset language
        \System::loadLanguageFile('default', $this->languageBackup);

        return $data;
    }

    /**
     * Registers parsers
     */
    private function registerParsers()
    {
        $i = 0;
        foreach ($this->parsers as $parser) {
            if (!$parser instanceof ParserInterface) {
                throw new \InvalidArgumentException('Every parser must implement the ParserInterface');
            }

            // set context
            if ($parser instanceof ContextAwareInterface) {
                $parser->setContext($this->context);
            }

            // set routing
            if ($parser instanceof RoutingAwareInterface) {
                $parser->setRouting($this->routing);
            }

            $parser->register($this);
        }
    }
}